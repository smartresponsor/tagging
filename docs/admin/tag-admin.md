# Tag Admin Shell

Files:
- `admin/index.html`
- `admin/app.js`
- `admin/style.css`

Expected backend surface:
- `GET /tag/_status`
- `GET /tag/_surface`
- `POST /tag`
- `POST /tag/{id}/assign`
- `POST /tag/{id}/unassign`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`

The shell is intentionally static. It is a demo/discovery helper, not an authoritative admin console.
