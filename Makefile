up:
	docker compose up -d

build:
	docker compose build

down:
	docker compose down

exec:
	docker compose exec php sh

install:
	docker compose exec php composer install

test:
	docker compose exec php composer check:test

codestyle:
	docker compose exec php composer check:codestyle

infection:
	docker compose exec php composer check:infection