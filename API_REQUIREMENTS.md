# API 需求規格書 (API Requirements Specification)

本文檔基於前端專案現狀與 `BACKEND_ARCHITECTURE.md` 架構規劃，定義前後端串接所需的 API 介面。

## 1. 通用規範 (General Standards)

*   **Base URL**: `/api/v1`
*   **Authentication**:
    *   除登入、註冊、忘記密碼外，所有 API 需在 Header 帶入 JWT Token。
    *   `Authorization: Bearer <token>`
*   **Response Format**:
    ```json
    {
      "code": 200,   // HTTP Status Code (200, 400, 401, 403, 500)
      "message": "success", // 錯誤時為錯誤訊息
      "data": {}     // 成功時的資料 payload
    }
    ```
*   **Date Format**: ISO 8601 (`YYYY-MM-DD HH:mm:ss`)

---

## 2. 認證模組 (Auth Module)

| Method | Endpoint | Description | Request Body | Response Data |
| :--- | :--- | :--- | :--- | :--- |
| **POST** | `/auth/register` | 會員註冊 | `{ email, password, phone, fullName, inviteCode? }` | `{ token, user: { id, email, ... } }` |
| **POST** | `/auth/login` | 會員登入 | `{ email, password }` | `{ token, user: { id, email, ... } }` |
| **POST** | `/auth/forgot-password` | 發送重設密碼簡訊/郵件 | `{ phone }` | `{ message: "發送成功" }` |
| **POST** | `/auth/reset-password` | 重設密碼 (透過 token) | `{ token, newPassword }` | `{ message: "重設成功" }` |

---

## 3. 會員模組 (User Module)

負責管理會員基本資料與實名認證。

### 3.1 取得個人資料 (`GET /users/me`)
*   **Response**:
    ```json
    {
      "id": "uuid",
      "email": "user@example.com",
      "fullName": "王大明",
      "phone": "0912345678",
      "tier": "blue", // 會員等級
      "isVerified": false,
      "wallet": {
          "balance": 1000.00,
          "miles": 500
      }
    }
    ```

### 3.2 更新個人資料 (`PUT /users/me`)
*   **Body**: `{ fullName?, phone?, avatar? }`
*   **Response**: 更新後的 User Object。

### 3.3 實名認證 (`POST /users/me/verify`)
*   **Use Case**: 上傳身分證件進行 KYC。
*   **Content-Type**: `multipart/form-data`
*   **Body**:
    *   `idCardFront`: File
    *   `idCardBack`: File
    *   `handheldIdCard`: File
    *   `realName`: string
    *   `idNumber`: string
*   **Response**: `{ status: "pending", message: "審核中" }`

---

## 4. 錢包與交易模組 (Wallet & Transaction Module)

負責現金餘額、銀行帳戶綁定與提領。

### 4.1 查詢錢包詳情 (`GET /wallet/info`)
*   **Response**:
    ```json
    {
      "balance": 50000.00,
      "withdrawalPasswordSet": true, // 是否已設定提款密碼
      "bankAccount": { // 若未綁定則為 null
          "bankName": "中國信託",
          "branchName": "營業部",
          "accountName": "王大明",
          "accountNumber": "****5678"
      }
    }
    ```

### 4.2 設定/修改提款密碼 (`POST /wallet/password`)
*   **Body**: `{ password: "old_password", newPassword: "new_password" }` (首次設定 `password` 為 null)
*   **Response**: `{ success: true }`

### 4.3 綁定銀行帳戶 (`POST /wallet/bank`)
*   **Body**:
    ```json
    {
      "bankName": "String",
      "branchName": "String",
      "accountName": "String",
      "accountNumber": "String",
      "withdrawalPassword": "String" // 需驗證提款密碼
    }
    ```

### 4.4 申請提款 (`POST /wallet/withdraw`)
*   **Body**: `{ amount: 1000, withdrawalPassword: "xxx" }`
*   **Response**: `{ transactionId: "uuid", status: "pending" }`

### 4.5 交易紀錄 (`GET /wallet/transactions`)
*   **Query**: `?page=1&limit=20&type=deposit|withdrawal`
*   **Response**:
    ```json
    {
      "items": [
        {
          "id": "tx_123",
          "type": "withdrawal",
          "amount": -1000,
          "status": "completed",
          "createdAt": "2024-03-10 10:00:00"
        }
      ],
      "total": 50
    }
    ```

---

## 5. 里程積分模組 (Mileage Module)

### 5.1 里程紀錄 (`GET /mileage/history`)
*   **Query**: `?page=1&limit=20`
*   **Response**:
    ```json
    {
      "items": [
        {
          "id": 1,
          "type": "earn", // earn, redeem
          "amount": 500,
          "source": "Flight TPE-DXB",
          "createdAt": "2024-03-09 14:00:00"
        }
      ],
      "currentMiles": 1500
    }
    ```

---

## 6. 公告模組 (Announcement Module)

### 6.1 公告列表 (`GET /announcements`)
*   **Response**:
    ```json
    [
      { "id": 1, "title": "系統維護通知", "date": "2024-03-10", "isNew": true }
    ]
    ```

### 6.2 公告詳情 (`GET /announcements/{id}`)
*   **Response**:
    ```json
    {
      "id": 1,
      "title": "系統維護通知",
      "content": "親愛的用戶...\n...",
      "date": "2024-03-10 10:00:00"
    }
    ```

---

## 7. 客服模組 (Customer Service Module)

### 7.1 取得對話列表 (`GET /cs/messages`)
*   **Query**: `?lastId=xxx` (用於 cursor pagination 或 polling)
*   **Response**:
    ```json
    [
      {
        "id": 101,
        "sender": "admin", // admin | user
        "content": "您好，請問有什麼需要協助？",
        "createdAt": "2024-03-10 10:05:00"
      }
    ]
    ```

### 7.2 發送訊息 (`POST /cs/messages`)
*   **Body**: `{ content: "我無法提款", image?: File }`
*   **Response**: `{ id: 102, status: "sent" }`

---

## 8. 後台管理模組 (Admin Module - Require Admin Role)

### 8.1 會員列表 (`GET /admin/users`)
*   **Query**: `?page=1&search=email`

### 8.2 修改會員餘額 (`POST /admin/users/{id}/balance`)
*   **Body**: `{ amount: 100, type: "adjustment", reason: "人工補償" }`
*   **Response**: `{ currentBalance: 1100 }`

### 8.3 審核實名認證 (`POST /admin/users/{id}/verify`)
*   **Body**: `{ status: "approved" | "rejected", reason?: "證件模糊" }`
