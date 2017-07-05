# Overview

I would like a tool that can securely proxy and broker requests between a user and an upstream web resource (such as an API / website);

# Expectations

- User input and persistence of proxied services (ie, I can add and save an endpoint that I'd like to proxy)
- Provide for a pluggable middleware = req/res processor that can manipulate requests being forwarded to upstream and responses coming downstream
- Implement two such processors: 1) enforces an authentication policy. 2) does some header manipulation
- Should be capable of proxying interactive content with full integrity
- Should be capable of handling HTTP 3XX

# Assumptions

- Need not support websockets
