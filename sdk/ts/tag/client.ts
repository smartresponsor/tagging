// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

export type TagBody = {
  name?: string;
  slug?: string;
  locale?: string;
  weight?: number;
};

export type AssignBody = {
  entityType?: string;
  entityId?: string;
  entity_type?: string;
  entity_id?: string;
};

export type BulkAssignmentsBody = {
  operations: Array<{
    op: 'assign' | 'unassign';
    tagId: string;
    entityType: string;
    entityId: string;
    idem?: string;
  }>;
};

export type BulkToEntityBody = {
  entityType: string;
  entityId: string;
  tagIds: string[];
};

export class TagClient {
  constructor(private readonly baseUrl: string, private readonly headers: Record<string, string> = {}) {}

  private async req(path: string, init?: RequestInit): Promise<unknown> {
    const response = await fetch(this.baseUrl.replace(/\/$/, '') + path, {
      headers: { 'Content-Type': 'application/json', ...this.headers },
      ...init,
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const contentType = response.headers.get('content-type') ?? '';
    if (!contentType.includes('application/json')) {
      return response.text();
    }

    return response.json();
  }

  status(): Promise<unknown> {
    return this.req('/tag/_status');
  }

  surface(): Promise<unknown> {
    return this.req('/tag/_surface');
  }

  create(body: TagBody): Promise<unknown> {
    return this.req('/tag', { method: 'POST', body: JSON.stringify(body) });
  }

  get(id: string): Promise<unknown> {
    return this.req(`/tag/${encodeURIComponent(id)}`);
  }

  patch(id: string, body: TagBody): Promise<unknown> {
    return this.req(`/tag/${encodeURIComponent(id)}`, { method: 'PATCH', body: JSON.stringify(body) });
  }

  delete(id: string): Promise<unknown> {
    return this.req(`/tag/${encodeURIComponent(id)}`, { method: 'DELETE' });
  }

  assign(id: string, body: AssignBody): Promise<unknown> {
    return this.req(`/tag/${encodeURIComponent(id)}/assign`, { method: 'POST', body: JSON.stringify(body) });
  }

  unassign(id: string, body: AssignBody): Promise<unknown> {
    return this.req(`/tag/${encodeURIComponent(id)}/unassign`, { method: 'POST', body: JSON.stringify(body) });
  }

  assignments(entityType: string, entityId: string): Promise<unknown> {
    return this.req(`/tag/assignments?entityType=${encodeURIComponent(entityType)}&entityId=${encodeURIComponent(entityId)}`);
  }

  bulkAssignments(body: BulkAssignmentsBody): Promise<unknown> {
    return this.req('/tag/assignments/bulk', { method: 'POST', body: JSON.stringify(body) });
  }

  assignBulkToEntity(body: BulkToEntityBody): Promise<unknown> {
    return this.req('/tag/assignments/bulk-to-entity', { method: 'POST', body: JSON.stringify(body) });
  }

  search(q: string, pageSize = 20, pageToken?: string): Promise<unknown> {
    const params = new URLSearchParams({ q, pageSize: String(Math.max(1, Math.min(100, pageSize))) });
    if (pageToken) {
      params.set('pageToken', pageToken);
    }

    return this.req(`/tag/search?${params.toString()}`);
  }

  suggest(q: string, limit = 10): Promise<unknown> {
    const params = new URLSearchParams({ q, limit: String(Math.max(1, Math.min(50, limit))) });
    return this.req(`/tag/suggest?${params.toString()}`);
  }
}
