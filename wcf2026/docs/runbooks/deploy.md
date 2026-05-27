# Deploy runbook

> Maintained per phase. Phase 0 entries only — extend as features ship.

## Production

### Backend (Fly.io)
1. Push to `main` triggers `.github/workflows/deploy-backend.yml`.
2. CI builds the Docker image, pushes to Fly's registry, and runs `flyctl deploy --strategy rolling`.
3. Release command in `fly.toml` runs `php artisan migrate --force` before the new instance accepts traffic.
4. Health check `GET /api/v1/health` must return 200 within 60s or Fly auto-rolls back.

### Frontend (Cloudflare Pages)
1. Push to `main` triggers `.github/workflows/deploy-frontend.yml`.
2. CI runs `pnpm build` and publishes `frontend/dist` via Wrangler.
3. `_headers` and `_redirects` in `infra/cloudflare-pages/` are copied into `dist` before publish.

### Database (Neon)
- Schema changes ship via Laravel migrations executed by the backend's release command.
- Point-in-time recovery is enabled by default on Neon free tier (7 days).
- Manual rollback: `php artisan migrate:rollback --step=N` via `flyctl ssh console`.

## Preview environments

Each PR gets:
- A Neon database branch named `pr-{n}` (created by `.github/workflows/preview-env.yml`).
- A Fly preview app `wcf2026-api-pr-{n}`.
- A Cloudflare Pages preview deployment.
- Posted as a PR comment with both URLs.
- Torn down on PR close.

## Rollback

| Failure | Action |
|---|---|
| Backend deploy fails health check | Auto-rolled back by Fly. Inspect logs: `flyctl logs -a wcf2026-api`. |
| Backend deployed but broken | `flyctl releases list` → `flyctl deploy --image <previous>` |
| Frontend deployed but broken | Cloudflare Pages → Deployments → Rollback button |
| Bad migration | `flyctl ssh console -a wcf2026-api` → `php artisan migrate:rollback` |
