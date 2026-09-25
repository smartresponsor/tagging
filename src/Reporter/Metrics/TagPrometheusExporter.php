<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Reporter\Metrics;

final class TagPrometheusExporter
{
    /** @var array<string,array{help:string,labels:array<int,string>}> */
    private array $counters = [];
    /** @var array<string,array<string,int>> */
    private array $counterValues = [];
    /** @var array<string,array{help:string,labels:array<int,string>,buckets:array<int,float>}> */
    private array $histograms = [];
    /** @var array<string,array{series:array<string,array{count:int,sum:float,buckets:array<int,int>}>}> */
    private array $histValues = [];

    public function counter(string $nameEntity, string $help, array $labels = []): void
    {
        $this->counters[$nameEntity] = ['help' => $help, 'labels' => $labels];
    }

    public function inc(string $nameEntity, array $labels = [], int $n = 1): void
    {
        $key = $this->labelsKey($labels);
        $this->counterValues[$nameEntity] ??= [];
        $this->counterValues[$nameEntity][$key] ??= 0;
        $this->counterValues[$nameEntity][$key] += $n;
    }

    public function histogram(string $nameEntity, string $help, array $buckets, array $labels = []): void
    {
        sort($buckets, SORT_NUMERIC);
        $this->histograms[$nameEntity] = ['help' => $help, 'labels' => $labels, 'buckets' => $buckets];
        $this->histValues[$nameEntity] = ['series' => []];
    }

    public function observe(string $nameEntity, float $value, array $labels = []): void
    {
        if (!isset($this->histograms[$nameEntity])) {
            return;
        }

        $spec = $this->histograms[$nameEntity];
        $seriesKey = $this->labelsKey($labels);
        if (!isset($this->histValues[$nameEntity]['series'][$seriesKey])) {
            $this->histValues[$nameEntity]['series'][$seriesKey] = [
                'count' => 0,
                'sum' => 0.0,
                'buckets' => array_fill(0, count($spec['buckets']), 0),
            ];
        }

        $series = &$this->histValues[$nameEntity]['series'][$seriesKey];
        ++$series['count'];
        $series['sum'] += $value;

        $idx = count($spec['buckets']) - 1;
        foreach ($spec['buckets'] as $i => $bucket) {
            if ($value <= $bucket) {
                $idx = $i;
                break;
            }
        }
        ++$series['buckets'][$idx];
    }

    private function labelsKey(array $labels): string
    {
        if ([] === $labels) {
            return '__';
        }
        ksort($labels);
        $pairs = [];
        foreach ($labels as $k => $v) {
            $pairs[] = $k . '=' . $v;
        }

        return implode(',', $pairs);
    }

    public function renderText(): string
    {
        $out = [];
        foreach ($this->counters as $nameEntity => $meta) {
            $out[] = '# HELP ' . $nameEntity . ' ' . $this->esc($meta['help']);
            $out[] = '# TYPE ' . $nameEntity . ' counter';
            foreach (($this->counterValues[$nameEntity] ?? []) as $key => $val) {
                $labels = $this->labelsFromKey((string) $key);
                $out[] = $nameEntity . $this->fmtLabels($labels) . ' ' . $val;
            }
        }
        foreach ($this->histograms as $nameEntity => $meta) {
            $out[] = '# HELP ' . $nameEntity . ' ' . $this->esc($meta['help']);
            $out[] = '# TYPE ' . $nameEntity . ' histogram';
            foreach (($this->histValues[$nameEntity]['series'] ?? []) as $seriesKey => $series) {
                $labels = $this->labelsFromKey((string) $seriesKey);
                $cumulative = 0;
                foreach ($meta['buckets'] as $i => $bucket) {
                    $cumulative += $series['buckets'][$i] ?? 0;
                    $le = is_infinite($bucket) ? '+Inf' : (string) $bucket;
                    $out[] = $nameEntity . '_bucket' . $this->fmtLabels(array_merge($labels, ['le' => $le])) . ' ' . $cumulative;
                }
                $out[] = $nameEntity . '_count' . $this->fmtLabels($labels) . ' ' . $series['count'];
                $out[] = $nameEntity . '_sum' . $this->fmtLabels($labels) . ' ' . $series['sum'];
            }
        }

        return implode("\n", $out) . "\n";
    }

    private function esc(string $s): string
    {
        return str_replace(['\n', '\"'], ['\\n', '"'], $s);
    }

    /**
     * @return array<string,string>
     */
    private function labelsFromKey(string $key): array
    {
        if ('__' === $key) {
            return [];
        }
        $out = [];
        foreach (explode(',', $key) as $pair) {
            [$k, $v] = explode('=', $pair, 2);
            $out[$k] = $v;
        }

        return $out;
    }

    private function fmtLabels(array $labels): string
    {
        if ([] === $labels) {
            return '';
        }
        $pairs = [];
        foreach ($labels as $k => $v) {
            $pairs[] = $k . '="' . str_replace('"', '\\"', (string) $v) . '"';
        }

        return '{' . implode(',', $pairs) . '}';
    }
}
