// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
(function () {
  'use strict';

  const $ = (q) => document.querySelector(q);
  const cfg = {
    apiBase: localStorage.getItem('tag.apiBase') || 'http://127.0.0.1:8080',
    tenant: localStorage.getItem('tag.tenant') || 'demo',
  };

  $('#apiBase').value = cfg.apiBase;
  $('#tenant').value = cfg.tenant;

  const tabs = ['tour', 'search', 'create', 'assign'];
  function activate(name) {
    tabs.forEach((tab) => {
      $('#tab-' + tab).style.display = tab === name ? 'block' : 'none';
    });
  }
  activate('tour');

  document.querySelectorAll('nav button').forEach((btn) => {
    btn.addEventListener('click', () => activate(btn.dataset.tab));
  });

  function show(targetId, result) {
    $(targetId).textContent = 'HTTP ' + result.status + '\n' + result.text;
    return result;
  }

  function currentBase() {
    return (cfg.apiBase || '').trim().replace(/\/$/, '');
  }

  $('#saveCfg').addEventListener('click', () => {
    cfg.apiBase = $('#apiBase').value.trim() || 'http://127.0.0.1:8080';
    cfg.tenant = $('#tenant').value.trim() || 'demo';
    localStorage.setItem('tag.apiBase', cfg.apiBase);
    localStorage.setItem('tag.tenant', cfg.tenant);
    $('#pingOut').textContent = 'saved';
    setTimeout(() => $('#pingOut').textContent = '', 1200);
  });

  async function call(method, path, body) {
    const url = currentBase() + path;
    const headers = {
      'X-Tenant-Id': cfg.tenant,
    };
    if (body !== undefined) {
      headers['Content-Type'] = 'application/json';
      headers['X-Idempotency-Key'] = 'ui-' + Date.now();
    }
    try {
      const response = await fetch(url, {
        method,
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
      });
      const text = await response.text();
      try {
        return { status: response.status, text: JSON.stringify(JSON.parse(text), null, 2) };
      } catch (_) {
        return { status: response.status, text };
      }
    } catch (error) {
      return { status: 0, text: String(error && error.message ? error.message : error) };
    }
  }

  $('#ping').addEventListener('click', async () => {
    const result = await call('GET', '/tag/_status');
    $('#pingOut').textContent = result.status === 200 ? 'ok' : ('HTTP ' + result.status);
    setTimeout(() => $('#pingOut').textContent = '', 2000);
  });

  $('#btnLoadStatus').addEventListener('click', async () => {
    show('#tourOut', await call('GET', '/tag/_status'));
  });

  $('#btnLoadSurface').addEventListener('click', async () => {
    show('#tourOut', await call('GET', '/tag/_surface'));
  });

  $('#btnUseDemo').addEventListener('click', () => {
    $('#tagId').value = '01K3TAGDEMO00000000000001';
    $('#entityType').value = 'product';
    $('#entityId').value = 'demo-product-1';
    $('#q').value = 'elect';
    show('#tourOut', { status: 200, text: JSON.stringify({ ok: true, primaryTagId: '01K3TAGDEMO00000000000001', entityType: 'product', entityId: 'demo-product-1', query: 'elect' }, null, 2) });
    activate('assign');
  });

  $('#btnSearch').addEventListener('click', async () => {
    const q = $('#q').value.trim();
    show('#searchOut', await call('GET', '/tag/search?q=' + encodeURIComponent(q) + '&pageSize=10'));
  });

  $('#btnSuggest').addEventListener('click', async () => {
    const q = $('#q').value.trim();
    show('#searchOut', await call('GET', '/tag/suggest?q=' + encodeURIComponent(q) + '&limit=10'));
  });

  $('#btnCreate').addEventListener('click', async () => {
    const name = $('#createName').value.trim();
    if (!name) {
      show('#createOut', { status: 0, text: 'name is required' });
      return;
    }
    const payload = {
      name,
      locale: $('#createLocale').value.trim() || 'en',
      weight: Number($('#createWeight').value || '0'),
    };
    const result = show('#createOut', await call('POST', '/tag', payload));
    try {
      const parsed = JSON.parse(result.text);
      const id = parsed && parsed.result && parsed.result.id ? String(parsed.result.id) : '';
      if (id) {
        $('#tagId').value = id;
        activate('assign');
      }
    } catch (_) {
      // ignore non-json responses in the static shell
    }
  });

  function assignmentPayload() {
    return {
      entity_type: $('#entityType').value.trim(),
      entity_id: $('#entityId').value.trim(),
    };
  }

  $('#btnAssign').addEventListener('click', async () => {
    const tagId = $('#tagId').value.trim();
    if (!tagId) {
      show('#assignOut', { status: 0, text: 'tagId is required' });
      return;
    }
    show('#assignOut', await call('POST', '/tag/' + encodeURIComponent(tagId) + '/assign', assignmentPayload()));
  });

  $('#btnUnassign').addEventListener('click', async () => {
    const tagId = $('#tagId').value.trim();
    if (!tagId) {
      show('#assignOut', { status: 0, text: 'tagId is required' });
      return;
    }
    show('#assignOut', await call('POST', '/tag/' + encodeURIComponent(tagId) + '/unassign', assignmentPayload()));
  });

  $('#btnListAssignments').addEventListener('click', async () => {
    const entityType = $('#entityType').value.trim();
    const entityId = $('#entityId').value.trim();
    show('#assignOut', await call('GET', '/tag/assignments?entityType=' + encodeURIComponent(entityType) + '&entityId=' + encodeURIComponent(entityId) + '&limit=10'));
  });
})();
