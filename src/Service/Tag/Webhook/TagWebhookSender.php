<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag\Webhook;

use App\Service\Tag\Metric\TagMetrics;

final class TagWebhookSender
{
    public function __construct(private array $cfg){}

    private function dir(): string { return (string)($this->cfg['spool_dir'] ?? 'report/webhook/spool'); }
    private function dlq(): string { return (string)($this->cfg['dlq_path'] ?? 'report/webhook/dlq.ndjson'); }
    private function header(): string { return (string)($this->cfg['signature_header'] ?? 'X-SR-Signature'); }
    private function retries(): int { return max(0, (int)($this->cfg['retries'] ?? 5)); }
    private function baseDelay(): int { return max(1, (int)($this->cfg['base_delay_ms'] ?? 200)); }
    private function maxDelay(): int { return max(1000, (int)($this->cfg['max_delay_ms'] ?? 10000)); }
    private function dirMode(): int { return (int)($this->cfg['dir_mode'] ?? 0700); }

    public function enqueue(string $url, string $secret, string $type, array $payload): void
    {
        $job = [
            'id' => bin2hex(random_bytes(8)),
            'ts' => gmdate('c'),
            'url'=> $url,
            'secret' => $secret,
            'type' => $type,
            'payload' => $payload,
            'attempt' => 0,
            'next_at' => microtime(true),
        ];
        $dir = $this->dir();
        if (!is_dir($dir)) @mkdir($dir, $this->dirMode(), true);
        $this->writeJsonFile($dir . '/' . $job['id'] . '.json', $job);
    }

    /** @return int processed count */
    public function runOnce(int $limit = 50): int
    {
        $dir = $this->dir();
        if (!is_dir($dir)) return 0;
        $files = glob($dir.'/*.json'); if (!$files) return 0;
        $now = microtime(true);
        $n = 0;
        foreach ($files as $f) {
            if ($n >= $limit) break;
            $fp = @fopen($f, 'c+');
            if (!is_resource($fp)) continue;
            if (!flock($fp, LOCK_EX | LOCK_NB)) {
                fclose($fp);
                continue;
            }
            $contents = stream_get_contents($fp);
            $j = json_decode((string)$contents, true);
            if (!is_array($j)) {
                @unlink($f);
                fclose($fp);
                continue;
            }
            if (($j['next_at'] ?? 0) > $now) {
                fclose($fp);
                continue; // backoff window not reached
            }

            $ok = $this->deliver($j);
            if ($ok) {
                @unlink($f);
                fclose($fp);
                $n++;
                continue;
            }
            $j['attempt'] = (int)($j['attempt'] ?? 0) + 1;
            if ($j['attempt'] > $this->retries()) {
                $this->toDlq($j);
                @unlink($f);
                fclose($fp);
                TagMetrics::inc('tag_webhook_dlq_total', 1.0, ['url'=>$j['url'],'type'=>$j['type']]);
            } else {
                $delay = min($this->maxDelay(), $this->baseDelay() * (1 << ($j['attempt']-1)));
                $j['next_at'] = microtime(true) + ($delay/1000.0);
                $this->rewriteLockedFile($fp, $j);
                TagMetrics::inc('tag_webhook_retried_total', 1.0, ['url'=>$j['url'],'type'=>$j['type']]);
            }
            fclose($fp);
            $n++;
        }
        return $n;
    }

    private function toDlq(array $j): void
    {
        $line = json_encode(['ts'=>gmdate('c'), 'job'=>$j], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $path = $this->dlq();
        $dir = dirname($path);
        if ($line === false) {
            return;
        }
        if (!is_dir($dir) && !mkdir($dir, $this->dirMode(), true) && !is_dir($dir)) {
            return;
        }
        file_put_contents($path, $line."\n", FILE_APPEND | LOCK_EX);
    }

    private function deliver(array $j): bool
    {
        $body = json_encode(['ts'=>gmdate('c'), 'type'=>$j['type'], 'payload'=>$j['payload']], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            TagMetrics::inc('tag_webhook_failed_total', 1.0, ['url'=>$j['url'],'type'=>$j['type']]);
            return false;
        }
        $ch = curl_init($j['url']);
        if ($ch === false) return false;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            $this->header().': '.hash_hmac('sha256', $body, (string)($j['secret'] ?? '')),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $timeoutMs = (int)($this->cfg['timeout_ms'] ?? 1000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeoutMs);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($code >= 200 && $code < 300) {
            TagMetrics::inc('tag_webhook_delivered_total', 1.0, ['url'=>$j['url'],'type'=>$j['type']]);
            return true;
        }
        TagMetrics::inc('tag_webhook_failed_total', 1.0, ['url'=>$j['url'],'type'=>$j['type']]);
        return false;
    }

    private function writeJsonFile(string $path, array $payload): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, $this->dirMode(), true) && !is_dir($dir)) {
            return;
        }
        $tmp = $path . '.' . bin2hex(random_bytes(8)) . '.tmp';
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return;
        }
        file_put_contents($tmp, $json, LOCK_EX);
        rename($tmp, $path);
    }

    private function rewriteLockedFile($fp, array $payload): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return;
        }
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $json);
        fflush($fp);
    }
}
