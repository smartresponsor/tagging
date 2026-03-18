# SmartResponsor Tag Admin

Static no-build shell for the minimal public-ready Tag surface.

What it does:
- load `GET /tag/_surface`
- search and suggest tags
- create one tag
- assign and unassign one tag to one entity
- list assignments for one entity

What it does not do:
- bulk operations
- public runtime surface management
- purge or metrics panels
- HMAC signing

Open `admin/index.html` in a browser and point API Base to a running `host-minimal` instance.
