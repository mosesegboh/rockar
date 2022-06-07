# Shell to use for make
# Replace with the path to your shell binary of choice
SHELL := /bin/bash

# Pull in .env file for environment variables
include .env
export $(shell sed 's/=.*//' .env)

define get_docker_id_by_name
$(shell docker inspect --format="{{.Id}}" "$1")
endef

.PHONY: up stop down rebuild mysql db_import db_import_core_config db_import_financing db_import_uat dev_tools dev_tools_install dev_tools_finance

# Docker compose tasks
up:
	docker-compose up --detach

stop:
	docker-compose stop

down:
	docker-compose down --remove-orphans

rebuild:
	docker-compose up --detach --force-recreate --renew-anon-volumes --build

# mysql
mysql:
	docker exec -it $(call get_docker_id_by_name,rockar-mysql) mysql -uroot -p$(MYSQL_ROOT_PASSWORD) $(MYSQL_DATABASE)

# PHP
php:
	docker exec -it $(call get_docker_id_by_name,rockar-php-fpm) $(SHELL)

# DB set up
db_import: db_import_uat db_import_core_config

db_import_uat:
	docker exec -i $(call get_docker_id_by_name,rockar-mysql) mysql -uroot -p$(MYSQL_ROOT_PASSWORD) $(MYSQL_DATABASE) < $(UAT_DB_DUMPFILE)

db_import_core_config:
	docker exec -i $(call get_docker_id_by_name,rockar-mysql) mysql -uroot -p$(MYSQL_ROOT_PASSWORD) $(MYSQL_DATABASE) < ./mysql/sql/update_core_config_data.sql

# This is a workaround to make sure we can see models in the carousel in case DLM sync hasn't happened yet.
db_import_dlm_sync_patch:
	docker exec -i $(call get_docker_id_by_name,rockar-mysql) mysql -uroot -p$(MYSQL_ROOT_PASSWORD) $(MYSQL_DATABASE) < ./mysql/sql/update_rockar_financing_options_terms.sql

# Dev Tools
dev_tools:
	docker exec -it $(call get_docker_id_by_name,rockar-dev-tools) $(SHELL)

dev_tools_init: dev_tools_install dev_tools_finance

dev_tools_install:
	docker exec -it $(call get_docker_id_by_name,rockar-dev-tools) $(SHELL) -c "composer clearcache && \
composer update --prefer-source && \
composer run-script post-install-cmd -vvv -- --redeploy $ \
composer copy && \
cd /var/www/web/skin/frontend/rockar && \
npm install && \
gulp compile --hard-lint --dev && \
cd /var/www/web/skin/adminhtml/default/rockar && \
npm install && \
gulp compile --dev"

dev_tools_finance:
	docker exec -it $(call get_docker_id_by_name,rockar-dev-tools) php /var/www/web/shell/rockar/financing/financing_calculation.php -- regenerate