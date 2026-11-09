---
status: accepted
---

# Freeze the greeting and error contracts, quirks included

External behaviour is frozen as measured on 2026-11-09. Golden files may change only with approval.

The greeting text was changed on purpose in commit `e21c4b7` ("hello php", 2026-11-09) and is now `Hello from PHP <version>`, where `<version>` is the full PHP version of the running runtime (for example `8.5.12`). The old wording, `Hello from PHP 7.4, Zend Framework 2`, is gone. The new greeting is the frozen contract. Its patch version varies by runtime, so golden files normalise the version and nothing else. This edit replaces the earlier decision that the greeting text was frozen; no plan relied on that decision yet.

## Kept on purpose

- A hello request to `/api/hello` (route name `api-hello`) accepts every HTTP method and returns the greeting: status 200, `Content-Type: text/plain; charset=utf-8`, and the body `Hello from PHP <version>`. Measured 2026-11-09 on PHP 7.4.33, 8.2.35, 8.4.27 and 8.5.12. Golden files normalise `<version>` with `\d+\.\d+\.\d+`.
- A query string does not change the reply, so `/api/hello?x=1` returns the greeting.
- An unknown request is `text/plain; charset=utf-8` with body `Not found` when the status is 404. Measured for `/api/hello/`, `/API/HELLO`, `/nope` (GET and POST).
- Any other dispatch failure uses the same content type and the body `Error`. A status below 400 is forced to 500. Any other status at or above 400 is preserved.
- No `Accept` negotiation, no authentication, no CORS, no application log contract, no console output, and no stored data.
- A request for a real file or directory under the document root is served by the web server and does not enter the application (`public/.htaccess`).
