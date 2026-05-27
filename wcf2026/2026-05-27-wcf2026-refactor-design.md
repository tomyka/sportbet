# WCF2026 — Sport Prediction Platform Refactor: Design Spec

**Date:** 2026-05-27
**Author:** Brainstorming session (tomyka + Copilot CLI)
**Status:** Approved by user; pending spec-document review
**Supersedes:** `tomyka/el2025` (Laravel 11 monolith, Blade UI, shared hosting)

---

## 1. Purpose & goals

Refactor the existing **el2025** basketball totalizer into a **versatile, modern, multi-tournament prediction platform** that can host any football or basketball competition (FIFA World Cup, UEFA Euro, EuroLeague regular season + playoffs, custom tournaments).

**Non-negotiable goals:**
1. Versatile tournament structure — bracket (groups → knockout) **and** round-robin league → knockout, driven by data, not code per tournament.
2. Modern, mobile- and desktop-friendly UI (responsive + installable PWA).
3. Clear **backend / frontend** separation (REST API + SPA).
4. Comprehensive automated test suite with CI gates.
5. CI/CD pipeline (lint, test, security scan, deploy, preview envs).
6. Security best practices (CSRF, HttpOnly cookies, rate limiting, default-deny authz, audit log, dependency scanning).
7. Runs entirely on **free cloud infrastructure**.
8. Professional, maintainable codebase.

**Explicit non-goals (this iteration):**
- Real payments (group fees are informational only).
- Automatic real-time results from a paid sports-data API (manual admin entry; integration point left clean).
- Migration of historical data from `el2025` (old app stays read-only on its current host).
- Multi-language UI on day one (architected for i18n; ship English).
- Real-time leaderboard pushes (polling is sufficient).

---

## 2. Audit of the legacy system (`tomyka/el2025`)

### 2.1 Stack today
Laravel 11, PHP 8.2, Blade + Alpine.js + Tailwind v3, Vite, Laravel Breeze auth, PHPUnit. Docker compose present. Default Laravel README. Apparently deployed on Apache shared hosting (root `.htaccess`).

### 2.2 Functional capabilities (worth preserving)
- User auth and profiles.
- Private competitions ("groups") with informational fees & reward ratios, guest members, invite/leave.
- Three prediction modes: **score**, **standings/bracket position**, **survival**.
- Admin: teams, events (rounds), games, results, group/competition admin, messages, scoring matrix, point recalculation.
- Audit tables for logins and prediction changes.
- Per-game odds and odds-weighted points.

### 2.3 Defects & smells (must be fixed in the new system)

| # | Issue | Severity | Resolution in new design |
|---|---|---|---|
| 1 | Almost every route is public — only `/profile` PATCH/DELETE behind `auth`; **admin routes unprotected** | Critical | Sanctum SPA cookie auth + Laravel Policies, default-deny base policy, role-gated admin via `tournament_memberships.role` |
| 2 | FK columns declared as `tinyInteger` with `constrained()` — silently doesn't create real FKs, breaks past 127 rows; `team_id` declared `smallInteger` in one place | Critical | All FKs `bigint` via Laravel `foreignId()->constrained()` with explicit `ON DELETE` semantics |
| 3 | Tournament shape hardcoded in columns: `group_position / last16 / quarterfinal / semifinal / final` | Critical | Generic `stages` + `rounds` + `groups` + stage strategy classes |
| 4 | Term "groups" overloaded: bracket group **and** private competition | High | Renamed: `groups` = bracket groups within a stage; `competitions` = private leaderboards |
| 5 | No API layer; pure Blade; no path to a modern SPA/PWA | High | REST `/api/v1` + React SPA |
| 6 | `app/Services/` nearly empty; controllers are fat | High | Domain-oriented modules; controllers thin; services/policies/jobs separated |
| 7 | No tests beyond `TestCase.php`; `phpunit-report.xml` committed (smell) | High | Comprehensive pyramid (see §6); reports excluded from git |
| 8 | No `.github/workflows` — no CI/CD | High | 7 workflows (lint, test, e2e, security, deploy-be, deploy-fe, preview-env) |
| 9 | `docker-compose - Copy.yml` committed; `.env.example` committed (OK) but no infra-as-code | Medium | Clean `docker-compose.yml`, Fly `fly.toml`, Cloudflare Pages `wrangler.toml`, IaC in `infra/` |
| 10 | Naming inconsistencies (`teaminsert` vs `insertGame`) | Medium | RESTful resource naming throughout |
| 11 | Mass-assignment risk; no Form Requests visible | Medium | Every write endpoint backed by a `FormRequest` + Policy |
| 12 | Audit split across 3 tables, not all actions logged | Low | Single append-only `audit_events` covering result mutations, prediction edits, admin actions, logins |
| 13 | `binary('generated')` on `prediction_results` — opaque | Low | Replaced with explicit `submitted_at`, `locked_at` |
| 14 | No rate limiting / no email-throttling for login or password reset | Medium | Throttle middleware: 5/min/IP login, 10/h/email; password reset 3/h/email |
| 15 | Shared-host deployment, no scaling story, no backups | Medium | Fly.io container, Neon Postgres with point-in-time recovery |

---

## 3. Architecture overview

Two services, one monorepo, deployed independently:

```
┌──────────────────────────┐         ┌──────────────────────────┐         ┌─────────────────────┐
│  React SPA (PWA)         │  HTTPS  │  Laravel 11 API          │  TLS    │  Neon Postgres      │
│  Cloudflare Pages (CDN)  │ ──────▶ │  Fly.io (Docker)         │ ──────▶ │  Free tier, PITR    │
│  TS + Vite + Tailwind +  │ cookies │  PHP 8.3, Sanctum SPA    │         │                     │
│  TanStack Query + Router │ ◀────── │  REST /api/v1/*          │         └─────────────────────┘
└──────────────────────────┘ JSON    └──────────────────────────┘
                                            │
                                            ├── Mail (Resend free SMTP / Mailpit in dev)
                                            ├── Queue: Laravel database driver (Redis-free)
                                            └── Scheduler: in-container cron for points recalc & cleanup
```

### 3.1 Rationale
- **Cloudflare Pages**: free, unlimited bandwidth, global CDN, instant cold start.
- **Fly.io free allowance**: always-on small VM; Docker, no sleep, simple secrets, release-cmd for migrations.
- **Neon Postgres**: generous always-free tier, database branching enables real preview envs in CI.
- **Sanctum SPA cookie auth**: HttpOnly + CSRF protected, no JWT rotation pain; ideal for first-party SPAs.

### 3.2 Backend modules (replace today's fat controllers)
| Module | Responsibility |
|---|---|
| `Auth` | register, login, logout, email verify, password reset, 2FA-ready |
| `Tournaments` | tournament definitions, stages, groups, rounds (multi-tenant core) |
| `Teams` | team catalog, scoped per tournament |
| `Fixtures` | scheduled matches, real results |
| `Predictions` | score / bracket / survival; deadline enforcement |
| `Scoring` | configurable rule engine; idempotent recalculation on result change |
| `Leaderboards` | per-competition, per-tournament, per-mode rankings |
| `Competitions` | private leaderboards (informational fees), invitations, memberships, messages |
| `Admin` | role-gated management endpoints |
| `Audit` | append-only log |

### 3.3 Frontend slices
`auth/`, `tournaments/`, `predictions/`, `leaderboards/`, `competitions/`, `profile/`, `admin/`, `shared/` (UI kit, API client, hooks). State: **TanStack Query** for server state; minimal **Zustand** for ephemeral UI state. PWA via `vite-plugin-pwa` (installable, offline shell, cached fixtures).

### 3.4 Cross-cutting concerns
- Form Request validation (HTTP) → strategy class (domain) → DB constraints (last line).
- Frontend zod schemas generated from OpenAPI via `openapi-typescript`.
- Default-deny base Policy; all writes require an explicit policy method.
- `ThrottleRequests` per route group.
- Structured JSON logs to stdout, captured by Fly.io.

---

## 4. Data model

All FKs are `bigint` (Laravel `foreignId()`); times are `timestamptz` UTC; soft-deletes on long-lived editable resources; `jsonb` for flexible config & breakdowns; `tournament_id` denormalized on tournament-scoped tables, plus a global Eloquent scope to prevent cross-tenant leaks.

### 4.1 Multi-tenant core
- **`tournaments`** — `id, name, slug, sport (enum: football|basketball|other), status (draft|open|locked|finished), starts_at, ends_at, owner_user_id, settings jsonb`
- **`tournament_memberships`** — `user_id, tournament_id, role (player|admin|owner)`

### 4.2 Generic tournament structure
- **`stages`** — `tournament_id, name, ord, type (group_stage|round_robin|knockout), config jsonb` (teams_advance, legs, tie-break rules, etc.)
- **`groups`** — `stage_id, name` (only for group-based stages)
- **`rounds`** — `stage_id, name, ord, deadline_at`
- **`teams`** — `tournament_id, name, short_name, logo_url`
- **`team_stage_entries`** — `team_id, stage_id, group_id?, seed`
- **`fixtures`** — `round_id, kickoff_at, home_team_id, away_team_id, status (scheduled|live|finished|cancelled), home_score, away_score, winner_team_id, neutral_venue, leg`

Supports World/Euro Cup, EuroLeague regular season + playoffs, and arbitrary shapes — configured per tournament, no code change.

### 4.3 Predictions (one table per mode)
- **`score_predictions`** — `user_id, fixture_id, home_score, away_score, submitted_at` (unique `(user_id, fixture_id)`; server rejects edits past `kickoff_at`)
- **`bracket_predictions`** — `user_id, stage_id, team_id, payload jsonb` (e.g., `{predicted_group_position: 2}` or `{advances_to: "semifinal"}`); a per-stage **strategy class** validates payload shape against `stages.type`; locked at stage start
- **`survival_picks`** — `user_id, round_id, team_id, eliminated_at?` (one pick per round, locked at `round.deadline_at`)

### 4.4 Scoring engine
- **`point_rules`** — `tournament_id, mode, key (exact_score|correct_winner|goal_diff|survived_round|...), value decimal`
- **`points_matrix`** — `tournament_id, home_diff, away_diff, points` (per-tournament, replaces the old global `points_calculations`)
- **`score_results`** — `user_id, fixture_id, breakdown jsonb, total_points`
- **`bracket_results`** — `user_id, stage_id, total_points`
- **`survival_results`** — `user_id, round_id, points`
- **`leaderboard_entries`** — `(tournament_id, competition_id?, user_id, mode, total_points, rank)` — denormalized, refreshed by a queued job whenever a result changes (idempotent)

### 4.5 Private competitions
- **`competitions`** — `tournament_id, name, fee int, reward_description, owner_user_id, invite_code, is_public`
- **`competition_memberships`** — `user_id, competition_id, status (invited|active|banned), is_guest`
- **`competition_messages`** — `competition_id, body, posted_by, is_active`

### 4.6 Identity, audit, infra
- **`users`** — Laravel default + `display_name, time_zone, locale, is_global_admin`
- **`audit_events`** — `actor_user_id, action, subject_type, subject_id, changes jsonb, occurred_at, ip, user_agent` (append-only)
- **`personal_access_tokens`** — Sanctum default
- Laravel default `sessions / cache / jobs / failed_jobs / password_reset_tokens`

### 4.7 Design decisions worth flagging
1. Term split removed naming collision (`groups` vs `competitions`).
2. Denormalized `tournament_id` + global scope prevents cross-tenant data leaks.
3. UTC in DB; user TZ only at render.
4. Predictions never deletable, only locked, to preserve integrity.
5. Postgres `jsonb` with check constraints where useful; structural validation in PHP value objects.

---

## 5. API surface

Base: `/api/v1`, JSON only, Sanctum SPA cookie auth, CSRF protected. Default rate limit `60/min`; auth endpoints `5/min`. OpenAPI 3.1 spec generated from PHP attributes (Scramble); committed to `docs/api/openapi.yaml`; CI verifies the committed spec matches code. Frontend types & zod schemas derived via `openapi-typescript`.

### 5.1 Endpoints (abbreviated)

```
# Auth
POST  /auth/register            POST  /auth/login            POST  /auth/logout
POST  /auth/email/verify        POST  /auth/password/forgot  POST  /auth/password/reset
GET   /auth/me                  PATCH /auth/me               POST  /auth/password
GET   /sanctum/csrf-cookie

# Tournaments (read = any auth'd; write = tournament admin/owner)
GET    /tournaments
POST   /tournaments                                  (global admin)
GET    /tournaments/{slug}
PATCH  /tournaments/{slug}
GET    /tournaments/{slug}/stages
GET    /tournaments/{slug}/teams
GET    /tournaments/{slug}/fixtures?round={id}
GET    /tournaments/{slug}/leaderboard?mode=&competition_id=

# Predictions (server-locked at deadline)
GET/PUT  /tournaments/{slug}/predictions/score      |  PUT /fixtures/{id}/prediction
GET/PUT  /tournaments/{slug}/predictions/bracket    |  PUT /stages/{id}/bracket-prediction
GET/PUT  /tournaments/{slug}/predictions/survival   |  PUT /rounds/{id}/survival-pick
GET      /tournaments/{slug}/predictions/history

# Competitions
GET/POST  /competitions
GET/PATCH/DELETE  /competitions/{id}
POST  /competitions/{id}/join     POST /competitions/{id}/leave
POST  /competitions/{id}/members/{userId}/kick
GET   /competitions/{id}/leaderboard?mode=
POST  /competitions/{id}/messages

# Admin (tournament-scoped, role-gated)
POST/PATCH/DELETE  /tournaments/{slug}/stages|groups|rounds|teams|fixtures
PUT                /fixtures/{id}/result            (triggers scoring job)
GET/PATCH          /tournaments/{slug}/point-rules
GET/PATCH          /tournaments/{slug}/points-matrix
GET                /tournaments/{slug}/audit
```

### 5.2 Conventions
- **Errors:** RFC 7807 `application/problem+json` (`{type, title, status, detail, errors}`).
- **Pagination:** cursor-based (`?cursor=&limit=50`).
- **Caching:** `ETag` + `Cache-Control` on reads; SPA uses stale-while-revalidate.
- **Idempotency:** `PUT` for upserts; `Idempotency-Key` header accepted on result-mutation endpoints.
- **Versioning:** `/v1` in URL; breaking changes ship as `/v2`, co-deployed during deprecation.
- **Real-time:** out of scope; SPA polls leaderboards on focus + 30s background refresh during live rounds.

---

## 6. Testing, CI/CD, security, observability

### 6.1 Testing

**Backend (Pest / PHPUnit)**
- **Unit** — point-rule engine, scoring strategies, prediction-lock policies, value objects (pure, no DB).
- **Integration** — Form Requests → controllers → DB, `RefreshDatabase` against ephemeral Postgres in CI.
- **Contract** — `Spectator` validates every response against the OpenAPI schema (spec is source of truth).
- **Authorization** — per-policy matrix `(actor_role × resource_owner) → expected outcome`.
- **Mutation testing** — Infection PHP, weekly job, non-blocking.

**Frontend (Vitest + Playwright)**
- **Unit** — utilities, hooks, zod schemas.
- **Component** — every page-level component with happy path + key error states; API mocked via MSW handlers generated from OpenAPI.
- **E2E** — real backend in Docker compose; flows: register → verify → login → join competition → submit each prediction mode → admin enters result → leaderboard updates → logout; mobile viewports included.
- **Visual regression** — Playwright screenshots, gated, non-blocking.
- **Accessibility** — `@axe-core/playwright` on every E2E page; CI fails on serious violations.

**Load smoke** — nightly k6 (50 RPS for 1 min on leaderboard + prediction submit); fails if p95 > 500ms.

**Coverage gates:** backend ≥ 80% line / 75% branch on `app/`; frontend ≥ 75% on `src/`.

### 6.2 CI/CD (GitHub Actions)

| Workflow | Trigger | Job |
|---|---|---|
| `backend-ci.yml` | PR, main | Pint, PHPStan L8, Pest (Postgres service), coverage upload |
| `frontend-ci.yml` | PR, main | eslint, `tsc --noEmit`, vitest, build, coverage |
| `e2e.yml` | PR, main | docker compose stack + Playwright (3 browsers × 2 viewports) |
| `security.yml` | weekly + PR | `composer audit`, `pnpm audit`, CodeQL, Trivy, Gitleaks |
| `deploy-backend.yml` | push to main | build & push image, `flyctl deploy`, `php artisan migrate --force` via release-cmd, auto-rollback on health-check fail |
| `deploy-frontend.yml` | push to main | build SPA, publish to Cloudflare Pages |
| `preview-env.yml` | PR open / close | Neon DB branch + Fly preview app + CF Pages preview; URL posted as PR comment; torn down on PR close |

**Branching:** trunk-based, short-lived PR branches, `main` always deployable. Conventional Commits; auto-changelog via release-please.

### 6.3 Security
- HTTPS only; HSTS; CSP (script-src self + nonce); X-Frame-Options DENY; COOP/COEP.
- Sanctum cookies: `HttpOnly`, `Secure`, `SameSite=Lax`, sliding-refresh on activity.
- Passwords: Argon2id (Laravel 11 default).
- Email verification required before any write action.
- Throttling: 5/min/IP login, 10/h/email; 3/h/email password reset; 60/min/user default.
- All mutating endpoints CSRF-protected; all write payloads schema-validated.
- Authz via Laravel Policies; **default-deny** base policy.
- Secrets in Fly.io + GitHub Actions secrets; never committed; `.env.example` only.
- Dependabot + Renovate enabled.
- Audit log entries for every admin action and result mutation; immutable.
- DB-level tenant isolation: `tournament_id` FKs `ON DELETE RESTRICT`; global Eloquent scope.

### 6.4 Observability
- Structured JSON logs to stderr → Fly.io log stream; `X-Request-ID` propagated.
- Sentry (free tier) for backend + frontend error reporting.
- UptimeRobot free hitting `/api/v1/health` every 5 min.
- `php artisan health:check` covers DB, queue, mailer, disk.

---

## 7. Repo layout

```
sportbet/                           (existing git repo)
├─ wcf2026/                         NEW project root
│  ├─ backend/                      Laravel 11 API
│  │  ├─ app/{Domain,Http,Models,Policies,Services,Jobs,Console}
│  │  ├─ database/{migrations,factories,seeders}
│  │  ├─ tests/{Unit,Feature,Contract}
│  │  ├─ openapi/                   generated spec
│  │  └─ Dockerfile  fly.toml  composer.json
│  ├─ frontend/                     React + TS SPA/PWA
│  │  ├─ src/{app,features/{auth,tournaments,predictions,leaderboards,competitions,profile,admin},shared}
│  │  ├─ tests/{unit,e2e}
│  │  └─ public/  vite.config.ts  package.json  tsconfig.json
│  ├─ docs/
│  │  ├─ architecture.md   adr/0001-*.md ...
│  │  ├─ api/openapi.yaml
│  │  └─ runbooks/{deploy.md,incident.md,scoring-recalc.md}
│  ├─ infra/{fly,cloudflare-pages,github-actions}
│  ├─ docker-compose.yml
│  ├─ .env.example  .editorconfig  Makefile
│  └─ README.md
├─ .github/workflows/               7 workflows
├─ el2025/                          legacy, untouched
└─ el2026-27/                       legacy, untouched
```

`Makefile` targets: `make up`, `make test`, `make lint`, `make e2e`, `make seed-demo`, `make typegen`.

Local dev: `git clone … && cd wcf2026 && make up` then `make seed-demo` → demo tournament with 32 teams, two stages, sample users. Frontend `pnpm dev` hot-reloads against local API. Pre-commit hook (Husky): Pint + Prettier + ESLint + typecheck on staged files only.

---

## 8. Phased rollout

Each phase ends green (CI passing + deployed preview env + demo-able feature). No phase merges until tests pass.

| Phase | Outcome |
|---|---|
| **0. Bootstrap** | Repo skeleton, both apps "hello world", Dockerfile, docker-compose, CI skeleton (lint+test), Fly app + Neon DB + CF Pages provisioned, end-to-end deploy works |
| **1. Auth & users** | Sanctum SPA cookie auth end-to-end, register/login/verify/reset, profile, `audit_events` wired, full auth test coverage |
| **2. Tournament & teams CRUD** | Global-admin creates tournament/stages/groups/rounds/teams/fixtures; public read APIs; SPA tournament browser + admin skeleton |
| **3. Score predictions + scoring engine** | Submit/update score prediction (locked at kickoff), admin enters real results, points recalc job, leaderboard endpoint + UI |
| **4. Bracket predictions** | Stage strategy classes; bracket UI per stage type (group_stage / round_robin / knockout); scoring + leaderboard |
| **5. Survival picks** | Pick per round, elimination logic, survival leaderboard |
| **6. Private competitions** | Create competition, invite codes, join/leave, per-competition leaderboards, messages |
| **7. PWA polish + a11y** | Installable, offline shell, cached fixtures; Lighthouse ≥ 90 all categories; axe clean |
| **8. Hardening & launch** | k6 load smoke, Sentry wired, runbooks complete, Neon PITR backup verified, security workflow green, README + user docs |

---

## 9. Open items (captured, non-blocking)

- **Automatic result fetcher**: future integration point — `ResultSource` interface with a `ManualAdmin` implementation today; a paid sports API could be slotted in later without touching prediction/scoring code.
- **i18n**: copy lives in a single `messages.ts` (frontend) and Laravel translation files (backend) from day one, so adding a locale is a swap, not a refactor.
- **Real-time leaderboard**: optional Server-Sent Events upgrade if polling proves insufficient.
- **2FA**: schema-ready (Sanctum + a `two_factor_*` column set on users), UI deferred.

---

## 10. Glossary

| Term | Meaning |
|---|---|
| Tournament | A single event instance (e.g., "FIFA World Cup 2026") |
| Stage | A phase within a tournament (group_stage, round_robin, knockout) |
| Group | A bracket group inside a group_stage (e.g., "Group A") |
| Round | A matchday or knockout round inside a stage |
| Fixture | A scheduled match between two teams |
| Competition | A private leaderboard among users predicting on the same tournament (was "group" in el2025) |
| Score prediction | User's predicted scoreline for a fixture |
| Bracket prediction | User's predicted progression/standing at stage level |
| Survival pick | User's per-round pick of a team to "survive" |
