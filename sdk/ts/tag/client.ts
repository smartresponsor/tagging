/* version: 1.2.0 (was 1.1.0 */
/**
 * SmartResponsor Tag SDK (TypeScript, E11)
 */
export class TagClient {
  constructor(private baseUrl: string, private headers: Record<string, string> = {}) {
  }

  private async req(path: string, init?: RequestInit) {
    const r = await fetch(this.baseUrl.replace(/\/$/, '') + path, {
      headers: {'Content-Type': 'application/json', ...this.headers},
      ...init
    });
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return r.headers.get('content-type')?.includes('application/json') ? r.json() : r.text();
  }

  list(q = '', limit = 20, offset = 0) {
    return this.req(`/tag?query=${encodeURIComponent(q)}&limit=${limit}&offset=${offset}`);
  }

  create(label: string, slug?: string) {
    return this.req('/tag', {method: 'POST', body: JSON.stringify({label, slug})});
  }

  remove(id: string) {
    return this.req(`/tag/${id}`, {method: 'DELETE'});
  }

  assign(tagId: string, type: string, id: string) {
    return this.req('/tag/assign', {method: 'POST', body: JSON.stringify({tagId, assignedType: type, assignedId: id})});
  }

  facet(type: string, limit = 50) {
    return this.req(`/tag/facet?type=${encodeURIComponent(type)}&limit=${limit}`);
  }

  cloud(limit = 100) {
    return this.req(`/tag/cloud?limit=${limit}`);
  }

  putLabel(tagId: string, locale: string, label: string) {
    return this.req(`/tag/${tagId}/label`, {method: 'POST', body: JSON.stringify({locale, label})});
  }

  listLabels(tagId: string) {
    return this.req(`/tag/${tagId}/labels`);
  }

  classify(tagId: string, key: string, value: string) {
    return this.req(`/tag/${tagId}/classify`, {method: 'POST', body: JSON.stringify({key, value})});
  }

  replay(tagId: string) {
    return this.req(`/tag/${tagId}/replay`, {method: 'POST'});
  }

  putPolicy(body: Record<string, unknown>) {
    return this.req('/tag/policy', {method: 'PUT', body: JSON.stringify(body)});
  }

  auditPolicy() {
    return this.req('/tag/policy/report', {method: 'GET'});
  }

  putQuota(per_minute: number, max_tags_per_entity: number) {
    return this.req('/tag/quota', {method: 'PUT', body: JSON.stringify({per_minute, max_tags_per_entity})});
  }
}

merge(fromId
:
string, toTagId
:
string, moveAssignments = true, copySynonyms = true
)
{
  return this.req(`/tag/${fromId}/merge`, {
    method: 'POST',
    body: JSON.stringify({toTagId, moveAssignments, copySynonyms})
  });
}
split(id
:
string, newTags
:
{
  string, slug ? : string
}
[]
)
{
  return this.req(`/tag/${id}/split`, {method: 'POST', body: JSON.stringify({newTags})});
}
bulkImport(items
:
any[]
)
{
  return this.req(`/tag/bulk/import`, {method: 'POST', body: JSON.stringify({items})});
}
bulkJobStatus(jobId
:
string
)
{
  return this.req(`/tag/bulk/${jobId}`);
}
resolveRedirect(fromId
:
string
)
{
  return this.req(`/tag/redirect/${fromId}`);
}
