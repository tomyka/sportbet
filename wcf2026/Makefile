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
