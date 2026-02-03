# Tag Role-gate (E23)
Roles:
  - tag.viewer  : read
  - tag.editor  : write
  - tag.owner   : admin

Headers:
  - X-Actor-Id : string
  - X-Roles    : CSV of roles (e.g., "tag.viewer,tag.editor")

Config:
  config/tag_rbac.yaml controls mappings and fallback_allow_all.
  Optional path_overrides let you force 'admin' op on specific prefixes.

Integration:
  - Place Authorize middleware early in the HTTP pipeline.
  - In prod, integrate with Role component to resolve roles by token/subject instead of X-Roles.
  - Keep fallback_allow_all=true for early bring-up; set to false when Role is connected.

Error:
  - 403 {"code":"forbidden","op":"write"} when role is insufficient.
