### This is a reference (complete) MAKE file setup
### Remove the functionalities you don't need

.PHONY: help

## --- Mandatory variables ---

docker-compose=docker compose
main-container-name=app

help: ### Display available targets and their descriptions
	@echo "Usage: make [target]"
	@echo "Targets:"
	@awk 'BEGIN {FS = ":.*?### "}; /^[a-zA-Z_-]+:.*?### / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
	@echo ""


## --- General ---

# General git commands
include vendor/team-mate-pro/make/git/MAKE_GIT_v1

# Docker
include vendor/team-mate-pro/make/docker/MAKE_DOCKER_v1

# Claude Code
include vendor/team-mate-pro/make/claude/MAKE_CLAUDE_v1

# --- Backend ---


# PHPCS
include vendor/team-mate-pro/make/phpcs/MAKE_PHPCS_v1

# PHPUNIT
include vendor/team-mate-pro/make/phpunit/MAKE_PHPUNIT_v1

# PHPSTAN
include vendor/team-mate-pro/make/phpstan/MAKE_PHPSTAN_v1

## --- Mandatory aliases ---

start: ### Full start and rebuild of the container
	$(docker-compose) build
	$(docker-compose) up -d

fast: ### Fast start already built containers
	$(docker-compose) up -d

stop: ### Stop all existing containers
	$(docker-compose) down

check: ### [c] Should run all mandatory checks that run in CI and CD process
	make phpcs
	make phpstan
	make tests_unit

check_fast: ### [cf] Should run all mandatory checks that run in CI and CD process skipping heavy ones like functional tests
	make phpcs_fix
	make phpcs
	make phpstan
	make tests_unit

fix: ### [f] Should run auto fix checks that run in CI and CD process
	make phpcs_fix

tests: ### [t] Run all tests defined in the project
	make tests_unit

## --- Project related scripts ---

c: check
cf: check_fast
f: fix
t: tests

## Local

tag: ### Create and push next incremental git tag (e.g., 1.0.1 -> 1.0.2), push current branch, and manage dev-master tag
	@echo "Fetching existing tags..."
	@git fetch --tags 2>/dev/null || true
	@CURRENT_BRANCH=$$(git rev-parse --abbrev-ref HEAD); \
	echo "Current branch: $$CURRENT_BRANCH"; \
	echo "Pushing branch to remote..."; \
	git push origin $$CURRENT_BRANCH || true; \
	echo "Removing dev-master tag from remote..."; \
	git push origin :refs/tags/dev-master 2>/dev/null || echo "No remote dev-master tag to remove"; \
	echo "Removing dev-master tag locally..."; \
	git tag -d dev-master 2>/dev/null || echo "No local dev-master tag to remove"; \
	LATEST_TAG=$$(git tag -l | grep -E '^[0-9]+\.[0-9]+\.[0-9]+$$' | sort -V | tail -n 1); \
	if [ -z "$$LATEST_TAG" ]; then \
		NEXT_TAG="1.0.0"; \
	else \
		MAJOR=$$(echo $$LATEST_TAG | cut -d. -f1); \
		MINOR=$$(echo $$LATEST_TAG | cut -d. -f2); \
		PATCH=$$(echo $$LATEST_TAG | cut -d. -f3); \
		NEXT_PATCH=$$((PATCH + 1)); \
		NEXT_TAG="$$MAJOR.$$MINOR.$$NEXT_PATCH"; \
	fi; \
	echo "Latest tag: $$LATEST_TAG"; \
	echo "Creating incremental tag: $$NEXT_TAG"; \
	git tag $$NEXT_TAG && \
	git push origin $$NEXT_TAG && \
	echo "Tag $$NEXT_TAG created and pushed successfully!"; \
	echo "Creating dev-master tag..."; \
	git tag dev-master && \
	git push origin dev-master && \
	echo "dev-master tag created and pushed successfully!"
