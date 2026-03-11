#!/bin/bash
set -e

ENV=${1:-production}
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
DOCKER_DIR="$PROJECT_DIR/docker"
ENV_FILE="$DOCKER_DIR/.env"
ENV_TEMPLATE="$DOCKER_DIR/envs/${ENV}.env"
COMPOSE_FILE="$DOCKER_DIR/docker-compose.yml"

# 確認環境參數所對應的範本存在
if [ ! -f "$ENV_TEMPLATE" ]; then
  echo "Error: Environment template not found: $ENV_TEMPLATE"
  exit 1
fi

# 若 docker/.env 不存在，從對應環境的範本複製
if [ ! -f "$ENV_FILE" ]; then
  echo "No .env found in docker/, copying from $ENV_TEMPLATE ..."
  cp "$ENV_TEMPLATE" "$ENV_FILE"
fi

echo "Starting [$ENV] environment ..."
docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" up -d --build

echo "Done. Containers are running."
