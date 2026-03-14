<template>
  <div class="admin-page">
    <div class="admin-header-bar">
      <div class="header-left">
        <button class="back-btn" @click="handleBack">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <h1>Admin Dashboard</h1>
      </div>
      <div class="view-toggle">
        <button 
          :class="['toggle-btn', { active: viewMode === 'formatted' }]" 
          @click="viewMode = 'formatted'"
        >
          <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>
          </svg>
          Visual
        </button>
        <button 
          :class="['toggle-btn', { active: viewMode === 'raw' }]" 
          @click="viewMode = 'raw'"
        >
          <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <path d="M14 2v6h6"/>
            <path d="M16 13H8"/>
            <path d="M16 17H8"/>
            <path d="M10 9H8"/>
          </svg>
          Raw JSON
        </button>
      </div>
    </div>
    
    <div class="admin-content">
      <div v-if="items.length === 0" class="empty-state">
        <div class="empty-icon">📂</div>
        <p>LocalStorage is empty</p>
      </div>

      <!-- Formatted View -->
      <div v-else-if="viewMode === 'formatted'" class="dashboard-grid">
        <div v-for="(item, index) in items" :key="index" class="dashboard-card">
          <div class="card-header">
            <span class="card-title">{{ formatKeyTitle(item.key) }}</span>
            <span class="key-badge">{{ item.key }}</span>
          </div>
          
          <div class="card-body">
            <!-- Table for Arrays -->
            <div v-if="Array.isArray(item.parsedValue) && item.parsedValue.length > 0" class="table-container">
              <table>
                <thead>
                  <tr>
                    <th v-for="header in getHeaders(item.parsedValue[0])" :key="header">{{ header }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, idx) in item.parsedValue.slice(0, 50)" :key="idx">
                    <td v-for="header in getHeaders(item.parsedValue[0])" :key="header">
                      {{ formatCell(row[header]) }}
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="item.parsedValue.length > 50" class="more-indicator">
                ... {{ item.parsedValue.length - 50 }} more items
              </div>
            </div>
            
            <!-- Key-Value for Objects -->
            <div v-else-if="isObject(item.parsedValue)" class="kv-grid">
              <div v-for="(val, key) in item.parsedValue" :key="key" class="kv-item">
                <span class="kv-label">{{ key }}</span>
                <span class="kv-value">{{ formatCell(val) }}</span>
              </div>
            </div>
            
            <!-- Fallback for simple values or empty arrays -->
            <div v-else class="simple-value">
              {{ String(item.parsedValue) }}
            </div>
          </div>
        </div>
      </div>
      
      <!-- Raw View (Original) -->
      <div v-else class="raw-list">
        <div v-for="(item, index) in items" :key="index" class="storage-item">
          <div class="item-header-raw">
             <svg width="20" height="20" viewBox="0 0 24 24" fill="none" class="icon" xmlns="http://www.w3.org/2000/svg">
               <path d="M4 7V17C4 19.2091 7.58172 21 12 21C16.4183 21 20 19.2091 20 17V7M4 7C4 9.20914 7.58172 11 12 11C16.4183 11 20 9.20914 20 7M4 7C4 4.79086 7.58172 3 12 3C16.4183 3 20 4.79086 20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M20 12C20 14.2091 16.4183 16 12 16C7.58172 16 4 14.2091 4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
             </svg>
             <span class="key-name">{{ item.key }}</span>
          </div>
          <div class="item-value">
            <pre>{{ item.rawValue }}</pre>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const items = ref([])
const viewMode = ref('formatted') // 'formatted' or 'raw'

const handleBack = () => {
  router.push('/')
}

onMounted(() => {
  // Disable max-width constraint
  const appEl = document.getElementById('app')
  if (appEl) {
    appEl.style.maxWidth = 'none'
    appEl.style.backgroundColor = '#f0f2f5' // Light background for admin
  }

  // Load Data
  const keys = Object.keys(localStorage)
  items.value = keys.map(key => {
    const rawVal = localStorage.getItem(key)
    let parsedVal = rawVal
    try {
      parsedVal = JSON.parse(rawVal)
    } catch (e) {
      // Keep as string
    }
    return {
      key: key,
      rawValue: rawVal,
      parsedValue: parsedVal
    }
  })
})

onUnmounted(() => {
  // Restore max-width constraint
  const appEl = document.getElementById('app')
  if (appEl) {
    appEl.style.maxWidth = ''
    appEl.style.backgroundColor = ''
  }
})

// Helpers
const formatKeyTitle = (key) => {
  return key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1')
}

const isObject = (val) => {
  return val && typeof val === 'object' && !Array.isArray(val)
}

const getHeaders = (obj) => {
  if (!obj || typeof obj !== 'object') return []
  return Object.keys(obj)
}

const formatCell = (val) => {
  if (val === null || val === undefined) return '-'
  if (typeof val === 'boolean') return val ? 'Yes' : 'No'
  if (typeof val === 'object') return JSON.stringify(val)
  return String(val)
}
</script>

<style scoped>
.admin-page {
  min-height: 100vh;
  background-color: #f0f2f5;
  color: #1f2937;
  padding-bottom: 3rem;
}

.admin-header-bar {
  background: #ffffff;
  padding: 1rem 2rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-left h1 {
  margin: 0;
  font-size: 1.5rem;
  color: #111827;
  font-weight: 700;
}

.back-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  color: #6b7280;
  border-radius: 50%;
  transition: background-color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.back-btn:hover {
  background-color: #f3f4f6;
  color: #111827;
}

.view-toggle {
  display: flex;
  background: #f3f4f6;
  border-radius: 8px;
  padding: 0.25rem;
  gap: 0.25rem;
}

.toggle-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  background: transparent;
  color: #6b7280;
  font-weight: 500;
  font-size: 0.875rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.toggle-btn:hover {
  color: #374151;
}

.toggle-btn.active {
  background: #ffffff;
  color: #2563eb;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.icon-sm {
  width: 16px;
  height: 16px;
}

.admin-content {
  padding: 2rem;
  max-width: 1280px;
  margin: 0 auto;
}

.empty-state {
  text-align: center;
  padding: 4rem;
  color: #9ca3af;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

/* Dashboard Grid */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
}

/* Make single wide items (like transactions list) span full width if needed, 
   but grid is usually fine. Specific overrides could be added based on key name if desired. */

.dashboard-card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
  border: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
}

.card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f9fafb;
}

.card-title {
  font-weight: 600;
  color: #111827;
  font-size: 1rem;
}

.key-badge {
  font-size: 0.75rem;
  background: #e5e7eb;
  color: #4b5563;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-family: monospace;
}

.card-body {
  padding: 1.5rem;
  overflow-x: auto;
}

/* Table Styles */
.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
  text-align: left;
}

th {
  background: #f3f4f6;
  font-weight: 600;
  color: #374151;
  padding: 0.75rem 1rem;
  white-space: nowrap;
}

td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #eff6ff;
  color: #1f2937;
}

tr:last-child td {
  border-bottom: none;
}

/* Key Value Grid */
.kv-grid {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0.75rem 2rem;
  align-items: baseline;
}

.kv-item {
  display: contents; /* Allows children to participate in parent grid */
}

.kv-label {
  font-weight: 500;
  color: #6b7280;
  font-size: 0.875rem;
}

.kv-value {
  color: #111827;
  font-size: 0.95rem;
  word-break: break-word;
}

/* Raw Styles (Legacy) */
.raw-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.storage-item {
  background: white;
  margin-bottom: 1rem;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  color: #000000;
}

.item-header-raw {
  background: #f3f4f6;
  padding: 0.75rem 1rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #1f2937;
  border-bottom: 1px solid #e5e7eb;
}

.item-value {
  padding: 1rem;
  font-family: monospace;
  font-size: 0.875rem;
  overflow-x: auto;
  white-space: pre-wrap;
  word-break: break-all;
  color: #374151;
  background: #ffffff;
}

.icon {
  color: #6b7280;
}
</style>