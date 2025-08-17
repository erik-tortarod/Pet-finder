PROJECT_DIR = projects/web

.PHONY: help install db down install-dependencies run

# Default target
help: ## Show this help message
	@echo "Pet-finder Commands:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

install-dependencies: ## Install system dependencies (PHP, Node.js, Composer)
	@./infra/scripts/install-dependencies.sh

install: ## Install project dependencies (composer + npm + build)
	@./infra/scripts/install-project.sh

db: ## Setup MySQL database with Docker
	@./infra/scripts/setup-database.sh

down: ## Stop and remove database container
	@./infra/scripts/stop-database.sh

run: ## Start Symfony development server
	@echo "Starting Symfony development server..."
	cd $(PROJECT_DIR) && symfony server:start
