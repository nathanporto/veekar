BACKEND = docker compose exec backend

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache backend

logs:
	docker compose logs -f backend

migrate:
	$(BACKEND) php artisan migrate

migrate-fresh:
	$(BACKEND) php artisan migrate:fresh --seed

seed:
	$(BACKEND) php artisan db:seed

tinker:
	$(BACKEND) php artisan tinker

artisan:
	$(BACKEND) php artisan $(cmd)

composer:
	$(BACKEND) composer $(cmd)

bash:
	$(BACKEND) sh
