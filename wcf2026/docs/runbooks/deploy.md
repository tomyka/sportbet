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

## Required GitHub secrets and variables

### Secrets

| Secret / Var               | Where to get it                         | Used in                       |
|----------------------------|-----------------------------------------|-------------------------------|
| `FLY_API_TOKEN`            | `flyctl auth token`                     | deploy-backend, preview-env   |
| `CLOUDFLARE_API_TOKEN`     | CF dashboard → My Profile → API Tokens  | deploy-frontend, preview-env  |
| `CLOUDFLARE_ACCOUNT_ID`    | CF dashboard sidebar                    | deploy-frontend, preview-env  |
| `NEON_PROJECT_ID`          | Neon dashboard → project settings       | preview-env                   |
| `NEON_API_KEY`             | Neon dashboard → API keys               | preview-env                   |
| `PREVIEW_APP_KEY`          | `php artisan key:generate --show`       | preview-env                   |

### Variables

| Variable                   | Value                                   | Used in                       |
|----------------------------|-----------------------------------------|-------------------------------|
| `PROD_API_BASE_URL`        | `https://api.sportbet.lt`               | deploy-frontend               |

### Production backend secrets (Fly.io)

Set once via `flyctl secrets set --app wcf2026-backend ...` (not GitHub secrets):

| Fly secret              | Value                                                              |
|-------------------------|--------------------------------------------------------------------|
| `APP_KEY`               | `php artisan key:generate --show`                                  |
| `APP_URL`               | `https://api.sportbet.lt`                                          |
| `DB_CONNECTION`         | `pgsql`                                                            |
| `DB_URL`                | Production Neon connection string (use the **pooled** endpoint)    |
| `FRONTEND_URL`          | `https://app.sportbet.lt`                                          |
| `SANCTUM_STATEFUL_DOMAINS` | `app.sportbet.lt`                                               |
| `SESSION_DOMAIN`        | `.sportbet.lt`                                                     |
| `SESSION_SECURE_COOKIE` | `true`                                                             |
| `SESSION_SAME_SITE`     | `lax`                                                              |

## One-time provisioning checklist

1. **Register domain `sportbet.lt`** and point nameservers to Cloudflare.

2. **Cloudflare Pages:** Create project `wcf2026-frontend`.
   - Attach custom domain `app.sportbet.lt`.
   - Set production environment variable: `VITE_API_BASE_URL=https://api.sportbet.lt`.

3. **Fly.io:** Create app and attach custom domain.
   ```bash
   flyctl apps create wcf2026-backend --org personal
   flyctl certs add api.sportbet.lt --app wcf2026-backend
   ```
   - Add CNAME in Cloudflare DNS: `api.sportbet.lt → wcf2026-backend.fly.dev` (proxy OFF).

4. **Neon:** Create project.
   - Copy `NEON_PROJECT_ID` and `NEON_API_KEY` (read-write scope) into GitHub Actions secrets.

5. **Fly secrets:** Set production backend secrets per table above.
   ```bash
   flyctl secrets set --app wcf2026-backend \
     APP_KEY=... \
     APP_URL=https://api.sportbet.lt \
     DB_CONNECTION=pgsql \
     DB_URL=... \
     FRONTEND_URL=https://app.sportbet.lt \
     SANCTUM_STATEFUL_DOMAINS=app.sportbet.lt \
     SESSION_DOMAIN=.sportbet.lt \
     SESSION_SECURE_COOKIE=true \
     SESSION_SAME_SITE=lax
   ```

6. **GitHub Actions:** Add the 6 secrets and 1 variable per tables above to repository settings → Secrets and variables → Actions.
