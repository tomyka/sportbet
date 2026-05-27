# WCF2026 Phase 0 — Bootstrap Implementation Plan

> **For agentic workers:** REQUIRED: Use `superpowers:subagent-driven-development` (if subagents available) or `superpowers:executing-plans` to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up the WCF2026 monorepo with a deployable Laravel API + React SPA skeleton, full CI/CD, and one end-to-end "hello world" round trip — before any feature code is written.

**Architecture:** Monorepo under `wcf2026/` containing `backend/` (Laravel 11 + PHP 8.3 + Postgres + Sanctum) and `frontend/` (React 18 + TypeScript + Vite + Tailwind v3 + TanStack Query). Backend deploys to Fly.io via Docker; frontend deploys to Cloudflare Pages; database is Neon Postgres. Seven GitHub Actions workflows handle lint, test, E2E, security, deploy-be, deploy-fe, preview-env.

**Tech Stack:** PHP 8.3, Laravel 11, Laravel Sanctum, Pest 3, PHPStan 1, Laravel Pint, Scramble, Postgres 16; Node 20, pnpm 9, React 18, TypeScript 5, Vite 5, Tailwind 3, TanStack Query 5, TanStack Router 1, Vitest 2, Playwright 1.48, MSW 2; Docker, GitHub Actions, Fly.io, Cloudflare Pages, Neon.

**Spec:** `wcf2026/2026-05-27-wcf2026-refactor-design.md`

**Working directory throughout:** `D:\Projects\sportbet\wcf2026\` (Windows). All `cd` instructions assume PowerShell from the repo root `D:\Projects\sportbet\`.

**Conventions:**
- Conventional Commits (`feat:`, `fix:`, `chore:`, `docs:`, `test:`, `ci:`, `build:`).
- Every commit includes `Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>`.
- TDD where applicable: write failing test → run → implement → run → commit.
- Commit cadence: at the end of each task, never mid-task.
- For tasks that are pure configuration (no test possible), verification is a documented command + expected output.

---

## Chunk 1: Repo skeleton & top-level tooling

### Task 1.1: Create the wcf2026 directory layout

**Files:**
- Create: `wcf2026/backend/.gitkeep`
- Create: `wcf2026/frontend/.gitkeep`
- Create: `wcf2026/docs/adr/.gitkeep`
- Create: `wcf2026/docs/runbooks/.gitkeep`
- Create: `wcf2026/docs/api/.gitkeep`
- Create: `wcf2026/infra/fly/.gitkeep`
- Create: `wcf2026/infra/cloudflare-pages/.gitkeep`

- [ ] **Step 1: Create directories**

Run in PowerShell from `D:\Projects\sportbet\`:
```powershell
$dirs = @(
  "wcf2026\backend",
  "wcf2026\frontend",
  "wcf2026\docs\adr",
  "wcf2026\docs\runbooks",
  "wcf2026\docs\api",
  "wcf2026\infra\fly",
  "wcf2026\infra\cloudflare-pages"
)
foreach ($d in $dirs) { New-Item -ItemType Directory -Force -Path $d | Out-Null; New-Item -ItemType File -Force -Path "$d\.gitkeep" | Out-Null }
```

- [ ] **Step 2: Verify**

Run: `Get-ChildItem -Recurse -Force wcf2026 | Where-Object { $_.Name -eq '.gitkeep' } | Select-Object FullName`
Expected: 7 paths printed.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026
git commit -m "chore(wcf2026): scaffold monorepo directory layout`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 1.2: Top-level `.editorconfig`

**Files:**
- Create: `wcf2026/.editorconfig`

- [ ] **Step 1: Write `.editorconfig`**

```ini
root = true

[*]
charset = utf-8
end_of_line = lf
indent_style = space
indent_size = 4
insert_final_newline = true
trim_trailing_whitespace = true

[*.{js,ts,jsx,tsx,json,yml,yaml,html,css,scss,md}]
indent_size = 2

[*.md]
trim_trailing_whitespace = false

[Makefile]
indent_style = tab
```

- [ ] **Step 2: Verify**

Run: `Get-Content wcf2026\.editorconfig | Select-Object -First 2`
Expected: First line is `root = true`.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\.editorconfig
git commit -m "chore(wcf2026): add .editorconfig`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 1.3: Top-level `.gitignore`

**Files:**
- Create: `wcf2026/.gitignore`

- [ ] **Step 1: Write `.gitignore`**

```gitignore
# OS
.DS_Store
Thumbs.db

# Editors
.idea/
.vscode/
*.swp
*.swo
*~

# Env files (real envs never committed; examples are)
.env
.env.*
!.env.example
!.env.example.*

# Logs & coverage
*.log
coverage/
.nyc_output/

# Build outputs
dist/
build/
*.tsbuildinfo

# Test artifacts
playwright-report/
test-results/
phpunit-report.xml
.phpunit.cache/

# Dependencies
node_modules/
vendor/

# PHP
storage/framework/cache/data/*
storage/framework/sessions/*
storage/framework/views/*
storage/logs/*
!storage/framework/cache/data/.gitkeep
!storage/framework/sessions/.gitkeep
!storage/framework/views/.gitkeep
!storage/logs/.gitkeep

# Generated OpenAPI artifact (regenerate per build)
docs/api/openapi.generated.yaml
```

- [ ] **Step 2: Verify**

Run: `Select-String -Path wcf2026\.gitignore -Pattern 'node_modules','vendor','docs/api/openapi.generated.yaml' | Measure-Object | Select-Object -ExpandProperty Count`
Expected: 3.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\.gitignore
git commit -m "chore(wcf2026): add top-level .gitignore`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 1.4: Top-level `README.md`

**Files:**
- Create: `wcf2026/README.md`

- [ ] **Step 1: Write `README.md`**

```markdown
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
```

- [ ] **Step 2: Verify**

Run: `Get-Content wcf2026\README.md | Select-String "WCF2026"`
Expected: matches "WCF2026 — Sport Prediction Platform".

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\README.md
git commit -m "docs(wcf2026): add top-level README`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 1.5: Top-level `Makefile`

**Files:**
- Create: `wcf2026/Makefile`

- [ ] **Step 1: Write `Makefile`**

```makefile
.PHONY: help up down restart logs test test-backend test-frontend lint lint-backend lint-frontend e2e seed-demo typegen clean

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-20s %s\n", $$1, $$2}'

up: ## Start backend + postgres + mailpit
	docker compose up -d
	@echo "Backend:  http://api.lvh.me:8080"
	@echo "Mailpit:  http://localhost:8025"

down: ## Stop containers
	docker compose down

restart: down up ## Restart containers

logs: ## Tail container logs
	docker compose logs -f --tail=200

test: test-backend test-frontend ## Run all tests

test-backend: ## Run backend Pest tests
	docker compose exec backend php artisan test

test-frontend: ## Run frontend Vitest tests
	cd frontend && pnpm test

lint: lint-backend lint-frontend ## Run all linters

lint-backend: ## Run backend linters (Pint + PHPStan)
	docker compose exec backend ./vendor/bin/pint --test
	docker compose exec backend ./vendor/bin/phpstan analyse --memory-limit=512M

lint-frontend: ## Run frontend linters (ESLint + tsc)
	cd frontend && pnpm lint && pnpm typecheck

e2e: ## Run Playwright E2E suite
	cd frontend && pnpm e2e

seed-demo: ## Seed demo tournament (no-op until Phase 2)
	docker compose exec backend php artisan db:seed --class=DemoTournamentSeeder || true

typegen: ## Regenerate frontend types from OpenAPI
	cd frontend && pnpm typegen

clean: ## Remove containers, volumes, build outputs
	docker compose down -v
	rm -rf frontend/node_modules frontend/dist
	rm -rf backend/vendor backend/storage/framework/cache/data/*
```

- [ ] **Step 2: Verify**

Run: `make -C wcf2026 help`

Expected: a list of targets printed, including `up`, `down`, `test`, `lint`, `e2e`, `seed-demo`, `typegen`, `clean`.

> **Windows note:** the Makefile uses Unix tools (`grep`, `awk`, `rm`). On Windows run `make` from **WSL** or **Git Bash**, not raw PowerShell/cmd. Most targets shell out to `docker compose`, which runs the same regardless. If you cannot use `make`, the underlying commands are documented in each target body and can be run directly.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\Makefile
git commit -m "build(wcf2026): add top-level Makefile`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 1.6: ADR-0001 — Adopt hybrid Laravel API + React SPA architecture

**Files:**
- Create: `wcf2026/docs/adr/0001-hybrid-laravel-api-react-spa.md`

- [ ] **Step 1: Write ADR-0001**

```markdown
# 1. Hybrid Laravel API + React SPA architecture

Date: 2026-05-27
Status: Accepted

## Context

The legacy `el2025` app is a Laravel 11 monolith with Blade + Alpine.js, deployed to Apache shared hosting. To deliver a versatile, mobile-friendly, securely deployable platform on free cloud infrastructure, we need a clear backend/frontend split, modern UI patterns, and an API that future clients (e.g., a mobile app) can also use.

## Decision

We will run two independently deployable services in one monorepo:

- **Backend** — Laravel 11 exposing a REST `/api/v1` plus Sanctum session endpoints.
- **Frontend** — React 18 + TypeScript SPA/PWA consuming the API.

The two services share authentication via Sanctum SPA cookie auth.

## Consequences

**Positive:**
- Clean separation of concerns.
- Frontend can iterate independently of backend deploys.
- Same API can serve future non-SPA clients without rework.
- Each service hosted where it's strongest (CDN for SPA, container for API).

**Negative:**
- Two build pipelines instead of one.
- Cross-origin / cross-subdomain cookie configuration is non-trivial (see ADR-0002).
- More moving parts to monitor.

**Alternatives considered:**
- Pure Inertia.js (rejected — tight Laravel coupling defeats the "separate frontend" goal).
- Greenfield Node/TS backend (rejected — discards domain logic that already works in PHP).
- Stay on Blade (rejected — no path to a modern mobile-friendly UI).
```

- [ ] **Step 2: Verify**

Run: `Get-Content wcf2026\docs\adr\0001-hybrid-laravel-api-react-spa.md | Select-String "Status: Accepted"`
Expected: matches.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\docs\adr\0001-hybrid-laravel-api-react-spa.md
git commit -m "docs(adr): 0001 hybrid Laravel API + React SPA`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 1.7: ADR-0002 — Cookie/domain strategy (placeholder for runtime decision)

**Files:**
- Create: `wcf2026/docs/adr/0002-cookie-domain-strategy.md`

- [ ] **Step 1: Write ADR-0002**

```markdown
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
```

- [ ] **Step 2: Verify**

Run: `Get-Content wcf2026\docs\adr\0002-cookie-domain-strategy.md | Select-String "Status: Proposed"`
Expected: matches.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\docs\adr\0002-cookie-domain-strategy.md
git commit -m "docs(adr): 0002 cookie/domain strategy (proposed)`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 1.8: Runbook stub — Phase 0 deploy

**Files:**
- Create: `wcf2026/docs/runbooks/deploy.md`

- [ ] **Step 1: Write `deploy.md`**

```markdown
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
```

- [ ] **Step 2: Verify**

Run: `Get-Content wcf2026\docs\runbooks\deploy.md | Select-String "Rollback"`
Expected: matches at least once.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\docs\runbooks\deploy.md
git commit -m "docs(runbook): add Phase 0 deploy runbook stub`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

End of Chunk 1. ✅ All tooling scaffolds in place; no code yet.

---

## Chunk 2: Backend bootstrap (Laravel + Postgres + Sanctum + health endpoint)

### Task 2.1: Install Laravel 11 skeleton into `backend/`

**Files:** `wcf2026/backend/**` (full skeleton)

- [ ] **Step 1: Run the Laravel installer (Composer create-project)**

```powershell
cd D:\Projects\sportbet\wcf2026
# Remove the placeholder so create-project can populate it
Remove-Item backend\.gitkeep -Force
Remove-Item backend -Recurse -Force
composer create-project laravel/laravel:^11.0 backend --prefer-dist --no-interaction
```

- [ ] **Step 2: Verify**

Run: `Test-Path wcf2026\backend\artisan` → Expected `True`.
Run: `cd wcf2026\backend; php artisan --version` → Expected `Laravel Framework 11.x.x`.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026/backend
git commit -m "feat(backend): scaffold Laravel 11 skeleton`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 2.2: Pin PHP 8.3 + add backend dependencies

**Files:** `wcf2026/backend/composer.json`

- [ ] **Step 1: Edit `backend/composer.json`** — bump PHP, add Sanctum, Scramble, PHPStan, Pest, Larastan.

Set `"require"` (replace existing block):
```json
"require": {
    "php": "^8.3",
    "laravel/framework": "^11.9",
    "laravel/sanctum": "^4.0",
    "laravel/tinker": "^2.9",
    "dedoc/scramble": "^0.11"
}
```

Set `"require-dev"`:
```json
"require-dev": {
    "fakerphp/faker": "^1.23",
    "larastan/larastan": "^2.9",
    "laravel/pint": "^1.13",
    "mockery/mockery": "^1.6",
    "nunomaduro/collision": "^8.0",
    "pestphp/pest": "^3.0",
    "pestphp/pest-plugin-laravel": "^3.0",
    "phpstan/phpstan": "^1.11",
    "phpunit/phpunit": "^11.0"
}
```

Add to `config.allow-plugins`:
```json
"pestphp/pest-plugin": true
```

- [ ] **Step 2: Install**

```powershell
cd wcf2026\backend
composer update --no-interaction
```

- [ ] **Step 3: Verify**

Run: `composer show laravel/sanctum dedoc/scramble pestphp/pest larastan/larastan | Select-String "name"`
Expected: 4 lines, one per package.

- [ ] **Step 4: Commit**

```powershell
git add wcf2026\backend\composer.json wcf2026\backend\composer.lock
git commit -m "feat(backend): add Sanctum, Scramble, Pest, PHPStan; require PHP 8.3`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 2.3: Configure Postgres as default DB

**Files:** `wcf2026/backend/.env.example`, `wcf2026/backend/config/database.php` (verify only)

- [ ] **Step 1: Update `.env.example`**

Replace the `DB_*` block with:
```
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=wcf2026
DB_USERNAME=wcf2026
DB_PASSWORD=wcf2026
```

Also set:
```
APP_NAME=WCF2026
APP_URL=http://api.lvh.me:8080
SESSION_DRIVER=database
SESSION_DOMAIN=.lvh.me
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
SANCTUM_STATEFUL_DOMAINS=app.lvh.me:5173,app.lvh.me
FRONTEND_URL=http://app.lvh.me:5173
```

- [ ] **Step 2: Verify `database.php` ships the `pgsql` driver**

Run: `Select-String -Path wcf2026\backend\config\database.php -Pattern "'pgsql'" | Measure-Object | Select-Object -ExpandProperty Count`
Expected: ≥ 1.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\backend\.env.example
git commit -m "feat(backend): default to Postgres + Sanctum SPA env`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 2.4: Publish Sanctum config & enable SPA stateful middleware

**Files:** `wcf2026/backend/config/sanctum.php` (published), `wcf2026/backend/bootstrap/app.php`

- [ ] **Step 1: Publish Sanctum config & migrations**

```powershell
cd wcf2026\backend
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

- [ ] **Step 2: Edit `bootstrap/app.php`** — register Sanctum's stateful API middleware

Find the `->withMiddleware(function (Middleware $middleware) {` block. Replace its body with:
```php
$middleware->statefulApi();
$middleware->trustProxies(at: '*');
```

- [ ] **Step 3: Verify**

Run: `Select-String -Path wcf2026\backend\bootstrap\app.php -Pattern "statefulApi"`
Expected: one match.

- [ ] **Step 4: Commit**

```powershell
git add wcf2026\backend\config\sanctum.php wcf2026\backend\database\migrations wcf2026\backend\bootstrap\app.php
git commit -m "feat(backend): publish Sanctum config; enable stateful API middleware`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 2.5: Add Pest as the test runner

**Files:** `wcf2026/backend/tests/Pest.php`, `wcf2026/backend/phpunit.xml`

- [ ] **Step 1: Install Pest's test harness**

```powershell
cd wcf2026\backend
php vendor\bin\pest --init
```

(This generates `tests/Pest.php` and sets `tests/TestCase.php` up correctly.)

- [ ] **Step 2: Verify Pest runs**

Run: `cd wcf2026\backend; php vendor\bin\pest`
Expected: at least one test passes (the default `ExampleTest`).

> **Windows note:** On Windows/PowerShell always invoke vendor binaries via `php vendor\bin\<tool>` (or the `.bat` shim, e.g. `vendor\bin\pest.bat`). The Unix-style `./vendor/bin/...` invocation only works on Linux/macOS and in CI.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\backend\tests wcf2026\backend\phpunit.xml
git commit -m "test(backend): initialize Pest`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 2.6: Add PHPStan/Larastan config

**Files:** `wcf2026/backend/phpstan.neon`

- [ ] **Step 1: Create `phpstan.neon`**

```neon
includes:
    - ./vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app
        - database
        - tests
    level: 8
    ignoreErrors: []
    excludePaths:
        - app/Console/Kernel.php
```

- [ ] **Step 2: Verify it runs clean**

Run: `cd wcf2026\backend; php vendor\bin\phpstan analyse --memory-limit=512M --no-progress`
Expected: `[OK] No errors`.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\backend\phpstan.neon
git commit -m "build(backend): add Larastan level 8 config`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 2.7: TDD — `GET /api/v1/health` endpoint

**Files:**
- Create: `wcf2026/backend/tests/Feature/HealthControllerTest.php`
- Create: `wcf2026/backend/app/Http/Controllers/HealthController.php`
- Create: `wcf2026/backend/routes/api.php`
- Modify: `wcf2026/backend/bootstrap/app.php` (register api routes)

- [ ] **Step 1: Write failing test** — `tests/Feature/HealthControllerTest.php`

```php
<?php

use function Pest\Laravel\getJson;

it('returns 200 with status ok and db check', function () {
    getJson('/api/v1/health')
        ->assertOk()
        ->assertJsonStructure(['status', 'checks' => ['database']])
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('checks.database', 'ok');
});
```

- [ ] **Step 2: Run test (must FAIL with 404)**

Run: `cd wcf2026\backend; php vendor\bin\pest --filter=HealthControllerTest`
Expected: FAIL with 404 Not Found.

- [ ] **Step 3: Create `routes/api.php`**

```php
<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('health');
});
```

- [ ] **Step 4: Register API routes in `bootstrap/app.php`**

In the `->withRouting(...)` call, add the `api` argument:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    apiPrefix: 'api',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

- [ ] **Step 5: Create `HealthController`**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dbOk = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $dbOk = false;
        }

        return response()->json([
            'status' => $dbOk ? 'ok' : 'degraded',
            'checks' => ['database' => $dbOk ? 'ok' : 'down'],
        ], $dbOk ? 200 : 503);
    }
}
```

- [ ] **Step 6: Run test (must PASS)**

Run: `cd wcf2026\backend; php vendor\bin\pest --filter=HealthControllerTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```powershell
git add wcf2026\backend\app\Http\Controllers\HealthController.php wcf2026\backend\routes\api.php wcf2026\backend\bootstrap\app.php wcf2026\backend\tests\Feature\HealthControllerTest.php
git commit -m "feat(backend): GET /api/v1/health endpoint`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 2.8: CORS for the SPA origin

**Files:** `wcf2026/backend/config/cors.php`

- [ ] **Step 1: Publish CORS config if missing**

```powershell
cd wcf2026\backend
php artisan config:publish cors
```

- [ ] **Step 2: Edit `config/cors.php`**

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://app.lvh.me:5173')],
    'allowed_origins_patterns' => [
        '#^https://app\\.pr-\\d+\\..+$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['X-Request-Id'],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

- [ ] **Step 3: Add a feature test**

Create `tests/Feature/CorsTest.php`:
```php
<?php

it('allows the SPA origin with credentials', function () {
    $response = $this->withHeaders([
        'Origin' => 'http://app.lvh.me:5173',
    ])->getJson('/api/v1/health');

    $response->assertOk();
    expect($response->headers->get('Access-Control-Allow-Origin'))->toBe('http://app.lvh.me:5173');
    expect($response->headers->get('Access-Control-Allow-Credentials'))->toBe('true');
});
```

- [ ] **Step 4: Run test**

Run: `cd wcf2026\backend; php vendor\bin\pest --filter=CorsTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```powershell
git add wcf2026\backend\config\cors.php wcf2026\backend\tests\Feature\CorsTest.php
git commit -m "feat(backend): CORS allow SPA origin with credentials`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 2.9: Scramble OpenAPI generation

**Files:** `wcf2026/backend/config/scramble.php`

- [ ] **Step 1: Publish Scramble config**

```powershell
cd wcf2026\backend
php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider" --tag="scramble-config"
```

- [ ] **Step 2: Edit `config/scramble.php`** — set API path & version:

```php
'api_path' => 'api/v1',
'api_domain' => null,
'info' => [
    'version' => env('API_VERSION', '0.1.0'),
    'description' => 'WCF2026 prediction platform API',
],
'middleware' => [
    'web',
    Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess::class,
],
```

- [ ] **Step 3: Verify**

Run: `cd wcf2026\backend; php artisan scramble:export wcf2026-openapi.json && Test-Path wcf2026-openapi.json`
Expected: `True`. Then `Remove-Item wcf2026-openapi.json`.

- [ ] **Step 4: Commit**

```powershell
git add wcf2026\backend\config\scramble.php
git commit -m "build(backend): configure Scramble OpenAPI export`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

End of Chunk 2.

---

## Chunk 3: Frontend bootstrap (Vite + React 18 + TS + Tailwind + tests)

### Task 3.1: Scaffold Vite + React + TS into `frontend/`

**Files:** `wcf2026/frontend/**`

- [ ] **Step 1: Run Vite scaffold**

```powershell
cd D:\Projects\sportbet\wcf2026
Remove-Item frontend\.gitkeep -Force
Remove-Item frontend -Recurse -Force
pnpm create vite@^5 frontend --template react-ts --no-git
cd frontend
pnpm install
```

- [ ] **Step 2: Verify**

Run: `Test-Path wcf2026\frontend\package.json` → `True`.
Run: `Get-Content wcf2026\frontend\package.json | Select-String '"react"'` → matches `^18`.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026/frontend
git commit -m "feat(frontend): scaffold Vite + React 18 + TS`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 3.2: Add Tailwind CSS

**Files:** `wcf2026/frontend/tailwind.config.ts`, `wcf2026/frontend/postcss.config.js`, `wcf2026/frontend/src/index.css`

- [ ] **Step 1: Install**

```powershell
cd wcf2026\frontend
pnpm add -D tailwindcss@^3 postcss autoprefixer
pnpm exec tailwindcss init -p
```

- [ ] **Step 2: Rename `tailwind.config.js` → `tailwind.config.ts`** and set:

```ts
import type { Config } from 'tailwindcss';

export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: { extend: {} },
  plugins: [],
} satisfies Config;
```

- [ ] **Step 3: Replace `src/index.css`** with:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

- [ ] **Step 4: Verify** — run `cd wcf2026\frontend; pnpm build`. Expected: build succeeds; `dist/assets/index-*.css` contains Tailwind utility classes (run `Select-String -Path wcf2026\frontend\dist\assets\index-*.css -Pattern "--tw-"` → at least one match).

- [ ] **Step 5: Commit**

```powershell
git add wcf2026\frontend
git commit -m "feat(frontend): add Tailwind CSS`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 3.3: Add TanStack Query, TanStack Router, Zod, Axios

**Files:** `wcf2026/frontend/package.json`, `wcf2026/frontend/src/lib/api-client.ts`, `wcf2026/frontend/src/main.tsx`

- [ ] **Step 1: Install**

```powershell
cd wcf2026\frontend
pnpm add @tanstack/react-query @tanstack/react-router axios zod
pnpm add -D @tanstack/router-devtools @tanstack/router-vite-plugin
```

- [ ] **Step 2: Create `src/lib/api-client.ts`**

```ts
import axios from 'axios';

export const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? 'http://api.lvh.me:8080';

export const apiClient = axios.create({
  baseURL: apiBaseUrl,
  withCredentials: true,
  headers: { Accept: 'application/json' },
});

export async function ensureCsrfCookie(): Promise<void> {
  await apiClient.get('/sanctum/csrf-cookie');
}
```

- [ ] **Step 3: Wire QueryClient in `src/main.tsx`**

```tsx
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import App from './App.tsx';
import './index.css';

const queryClient = new QueryClient();

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <App />
    </QueryClientProvider>
  </StrictMode>,
);
```

- [ ] **Step 4: Verify** — `cd wcf2026\frontend; pnpm build`. Expected: success.

- [ ] **Step 5: Commit**

```powershell
git add wcf2026\frontend
git commit -m "feat(frontend): add TanStack Query + axios api client`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 3.4: Add ESLint + Prettier

**Files:** `wcf2026/frontend/eslint.config.js` (already present from Vite), `wcf2026/frontend/.prettierrc.json`, `wcf2026/frontend/package.json`

- [ ] **Step 1: Install Prettier**

```powershell
cd wcf2026\frontend
pnpm add -D prettier eslint-config-prettier
```

- [ ] **Step 2: Create `.prettierrc.json`**

```json
{
  "semi": true,
  "singleQuote": true,
  "trailingComma": "all",
  "printWidth": 100
}
```

- [ ] **Step 3: Update `eslint.config.js`** — Vite scaffolds an ESLint v9 flat config (an array of config objects exported via `export default`). Import the Prettier preset and append it to the array so it always wins over conflicting rules:

```js
import prettier from 'eslint-config-prettier';
// ...existing imports stay...

export default [
  // ...existing flat-config entries stay as-is...
  prettier,
];
```

- [ ] **Step 4: Add scripts to `package.json`**

```json
"format": "prettier --write \"src/**/*.{ts,tsx,css,md}\"",
"format:check": "prettier --check \"src/**/*.{ts,tsx,css,md}\"",
"typecheck": "tsc --noEmit"
```

- [ ] **Step 5: Verify**

Run: `cd wcf2026\frontend; pnpm lint; pnpm typecheck; pnpm format:check`
Expected: all three succeed (after `pnpm format` once to normalize files).

- [ ] **Step 6: Commit**

```powershell
git add wcf2026\frontend
git commit -m "build(frontend): add Prettier + lint/format/typecheck scripts`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 3.5: Add Vitest + Testing Library + MSW

**Files:** `wcf2026/frontend/vitest.config.ts`, `wcf2026/frontend/src/test/setup.ts`, `wcf2026/frontend/src/test/server.ts`

- [ ] **Step 1: Install**

```powershell
cd wcf2026\frontend
pnpm add -D vitest @vitest/coverage-v8 jsdom @testing-library/react @testing-library/jest-dom @testing-library/user-event msw
```

- [ ] **Step 2: Create `vitest.config.ts`**

```ts
import { defineConfig, mergeConfig } from 'vitest/config';
import viteConfig from './vite.config';

export default mergeConfig(
  viteConfig,
  defineConfig({
    test: {
      environment: 'jsdom',
      globals: true,
      setupFiles: ['./src/test/setup.ts'],
      coverage: {
        provider: 'v8',
        reporter: ['text', 'lcov', 'html'],
        thresholds: { lines: 75, branches: 70, functions: 75, statements: 75 },
        include: ['src/**/*.{ts,tsx}'],
        exclude: ['src/test/**', 'src/main.tsx', 'src/**/*.d.ts'],
      },
    },
  }),
);
```

- [ ] **Step 3: Create `src/test/setup.ts`**

```ts
import '@testing-library/jest-dom/vitest';
import { afterAll, afterEach, beforeAll } from 'vitest';
import { server } from './server';

beforeAll(() => server.listen({ onUnhandledRequest: 'error' }));
afterEach(() => server.resetHandlers());
afterAll(() => server.close());
```

- [ ] **Step 4: Create `src/test/server.ts`**

```ts
import { setupServer } from 'msw/node';
import { http, HttpResponse } from 'msw';

export const handlers = [
  http.get('*/api/v1/health', () =>
    HttpResponse.json({ status: 'ok', checks: { database: 'ok' } }),
  ),
];

export const server = setupServer(...handlers);
```

- [ ] **Step 5: Add scripts to `package.json`**

```json
"test": "vitest run --passWithNoTests",
"test:watch": "vitest",
"test:coverage": "vitest run --coverage --passWithNoTests"
```

- [ ] **Step 6: Verify** — `cd wcf2026\frontend; pnpm test` — expected: 0 tests, but exits 0 with no errors.

- [ ] **Step 7: Commit**

```powershell
git add wcf2026\frontend
git commit -m "test(frontend): add Vitest + Testing Library + MSW`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 3.6: TDD — Health page that calls `/api/v1/health`

**Files:**
- Create: `wcf2026/frontend/src/features/health/HealthPage.tsx`
- Create: `wcf2026/frontend/src/features/health/HealthPage.test.tsx`
- Create: `wcf2026/frontend/src/features/health/useHealth.ts`
- Modify: `wcf2026/frontend/src/App.tsx`

- [ ] **Step 1: Write failing test** — `HealthPage.test.tsx`

```tsx
import { render, screen, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { HealthPage } from './HealthPage';

function renderPage() {
  const qc = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return render(
    <QueryClientProvider client={qc}>
      <HealthPage />
    </QueryClientProvider>,
  );
}

test('renders backend health status from /api/v1/health', async () => {
  renderPage();
  await waitFor(() => expect(screen.getByTestId('health-status')).toHaveTextContent('ok'));
  expect(screen.getByTestId('health-db')).toHaveTextContent('ok');
});
```

- [ ] **Step 2: Run test (must FAIL)** — `cd wcf2026\frontend; pnpm test -- HealthPage`. Expected: FAIL (module not found).

- [ ] **Step 3: Create `useHealth.ts`**

```ts
import { useQuery } from '@tanstack/react-query';
import { z } from 'zod';
import { apiClient } from '../../lib/api-client';

const healthSchema = z.object({
  status: z.enum(['ok', 'degraded']),
  checks: z.object({ database: z.string() }),
});

export type Health = z.infer<typeof healthSchema>;

export function useHealth() {
  return useQuery({
    queryKey: ['health'],
    queryFn: async (): Promise<Health> => {
      const res = await apiClient.get('/api/v1/health');
      return healthSchema.parse(res.data);
    },
  });
}
```

- [ ] **Step 4: Create `HealthPage.tsx`**

```tsx
import { useHealth } from './useHealth';

export function HealthPage() {
  const { data, isLoading, isError } = useHealth();
  if (isLoading) return <p>Loading…</p>;
  if (isError || !data) return <p>Failed to load.</p>;
  return (
    <section className="p-4">
      <h1 className="text-xl font-semibold">Backend health</h1>
      <p>Status: <span data-testid="health-status">{data.status}</span></p>
      <p>DB: <span data-testid="health-db">{data.checks.database}</span></p>
    </section>
  );
}
```

- [ ] **Step 5: Use it from `App.tsx`** — replace body of `App` with `<HealthPage />`.

- [ ] **Step 6: Run test (must PASS)** — `cd wcf2026\frontend; pnpm test -- HealthPage`. Expected: PASS.

- [ ] **Step 7: Commit**

```powershell
git add wcf2026\frontend\src
git commit -m "feat(frontend): health page calling /api/v1/health`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 3.7: Add PWA plugin (vite-plugin-pwa)

**Files:** `wcf2026/frontend/vite.config.ts`, `wcf2026/frontend/package.json`

- [ ] **Step 1: Install**

```powershell
cd wcf2026\frontend
pnpm add -D vite-plugin-pwa
```

- [ ] **Step 2: Edit `vite.config.ts`** — add the plugin:

```ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
  plugins: [
    react(),
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'WCF2026',
        short_name: 'WCF2026',
        theme_color: '#0f172a',
        background_color: '#0f172a',
        display: 'standalone',
        start_url: '/',
        icons: [],
      },
    }),
  ],
  server: { host: '0.0.0.0', port: 5173 },
});
```

- [ ] **Step 3: Verify** — `cd wcf2026\frontend; pnpm build`. Expected: `dist/sw.js` and `dist/manifest.webmanifest` exist (check with `Test-Path`).

- [ ] **Step 4: Commit**

```powershell
git add wcf2026\frontend
git commit -m "feat(frontend): enable PWA via vite-plugin-pwa`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 3.8: Add Playwright (e2e shell only — actual e2e specs land in Chunk 6)

**Files:** `wcf2026/frontend/playwright.config.ts`, `wcf2026/frontend/e2e/.gitkeep`

- [ ] **Step 1: Install**

```powershell
cd wcf2026\frontend
pnpm add -D @playwright/test
pnpm exec playwright install --with-deps chromium
```

- [ ] **Step 2: Create `playwright.config.ts`**

```ts
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  reporter: process.env.CI ? [['github'], ['list']] : 'list',
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL ?? 'http://app.lvh.me:5173',
    trace: 'on-first-retry',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
```

- [ ] **Step 3: Add scripts**

```json
"e2e": "playwright test",
"e2e:install": "playwright install --with-deps chromium"
```

- [ ] **Step 4: Verify** — `cd wcf2026\frontend; pnpm exec playwright --version`. Expected: a version string.

- [ ] **Step 5: Commit**

```powershell
git add wcf2026\frontend\playwright.config.ts wcf2026\frontend\package.json wcf2026\frontend\pnpm-lock.yaml wcf2026\frontend\e2e
git commit -m "test(frontend): add Playwright config (specs added in Chunk 6)`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

End of Chunk 3.

---

## Chunk 4: Local dev integration (docker-compose + Sanctum round-trip)

### Task 4.1: `docker-compose.yml` with Postgres, backend (php artisan serve), mailpit

**Files:** `wcf2026/docker-compose.yml`, `wcf2026/backend/Dockerfile.dev`

- [ ] **Step 1: Create `backend/Dockerfile.dev`**

```dockerfile
FROM php:8.3-cli-alpine

RUN apk add --no-cache git unzip postgresql-dev oniguruma-dev libzip-dev icu-dev \
    && docker-php-ext-install pdo pdo_pgsql bcmath intl zip pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

- [ ] **Step 2: Create `wcf2026/docker-compose.yml`**

```yaml
services:
  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_USER: wcf2026
      POSTGRES_PASSWORD: wcf2026
      POSTGRES_DB: wcf2026
    ports: ["5432:5432"]
    volumes: ["pgdata:/var/lib/postgresql/data"]
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U wcf2026"]
      interval: 5s
      timeout: 3s
      retries: 10

  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile.dev
    working_dir: /app
    volumes:
      - ./backend:/app
    ports: ["8080:8000"]
    environment:
      DB_HOST: postgres
      MAIL_HOST: mailpit
      MAIL_PORT: 1025
      APP_URL: http://api.lvh.me:8080
    depends_on:
      postgres:
        condition: service_healthy

  mailpit:
    image: axllent/mailpit:latest
    ports: ["1025:1025", "8025:8025"]

volumes:
  pgdata:
```

- [ ] **Step 3: Verify**

Run: `cd wcf2026; docker compose config` — expected: prints resolved config with no errors.

- [ ] **Step 4: Commit**

```powershell
git add wcf2026\docker-compose.yml wcf2026\backend\Dockerfile.dev
git commit -m "feat(infra): docker-compose dev stack (postgres + backend + mailpit)`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 4.2: Bootstrap database & run migrations

**Files:** none (operational)

- [ ] **Step 1: Bring up Postgres**

```powershell
cd D:\Projects\sportbet\wcf2026
docker compose up -d postgres
```

- [ ] **Step 2: Generate APP_KEY & .env**

```powershell
cd backend
Copy-Item .env.example .env -Force
docker compose -f ..\docker-compose.yml run --rm backend php artisan key:generate
```

- [ ] **Step 3: Run migrations (creates `users`, `personal_access_tokens`, `sessions`, etc.)**

```powershell
docker compose -f ..\docker-compose.yml run --rm backend php artisan migrate --force
```

- [ ] **Step 4: Verify** — `docker compose -f ..\docker-compose.yml exec postgres psql -U wcf2026 -d wcf2026 -c "\dt"` shows tables.

- [ ] **Step 5: No commit** (operational only; `.env` is gitignored).

---

### Task 4.3: TDD — end-to-end Sanctum cookie round-trip

**Files:**
- Create: `wcf2026/backend/database/factories/UserFactory.php` (already shipped with Laravel; verify)
- Create: `wcf2026/backend/app/Http/Controllers/Auth/SessionController.php`
- Create: `wcf2026/backend/tests/Feature/Auth/SessionControllerTest.php`
- Modify: `wcf2026/backend/routes/api.php`

- [ ] **Step 1: Update `tests/Pest.php`** — ensure feature tests refresh the DB. Add (or merge with the existing `uses(...)` call):

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');
```

- [ ] **Step 2: Write failing test** — `tests/Feature/Auth/SessionControllerTest.php`

```php
<?php

use App\Models\User;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('logs in via cookie session and returns the authenticated user', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('secret-pass'),
    ]);

    // CSRF cookie endpoint must respond 204
    $this->get('/sanctum/csrf-cookie')->assertNoContent();

    postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'secret-pass',
    ])->assertOk()->assertJsonPath('data.email', 'test@example.com');

    getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);

    postJson('/api/v1/auth/logout')->assertNoContent();

    getJson('/api/v1/auth/me')->assertUnauthorized();
});
```

- [ ] **Step 3: Run test (must FAIL)** — `cd wcf2026\backend; php vendor\bin\pest --filter=SessionControllerTest`. Expected: 404 / route not found.

- [ ] **Step 4: Create `SessionController`**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, remember: true)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        $request->session()->regenerate();

        return response()->json(['data' => $request->user()->only(['id', 'email', 'name'])]);
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->only(['id', 'email', 'name'])]);
    }

    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
```

- [ ] **Step 5: Add routes to `routes/api.php`**

```php
use App\Http\Controllers\Auth\SessionController;

Route::prefix('v1')->group(function () {
    Route::get('/health', \App\Http\Controllers\HealthController::class);

    Route::post('/auth/login', [SessionController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [SessionController::class, 'show']);
        Route::post('/auth/logout', [SessionController::class, 'destroy']);
    });
});
```

- [ ] **Step 6: Run test (must PASS)** — `cd wcf2026\backend; php vendor\bin\pest --filter=SessionControllerTest`. Expected: PASS.

- [ ] **Step 7: Commit**

```powershell
git add wcf2026\backend\tests\Pest.php wcf2026\backend\app\Http\Controllers\Auth wcf2026\backend\routes\api.php wcf2026\backend\tests\Feature\Auth
git commit -m "feat(backend): Sanctum cookie auth (login/me/logout)`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 4.4: Frontend login form + smoke e2e using MSW

**Files:**
- Create: `wcf2026/frontend/src/features/auth/LoginForm.tsx`
- Create: `wcf2026/frontend/src/features/auth/LoginForm.test.tsx`
- Update: `wcf2026/frontend/src/test/server.ts` (add login handlers)

- [ ] **Step 1: Add MSW handlers** to `src/test/server.ts`:

```ts
http.get('*/sanctum/csrf-cookie', () => new HttpResponse(null, { status: 204 })),
http.post('*/api/v1/auth/login', async ({ request }) => {
  const body = await request.json() as { email: string; password: string };
  if (body.email === 'test@example.com' && body.password === 'secret-pass') {
    return HttpResponse.json({ data: { id: 1, email: body.email, name: 'Test' } });
  }
  return HttpResponse.json({ message: 'Invalid' }, { status: 422 });
}),
```

- [ ] **Step 2: Write failing test** — `LoginForm.test.tsx`

```tsx
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { LoginForm } from './LoginForm';

test('logs in and shows the user email on success', async () => {
  const qc = new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
  render(
    <QueryClientProvider client={qc}>
      <LoginForm />
    </QueryClientProvider>,
  );

  await userEvent.type(screen.getByLabelText(/email/i), 'test@example.com');
  await userEvent.type(screen.getByLabelText(/password/i), 'secret-pass');
  await userEvent.click(screen.getByRole('button', { name: /sign in/i }));

  expect(await screen.findByText('test@example.com')).toBeInTheDocument();
});
```

- [ ] **Step 3: Run test (must FAIL)** — `pnpm test -- LoginForm`. Expected: module not found.

- [ ] **Step 4: Create `LoginForm.tsx`**

```tsx
import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { apiClient, ensureCsrfCookie } from '../../lib/api-client';

type Me = { id: number; email: string; name: string };

export function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [me, setMe] = useState<Me | null>(null);

  const mutation = useMutation({
    mutationFn: async () => {
      await ensureCsrfCookie();
      const res = await apiClient.post<{ data: Me }>('/api/v1/auth/login', { email, password });
      return res.data.data;
    },
    onSuccess: setMe,
  });

  if (me) return <p>{me.email}</p>;

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        mutation.mutate();
      }}
      className="p-4 space-y-2"
    >
      <label className="block">
        Email
        <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} className="block border" />
      </label>
      <label className="block">
        Password
        <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} className="block border" />
      </label>
      <button type="submit" disabled={mutation.isPending}>Sign in</button>
      {mutation.isError && <p role="alert">Login failed</p>}
    </form>
  );
}
```

- [ ] **Step 5: Run test (must PASS)** — `pnpm test -- LoginForm`. Expected: PASS.

- [ ] **Step 6: Commit**

```powershell
git add wcf2026\frontend\src
git commit -m "feat(frontend): login form using Sanctum cookie flow`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 4.5: Manual smoke — verify cookie round-trip against the live backend

**Files:** none (operational + recorded in runbook)

- [ ] **Step 1: Bring up the stack**

```powershell
cd D:\Projects\sportbet\wcf2026
docker compose up -d
cd frontend
$env:VITE_API_BASE_URL = "http://api.lvh.me:8080"
pnpm dev --host
```

- [ ] **Step 2: Seed a test user**

```powershell
docker compose -f ..\docker-compose.yml exec backend php artisan tinker --execute="\App\Models\User::factory()->create(['email'=>'test@example.com','password'=>bcrypt('secret-pass')]);"
```

- [ ] **Step 3: Browse to `http://app.lvh.me:5173`** — log in with `test@example.com` / `secret-pass`. Open DevTools → Application → Cookies → confirm `XSRF-TOKEN` and `wcf2026_session` cookies are set on `.lvh.me`.

- [ ] **Step 4: Record outcome** — append the verification result (date + ✅/❌) to `wcf2026/docs/runbooks/deploy.md` under a new `## Manual smoke results` section.

- [ ] **Step 5: Commit** the runbook update only.

```powershell
git add wcf2026\docs\runbooks\deploy.md
git commit -m "docs: record local Sanctum cookie smoke result`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

End of Chunk 4.

---

## Chunk 5: Containerization & deploy targets (Fly.io, Cloudflare Pages)

### Task 5.1: Production multi-stage backend `Dockerfile`

**Files:** `wcf2026/backend/Dockerfile`, `wcf2026/backend/docker/entrypoint.sh`, `wcf2026/backend/docker/php.ini`, `wcf2026/backend/docker/nginx.conf`

- [ ] **Step 1: Create `backend/Dockerfile`**

```dockerfile
# syntax=docker/dockerfile:1.7

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

FROM php:8.3-fpm-alpine AS runtime
RUN apk add --no-cache nginx supervisor postgresql-dev oniguruma-dev libzip-dev icu-dev bash \
    && docker-php-ext-install pdo pdo_pgsql bcmath intl zip pcntl opcache

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY . .
# Autoload only; config/route caches are warmed at boot AFTER env injection
# (see docker/entrypoint.sh). Doing it here would bake build-time env into
# the image and override Fly secrets at runtime.
RUN composer dump-autoload --optimize --classmap-authoritative \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]
```

- [ ] **Step 2: Create `docker/entrypoint.sh`**

> Migrations are run by Fly's `release_command` (see `fly.toml` in Task 5.2), **not** here, so concurrent boots never race on schema changes. The entrypoint only warms caches with the live, secret-injected env, then starts the web stack.

```bash
#!/usr/bin/env bash
set -euo pipefail

# Fly's `release_command` runs the image with extra args (e.g. `php artisan migrate --force`).
# When args are present, exec them and exit; otherwise warm caches and start the web stack.
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

php artisan config:cache
php artisan route:cache
php artisan storage:link || true

nginx -g 'daemon off;' &
exec php-fpm
```

- [ ] **Step 3: Create `docker/nginx.conf`** — fronting Laravel's `public/` and proxying PHP to local php-fpm on port 9000:

```nginx
user www-data;
worker_processes auto;
error_log /dev/stderr warn;
pid /run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    sendfile on;
    keepalive_timeout 65;
    server_tokens off;
    client_max_body_size 20m;
    access_log /dev/stdout;

    server {
        listen 8080 default_server;
        server_name _;
        root /app/public;
        index index.php index.html;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
            fastcgi_read_timeout 60s;
        }

        location ~ /\.(?!well-known) {
            deny all;
        }
    }
}
```

- [ ] **Step 4: Create `docker/php.ini`**

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
opcache.enable = 1
opcache.validate_timestamps = 0
expose_php = Off
```

- [ ] **Step 5: Verify build**

Run: `cd wcf2026\backend; docker build -t wcf2026-backend:test .`
Expected: image builds successfully.

- [ ] **Step 6: Commit**

```powershell
git add wcf2026\backend\Dockerfile wcf2026\backend\docker
git commit -m "feat(backend): production multi-stage Dockerfile`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 5.2: `fly.toml` for backend on Fly.io

**Files:** `wcf2026/backend/fly.toml`

- [ ] **Step 1: Create `backend/fly.toml`** (app name will be set when account is provisioned in Chunk 7; placeholder for now)

```toml
app = "wcf2026-backend"
primary_region = "fra"

[build]
  dockerfile = "Dockerfile"

[deploy]
  release_command = "php artisan migrate --force"
  strategy = "rolling"

[env]
  APP_ENV = "production"
  LOG_CHANNEL = "stderr"
  SESSION_DRIVER = "database"
  SESSION_SECURE_COOKIE = "true"
  SESSION_SAME_SITE = "lax"

[http_service]
  internal_port = 8080
  force_https = true
  auto_stop_machines = false
  auto_start_machines = true
  min_machines_running = 1
  processes = ["app"]

  [http_service.concurrency]
    type = "requests"
    soft_limit = 200
    hard_limit = 250

  [[http_service.checks]]
    grace_period = "10s"
    interval = "15s"
    method = "GET"
    timeout = "5s"
    path = "/api/v1/health"

[[vm]]
  size = "shared-cpu-1x"
  memory = "256mb"
  cpu_kind = "shared"
  cpus = 1
```

- [ ] **Step 2: Verify** — `Select-String -Path wcf2026\backend\fly.toml -Pattern "internal_port"` → matches.

- [ ] **Step 3: Commit**

```powershell
git add wcf2026\backend\fly.toml
git commit -m "feat(infra): fly.toml for backend`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 5.3: Cloudflare Pages configuration

**Files:** `wcf2026/infra/cloudflare-pages/_headers`, `wcf2026/infra/cloudflare-pages/_redirects`, `wcf2026/infra/cloudflare-pages/README.md`

- [ ] **Step 1: Create `infra/cloudflare-pages/_headers`**

> The CSP `connect-src` allows the production backend host (filled in by Task 7.5 once ADR-0002 is finalized) **and** any Fly preview app (`https://*.fly.dev`) so per-PR preview deploys (Task 7.3) can reach their preview backend. Tighten further once the production host is final.

```
/*
  Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
  X-Content-Type-Options: nosniff
  X-Frame-Options: DENY
  Referrer-Policy: strict-origin-when-cross-origin
  Permissions-Policy: camera=(), microphone=(), geolocation=()
  Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self' https://wcf2026-backend.fly.dev https://*.fly.dev; frame-ancestors 'none'
```

- [ ] **Step 2: Create `_redirects`**

```
/*    /index.html   200
```

- [ ] **Step 3: Create `README.md`** documenting:
  - Project name: `wcf2026-frontend`
  - Build command: `cd frontend && pnpm install --frozen-lockfile && pnpm build`
  - Build output: `frontend/dist`
  - Env vars: `VITE_API_BASE_URL` (per environment)
  - That `_headers` and `_redirects` must be copied into `frontend/public/` by the build workflow (Chunk 7 handles this).

- [ ] **Step 4: Commit**

```powershell
git add wcf2026\infra\cloudflare-pages
git commit -m "feat(infra): Cloudflare Pages headers/redirects/readme`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

End of Chunk 5.

---

## Chunk 6: CI workflows (GitHub Actions)

All workflow files live under `.github/workflows/` at the **repository root** (D:\Projects\sportbet\.github\workflows\), not under `wcf2026/`. GitHub Actions only reads workflows from the repo root `.github/` directory.

### Task 6.1: `backend-ci.yml`

**Files:** `.github/workflows/backend-ci.yml`

- [ ] **Step 1: Create the workflow**

```yaml
name: backend-ci

on:
  push:
    branches: [main]
    paths: ['wcf2026/backend/**', '.github/workflows/backend-ci.yml']
  pull_request:
    paths: ['wcf2026/backend/**', '.github/workflows/backend-ci.yml']

defaults:
  run:
    working-directory: wcf2026/backend

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16-alpine
        env:
          POSTGRES_USER: wcf2026
          POSTGRES_PASSWORD: wcf2026
          POSTGRES_DB: wcf2026_test
        ports: ['5432:5432']
        options: >-
          --health-cmd "pg_isready -U wcf2026"
          --health-interval 5s
          --health-timeout 3s
          --health-retries 10

    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo, pdo_pgsql, bcmath, intl, zip, pcntl
          coverage: pcov
          tools: composer:v2

      - name: Cache Composer
        uses: actions/cache@v4
        with:
          path: ~/.composer/cache
          key: composer-${{ hashFiles('wcf2026/backend/composer.lock') }}

      - run: composer install --prefer-dist --no-interaction --no-progress

      - name: Prepare .env
        run: |
          cp .env.example .env
          php artisan key:generate
          php artisan migrate --force --no-interaction
        env:
          DB_HOST: 127.0.0.1
          DB_DATABASE: wcf2026_test

      - run: ./vendor/bin/pint --test
      - run: ./vendor/bin/phpstan analyse --no-progress --memory-limit=512M
      - name: Run tests with coverage
        run: ./vendor/bin/pest --coverage --min=80 --coverage-clover=coverage.xml
        env:
          DB_HOST: 127.0.0.1
          DB_DATABASE: wcf2026_test

      - uses: actions/upload-artifact@v4
        if: always()
        with:
          name: backend-coverage
          path: wcf2026/backend/coverage.xml
```

- [ ] **Step 2: Verify YAML parses** — `Get-Content .github\workflows\backend-ci.yml | Out-String | python -c "import sys, yaml; yaml.safe_load(sys.stdin.read())"` (or use `yq`). Expected: no error.

- [ ] **Step 3: Commit**

```powershell
git add .github\workflows\backend-ci.yml
git commit -m "ci: backend test/lint/static-analysis workflow`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 6.2: `frontend-ci.yml`

**Files:** `.github/workflows/frontend-ci.yml`

- [ ] **Step 1: Create**

```yaml
name: frontend-ci

on:
  push:
    branches: [main]
    paths: ['wcf2026/frontend/**', '.github/workflows/frontend-ci.yml']
  pull_request:
    paths: ['wcf2026/frontend/**', '.github/workflows/frontend-ci.yml']

defaults:
  run:
    working-directory: wcf2026/frontend

jobs:
  build:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4
      - uses: pnpm/action-setup@v4
        with:
          version: 9
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'pnpm'
          cache-dependency-path: wcf2026/frontend/pnpm-lock.yaml

      - run: pnpm install --frozen-lockfile
      - run: pnpm lint
      - run: pnpm typecheck
      - run: pnpm format:check
      - run: pnpm test:coverage
      - run: pnpm build

      - uses: actions/upload-artifact@v4
        if: always()
        with:
          name: frontend-coverage
          path: wcf2026/frontend/coverage
```

- [ ] **Step 2: Verify YAML parses**, then **commit**:

```powershell
git add .github\workflows\frontend-ci.yml
git commit -m "ci: frontend lint/type/test/build workflow`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 6.3: `e2e.yml` (Playwright with seeded smoke spec)

**Files:**
- Create: `.github/workflows/e2e.yml`
- Create: `wcf2026/frontend/e2e/smoke.spec.ts`

- [ ] **Step 1: Write the smoke spec**

```ts
import { test, expect } from '@playwright/test';

test('home page renders backend health', async ({ page }) => {
  await page.goto('/');
  await expect(page.getByTestId('health-status')).toHaveText('ok');
});
```

- [ ] **Step 2: Create the workflow**

```yaml
name: e2e

on:
  push:
    branches: [main]
    paths:
      - 'wcf2026/backend/**'
      - 'wcf2026/frontend/**'
      - '.github/workflows/e2e.yml'
  pull_request:
    paths:
      - 'wcf2026/backend/**'
      - 'wcf2026/frontend/**'
      - '.github/workflows/e2e.yml'

jobs:
  e2e:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16-alpine
        env:
          POSTGRES_USER: wcf2026
          POSTGRES_PASSWORD: wcf2026
          POSTGRES_DB: wcf2026_e2e
        ports: ['5432:5432']
        options: >-
          --health-cmd "pg_isready -U wcf2026"
          --health-interval 5s
          --health-timeout 3s
          --health-retries 10

    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo, pdo_pgsql, bcmath, intl, zip
          tools: composer:v2

      - uses: pnpm/action-setup@v4
        with:
          version: 9
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'pnpm'
          cache-dependency-path: wcf2026/frontend/pnpm-lock.yaml

      - name: Install backend
        working-directory: wcf2026/backend
        run: |
          composer install --prefer-dist --no-interaction
          cp .env.example .env
          php artisan key:generate
          php artisan migrate --force
        env:
          DB_HOST: 127.0.0.1
          DB_DATABASE: wcf2026_e2e

      - name: Start backend
        working-directory: wcf2026/backend
        run: nohup php artisan serve --host=127.0.0.1 --port=8080 > backend.log 2>&1 &
        env:
          DB_HOST: 127.0.0.1
          DB_DATABASE: wcf2026_e2e
          FRONTEND_URL: http://127.0.0.1:5173
          SANCTUM_STATEFUL_DOMAINS: 127.0.0.1:5173

      - name: Install frontend
        working-directory: wcf2026/frontend
        run: |
          pnpm install --frozen-lockfile
          pnpm exec playwright install --with-deps chromium

      - name: Build & preview frontend
        working-directory: wcf2026/frontend
        run: |
          VITE_API_BASE_URL=http://127.0.0.1:8080 pnpm build
          nohup pnpm exec vite preview --host 127.0.0.1 --port 5173 > vite.log 2>&1 &

      - name: Wait for services
        run: |
          pnpm dlx wait-on http://127.0.0.1:8080/api/v1/health http://127.0.0.1:5173 --timeout 60000

      - name: Run Playwright
        working-directory: wcf2026/frontend
        run: pnpm exec playwright test
        env:
          PLAYWRIGHT_BASE_URL: http://127.0.0.1:5173

      - uses: actions/upload-artifact@v4
        if: failure()
        with:
          name: playwright-report
          path: wcf2026/frontend/playwright-report
```

- [ ] **Step 3: Verify YAML parses**, then **commit**:

```powershell
git add .github\workflows\e2e.yml wcf2026\frontend\e2e\smoke.spec.ts
git commit -m "ci: e2e workflow with Playwright smoke spec`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 6.4: `security.yml` (dependency, secret, image scans)

**Files:** `.github/workflows/security.yml`

- [ ] **Step 1: Create**

```yaml
name: security

on:
  push:
    branches: [main]
  pull_request:
  schedule:
    - cron: '0 6 * * 1'  # Mondays 06:00 UTC

permissions:
  contents: read
  security-events: write
  actions: read

jobs:
  composer-audit:
    runs-on: ubuntu-latest
    defaults:
      run:
        working-directory: wcf2026/backend
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          tools: composer:v2
      - run: composer install --prefer-dist --no-interaction
      - run: composer audit --no-interaction

  pnpm-audit:
    runs-on: ubuntu-latest
    defaults:
      run:
        working-directory: wcf2026/frontend
    steps:
      - uses: actions/checkout@v4
      - uses: pnpm/action-setup@v4
        with:
          version: 9
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'pnpm'
          cache-dependency-path: wcf2026/frontend/pnpm-lock.yaml
      - run: pnpm install --frozen-lockfile
      - run: pnpm audit --audit-level high --prod

  gitleaks:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with: { fetch-depth: 0 }
      - uses: gitleaks/gitleaks-action@v2
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}

  trivy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Build backend image
        run: docker build -t wcf2026-backend:scan wcf2026/backend
      - uses: aquasecurity/trivy-action@0.24.0
        with:
          image-ref: wcf2026-backend:scan
          format: sarif
          output: trivy.sarif
          severity: CRITICAL,HIGH
          exit-code: '1'
          ignore-unfixed: true
      - uses: github/codeql-action/upload-sarif@v3
        if: always()
        with:
          sarif_file: trivy.sarif

  codeql:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        language: ['javascript-typescript', 'php']
    steps:
      - uses: actions/checkout@v4
      - uses: github/codeql-action/init@v3
        with:
          languages: ${{ matrix.language }}
      - uses: github/codeql-action/analyze@v3
        with:
          category: '/language:${{ matrix.language }}'
```

- [ ] **Step 2: Verify YAML parses**, then **commit**:

```powershell
git add .github\workflows\security.yml
git commit -m "ci: security scans (composer audit, pnpm audit, gitleaks, trivy, codeql)`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

End of Chunk 6.

---

## Chunk 7: Deploy workflows + finalize ADR-0002

The actual provisioning steps (creating the Neon project, Fly app, Cloudflare Pages project, configuring secrets) are **operational** and documented in `docs/runbooks/deploy.md`. The workflows themselves can be committed first; they will no-op until secrets exist.

### Task 7.1: `deploy-backend.yml` (Fly.io)

**Files:** `.github/workflows/deploy-backend.yml`

- [ ] **Step 1: Create**

```yaml
name: deploy-backend

on:
  push:
    branches: [main]
    paths:
      - 'wcf2026/backend/**'
      - '.github/workflows/deploy-backend.yml'
  workflow_dispatch:

concurrency:
  group: deploy-backend
  cancel-in-progress: false

jobs:
  deploy:
    runs-on: ubuntu-latest
    if: ${{ github.event_name == 'workflow_dispatch' || github.ref == 'refs/heads/main' }}
    steps:
      - uses: actions/checkout@v4
      - uses: superfly/flyctl-actions/setup-flyctl@master
      - name: Deploy
        working-directory: wcf2026/backend
        run: flyctl deploy --remote-only --strategy=rolling
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN }}
      - name: Smoke health
        run: |
          for i in 1 2 3 4 5; do
            if curl -fsS https://wcf2026-backend.fly.dev/api/v1/health; then exit 0; fi
            sleep 10
          done
          exit 1
```

- [ ] **Step 2: Verify YAML, commit**:

```powershell
git add .github\workflows\deploy-backend.yml
git commit -m "ci: deploy backend to Fly.io`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 7.2: `deploy-frontend.yml` (Cloudflare Pages)

**Files:** `.github/workflows/deploy-frontend.yml`

- [ ] **Step 1: Create**

```yaml
name: deploy-frontend

on:
  push:
    branches: [main]
    paths:
      - 'wcf2026/frontend/**'
      - 'wcf2026/infra/cloudflare-pages/**'
      - '.github/workflows/deploy-frontend.yml'
  workflow_dispatch:

concurrency:
  group: deploy-frontend
  cancel-in-progress: true

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: pnpm/action-setup@v4
        with:
          version: 9
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'pnpm'
          cache-dependency-path: wcf2026/frontend/pnpm-lock.yaml

      - name: Build
        working-directory: wcf2026/frontend
        run: |
          pnpm install --frozen-lockfile
          cp ../infra/cloudflare-pages/_headers public/_headers
          cp ../infra/cloudflare-pages/_redirects public/_redirects
          pnpm build
        env:
          VITE_API_BASE_URL: ${{ vars.PROD_API_BASE_URL }}

      - name: Publish to Cloudflare Pages
        uses: cloudflare/wrangler-action@v3
        with:
          apiToken: ${{ secrets.CLOUDFLARE_API_TOKEN }}
          accountId: ${{ secrets.CLOUDFLARE_ACCOUNT_ID }}
          command: pages deploy wcf2026/frontend/dist --project-name=wcf2026-frontend --branch=main
```

- [ ] **Step 2: Verify YAML, commit**:

```powershell
git add .github\workflows\deploy-frontend.yml
git commit -m "ci: deploy frontend to Cloudflare Pages`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 7.3: `preview-env.yml` (PR preview: Neon branch + Fly preview app + CF Pages preview)

**Files:** `.github/workflows/preview-env.yml`

- [ ] **Step 1: Create**

```yaml
name: preview-env

on:
  pull_request:
    types: [opened, synchronize, reopened, closed]

concurrency:
  group: preview-${{ github.event.pull_request.number }}
  cancel-in-progress: true

permissions:
  pull-requests: write
  contents: read

jobs:
  up:
    if: github.event.action != 'closed'
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Create Neon branch
        id: neon
        uses: neondatabase/create-branch-action@v5
        with:
          project_id: ${{ secrets.NEON_PROJECT_ID }}
          branch_name: pr-${{ github.event.pull_request.number }}
          api_key: ${{ secrets.NEON_API_KEY }}

      - uses: superfly/flyctl-actions/setup-flyctl@master

      - name: Create or update Fly preview app
        working-directory: wcf2026/backend
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN }}
          PREVIEW_APP: wcf2026-backend-pr-${{ github.event.pull_request.number }}
          NEON_DB_URL: ${{ steps.neon.outputs.db_url }}
          PREVIEW_FRONTEND_URL: https://pr-${{ github.event.pull_request.number }}.wcf2026-frontend.pages.dev
        run: |
          flyctl apps create "$PREVIEW_APP" --org personal || true
          flyctl secrets set --app "$PREVIEW_APP" \
            APP_ENV=production \
            DB_CONNECTION=pgsql \
            DB_URL="$NEON_DB_URL" \
            APP_KEY="${{ secrets.PREVIEW_APP_KEY }}" \
            FRONTEND_URL="$PREVIEW_FRONTEND_URL" \
            SANCTUM_STATEFUL_DOMAINS="pr-${{ github.event.pull_request.number }}.wcf2026-frontend.pages.dev" \
            SESSION_DOMAIN="" \
            SESSION_SECURE_COOKIE=true \
            SESSION_SAME_SITE=none
          flyctl deploy --app "$PREVIEW_APP" --remote-only

      - uses: pnpm/action-setup@v4
        with:
          version: 9
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'pnpm'
          cache-dependency-path: wcf2026/frontend/pnpm-lock.yaml

      - name: Build & deploy CF Pages preview
        working-directory: wcf2026/frontend
        env:
          VITE_API_BASE_URL: https://wcf2026-backend-pr-${{ github.event.pull_request.number }}.fly.dev
        run: |
          pnpm install --frozen-lockfile
          cp ../infra/cloudflare-pages/_headers public/_headers
          cp ../infra/cloudflare-pages/_redirects public/_redirects
          pnpm build

      - uses: cloudflare/wrangler-action@v3
        with:
          apiToken: ${{ secrets.CLOUDFLARE_API_TOKEN }}
          accountId: ${{ secrets.CLOUDFLARE_ACCOUNT_ID }}
          command: pages deploy wcf2026/frontend/dist --project-name=wcf2026-frontend --branch=pr-${{ github.event.pull_request.number }}

      - name: Comment preview URLs on PR
        uses: marocchino/sticky-pull-request-comment@v2
        with:
          message: |
            Preview environment for PR #${{ github.event.pull_request.number }}:
            - Frontend: https://pr-${{ github.event.pull_request.number }}.wcf2026-frontend.pages.dev
            - Backend:  https://wcf2026-backend-pr-${{ github.event.pull_request.number }}.fly.dev/api/v1/health
            - Neon branch: pr-${{ github.event.pull_request.number }}

  down:
    if: github.event.action == 'closed'
    runs-on: ubuntu-latest
    steps:
      - uses: superfly/flyctl-actions/setup-flyctl@master
      - name: Destroy Fly preview app
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN }}
        run: flyctl apps destroy wcf2026-backend-pr-${{ github.event.pull_request.number }} --yes || true

      - name: Delete Neon branch
        uses: neondatabase/delete-branch-action@v3
        with:
          project_id: ${{ secrets.NEON_PROJECT_ID }}
          branch: pr-${{ github.event.pull_request.number }}
          api_key: ${{ secrets.NEON_API_KEY }}
```

- [ ] **Step 2: Verify YAML, commit**:

```powershell
git add .github\workflows\preview-env.yml
git commit -m "ci: per-PR preview env (Neon branch + Fly app + CF Pages)`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 7.4: Document required secrets/vars in the deploy runbook

**Files:** `wcf2026/docs/runbooks/deploy.md`

- [ ] **Step 1: Append a "Required GitHub secrets" section** listing:

| Secret / Var               | Where to get it                         | Used in                       |
|----------------------------|-----------------------------------------|-------------------------------|
| `FLY_API_TOKEN`            | `flyctl auth token`                     | deploy-backend, preview-env   |
| `CLOUDFLARE_API_TOKEN`     | CF dashboard → My Profile → API Tokens  | deploy-frontend, preview-env  |
| `CLOUDFLARE_ACCOUNT_ID`    | CF dashboard sidebar                    | deploy-frontend, preview-env  |
| `NEON_PROJECT_ID`          | Neon dashboard → project settings       | preview-env                   |
| `NEON_API_KEY`             | Neon dashboard → API keys               | preview-env                   |
| `PREVIEW_APP_KEY`          | `php artisan key:generate --show`       | preview-env                   |
| `vars.PROD_API_BASE_URL`   | Production backend URL                  | deploy-frontend               |

**Production backend secrets (set once via `flyctl secrets set --app wcf2026-backend ...`, not GitHub secrets):**

| Fly secret              | Value                                                              |
|-------------------------|--------------------------------------------------------------------|
| `APP_KEY`               | `php artisan key:generate --show`                                  |
| `APP_URL`               | Final production API URL (per ADR-0002)                            |
| `DB_CONNECTION`         | `pgsql`                                                            |
| `DB_URL`                | Production Neon connection string (use the **pooled** endpoint)    |
| `FRONTEND_URL`          | Final production SPA URL (per ADR-0002)                            |
| `SANCTUM_STATEFUL_DOMAINS` | Comma-list of SPA hosts (per ADR-0002)                          |
| `SESSION_DOMAIN`        | Per ADR-0002 (e.g. `.wcf2026.example` for Strategy A, empty for B) |
| `SESSION_SECURE_COOKIE` | `true`                                                             |
| `SESSION_SAME_SITE`     | `lax` (Strategy A) or `none` (Strategy B)                          |

Plus the **one-time provisioning checklist** (Neon project + Fly app + Cloudflare Pages project creation steps).

- [ ] **Step 2: Commit**

```powershell
git add wcf2026\docs\runbooks\deploy.md
git commit -m "docs(runbook): document required secrets and provisioning`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 7.5: Finalize ADR-0002 (cookie/domain strategy decision)

**Files:** `wcf2026/docs/adr/0002-cookie-domain-strategy.md`

- [ ] **Step 1: Decide A vs B**

Operational decision input needed from the user:
- **Strategy A** — buy a domain (~$10/yr), use `app.<domain>` and `api.<domain>`; cookies stay first-party, `SameSite=Lax`.
- **Strategy B** — use free `*.pages.dev` and `*.fly.dev` subdomains; cookies must be `SameSite=None; Secure` (cross-site), which means we may be affected by future third-party cookie deprecation.

Default recommendation: **Strategy A**, because cookie auth is materially simpler and safer with same-site cookies. But Strategy B is fully viable on the free tier.

- [ ] **Step 2: Update `0002-cookie-domain-strategy.md`** — flip status from `Proposed` to `Accepted`, fill in:
  - The chosen strategy
  - The decided domain (if A) or platform hostnames (if B)
  - The `SESSION_DOMAIN`, `SESSION_SAME_SITE`, `SESSION_SECURE_COOKIE`, `SANCTUM_STATEFUL_DOMAINS` values for production
  - The consequences (third-party cookie risk if B; cost + DNS setup if A)

- [ ] **Step 3: Update `wcf2026/backend/fly.toml`** if needed to reflect chosen `app` name (or keep `wcf2026-backend`).

- [ ] **Step 4: Commit**

```powershell
git add wcf2026\docs\adr\0002-cookie-domain-strategy.md wcf2026\backend\fly.toml
git commit -m "docs(adr): finalize cookie/domain strategy (ADR-0002)`n`nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

### Task 7.6: End-of-Phase-0 verification checklist

- [ ] All 7 workflow files exist and YAML-parse cleanly.
- [ ] `make help` (or the Windows equivalent documented in Task 1.5) lists all available commands.
- [ ] `cd wcf2026 && docker compose up -d && curl -fsS http://api.lvh.me:8080/api/v1/health` returns `{"status":"ok",...}`.
- [ ] `cd wcf2026/frontend && pnpm build && pnpm test && pnpm typecheck && pnpm lint` all succeed.
- [ ] `cd wcf2026\backend; php vendor\bin\pest --coverage --min=80; php vendor\bin\phpstan analyse --no-progress` both succeed.
- [ ] Local Sanctum cookie smoke (Task 4.5) verified ✅ and recorded in `docs/runbooks/deploy.md`.
- [ ] ADR-0001 (status: Accepted) and ADR-0002 (status: Accepted) both present.
- [ ] `(git log --oneline | Measure-Object).Count` shows the expected number of commits (one per task — roughly 35–40 commits in Phase 0).

End of Chunk 7. **Phase 0 plan complete.**

---

## Closing notes

- This plan should be executed in sequence; each chunk depends on the previous chunk's artifacts.
- Long-running operational tasks (Task 4.5 smoke test, Task 7.5 ADR decision, secret provisioning in 7.4) **require user input** — stop and ask when you reach them.
- After Phase 0, the next plan file should be `2026-05-27-wcf2026-phase-1-domain-model-plan.md`, covering the core data model migrations (users → tournaments → stages → groups → teams → fixtures → predictions, with composite FK constraints per the spec).
