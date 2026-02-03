/* version: 1.2.0 (was 1.1.0 */
<?php
declare(strict_types=1);
namespace SR\SDK\Tag;

/**
 * SmartResponsor Tag SDK (PHP, E11)
 */
final class Client {
  public function __construct(private string $baseUrl, private array $headers = []){}
  private function req(string $path, string $method='GET', ?array $body=null): array {
    $ch = curl_init(rtrim($this->baseUrl,'/') . $path);
    $hdrs = array_merge(['Content-Type: application/json'], array_map(fn($k,$v)=>"$k: $v", array_keys($this->headers), $this->headers));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_CUSTOMREQUEST=>$method, CURLOPT_HTTPHEADER=>$hdrs]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($res === false || $code >= 400) throw new \RuntimeException('HTTP ' . $code . ' ' . curl_error($ch));
    return json_decode($res, true) ?? [];
  }
  public function list(string $q='', int $limit=20, int $offset=0): array { return $this->req("/tag?query=".urlencode($q)."&limit=$limit&offset=$offset"); }
  public function create(string $label, ?string $slug=null): array { return $this->req("/tag","POST",['label'=>$label,'slug'=>$slug]); }
  public function remove(string $id): array { return $this->req("/tag/$id","DELETE"); }
  public function assign(string $tagId, string $type, string $id): array { return $this->req("/tag/assign","POST",['tagId'=>$tagId,'assignedType'=>$type,'assignedId'=>$id]); }
  public function facet(string $type,int $limit=50): array { return $this->req("/tag/facet?type=".urlencode($type)."&limit=$limit"); }
  public function cloud(int $limit=100): array { return $this->req("/tag/cloud?limit=$limit"); }
  public function putLabel(string $tagId,string $locale,string $label): array { return $this->req("/tag/$tagId/label","POST",['locale'=>$locale,'label'=>$label]); }
  public function listLabels(string $tagId): array { return $this->req("/tag/$tagId/labels"); }
  public function classify(string $tagId,string $key,string $value): array { return $this->req("/tag/$tagId/classify","POST",['key'=>$key,'value'=>$value]); }
  public function replay(string $tagId): array { return $this->req("/tag/$tagId/replay","POST"); }
  public function putPolicy(array $body): array { return $this->req("/tag/policy","PUT",$body); }
  public function auditPolicy(): array { return $this->req("/tag/policy/report"); }
  public function putQuota(int $per_minute, int $max_tags_per_entity): array { return $this->req("/tag/quota","PUT",['per_minute'=>$per_minute,'max_tags_per_entity'=>$max_tags_per_entity]); }
}

public function merge(string $fromId, string $toTagId, bool $moveAssignments=true, bool $copySynonyms=true): array {
  return $this->req("/tag/$fromId/merge","POST",[
    'toTagId'=>$toTagId,'moveAssignments'=>$moveAssignments,'copySynonyms'=>$copySynonyms]);
}
public function split(string $id, array $newTags): array {
  return $this->req("/tag/$id/split","POST",['newTags'=>$newTags]);
}
public function bulkImport(array $items): array {
  return $this->req("/tag/bulk/import","POST",['items'=>$items]);
}
public function bulkJobStatus(string $jobId): array { return $this->req("/tag/bulk/$jobId"); }
public function resolveRedirect(string $fromId): array { return $this->req("/tag/redirect/$fromId"); }
