#!/bin/bash

DIR="${BASH_SOURCE%/*}"
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
. "$DIR/../common/constants.sh"

cd "$PROJECT_PATH" || exit

docker compose up -d

