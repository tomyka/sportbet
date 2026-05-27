# WCF2026 — Sport Prediction Platform

Versatile multi-tournament prediction platform supporting football and basketball events (FIFA World Cup, UEFA Euro, EuroLeague regular season + playoffs, custom tournaments).

> Refactor of [`tomyka/el2025`](https://github.com/tomyka/el2025). See [`2026-05-27-wcf2026-refactor-design.md`](./2026-05-27-wcf2026-refactor-design.md) for the design spec.

## Architecture

- **Backend** — Laravel 11 API at `backend/` (PHP 8.3, Postgres, Sanctum SPA cookie auth)
- **Frontend** — React 18 + TypeScript SPA/PWA at `frontend/` (Vite, Tailwind, TanStack Query/Router)
- **Database** — Postgres 16 (Neon in production)
- **Hosting** — Cloudflare Pages (frontend), Fly.io (backend), Neon (DB)

## Local development

Prerequisites: Docker Desktop, Node 20, pnpm 9, PHP 8.3, Composer 2.

```bash
cd wcf2026
make up           # start postgres + backend in docker
make seed-demo    # seed a demo tournament
cd frontend && pnpm install && pnpm dev   # frontend at http://app.lvh.me:5173
```

Backend: http://api.lvh.me:8080  •  Frontend: http://app.lvh.me:5173  •  Mailpit: http://localhost:8025

## Common targets

| Command | Description |
|---|---|
| `make up` | Start backend + Postgres + Mailpit |
| `make down` | Stop containers |
| `make test` | Run backend + frontend tests |
| `make lint` | Run backend + frontend linters |
| `make e2e` | Run Playwright E2E suite |
| `make seed-demo` | Seed demo tournament |
| `make typegen` | Regenerate frontend types from OpenAPI |

## Documentation

- [Design spec](./2026-05-27-wcf2026-refactor-design.md)
- [Architecture decisions](./docs/adr/)
- [Runbooks](./docs/runbooks/)
- [API (OpenAPI)](./docs/api/openapi.yaml)

## Status

Phase 0 (bootstrap) — see `2026-05-27-wcf2026-phase-0-bootstrap-plan.md`.
