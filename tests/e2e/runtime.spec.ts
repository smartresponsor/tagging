import { expect, test } from '@playwright/test';

test('runtime status and discovery surface are reachable', async ({ request }) => {
  const status = await request.get('/tag/_status');
  expect(status.ok()).toBeTruthy();
  const statusPayload = await status.json();
  expect(statusPayload.ok).toBe(true);

  const surface = await request.get('/tag/_surface');
  expect(surface.ok()).toBeTruthy();

  const payload = await surface.json();
  expect(payload.ok).toBe(true);
  expect(payload.surface.discovery).toBe('/tag/_surface');
  expect(payload.surface.search).toBe('GET /tag/search');
});
