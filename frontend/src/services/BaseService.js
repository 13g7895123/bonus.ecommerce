// src/services/BaseService.js
import axios from 'axios';
import config from './config';

// 建立 Axios 實例
const http = axios.create({
  baseURL: config.apiUrl,
  headers: {
    'Content-Type': 'application/json',
  },
});

http.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// 模擬 API 實作
import { mockDb } from './mockDb';

export class BaseService {
  constructor(endpoint, mockTable) {
    this.endpoint = endpoint;
    this.mockTable = mockTable || endpoint.replace('/', '');
    this.useMock = config.useMock;
    this.http = http;
    this.db = mockDb;
  }

  // 通用方法
  
  // 為了支持切換，我們每一個業務方法都需要檢查 this.useMock
  // 這裡提供一些 helper
  
  async _post(path, data, mockHandler) {
    if (this.useMock) {
      if (!mockHandler) throw new Error('Mock handler not implemented');
      try {
        const result = await mockHandler(data);
        return { data: result, status: 200, statusText: 'OK' }; // Mimic axios response structure
      } catch (err) {
        // Mimic axios error
        throw { 
          response: { 
            data: { message: err.message }, 
            status: 400 
          } 
        };
      }
    }
    return this.http.post(this.endpoint + path, data);
  }

  async _get(path, mockHandler) {
    if (this.useMock) {
      if (!mockHandler) throw new Error('Mock handler not implemented');
       try {
        const result = await mockHandler();
        return { data: result, status: 200 };
      } catch (err) {
        throw { response: { data: { message: err.message }, status: 400 } };
      }
    }
    return this.http.get(this.endpoint + path);
  }
}
