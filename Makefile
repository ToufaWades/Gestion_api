# Convenience Makefile for local tasks

.PHONY: build up migrate seed fresh

build:
	docker compose build --pull

up:
	docker compose up -d --remove-orphans

migrate:
	php artisan migrate --force

fresh:
	php artisan migrate:fresh --seed

seed:
	php artisan db:seed --class=Database\\Seeders\\SeedDemoData
