<template>
  <div class="admin-shell">
    <!-- ── 側欄 ── -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-logo">
        <img src="/logo.png" alt="Logo" class="logo-img" />
        <span v-if="!sidebarCollapsed" class="logo-text">Admin Panel</span>
      </div>
      <nav class="sidebar-nav">
        <div
          v-for="item in navItems"
          :key="item.key"
          class="nav-item"
          :class="{ active: isActive(item.key) }"
          @click="navigate(item.key)"
        >
          <component :is="item.icon" :size="18" />
          <span v-if="!sidebarCollapsed" class="nav-label">{{ item.label }}</span>
        </div>
      </nav>
      <div class="sidebar-footer">
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
          <ChevronLeft v-if="!sidebarCollapsed" :size="16" />
          <ChevronRight v-else :size="16" />
        </button>
      </div>
    </aside>

    <!-- ── 主區域 ── -->
    <main class="main-area">
      <div class="top-bar">
        <h1 class="section-heading">{{ currentNavItem?.label }}</h1>
        <button class="icon-btn" @click="$router.push('/')">
          <Home :size="16" /><span>返回前台</span>
        </button>
      </div>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Users, ShieldCheck, Coins, FileEdit, Gift, ShoppingBag, TicketPercent, Home, ChevronLeft, ChevronRight, Smartphone, MessageSquare } from 'lucide-vue-next'

const router = useRouter()
const route  = useRoute()

const sidebarCollapsed = ref(false)

const navItems = [
  { key: 'users',           label: '使用者管理',   icon: Users },
  { key: 'kyc',             label: '實名認證審核', icon: ShieldCheck },
  { key: 'mileage-items',   label: '里程兌換項目', icon: Coins },
  { key: 'mileage-rewards', label: '里程回饋商品', icon: Gift },
  { key: 'reward-orders',   label: '里程回饋訂單', icon: ShoppingBag },
  { key: 'mileage-codes',   label: '里程代碼管理', icon: TicketPercent },
  { key: 'content',         label: '內容管理',      icon: FileEdit },
  { key: 'customer-service', label: '在線客服對話',  icon: MessageSquare },
  // { key: 'phone-verifications', label: '手機驗證管理', icon: Smartphone },
  // { key: 'sms-logs',            label: 'SMS 簡訊紀錄',    icon: MessageSquare },
]

const isActive = (key) => route.path === `/admin/${key}`
const currentNavItem = computed(() => navItems.find(n => isActive(n.key)))

const navigate = (key) => { router.push(`/admin/${key}`) }
</script>

<style>
/* 全域 admin panel 共用樣式（供子頁面使用） */
@import '../admin-panel.css';
</style>

<style scoped>
@import '../admin.css';

.admin-shell {
  display: flex;
  min-height: 100vh;
  background: #f1f5f9;
  color: #1e293b;
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  width: 100vw;
  position: relative;
  left: 50%;
  transform: translateX(-50%);
  box-sizing: border-box;
}

/* Sidebar */
.sidebar { width: 240px; min-height: 100vh; background: #1e293b; color: #cbd5e1; display: flex; flex-direction: column; transition: width 0.2s; flex-shrink: 0; position: sticky; top: 0; height: 100vh; overflow: hidden; }
.sidebar.collapsed { width: 60px; }
.sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 1.25rem 1rem; border-bottom: 1px solid #334155; overflow: hidden; }
.logo-img { width: 32px; height: 32px; object-fit: contain; flex-shrink: 0; }
.logo-text { font-size: 0.95rem; font-weight: 700; color: #f8fafc; white-space: nowrap; }
.sidebar-nav { flex: 1; padding: 0.75rem 0.5rem; display: flex; flex-direction: column; gap: 2px; }
.nav-item { display: flex; align-items: center; gap: 10px; padding: 0.6rem 0.75rem; border-radius: 8px; cursor: pointer; transition: all 0.15s; white-space: nowrap; color: #94a3b8; font-size: 0.875rem; }
.nav-item:hover { background: #334155; color: #f8fafc; }
.nav-item.active { background: #3b82f6; color: #fff; }
.nav-label { flex: 1; overflow: hidden; }
.sidebar-footer { padding: 0.75rem 0.5rem; border-top: 1px solid #334155; }
.collapse-btn { background: transparent; border: none; color: #94a3b8; cursor: pointer; width: 100%; display: flex; justify-content: center; padding: 6px; border-radius: 6px; transition: background 0.15s; }
.collapse-btn:hover { background: #334155; color: #fff; }

/* Main */
.main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.top-bar { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 10; }
.section-heading { font-size: 1.1rem; font-weight: 700; margin: 0; }
.icon-btn { display: inline-flex; align-items: center; gap: 6px; padding: 0.45rem 0.85rem; border: 1px solid #e2e8f0; background: #fff; border-radius: 8px; font-size: 0.85rem; cursor: pointer; transition: background 0.15s; }
.icon-btn:hover { background: #f8fafc; }
</style>
