#!/bin/bash

DIR="${BASH_SOURCE%/*}"
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
. "$DIR/../common/constants.sh"

cd "$PROJECT_PATH" || exit

docker exec -it jirag_app composer dump-autoload

