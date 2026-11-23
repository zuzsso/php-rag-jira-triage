#!/bin/bash

DIR="${BASH_SOURCE%/*}"
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
. "$DIR/../common/constants.sh"

docker compose exec -T "${PHP_APP_CONTAINER_NAME}" php "${CONTROLLER_PATH}"CLI/Fetch/FetchClosedJiraTickets.php
