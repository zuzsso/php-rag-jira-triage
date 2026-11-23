#!/bin/bash

# shellcheck disable=SC2034

SCRIPT_PATH=$(dirname "$(readlink -f "$0")");
PROJECT_PATH="$SCRIPT_PATH/../../"

PHP_APP_CONTAINER_NAME="app"
PHP_APP_PROJECT_ROOT_DIR="/var/www/html/"
CONTROLLER_PATH="${PHP_APP_PROJECT_ROOT_DIR}"src/Controller/