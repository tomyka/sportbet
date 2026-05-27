# Cloudflare Pages configuration for WCF2026 frontend

## Project settings

- **Project name:** `wcf2026-frontend`
- **Build command:** `cd frontend && pnpm install --frozen-lockfile && pnpm build`
- **Build output directory:** `frontend/dist`
- **Root directory:** `/` (repository root)

## Environment variables

### Production
- `VITE_API_BASE_URL=https://api.sportbet.lt`

### Preview
Preview environments (per-PR deployments) set `VITE_API_BASE_URL` dynamically in the `preview-env.yml` workflow to point to the corresponding Fly.io preview app.

## Custom domain

- **Production:** `app.sportbet.lt`
- Configured in Cloudflare Pages → Project Settings → Custom domains
- DNS managed in Cloudflare (CNAME proxied through Cloudflare CDN)

## Security headers and SPA routing

The `_headers` and `_redirects` files in this directory define:
- Security headers (CSP, HSTS, X-Frame-Options, etc.)
- SPA client-side routing (all requests → `/index.html`)

These files are copied into `frontend/public/` by the deploy workflows before build, ensuring they are included in the final `dist/` output that Cloudflare Pages serves.
