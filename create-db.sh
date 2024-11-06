#!/bin/bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<- EOSQL
    CREATE DATABASE ${POSTGRES_DB:-app}_test;
    GRANT ALL PRIVILEGES ON DATABASE ${POSTGRES_DB:-app}_test TO ${POSTGRES_USER:-app};
EOSQL
