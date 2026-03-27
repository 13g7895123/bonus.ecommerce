#!/bin/bash
set -e

ENV=${1:-production}
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
DOCKER_DIR="$PROJECT_DIR/docker"
ENV_FILE="$DOCKER_DIR/.env"
ENV_TEMPLATE="$DOCKER_DIR/envs/.env.${ENV}"
COMPOSE_FILE="$DOCKER_DIR/docker-compose.yml"

# ─── 確認環境參數所對應的範本存在 ─────────────────────────────────────────────
if [ ! -f "$ENV_TEMPLATE" ]; then
  echo "Error: Environment template not found: $ENV_TEMPLATE"
  exit 1
fi

# ─── 檢查專案是否正在運行，若是則完整停止（含網路移除）─────────────────────
RUNNING=$(docker compose --env-file "${ENV_FILE:-$ENV_TEMPLATE}" -f "$COMPOSE_FILE" ps -q 2>/dev/null | wc -l)
if [ "$RUNNING" -gt 0 ]; then
  echo "Project is currently running. Stopping all containers and removing networks ..."
  docker compose --env-file "${ENV_FILE:-$ENV_TEMPLATE}" -f "$COMPOSE_FILE" down --remove-orphans
  echo "Project stopped and networks removed."
  echo ""
fi

# ─── 從範本同步 docker/.env，但保留後端 .env 中已填寫的 SMS API 憑證 ─────────
echo "Syncing .env from $ENV_TEMPLATE ..."

# 定義需要智慧保留的 SMS API 金鑰清單（存在且非空則不覆蓋）
SMS_KEYS=(TWILIO_ACCOUNT_SID TWILIO_AUTH_TOKEN TWILIO_VERIFY_SERVICE_SID)

# 備份舊的 .env（已有值）
OLD_ENV_TMP="${ENV_FILE}.pre_deploy.$$"
[ -f "$ENV_FILE" ] && cp "$ENV_FILE" "$OLD_ENV_TMP"

# 以範本為基礎覆蓋 .env
cp "$ENV_TEMPLATE" "$ENV_FILE"

# 對 SMS API 金鑰執行智慧合併：舊值非空則保留，否則使用範本值
if [ -f "$OLD_ENV_TMP" ]; then
  for key in "${SMS_KEYS[@]}"; do
    old_val=$(grep "^${key}=" "$OLD_ENV_TMP" | head -1 | cut -d'=' -f2-)
    if [ -n "$old_val" ]; then
      if grep -q "^${key}=" "$ENV_FILE"; then
        sed -i "s|^${key}=.*|${key}=${old_val}|" "$ENV_FILE"
      else
        echo "${key}=${old_val}" >> "$ENV_FILE"
      fi
      echo "  [preserve] ${key} (existing value kept)"
    else
      echo "  [template] ${key} (using template value)"
    fi
  done
  rm -f "$OLD_ENV_TMP"
fi

echo "Starting [$ENV] environment ..."
docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" up -d --build

echo "Done. Containers are running."
