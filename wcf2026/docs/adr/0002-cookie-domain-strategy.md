# 2. Cookie / domain strategy

Date: 2026-05-27
Status: Accepted

## Context

Sanctum SPA cookie auth requires the SPA and the API to share a parent registrable domain so the session cookie is first-party with `SameSite=Lax`. Default platform hostnames (`*.pages.dev`, `*.fly.dev`) are different registrable domains.

Two viable strategies (full detail in spec §3.1.1):

- **Strategy A** — buy/own a single custom domain (≈ $10/yr) and assign `app.<domain>` to Cloudflare Pages and `api.<domain>` to Fly.io. Use `SameSite=Lax`. Strongest CSRF posture.
- **Strategy B** — stay on platform hostnames. Use `SameSite=None; Secure` plus explicit CORS allow-list. Fully free but weaker (third-party-cookie blocking can break sessions; CSRF defense leans entirely on the `X-XSRF-TOKEN` header).

## Decision

**Strategy A** chosen with domain **`sportbet.lt`**.

Production configuration:
- SPA: `https://app.sportbet.lt` (Cloudflare Pages with custom domain)
- API: `https://api.sportbet.lt` (Fly.io with custom domain via TLS certificate)
- `SESSION_DOMAIN=.sportbet.lt`
- `SESSION_SAME_SITE=lax`
- `SESSION_SECURE_COOKIE=true`
- `SANCTUM_STATEFUL_DOMAINS=app.sportbet.lt`

## Consequences

- **Cost:** ~$10/yr for domain registration.
- **DNS setup:** One-time configuration (nameservers to Cloudflare, CNAME for API with proxy OFF).
- **Cookie behavior:** First-party cookies, `SameSite=Lax` — immune to third-party cookie deprecation, strongest CSRF protection.
- **Local development:** Uses `.lvh.me` (public DNS wildcard resolving to `127.0.0.1`) so cookies behave realistically without `/etc/hosts` edits.
- **Preview environments:** Use free platform hostnames (`*.pages.dev`, `*.fly.dev`) with `SameSite=None; Secure` since they lack a shared domain. Preview sessions are functionally isolated from production.
