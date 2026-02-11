# Signing v2 (E21)

Headers:

- X-SR-Signature: hex HMAC-SHA256 of canonical string
- X-SR-Timestamp: unix seconds
- X-SR-Nonce: random string (uuid)

Canonical string:
method + '\n' + path + '\n' + timestamp + '\n' + nonce + '\n' + sha256(body)

Rotation:

- tag_security.yaml has hmac.current and hmac.previous.
- Both accepted; on rotation, move current→previous and set new current.

Replay:

- Nonce cache keeps seen nonces for ttl_seconds; reuse → 403 replay_detected.
