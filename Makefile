.PHONY: run-local run-local-cron run-dev stop-local stop-dev rm-local rm-dev create-network rm-network database migrations seeders rm-database rm-migrations rm-seeders app\:up app\:logs app\:sh app\:build app\:build\:all local\:build local\:sh app\:start app\:stop db\:create db\:drop db\:status db\:migrate db\:migrate\:undo db\:seed db\:seed\:undo db\:reset test\:db\:create test\:run test\:unit\:run test\:integration\:run

# Define targets
run-local:
	@docker network create backend_test || true
	docker compose -f ./docker/docker-compose.local.yml --project-directory ./ -p backend_api down && \
	docker compose -f ./docker/docker-compose.local.yml --project-directory ./ -p backend_api build --no-cache && \
	docker compose -f ./docker/docker-compose.local.yml --project-directory ./ -p backend_api up -d db app

# Define targets
run-dev:
	@docker network create backend_test_dev || true
	docker compose -f ./docker/docker-compose.development.yml --project-directory ./ -p backend_api_dev down && \
	docker compose -f ./docker/docker-compose.development.yml --project-directory ./ -p backend_api_dev build --no-cache && \
	docker compose -f ./docker/docker-compose.development.yml --project-directory ./ -p backend_api_dev up -d db app

generate-key:
	@docker exec backend_api php artisan key:generate
stop-local:
	@docker compose -f ./docker/docker-compose.local.yml --project-directory ./ -p backend_api down

rm-local:
	@docker compose -f ./docker/docker-compose.local.yml --project-directory ./ -p backend_api down -v --remove-orphans


create-network:
	@docker network create backend_api

rm-network:
	@docker network rm backend_api

migrations:
	@docker exec backend_api php artisan migrate

seeders:
	@docker exec backend_api php artisan db:seed

Re-seed:
	@docker exec backend_api php artisan migrate:fresh --seed

test:
	@docker exec backend_api php artisan test


# generate-key-dev:
# 	@docker exec backend_api_dev php artisan key:generate

# stop-dev:
# 	@docker compose -f ./docker/docker-compose.development.yml --project-directory ./ -p backend_api_dev down

# rm-dev:
# 	@docker compose -f ./docker/docker-compose.development.yml --project-directory ./ -p backend_api_dev down -v --remove-orphans

# create-network_dev:
# 	@docker network create backend_api_dev

# rm-network_dev:
# 	@docker network rm backend_api_dev


# migrations_dev:
# 	@docker exec backend_api_dev php artisan migrate

# seeders_dev:
# 	@docker exec backend_api_dev php artisan db:seed

# Re-seed_dev:
# 	@docker exec backend_api_dev php artisan migrate:fresh --seed

# test_dev:
# 	@docker exec backend_api_dev php artisan test
