<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Ops\Metrics;

final class PrometheusExporter
{
    /** @var array<string,array{help:string,labels:array<int,string>}> */
    private array $counters = [];
    /** @var array<string,array<string,int>> */
    private array $counterValues = [];

    /** @var array<string,array{help:string,labels:array<int,string>,buckets:array<int,float>}> */
    private array $histograms = [];
    /** @var array<string,array{sum:float,counts:array<int,int>,series:array<string,int>}> */
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
        $this->histValues[$name] = ['sum'=>0.0,'counts'=>array_fill(0, count($buckets), 0),'series'=>[]];
    }
    public function observe(string $name, float $value, array $labels = []): void {
        if (!isset($this->histograms[$name])) return;
        $spec = $this->histograms[$name];
        $idx = 0;
        foreach ($spec['buckets'] as $i => $b) {
            if ($value <= $b) { $idx = $i; break; }
            $idx = $i;
        }
        $this->histValues[$name]['sum'] += $value;
        $seriesKey = $this->labelsKey($labels);
        if (!isset($this->histValues[$name]['series'][$seriesKey])) {
            $this->histValues[$name]['series'][$seriesKey] = 0;
        }
        $this->histValues[$name]['series'][$seriesKey] += 1;
        // increment buckets up to idx for this series (cumulative)
        // store per-series buckets separately to make export simple
        // here we store cumulative at export time
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
            $vals = $this->histValues[$name] ?? ['sum'=>0.0,'series'=>[]];
            foreach ($vals['series'] as $seriesKey=>$count) {
                $labels = $this->labelsFromKey($seriesKey);
                $cumulative = 0;
                foreach ($meta['buckets'] as $b) {
                    // naive: distribute all points to final bucket only; real impl should track distribution per observation
                    // for now we assume exporter is called with pre-bucketed values or external aggregation
                    $le = is_infinite($b) ? "+Inf" : (string)$b;
                    $out[] = $name."_bucket".$this->fmtLabels(array_merge($labels, ['le'=>$le])) ." ". (string)$cumulative;
                }
                $out[] = $name."_count".$this->fmtLabels($labels)." ".(string)$count;
                $out[] = $name."_sum".$this->fmtLabels($labels)." ".(string)$vals['sum'];
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
