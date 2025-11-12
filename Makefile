# TaskMaster AI - Docker Commands
.PHONY: help build up down restart logs shell composer artisan test migrate seed fresh install

# Colors for output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[1;33m
NC := \033[0m # No Color

help: ## Show this help message
	@echo "$(BLUE)TaskMaster AI - Docker Commands$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-20s$(NC) %s\n", $$1, $$2}'

build: ## Build Docker containers
	@echo "$(BLUE)Building Docker containers...$(NC)"
	docker-compose build

up: ## Start all Docker containers
	@echo "$(BLUE)Starting Docker containers...$(NC)"
	docker-compose up -d
	@echo "$(GREEN)✓ Application started!$(NC)"
	@echo "$(YELLOW)Access the application at: http://localhost:8000$(NC)"
	@echo "$(YELLOW)Adminer (DB UI) at: http://localhost:8080$(NC)"

down: ## Stop all Docker containers
	@echo "$(BLUE)Stopping Docker containers...$(NC)"
	docker-compose down

restart: ## Restart all Docker containers
	@echo "$(BLUE)Restarting Docker containers...$(NC)"
	docker-compose restart

stop: ## Stop all Docker containers (keep data)
	docker-compose stop

logs: ## Show logs from all containers
	docker-compose logs -f

logs-app: ## Show application logs
	docker-compose logs -f app

logs-nginx: ## Show nginx logs
	docker-compose logs -f nginx

logs-postgres: ## Show PostgreSQL logs
	docker-compose logs -f postgres

shell: ## Open bash shell in app container
	docker-compose exec app sh

shell-postgres: ## Open PostgreSQL shell
	docker-compose exec postgres psql -U taskmaster -d taskmaster

composer: ## Run composer install
	docker-compose exec app composer install

composer-update: ## Run composer update
	docker-compose exec app composer update

artisan: ## Run artisan command (use: make artisan cmd="migrate")
	docker-compose exec app php artisan $(cmd)

test: ## Run tests
	docker-compose exec app php artisan test

test-coverage: ## Run tests with coverage
	docker-compose exec app php artisan test --coverage

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh database migration (drops all tables)
	docker-compose exec app php artisan migrate:fresh

seed: ## Run database seeders
	docker-compose exec app php artisan db:seed

fresh: ## Fresh migration with seed
	docker-compose exec app php artisan migrate:fresh --seed

optimize: ## Optimize Laravel application
	docker-compose exec app php artisan optimize
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

clear: ## Clear all Laravel caches
	docker-compose exec app php artisan optimize:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	docker-compose exec app php artisan cache:clear

npm-install: ## Install npm dependencies
	docker-compose exec node npm install

npm-dev: ## Run npm dev
	docker-compose exec node npm run dev

npm-build: ## Build assets for production
	docker-compose exec node npm run build

install: build up composer npm-install key migrate seed ## Complete installation
	@echo "$(GREEN)✓ Installation complete!$(NC)"
	@echo "$(YELLOW)Access the application at: http://localhost:8000$(NC)"

key: ## Generate application key
	docker-compose exec app php artisan key:generate

permissions: ## Fix storage and cache permissions
	docker-compose exec app chmod -R 775 storage bootstrap/cache
	docker-compose exec app chown -R www-data:www-data storage bootstrap/cache

status: ## Show container status
	docker-compose ps

prune: ## Remove all stopped containers and unused volumes
	docker-compose down -v
	docker system prune -af --volumes

backup-db: ## Backup PostgreSQL database
	docker-compose exec postgres pg_dump -U taskmaster taskmaster > backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)✓ Database backup created$(NC)"

restore-db: ## Restore PostgreSQL database (use: make restore-db file=backup.sql)
	docker-compose exec -T postgres psql -U taskmaster taskmaster < $(file)
	@echo "$(GREEN)✓ Database restored$(NC)"

mcp-inspector: ## Open MCP Inspector
	docker-compose exec app php artisan mcp:inspector

mcp-start: ## Start MCP server (use: make mcp-start server=task-tools)
	docker-compose exec app php artisan mcp:start $(server)
