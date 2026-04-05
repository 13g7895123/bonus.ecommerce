#!/bin/bash
# =============================================================================
# debug-vps.sh  —  快速診斷正式環境狀態
# 用法: bash scripts/debug-vps.sh [docker-env-file]
#   docker-env-file 預設為 docker/.env
# =============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="${1:-$PROJECT_DIR/docker/.env}"

# ── 顏色 ────────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

ok()   { echo -e "  ${GREEN}✓${NC}  $*"; }
fail() { echo -e "  ${RED}✗${NC}  $*"; }
warn() { echo -e "  ${YELLOW}!${NC}  $*"; }
info() { echo -e "  ${CYAN}→${NC}  $*"; }
section() { echo -e "\n${BOLD}══ $* ══${NC}"; }

ERRORS=0
err() { fail "$*"; ERRORS=$((ERRORS+1)); }

# ── 載入 .env ────────────────────────────────────────────────────────────────
if [[ -f "$ENV_FILE" ]]; then
  # 只匯出非註解、非空行的 key=value
  set -o allexport
  # shellcheck disable=SC2046
  eval $(grep -E '^[A-Z_]+=.*' "$ENV_FILE" | sed 's/[[:space:]]*#.*//;/^$/d')
  set +o allexport
  info "已載入 env: $ENV_FILE"
else
  warn ".env 檔不存在: $ENV_FILE（使用 docker-compose 預設值）"
fi

FRONTEND_PORT="${FRONTEND_PORT:-8104}"
BACKEND_PORT="${BACKEND_PORT:-8204}"
DB_PORT="${DB_PORT:-8304}"
WS_PORT="${WS_PORT:-8504}"
DOMAIN="${DOMAIN:-}"

# ── 0. 系統資訊 ──────────────────────────────────────────────────────────────
section "系統資訊"
info "主機名稱 : $(hostname)"
info "主機 IP  : $(hostname -I | awk '{print $1}')"
info "OS       : $(uname -sr)"
info "Docker   : $(docker --version 2>/dev/null || echo '未安裝')"

# ── 1. Docker 容器狀態 ────────────────────────────────────────────────────────
section "Docker 容器狀態"
CONTAINERS=(frontend backend backend-web backend-ws db)
for svc in "${CONTAINERS[@]}"; do
  # 用 label 或名稱模糊比對
  CNAME=$(docker ps --format '{{.Names}}' | grep "$svc" | head -1 || true)
  if [[ -z "$CNAME" ]]; then
    err "容器未執行: *$svc*"
  else
    STATUS=$(docker inspect --format '{{.State.Status}}' "$CNAME")
    HEALTH=$(docker inspect --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}-{{end}}' "$CNAME")
    if [[ "$STATUS" == "running" ]]; then
      ok "$CNAME  status=$STATUS  health=$HEALTH"
    else
      err "$CNAME  status=$STATUS  health=$HEALTH"
    fi
  fi
done

# ── 2. Port 連通性 ────────────────────────────────────────────────────────────
section "Port 連通性（localhost）"
check_port() {
  local port=$1 label=$2
  if curl -sf --max-time 3 "http://localhost:$port" -o /dev/null 2>/dev/null; then
    ok "port $port ($label) — HTTP OK"
  elif nc -z -w 3 localhost "$port" 2>/dev/null; then
    ok "port $port ($label) — TCP OPEN"
  else
    err "port $port ($label) — 無法連線"
  fi
}
check_port "$FRONTEND_PORT" "frontend"
check_port "$BACKEND_PORT"  "backend-web"
check_port "$WS_PORT"       "backend-ws Swoole"

# ── 3. Swoole WebSocket Health ───────────────────────────────────────────────
section "Swoole WebSocket Health"
WS_HEALTH=$(curl -sf --max-time 3 "http://localhost:$WS_PORT/health" 2>/dev/null || echo "")
if [[ -n "$WS_HEALTH" ]]; then
  CONN=$(echo "$WS_HEALTH" | grep -o '"connections":[0-9]*' | cut -d: -f2 || echo "?")
  TICK=$(echo "$WS_HEALTH" | grep -o '"tickets":[0-9]*'    | cut -d: -f2 || echo "?")
  ok "Swoole 回應正常 — 目前連線:${CONN}  ticket:${TICK}"
  info "raw: $WS_HEALTH"
else
  err "Swoole /health 無回應 (port $WS_PORT)"
fi

# ── 4. 後端 API 健康檢查 ──────────────────────────────────────────────────────
section "後端 API"
API_RESP=$(curl -sf --max-time 5 "http://localhost:$BACKEND_PORT/" 2>/dev/null || echo "")
if [[ -n "$API_RESP" ]]; then
  ok "backend-web (port $BACKEND_PORT) 回應正常"
else
  err "backend-web (port $BACKEND_PORT) 無回應"
fi

# 測試一個已知的 API 路徑（不需要 token）
CS_CODE=$(curl -o /dev/null -sw '%{http_code}' --max-time 5 \
  "http://localhost:$BACKEND_PORT/api/v1/cs/messages" 2>/dev/null || echo "000")
if [[ "$CS_CODE" == "401" ]]; then
  ok "API /cs/messages → 401 Unauthorized（JWT 驗證正常）"
elif [[ "$CS_CODE" == "000" ]]; then
  err "API /cs/messages → 無回應"
else
  warn "API /cs/messages → HTTP $CS_CODE（非預期但可能正常）"
fi

# ── 5. WsNotifier 通道（php-fpm → Swoole）───────────────────────────────────
section "WsNotifier 內部通道 (php-fpm→Swoole)"
BACKEND_WS_CNAME=$(docker ps --format '{{.Names}}' | grep 'backend$\|backend-[^w]' | head -1 || true)
if [[ -n "$BACKEND_WS_CNAME" ]]; then
  NOTIFY_CODE=$(docker exec "$BACKEND_WS_CNAME" \
    curl -o /dev/null -sw '%{http_code}' --max-time 3 \
    -X POST "http://backend-ws:$WS_PORT/notify" \
    -H 'Content-Type: application/json' \
    -d '{"ticket_id":"__debug__","message":{"id":0,"content":"ping"}}' \
    2>/dev/null || echo "000")
  if [[ "$NOTIFY_CODE" == "200" ]]; then
    ok "backend → backend-ws /notify 通道正常"
  else
    err "backend → backend-ws /notify 失敗 (HTTP $NOTIFY_CODE)"
    warn "確認 WS_INTERNAL_HOST=backend-ws, WS_PORT=$WS_PORT 在 backend 容器環境變數中"
  fi
else
  warn "找不到 backend (php-fpm) 容器，跳過 WsNotifier 測試"
fi

# ── 6. 資料庫連線 ─────────────────────────────────────────────────────────────
section "資料庫"
DB_CNAME=$(docker ps --format '{{.Names}}' | grep 'db\|mysql' | head -1 || true)
if [[ -n "$DB_CNAME" ]]; then
  DB_CHECK=$(docker exec "$DB_CNAME" \
    mysqladmin ping -h localhost -u root -p"${DB_ROOT_PASSWORD:-rootpassword}" \
    2>/dev/null || echo "")
  if echo "$DB_CHECK" | grep -q "alive"; then
    ok "MySQL 正常"
  else
    err "MySQL 無回應"
  fi
else
  err "找不到 MySQL 容器"
fi

# ── 7. 近期容器 logs（只顯示 ERROR/WARNING）────────────────────────────────────
section "近期錯誤 logs（最近 50 行）"
LOG_SVCS=(backend backend-ws)
for svc in "${LOG_SVCS[@]}"; do
  CNAME=$(docker ps --format '{{.Names}}' | grep "$svc" | head -1 || true)
  if [[ -n "$CNAME" ]]; then
    ERRLOGS=$(docker logs "$CNAME" --tail=50 2>&1 | grep -iE 'error|fatal|exception|failed' | tail -10 || true)
    if [[ -n "$ERRLOGS" ]]; then
      warn "$CNAME 有錯誤訊息："
      echo "$ERRLOGS" | sed 's/^/     /'
    else
      ok "$CNAME 近期 logs 無明顯錯誤"
    fi
  fi
done

# ── 8. domain /ws 路徑（若有設定 DOMAIN）─────────────────────────────────────
if [[ -n "$DOMAIN" ]]; then
  section "Domain WebSocket 路徑 ($DOMAIN)"
  WS_CODE=$(curl -o /dev/null -sw '%{http_code}' --max-time 5 \
    -H "Connection: Upgrade" -H "Upgrade: websocket" \
    "https://$DOMAIN/ws?token=debug" 2>/dev/null || echo "000")
  case "$WS_CODE" in
    101) ok "wss://$DOMAIN/ws → 101 (WebSocket handshake OK)" ;;
    400) ok "wss://$DOMAIN/ws → 400 (到達 Swoole，但 token 無效 — 路由正常)" ;;
    404) err "wss://$DOMAIN/ws → 404 (NPM 沒有設定 /ws location)" ;;
    000) err "wss://$DOMAIN/ws → 無回應" ;;
    *)   warn "wss://$DOMAIN/ws → HTTP $WS_CODE" ;;
  esac
  info "提示：若想指定 DOMAIN，在 docker/.env 加入 DOMAIN=demo.mercylife.cc"
fi

# ── 摘要 ─────────────────────────────────────────────────────────────────────
echo ""
if [[ "$ERRORS" -eq 0 ]]; then
  echo -e "${GREEN}${BOLD}✓ 所有檢查通過${NC}"
else
  echo -e "${RED}${BOLD}✗ 共 $ERRORS 項檢查失敗，請依上方訊息排查${NC}"
fi
echo ""
