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

# ─── 無條件完整停止本專案（含 stopped 狀態的容器與網路）────────────────────
echo "Stopping and removing all project containers and networks ..."
docker compose --env-file "${ENV_FILE:-$ENV_TEMPLATE}" -f "$COMPOSE_FILE" down --remove-orphans 2>/dev/null || true
echo "Project cleanup done."
echo ""

# ─── Port 衝突清除 ────────────────────────────────────────────────────────────
# down 之後若某 port 仍被佔用，代表是本專案以外的 Docker 容器殘留
# → 找出該容器並強制移除；若是非 Docker 程序則中止並提示

release_port() {
  local port="$1" label="$2"

  # 確認是否被佔用（同時支援 ss / netstat）
  local in_use=false
  if command -v ss &>/dev/null; then
    ss -lntp 2>/dev/null | grep -q ":${port}[^0-9]" && in_use=true
  elif command -v netstat &>/dev/null; then
    netstat -lntp 2>/dev/null | grep -q ":${port}[^0-9]" && in_use=true
  fi
  $in_use || { echo "  [OK]   Port ${port} (${label}) is free"; return 0; }

  # 尋找正在使用此 port 的 Docker 容器
  local cid
  cid=$(docker ps --format '{{.ID}} {{.Ports}}' 2>/dev/null \
        | grep ":${port}->" | awk '{print $1}' | head -1)

  if [ -n "$cid" ]; then
    local name
    name=$(docker inspect --format '{{.Name}}' "$cid" 2>/dev/null | tr -d '/')
    echo "  [WARN] Port ${port} (${label}) held by Docker container '${name}' — force removing ..."
    docker rm -f "$cid" >/dev/null
    sleep 0.5
    echo "  [OK]   Port ${port} (${label}) released"
    return 0
  fi

  # 非 Docker 程序佔用 → 無法安全移除，中止
  echo "  [ERROR] Port ${port} (${label}) is occupied by a non-Docker process."
  echo "          Run: ss -lntp | grep :${port}  to identify the process."
  return 1
}

# 從範本載入 port 設定
set -a
# shellcheck disable=SC1090
source "$ENV_TEMPLATE" 2>/dev/null || true
set +a

FRONTEND_PORT="${FRONTEND_PORT:-80}"
BACKEND_PORT="${BACKEND_PORT:-8080}"
DB_PORT="${DB_PORT:-9307}"
PHPMYADMIN_PORT="${PHPMYADMIN_PORT:-9407}"

echo "Checking and releasing ports ..."
PORT_OK=true
release_port "$FRONTEND_PORT"   "frontend"   || PORT_OK=false
release_port "$BACKEND_PORT"    "backend"    || PORT_OK=false
release_port "$DB_PORT"         "db"         || PORT_OK=false
release_port "$PHPMYADMIN_PORT" "phpmyadmin" || PORT_OK=false

if [ "$PORT_OK" = false ]; then
  echo ""
  echo "Aborting: free the ports above and retry, or update $ENV_TEMPLATE"
  exit 1
fi
echo ""

# ─── 從範本同步 docker/.env，但保留後端 .env 中已填寫的 SMS API 憑證 ─────────
echo "Syncing .env from $ENV_TEMPLATE ..."

SMS_KEYS=(TWILIO_ACCOUNT_SID TWILIO_AUTH_TOKEN TWILIO_VERIFY_SERVICE_SID)

OLD_ENV_TMP="${ENV_FILE}.pre_deploy.$$"
[ -f "$ENV_FILE" ] && cp "$ENV_FILE" "$OLD_ENV_TMP"

cp "$ENV_TEMPLATE" "$ENV_FILE"

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
