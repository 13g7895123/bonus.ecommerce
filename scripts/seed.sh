#!/bin/bash
set -e

CONTAINER="ecommerce-backend-1"

# 確認 container 是否在執行中
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER}$"; then
  echo "Error: Container '$CONTAINER' is not running."
  echo "Please start the environment first: ./scripts/deploy.sh"
  exit 1
fi

echo "Running seeder in container: $CONTAINER ..."
docker exec "$CONTAINER" php spark db:seed InitialDataSeeder

echo "Seed complete."
