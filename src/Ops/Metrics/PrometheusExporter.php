<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Ops\Metrics;

final class PrometheusExporter
{
    /** @var array<string,array{help:string,labels:array<int,string>}> */
    private array $counters = [];
    /** @var array<string,array<string,int>> */
    private array $counterValues = [];

    /** @var array<string,array{help:string,labels:array<int,string>,buckets:array<int,float>}> */
    private array $histograms = [];
    /** @var array<string,array{series:array<string,array{count:int,sum:float,buckets:array<int,int>}>}> */
    private array $histValues = [];

    public function counter(string $name, string $help, array $labels = []): void {
        $this->counters[$name] = ['help'=>$help,'labels'=>$labels];
    }
    public function inc(string $name, array $labels = [], int $n = 1): void {
        $key = $this->labelsKey($labels);
        if (!isset($this->counterValues[$name])) $this->counterValues[$name] = [];
        if (!isset($this->counterValues[$name][$key])) $this->counterValues[$name][$key] = 0;
        $this->counterValues[$name][$key] += $n;
    }

    public function histogram(string $name, string $help, array $buckets, array $labels = []): void {
        sort($buckets, SORT_NUMERIC);
        $this->histograms[$name] = ['help'=>$help,'labels'=>$labels,'buckets'=>$buckets];
        $this->histValues[$name] = ['series'=>[]];
    }
    public function observe(string $name, float $value, array $labels = []): void {
        if (!isset($this->histograms[$name])) return;
        $spec = $this->histograms[$name];
        $seriesKey = $this->labelsKey($labels);
        if (!isset($this->histValues[$name]['series'][$seriesKey])) {
            $this->histValues[$name]['series'][$seriesKey] = [
                'count'=>0,
                'sum'=>0.0,
                'buckets'=>array_fill(0, count($spec['buckets']), 0),
            ];
        }
        $series = &$this->histValues[$name]['series'][$seriesKey];
        $series['count'] += 1;
        $series['sum'] += $value;
        if (!$spec['buckets']) return;
        $idx = count($spec['buckets']) - 1;
        foreach ($spec['buckets'] as $i => $b) {
            if ($value <= $b) { $idx = $i; break; }
        }
        $series['buckets'][$idx] += 1;
    }

    private function labelsKey(array $labels): string {
        if (!$labels) return '__';
        ksort($labels);
        $pairs = [];
        foreach ($labels as $k=>$v) $pairs[] = $k.'='+$v;
        return implode(',', $pairs);
    }

    public function renderText(): string {
        $out = [];
        foreach ($this->counters as $name=>$meta) {
            $out[] = "# HELP $name ".$this->esc($meta['help']);
            $out[] = "# TYPE $name counter";
            $vals = $this->counterValues[$name] ?? [];
            foreach ($vals as $key=>$val) {
                $labels = $this->labelsFromKey($key);
                $out[] = $name.$this->fmtLabels($labels).' '.(string)$val;
            }
        }
        foreach ($this->histograms as $name=>$meta) {
            $out[] = "# HELP $name ".$this->esc($meta['help']);
            $out[] = "# TYPE $name histogram";
            $vals = $this->histValues[$name] ?? ['series'=>[]];
            foreach ($vals['series'] as $seriesKey=>$series) {
                $labels = $this->labelsFromKey($seriesKey);
                $cumulative = 0;
                foreach ($meta['buckets'] as $i => $b) {
                    $cumulative += $series['buckets'][$i] ?? 0;
                    $le = is_infinite($b) ? "+Inf" : (string)$b;
                    $out[] = $name."_bucket".$this->fmtLabels(array_merge($labels, ['le'=>$le])) ." ". (string)$cumulative;
                }
                $out[] = $name."_count".$this->fmtLabels($labels)." ".(string)$series['count'];
                $out[] = $name."_sum".$this->fmtLabels($labels)." ".(string)$series['sum'];
            }
        }
        return implode("\n", $out)."\n";
    }

    private function esc(string $s): string { return str_replace(['\n','\"'], ['\\n','"'], $s); }
    private function labelsFromKey(string $key): array {
        if ($key === '__') return [];
        $out = [];
        foreach (explode(',', $key) as $pair) {
            [$k,$v] = explode('=', $pair, 2);
            $out[$k] = $v;
        }
        return $out;
    }
    private function fmtLabels(array $labels): string {
        if (!$labels) return '';
        $pairs = [];
        foreach ($labels as $k=>$v) $pairs[] = $k.'="'.str_replace('"','\"',$v).'"';
        return '{'.implode(',', $pairs).'}';
    }
}
