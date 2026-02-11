// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
(function () {
  'use strict';

  // State
  const $ = (q) => document.querySelector(q);
  const cfg = {
    apiBase: localStorage.getItem('tag.apiBase') || '/',
    tenant: localStorage.getItem('tag.tenant') || '',
    secret: localStorage.getItem('tag.secret') || '',
  };

  // UI init
  $('#apiBase').value = cfg.apiBase;
  $('#tenant').value = cfg.tenant;
  $('#secret').value = cfg.secret;

  // Tabs
  const tabs = ['search', 'assign', 'synonym'];

  function activate(name) {
    tabs.forEach(t => $('#tab-' + t).classList.remove('active'));
    $('#tab-' + name).classList.add('active');
  }

  activate('search');
  document.querySelectorAll('nav button').forEach(btn => {
    btn.addEventListener('click', () => activate(btn.dataset.tab));
  });

  // Save cfg
  $('#saveCfg').addEventListener('click', () => {
    cfg.apiBase = $('#apiBase').value.trim() || '/';
    cfg.tenant = $('#tenant').value.trim();
    cfg.secret = $('#secret').value;
    localStorage.setItem('tag.apiBase', cfg.apiBase);
    localStorage.setItem('tag.tenant', cfg.tenant);
    localStorage.setItem('tag.secret', cfg.secret);
  });

  // Shortcuts
  window.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      $('#q').focus();
    }
    if (e.ctrlKey && e.key === '1') {
      e.preventDefault();
      activate('search');
    }
    if (e.ctrlKey && e.key === '2') {
      e.preventDefault();
      activate('assign');
    }
    if (e.ctrlKey && e.key === '3') {
      e.preventDefault();
      activate('synonym');
    }
  });

  // Helpers
  function baseUrl(path) {
    const b = cfg.apiBase.endsWith('/') ? cfg.apiBase.slice(0, -1) : cfg.apiBase;
    return b + path;
  }

  async function hmacSign(method, path, body) {
    // SignatureV2 (assumed): ts \n nonce \n method \n path \n sha256(body)
    const enc = new TextEncoder();
    const ts = Math.floor(Date.now() / 1000).toString();
    const nonce = Math.random().toString(36).slice(2, 14);
    const bodyStr = body ? JSON.stringify(body) : '';
    const bodyHash = await crypto.subtle.digest('SHA-256', enc.encode(bodyStr));
    const bodyHex = [...new Uint8Array(bodyHash)].map(b => b.toString(16).padStart(2, '0')).join('');
    const payload = [ts, nonce, method.toUpperCase(), path, bodyHex].join('\n');
    const key = await crypto.subtle.importKey('raw', enc.encode(cfg.secret), {
      name: 'HMAC',
      hash: 'SHA-256'
    }, false, ['sign']);
    const sigBuf = await crypto.subtle.sign('HMAC', key, enc.encode(payload));
    const sigHex = [...new Uint8Array(sigBuf)].map(b => b.toString(16).padStart(2, '0')).join('');
    return {ts, nonce, sig: sigHex, bodyStr};
  }

  async function call(method, path, body) {
    const url = baseUrl(path);
    const {ts, nonce, sig, bodyStr} = await hmacSign(method, path, body);
    const headers = {
      'Content-Type': 'application/json',
      'X-Tenant-Id': cfg.tenant,
      'X-SR-Timestamp': ts,
      'X-SR-Nonce': nonce,
      'X-SR-Signature': sig,
    };
    const resp = await fetch(url, {
      method,
      headers,
      body: (method === 'GET' || method === 'DELETE') ? undefined : bodyStr
    });
    const text = await resp.text();
    let out = text;
    try {
      out = JSON.stringify(JSON.parse(text), null, 2);
    } catch {
    }
    return {status: resp.status, headers: resp.headers, text: out};
  }

  // Ping
  $('#ping').addEventListener('click', async () => {
    try {
      const r = await call('GET', '/tag/_status');
      $('#pingOut').textContent = 'HTTP ' + r.status;
      setTimeout(() => $('#pingOut').textContent = '', 3000);
    } catch (e) {
      $('#pingOut').textContent = 'ERR';
    }
  });

  // Search
  $('#btnSearch').addEventListener('click', async () => {
    const q = $('#q').value.trim();
    const r = await call('GET', '/tag/search?q=' + encodeURIComponent(q));
    $('#searchOut').textContent = 'HTTP ' + r.status + '\n' + r.text;
  });

  // Assignments
  $('#btnAssign').addEventListener('click', async () => {
    const eType = $('#entityType').value.trim();
    const eId = $('#entityId').value.trim();
    const tId = $('#tagId').value.trim();
    const r = await call('POST', `/tag/${encodeURIComponent(tId)}/assign`, {entityType: eType, entityId: eId});
    $('#assignOut').textContent = 'HTTP ' + r.status + '\n' + r.text;
  });
  $('#btnUnassign').addEventListener('click', async () => {
    const eType = $('#entityType').value.trim();
    const eId = $('#entityId').value.trim();
    const tId = $('#tagId').value.trim();
    const r = await call('POST', `/tag/${encodeURIComponent(tId)}/unassign`, {entityType: eType, entityId: eId});
    $('#assignOut').textContent = 'HTTP ' + r.status + '\n' + r.text;
  });
  $('#btnAssignBulk').addEventListener('click', async () => {
    const eType = $('#entityType').value.trim();
    const eId = $('#entityId').value.trim();
    const list = $('#tagIds').value.split(',').map(s => s.trim()).filter(Boolean);
    const r = await call('POST', `/tag/assign-bulk`, {entityType: eType, entityId: eId, tagIds: list});
    $('#assignOut').textContent = 'HTTP ' + r.status + '\n' + r.text;
  });
  $('#btnListAssignments').addEventListener('click', async () => {
    const eType = $('#entityType').value.trim();
    const eId = $('#entityId').value.trim();
    const r = await call('GET', `/tag/assignments?entityType=${encodeURIComponent(eType)}&entityId=${encodeURIComponent(eId)}`);
    $('#assignOut').textContent = 'HTTP ' + r.status + '\n' + r.text;
  });

  // Synonyms
  $('#btnSynList').addEventListener('click', async () => {
    const tId = $('#synTagId').value.trim();
    const r = await call('GET', `/tag/${encodeURIComponent(tId)}/synonym`);
    $('#synOut').textContent = 'HTTP ' + r.status + '\n' + r.text;
  });
  $('#btnSynAdd').addEventListener('click', async () => {
    const tId = $('#synTagId').value.trim();
    const lab = $('#synLabel').value.trim();
    const r = await call('POST', `/tag/${encodeURIComponent(tId)}/synonym`, {label: lab});
    $('#synOut').textContent = 'HTTP ' + r.status + '\n' + r.text;
  });
  $('#btnSynDel').addEventListener('click', async () => {
    const tId = $('#synTagId').value.trim();
    const lab = $('#synLabelDel').value.trim();
    const r = await call('DELETE', `/tag/${encodeURIComponent(tId)}/synonym`, {label: lab});
    $('#synOut').textContent = 'HTTP ' + r.status + '\n' + r.text;
  });

})();
