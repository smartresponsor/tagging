(function(){
  const base = window.__TAG_BASE__ || '';
  const tenant = window.__TENANT__ || 'demo';

  function h(m){ return {'X-Tenant-Id': tenant}; }

  const qNode = document.querySelector('#search-results');
  if (qNode){
    const q = qNode.getAttribute('data-q') || '';
    if (q){
      fetch(base + '/tag/search?q=' + encodeURIComponent(q), {headers: h('GET')})
        .then(r=>r.json()).then(d=>{
          qNode.textContent = JSON.stringify(d, null, 2);
        }).catch(e=>{ qNode.textContent = 'Error: '+e; });
    }
  }

  const card = document.querySelector('#tag-card');
  if (card){
    const id = card.getAttribute('data-id');
    fetch(base + '/tag/' + encodeURIComponent(id), {headers: h('GET')})
      .then(r=>r.json()).then(d=>{ card.textContent = JSON.stringify(d, null, 2); })
      .catch(e=>{ card.textContent = 'Error: '+e; });
  }

  const purge = document.querySelector('#purge-btn');
  if (purge){
    purge.addEventListener('click', function(){
      const id = purge.getAttribute('data-id');
      fetch(base + '/tag/_purge', {method:'POST', headers: Object.assign({'Content-Type':'application/json'}, h('POST')),
        body: JSON.stringify({action:'tag_ids', tag_ids:[id]})})
        .then(r=>r.json()).then(d=>{ alert('Purged: ' + JSON.stringify(d)); })
        .catch(e=> alert('Error: ' + e));
    });
  }

  const assignForm = document.querySelector('#assign-form');
  if (assignForm){
    assignForm.addEventListener('submit', function(ev){
      ev.preventDefault();
      const id = assignForm.getAttribute('data-id');
      const fd = new FormData(assignForm);
      const payload = {entity_type: fd.get('entity_type'), entity_id: fd.get('entity_id')};
      const idem = 'idem-' + Date.now();
      fetch(base + '/tag/' + encodeURIComponent(id) + '/assign', {
        method:'POST',
        headers: Object.assign({'Content-Type':'application/json','X-Idempotency-Key': idem}, h('POST')),
        body: JSON.stringify(payload)
      }).then(r=>r.json()).then(d=>{
        document.querySelector('#assign-result').textContent = JSON.stringify(d, null, 2);
      }).catch(e=>{ document.querySelector('#assign-result').textContent = 'Error: '+e; });
    });
  }

  // Metrics placeholder: if A2 exporter exists under /tag/_metrics.json
  const m = document.querySelector('#metrics');
  if (m){
    function tick(){
      fetch(base + '/tag/_metrics.json', {headers: h('GET')})
        .then(r=>r.json()).then(d=>{
          m.textContent = 'RPS:'+ (d.rps||'?') + ' p95:' + (d.p95||'?') + ' err:'+ (d.error_rate||'?');
        }).catch(()=>{ m.textContent = 'metrics: n/a'; });
    }
    tick(); setInterval(tick, 10000);
  }
})();