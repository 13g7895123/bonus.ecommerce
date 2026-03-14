// src/services/FileService.js
// 統一的檔案上傳與查詢 Service
import { BaseService } from './BaseService'

export class FileService extends BaseService {
  constructor() {
    super('/files', 'files')
  }

  /**
   * 上傳檔案
   * @param {File}   file  原始 File 物件
   * @param {string} type  avatar | kyc | cs_message | general
   * @returns {Promise<{id, uuid, url, path, mime_type, size, original_name}>}
   */
  async upload(file, type = 'general') {
    if (this.useMock) {
      // Mock 模式：用 FileReader 產生 base64 預覽 URL
      return new Promise((resolve) => {
        const reader = new FileReader()
        reader.onload = (e) => {
          const mockId = Date.now()
          resolve({
            id: mockId,
            uuid: `mock-${mockId}`,
            url: e.target.result,
            path: `uploads/${type}/mock_${mockId}`,
            mime_type: file.type,
            size: file.size,
            original_name: file.name,
            type,
            is_image: file.type.startsWith('image/'),
          })
        }
        reader.readAsDataURL(file)
      })
    }

    const formData = new FormData()
    formData.append('file', file)
    formData.append('type', type)

    const response = await this.http.post('/files/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data
  }

  /**
   * 取得單一檔案資訊（公開，不需 JWT）
   * @param {number} fileId
   * @returns {Promise<{id, uuid, url, mime_type, size, original_name, ...}>}
   */
  async getFile(fileId) {
    if (this.useMock) {
      return { id: fileId, url: null, mime_type: null, original_name: '' }
    }
    const response = await this.http.get(`/files/${fileId}`)
    return response.data
  }

  /**
   * 透過 UUID 取得檔案資訊
   */
  async getFileByUuid(uuid) {
    if (this.useMock) {
      return { uuid, url: null, mime_type: null, original_name: '' }
    }
    const response = await this.http.get(`/files/by-uuid/${uuid}`)
    return response.data
  }

  /**
   * 取得當前使用者自己上傳的所有檔案
   * @param {string|null} type  篩選類型
   */
  async getMyFiles(type = null) {
    if (this.useMock) {
      return []
    }
    const params = type ? { type } : {}
    const response = await this.http.get('/files/mine', { params })
    return response.data
  }

  /**
   * 刪除自己上傳的檔案
   * @param {number} fileId
   */
  async deleteFile(fileId) {
    if (this.useMock) {
      return { success: true }
    }
    const response = await this.http.delete(`/files/${fileId}`)
    return response.data
  }

  /**
   * 便捷方法：上傳並直接回傳 URL
   */
  async uploadAndGetUrl(file, type = 'general') {
    const result = await this.upload(file, type)
    return result.url
  }
}

// 匯出單例
export const fileService = new FileService()
