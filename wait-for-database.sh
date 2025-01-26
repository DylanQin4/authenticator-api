#!/bin/bash

set -e

host="$1"
shift
cmd="$@"

until pg_isready -h "$host" -U "${POSTGRES_USER:-postgres}"; do
  >&2 echo "PostgreSQL est indisponible - en attente..."
  sleep 5
done

>&2 echo "PostgreSQL est prêt - exécution de la commande"
exec $cmd
