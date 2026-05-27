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
