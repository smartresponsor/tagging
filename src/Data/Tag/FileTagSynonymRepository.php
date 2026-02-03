<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Data\Tag;

final class FileTagSynonymRepository
{
    public function __construct(private string $path='report/tag/synonym.ndjson')
    {
        $dir = \dirname($this->path);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        if (!file_exists($this->path)) file_put_contents($this->path, '');
    }

    /** @return array<int, array{tagId:string,label:string,createdAt:string}> */
    public function list(string $tagId): array
    {
        $out=[];
        $h=@fopen($this->path,'r');
        if ($h) {
            while (($line=fgets($h))!==false) {
                $j=json_decode(trim($line), true);
                if (!is_array($j)) continue;
                if (($j['tagId'] ?? '') === $tagId) $out[] = [
                    'tagId'=>(string)$j['tagId'],
                    'label'=>(string)$j['label'],
                    'createdAt'=>(string)$j['createdAt'],
                ];
            }
            fclose($h);
        }
        usort($out, fn($a,$b)=>strcmp($a['label'],$b['label']));
        return $out;
    }

    /** @return array<int, array{tagId:string,label:string,createdAt:string}> */
    public function listAll(): array
    {
        $map = $this->readAllMap();
        $out = [];
        foreach ($map as $j) {
            if (!is_array($j)) { continue; }
            $out[] = [
                'tagId' => (string)($j['tagId'] ?? ''),
                'label' => (string)($j['label'] ?? ''),
                'createdAt' => (string)($j['createdAt'] ?? ''),
            ];
        }
        usort($out, static function (array $a, array $b): int {
            $c = strcmp($a['tagId'], $b['tagId']);
            if ($c !== 0) { return $c; }
            return strcmp($a['label'], $b['label']);
        });
        return $out;
    }


    public function add(string $tagId, string $label): bool
    {
        $label = $this->norm($label);
        $all=$this->readAllMap();
        $k=$tagId.'|'.$label;
        if (isset($all[$k])) return false;
        $all[$k]=['tagId'=>$tagId,'label'=>$label,'createdAt'=>gmdate('c')];
        $this->writeAllMap($all);
        return true;
    }

    public function remove(string $tagId, string $label): bool
    {
        $label = $this->norm($label);
        $all=$this->readAllMap();
        $k=$tagId.'|'.$label;
        if (!isset($all[$k])) return false;
        unset($all[$k]);
        $this->writeAllMap($all);
        return true;
    }

    /** @return array<string,array> */
    private function readAllMap(): array
    {
        $map=[]; $h=@fopen($this->path,'r');
        if ($h){
            while(($line=fgets($h))!==false){
                $j=json_decode(trim($line), true);
                if (!is_array($j)) continue;
                $k=(string)$j['tagId'].'|'.(string)($j['label'] ?? '');
                $map[$k]=$j;
            }
            fclose($h);
        }
        return $map;
    }

    private function writeAllMap(array $map): void
    {
        $h=fopen($this->path,'w');
        foreach($map as $j){
            fwrite($h, json_encode($j, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n");
        }
        fclose($h);
    }

    private function norm(string $s): string
    {
        $s = trim(mb_strtolower($s));
        $s = preg_replace('/\s+/', ' ', $s);
        return $s ?? '';
    }
}
