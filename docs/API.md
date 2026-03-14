# 後端 API 說明文件

**Base URL:** `http://localhost:9207/api/v1`  
**回應格式（統一）：**
```json
{
  "code": 200,
  "message": "OK",
  "data": { ... }
}
```

---

## 認證機制

需要登入的 API 須在 Header 帶上 JWT Token：
```
Authorization: Bearer <token>
```
Token 透過登入取得，有效期 **86400 秒（24 小時）**。

---

## 目錄

- [Auth 認證](#auth-認證)
- [User 使用者](#user-使用者)
- [Wallet 錢包](#wallet-錢包)
- [Mileage 里程](#mileage-里程)
- [Announcements 公告](#announcements-公告)
- [Customer Service 客服](#customer-service-客服)
- [Admin 管理員](#admin-管理員)

---

## Auth 認證

### 註冊
`POST /auth/register`

**身份驗證：** 不需要

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "full_name": "張小明",
  "phone": "0912345678"
}
```

| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| email | string | ✅ | 電子郵件（唯一） |
| password | string | ✅ | 密碼（至少 6 字元） |
| full_name | string | ❌ | 真實姓名 |
| phone | string | ❌ | 手機號碼 |

**Response 201:**
```json
{
  "code": 201,
  "message": "Registration successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "full_name": "張小明",
      "tier": "regular",
      "role": "user",
      "status": "active"
    }
  }
}
```

---

### 登入
`POST /auth/login`

**身份驗證：** 不需要

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "code": 200,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "full_name": "張小明",
      "tier": "silver",
      "role": "user",
      "status": "active"
    }
  }
}
```

**可能的錯誤：**
- `401` Invalid email or password

---

### 忘記密碼
`POST /auth/forgot-password`

**身份驗證：** 不需要

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response 200:**
```json
{
  "code": 200,
  "message": "If the email exists, a reset link has been sent",
  "data": null
}
```

---

### 重設密碼
`POST /auth/reset-password`

**身份驗證：** 不需要

> 功能開發中，目前回傳佔位訊息。

---

## User 使用者

### 取得個人資料
`GET /users/me`

**身份驗證：** JWT 必填

**Response 200:**
```json
{
  "code": 200,
  "message": "OK",
  "data": {
    "id": 1,
    "email": "user@example.com",
    "full_name": "張小明",
    "phone": "0912345678",
    "tier": "silver",
    "role": "user",
    "is_verified": 0,
    "verify_status": "none",
    "status": "active",
    "created_at": "2026-03-14 08:00:00",
    "wallet": {
      "balance": 5000.00,
      "miles_balance": 500,
      "has_bank_account": false,
      "has_withdrawal_pw": false
    }
  }
}
```

**tier 可能值：** `regular` / `silver` / `gold` / `platinum`  
**verify_status 可能值：** `none` / `pending` / `verified` / `rejected`

---

### 更新個人資料
`PUT /users/me`

**身份驗證：** JWT 必填

**Request Body:**
```json
{
  "full_name": "李大華",
  "phone": "0987654321"
}
```

| 欄位 | 類型 | 說明 |
|------|------|------|
| full_name | string | 姓名 |
| phone | string | 手機號碼 |

**Response 200:**
```json
{
  "code": 200,
  "message": "Profile updated",
  "data": { ...使用者完整資料... }
}
```

---

### 提交身分驗證
`POST /users/me/verify`

**身份驗證：** JWT 必填  
**Content-Type:** `multipart/form-data`

**Request Body（multipart）:**

| 欄位 | 類型 | 說明 |
|------|------|------|
| id_front | file | 身份證正面 |
| id_back | file | 身份證背面 |
| selfie | file | 自拍照 |
| real_name | string | 真實姓名 |

**Response 200:**
```json
{
  "code": 200,
  "message": "Verification submitted",
  "data": null
}
```

---

## Wallet 錢包

### 取得錢包資訊
`GET /wallet/info`

**身份驗證：** JWT 必填

**Response 200:**
```json
{
  "code": 200,
  "message": "OK",
  "data": {
    "id": 1,
    "user_id": 1,
    "balance": 5000.00,
    "miles_balance": 500,
    "bank_name": "台灣銀行",
    "bank_account_masked": "****1234",
    "bank_account_name": "張小明",
    "has_bank_account": true,
    "has_withdrawal_pw": true
  }
}
```

> 銀行帳號以遮罩格式回傳（僅顯示末 4 碼）

---

### 設定提款密碼
`POST /wallet/password`

**身份驗證：** JWT 必填

**Request Body:**
```json
{
  "password": "123456",
  "old_password": "000000"
}
```

| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| password | string | ✅ | 新提款密碼（至少 4 字元） |
| old_password | string | 有舊密碼時必填 | 舊提款密碼 |

**Response 200:**
```json
{
  "code": 200,
  "message": "Withdrawal password updated",
  "data": null
}
```

---

### 綁定銀行帳戶
`POST /wallet/bank`

**身份驗證：** JWT 必填

**Request Body:**
```json
{
  "bank_name": "台灣銀行",
  "bank_account": "123456789012",
  "bank_account_name": "張小明",
  "withdrawal_password": "123456"
}
```

| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| bank_name | string | ✅ | 銀行名稱 |
| bank_account | string | ✅ | 銀行帳號 |
| bank_account_name | string | ✅ | 帳戶持有人姓名 |
| withdrawal_password | string | ✅ | 提款密碼（驗證用） |

**Response 200:**
```json
{
  "code": 200,
  "message": "Bank account bound",
  "data": null
}
```

---

### 提款
`POST /wallet/withdraw`

**身份驗證：** JWT 必填

**Request Body:**
```json
{
  "amount": 1000.00,
  "withdrawal_password": "123456"
}
```

| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| amount | number | ✅ | 提款金額（大於 0） |
| withdrawal_password | string | ✅ | 提款密碼 |

**Response 200:**
```json
{
  "code": 200,
  "message": "Withdrawal successful",
  "data": {
    "balance": 4000.00
  }
}
```

**可能的錯誤：**
- `400` Insufficient balance
- `400` Please set a withdrawal password first
- `400` Please bind a bank account first
- `400` Withdrawal password incorrect

---

### 交易紀錄
`GET /wallet/transactions`

**身份驗證：** JWT 必填

**Query Parameters:**

| 參數 | 類型 | 說明 |
|------|------|------|
| type | string | 篩選類型：`deposit` / `withdrawal` / `transfer` / `adjustment` |
| page | integer | 頁碼（預設 1） |
| limit | integer | 每頁筆數（預設 20） |

**Response 200:**
```json
{
  "code": 200,
  "message": "OK",
  "data": {
    "items": [
      {
        "id": 1,
        "user_id": 1,
        "type": "withdrawal",
        "amount": "1000.00",
        "status": "completed",
        "description": "Withdrawal to bank account",
        "reference_id": "WD_1710000000_1",
        "created_at": "2026-03-14 10:00:00"
      }
    ],
    "total": 1
  }
}
```

---

## Mileage 里程

### 里程歷史紀錄
`GET /mileage/history`

**身份驗證：** JWT 必填

**Query Parameters:**

| 參數 | 類型 | 說明 |
|------|------|------|
| page | integer | 頁碼（預設 1） |
| limit | integer | 每頁筆數（預設 20） |

**Response 200:**
```json
{
  "code": 200,
  "message": "OK",
  "data": {
    "items": [
      {
        "id": 1,
        "user_id": 1,
        "type": "earn",
        "amount": 500,
        "source": "registration_bonus",
        "created_at": "2026-03-14 08:00:00"
      }
    ],
    "total": 1
  }
}
```

**type 可能值：** `earn`（獲得）/ `redeem`（兌換）

---

## Announcements 公告

### 取得公告列表
`GET /announcements`

**身份驗證：** JWT 必填

**Query Parameters:**

| 參數 | 類型 | 說明 |
|------|------|------|
| page | integer | 頁碼（預設 1） |
| limit | integer | 每頁筆數（預設 20） |

**Response 200:**
```json
{
  "code": 200,
  "message": "OK",
  "data": {
    "items": [
      {
        "id": 1,
        "title": "系統維護公告",
        "published_at": "2026-03-11 08:00:00"
      }
    ],
    "total": 3
  }
}
```

> 列表僅回傳 `id`、`title`、`published_at`，不含內文

---

### 取得公告詳情
`GET /announcements/{id}`

**身份驗證：** JWT 必填

**Path Parameters:**

| 參數 | 類型 | 說明 |
|------|------|------|
| id | integer | 公告 ID |

**Response 200:**
```json
{
  "code": 200,
  "message": "OK",
  "data": {
    "id": 1,
    "title": "系統維護公告",
    "content": "親愛的會員您好...",
    "is_published": 1,
    "published_at": "2026-03-11 08:00:00",
    "created_at": "2026-03-11 08:00:00",
    "updated_at": "2026-03-11 08:00:00"
  }
}
```

**可能的錯誤：**
- `404` Announcement not found

---

## Customer Service 客服

### 取得客服訊息
`GET /cs/messages`

**身份驗證：** JWT 必填

**Query Parameters:**

| 參數 | 類型 | 說明 |
|------|------|------|
| page | integer | 頁碼（預設 1） |
| limit | integer | 每頁筆數（預設 50） |

**Response 200:**
```json
{
  "code": 200,
  "message": "OK",
  "data": {
    "ticket_id": "ticket_1_1710000000",
    "items": [
      {
        "id": 1,
        "ticket_id": "ticket_1_1710000000",
        "sender_type": "user",
        "sender_id": 1,
        "content": "您好，我想詢問...",
        "image_path": null,
        "read_at": null,
        "created_at": "2026-03-14 10:00:00"
      }
    ],
    "total": 1
  }
}
```

**sender_type 可能值：** `user` / `admin`

---

### 發送客服訊息
`POST /cs/messages`

**身份驗證：** JWT 必填  
**Content-Type:** `application/json` 或 `multipart/form-data`

**Request Body（JSON）:**
```json
{
  "content": "您好，我想詢問提款問題"
}
```

**Request Body（multipart，含圖片）:**

| 欄位 | 類型 | 說明 |
|------|------|------|
| content | string | 訊息內容 |
| image | file | 附圖 |

**Response 201:**
```json
{
  "code": 201,
  "message": "Message sent",
  "data": {
    "ticket_id": "ticket_1_1710000000"
  }
}
```

---

## Admin 管理員

> 以下 API 需要 **管理員帳號** 的 JWT Token（role = admin）。

### 取得使用者列表
`GET /admin/users`

**身份驗證：** JWT（admin）

**Query Parameters:**

| 參數 | 類型 | 說明 |
|------|------|------|
| page | integer | 頁碼（預設 1） |
| limit | integer | 每頁筆數（預設 20） |

**Response 200:**
```json
{
  "code": 200,
  "message": "OK",
  "data": {
    "items": [
      {
        "id": 1,
        "email": "user@test.com",
        "full_name": "Test User",
        "tier": "silver",
        "role": "user",
        "is_verified": 0,
        "verify_status": "none",
        "status": "active",
        "balance": 5000.00,
        "miles_balance": 500
      }
    ],
    "total": 2
  }
}
```

---

### 調整使用者餘額
`POST /admin/users/{id}/balance`

**身份驗證：** JWT（admin）

**Path Parameters:**

| 參數 | 類型 | 說明 |
|------|------|------|
| id | integer | 使用者 ID |

**Request Body:**
```json
{
  "amount": 1000.00,
  "description": "活動獎勵"
}
```

| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| amount | number | ✅ | 調整金額（正數=增加，負數=扣除） |
| description | string | ❌ | 調整原因說明 |

**Response 200:**
```json
{
  "code": 200,
  "message": "Balance adjusted",
  "data": {
    "balance": 6000.00
  }
}
```

---

### 審核身分驗證
`POST /admin/users/{id}/verify`

**身份驗證：** JWT（admin）

**Path Parameters:**

| 參數 | 類型 | 說明 |
|------|------|------|
| id | integer | 使用者 ID |

**Request Body:**
```json
{
  "action": "approve"
}
```

或拒絕：
```json
{
  "action": "reject",
  "reason": "資料模糊，請重新上傳"
}
```

| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| action | string | ✅ | `approve`（核准）或 `reject`（拒絕） |
| reason | string | ❌ | 拒絕原因（action=reject 時建議填寫） |

**Response 200:**
```json
{
  "code": 200,
  "message": "Verification approved",
  "data": null
}
```

---

## 錯誤代碼一覽

| HTTP Code | 說明 |
|-----------|------|
| 200 | 成功 |
| 201 | 建立成功 |
| 400 | 請求錯誤（參數缺失或不合法） |
| 401 | 未授權（未登入或 Token 無效/過期） |
| 403 | 禁止存取（權限不足） |
| 404 | 資源不存在 |
| 500 | 伺服器內部錯誤 |

---

## 測試帳號

| 角色 | Email | 密碼 | 說明 |
|------|-------|------|------|
| 一般使用者 | `user@test.com` | `Test123!` | 餘額 5000，里程 500 |
| 管理員 | `admin@example.com` | `Admin123!` | 具備所有權限 |

---

## 快速測試範例

```bash
# 登入
curl -X POST http://localhost:9207/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","password":"Test123!"}'

# 取得個人資料（替換 <TOKEN>）
curl http://localhost:9207/api/v1/users/me \
  -H "Authorization: Bearer <TOKEN>"

# 取得公告列表
curl http://localhost:9207/api/v1/announcements \
  -H "Authorization: Bearer <TOKEN>"
```
