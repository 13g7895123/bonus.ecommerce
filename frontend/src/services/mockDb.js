// src/services/mockDb.js

/* 
 * 模擬資料庫 
 * 處理 LocalStorage 的 CRUD，模擬延遲
 */

const DB_PREFIX = 'skywards_db_';
const DELAY_MS = 500; // 模擬網路延遲

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

const getTable = (table) => {
  const data = localStorage.getItem(DB_PREFIX + table);
  return data ? JSON.parse(data) : [];
};

const saveTable = (table, data) => {
  localStorage.setItem(DB_PREFIX + table, JSON.stringify(data));
};

export const mockDb = {
  async findOne(table, queryFn) {
    await delay(DELAY_MS);
    const items = getTable(table);
    return items.find(queryFn);
  },

  async insert(table, item) {
    await delay(DELAY_MS);
    const items = getTable(table);
    // 簡單產生 ID
    item.id = Date.now().toString(36) + Math.random().toString(36).substr(2);
    item.createdAt = new Date().toISOString();
    items.push(item);
    saveTable(table, items);
    return item;
  },

  async update(table, id, updates) {
    await delay(DELAY_MS);
    const items = getTable(table);
    const index = items.findIndex(i => i.id === id);
    if (index === -1) throw new Error('Not found');
    
    items[index] = { ...items[index], ...updates, updatedAt: new Date().toISOString() };
    saveTable(table, items);
    return items[index];
  },

  async list(table) {
    await delay(DELAY_MS);
    return getTable(table);
  }
};
