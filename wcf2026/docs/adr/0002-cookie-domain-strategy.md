# 2. Cookie / domain strategy

Date: 2026-05-27
Status: Proposed (decision deferred to end of Phase 0)

## Context

Sanctum SPA cookie auth requires the SPA and the API to share a parent registrable domain so the session cookie is first-party with `SameSite=Lax`. Default platform hostnames (`*.pages.dev`, `*.fly.dev`) are different registrable domains.

Two viable strategies (full detail in spec §3.1.1):

- **Strategy A** — buy/own a single custom domain (≈ $10/yr) and assign `app.<domain>` to Cloudflare Pages and `api.<domain>` to Fly.io. Use `SameSite=Lax`. Strongest CSRF posture.
- **Strategy B** — stay on platform hostnames. Use `SameSite=None; Secure` plus explicit CORS allow-list. Fully free but weaker (third-party-cookie blocking can break sessions; CSRF defense leans entirely on the `X-XSRF-TOKEN` header).

## Decision

DEFERRED until the end of Phase 0 (specifically: revisited in Chunk 7 once both deploy workflows are wired and a domain choice can be made with full context). Phase 0 wires both options into config so the choice flips a single environment variable (`SESSION_SAME_SITE` and `SESSION_DOMAIN`). The chosen strategy will be documented here before the first production deploy.

## Consequences

- Local development uses `.lvh.me` (a public DNS wildcard that resolves any subdomain to `127.0.0.1`) so cookies behave realistically without `/etc/hosts` edits.
- Preview environments use `app.pr-{n}.<domain>` and `api.pr-{n}.<domain>` with `Domain=.pr-{n}.<domain>` — preview sessions are isolated from production by domain.
