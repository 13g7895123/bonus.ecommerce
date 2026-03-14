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

http.interceptors.request.use(cfg => {
  const token = localStorage.getItem('token');
  if (token) cfg.headers.Authorization = `Bearer ${token}`;
  return cfg;
});

// 解包後端統一回應格式 { code, message, data } → data
http.interceptors.response.use(
  response => {
    if (
      response.data &&
      typeof response.data === 'object' &&
      'code' in response.data &&
      'data' in response.data
    ) {
      return { ...response, data: response.data.data };
    }
    return response;
  },
  error => {
    const message = error.response?.data?.message || error.message || '請求失敗';
    const status = error.response?.status || 500;
    return Promise.reject({ response: { data: { message }, status } });
  }
);

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

  async _post(path, data, mockHandler) {
    if (this.useMock) {
      if (!mockHandler) throw new Error('Mock handler not implemented');
      try {
        return await mockHandler(data);
      } catch (err) {
        throw { response: { data: { message: err.message }, status: 400 } };
      }
    }
    const response = await this.http.post(this.endpoint + path, data);
    return response.data;
  }

  async _put(path, data, mockHandler) {
    if (this.useMock) {
      if (!mockHandler) throw new Error('Mock handler not implemented');
      try {
        return await mockHandler(data);
      } catch (err) {
        throw { response: { data: { message: err.message }, status: 400 } };
      }
    }
    const response = await this.http.put(this.endpoint + path, data);
    return response.data;
  }

  // _get(path, mockHandler) 或 _get(path, params, mockHandler)
  async _get(path, paramsOrMock, mockHandler) {
    let params = {};
    if (typeof paramsOrMock === 'function') {
      mockHandler = paramsOrMock;
    } else if (paramsOrMock && typeof paramsOrMock === 'object') {
      params = paramsOrMock;
    }
    if (this.useMock) {
      if (!mockHandler) throw new Error('Mock handler not implemented');
      try {
        return await mockHandler();
      } catch (err) {
        throw { response: { data: { message: err.message }, status: 400 } };
      }
    }
    const response = await this.http.get(this.endpoint + path, { params });
    return response.data;
  }
}

