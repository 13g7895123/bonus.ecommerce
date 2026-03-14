# 後端系統架構規畫書

## 1. 系統概述

本系統為阿聯酋航空概念電商與會員積分平台，核心功能包含會員管理、里程累積與兌換、電子錢包提領與儲值、公告系統、以及後台管理。

本規劃旨在建立一個高內聚、低耦合、易於維護與擴充的後端架構。建議採用 **Laravel (PHP)** 或 **NestJS (Node.js)** 等支援完整 OOP 設計模式的框架。

---

## 2. 資料庫架構設計 (Database Schema)

採用關聯式資料庫 (MySQL / PostgreSQL)，遵循第三正規化設計。

### 2.1 核心資料表 (Core Tables)

#### Users (會員資料表)
管理會員基本資訊與驗證狀態。
*   `id` (PK, UUID)
*   `email` (Unique, Varchar, 帳號)
*   `password_hash` (Varchar)
*   `full_name` (Varchar)
*   `phone` (Varchar)
*   `tier` (Enum: 'blue', 'silver', 'gold', 'platinum') - 會員等級
*   `is_verified` (Boolean) - 實名認證狀態
*   `status` (Enum: 'active', 'suspended')
*   `created_at`, `updated_at`

#### UserWallets (電子錢包表)
將敏感的財務資訊與基本個資分離，提高安全性。
*   `id` (PK)
*   `user_id` (FK -> Users.id)
*   `balance` (Decimal, 18, 2) - 現金餘額
*   `miles_balance` (Integer) - 里程餘額
*   `withdrawal_password_hash` (Varchar, nullable) - 提款密碼
*   `bank_account_name` (Varchar, nullable) - 銀行戶名
*   `bank_name` (Varchar, nullable)
*   `bank_branch` (Varchar, nullable)
*   `bank_account_number` (Varchar, nullable)
*   `updated_at`

### 2.2 交易與紀錄 (Transactions & Logs)

#### Transactions (現金交易紀錄表)
紀錄所有現金流向 (儲值、提領、退款)。
*   `id` (PK, UUID)
*   `user_id` (FK)
*   `type` (Enum: 'deposit', 'withdrawal', 'refund', 'adjustment')
*   `amount` (Decimal) - 正數為加，負數為減
*   `status` (Enum: 'pending', 'completed', 'failed', 'cancelled')
*   `description` (Text)
*   `reference_id` (Varchar, nullable) - 外部金流訂單號
*   `created_at`

#### MileageRecords (里程紀錄表)
紀錄里程的獲取與消耗。
*   `id` (PK)
*   `user_id` (FK)
*   `type` (Enum: 'earn', 'redeem', 'expire', 'transfer')
*   `amount` (Integer)
*   `source` (Varchar) - 來源說明 (e.g., "Flight EK123", "Product Redemption")
*   `created_at`

### 2.3 內容與系統 (Content & System)

#### Announcements (公告表)
*   `id` (PK)
*   `title` (Varchar)
*   `content` (Text)
*   `is_published` (Boolean)
*   `published_at` (Datetime)
*   `created_at`

#### CustomerServiceMessages (客服訊息表)
*   `id` (PK)
*   `ticket_id` (UUID) - 對話群組 ID
*   `sender_type` (Enum: 'user', 'admin')
*   `sender_id` (FK -> Users.id, nullable if admin)
*   `content` (Text)
*   `read_at` (Datetime, nullable)
*   `created_at`

---

## 3. 後端 OOP 架構設計 (Backend Architecture)

採用 **分層架構 (Layered Architecture)**，配合 **依賴注入 (Dependency Injection)** 實現解耦。

### 3.1 層級職責

1.  **Controller Layer (控制器層)**
    *   負責接收 HTTP 請求，驗證輸入參數 (Validation)，呼叫 Service，並回傳標準化 JSON 回應。
    *   **不包含** 任何商業邏輯。
    *   *Examples:* `AuthController`, `WalletController`, `AdminUserController`.

2.  **Service Layer (服務層)**
    *   核心商業邏輯所在。
    *   處理交易 (Transactions)、邏輯運算、權限檢查。
    *   *Examples:*
        *   `AuthService`: 處理登入、註冊、Token 發放。
        *   `WalletService`: 處理餘額扣除、增加、提領申請邏輯 (需包含 DB Transaction)。
        *   `MileageService`: 計算會員等級升降、里程計算。

3.  **Repository Layer (倉儲層)**
    *   負責與資料庫溝通，封裝 SQL 或 ORM 操作。
    *   提供介面讓 Service 呼叫，若未來更換資料庫不需修改 Service 代碼。
    *   *Examples:* `UserRepository`, `TransactionRepository`.

4.  **Model / DTO (資料模型層)**
    *   `Entity`: 對應資料庫表的物件。
    *   `DTO (Data Transfer Object)`: 定義 API 輸入輸出的資料結構，避免直接暴露 Entity 結構。

### 3.2 類別設計範例 (以錢包功能為例)

#### Interface: IWalletService
```typescript
interface IWalletService {
    getBalance(userId: string): Promise<WalletDto>;
    deposit(userId: string, amount: number): Promise<Transaction>;
    withdraw(userId: string, amount: number, password: string): Promise<Transaction>;
}
```

#### Service: WalletService
```typescript
class WalletService implements IWalletService {
    constructor(
        private userRepo: UserRepository,
        private transactionRepo: TransactionRepository,
        private hashService: HashService
    ) {}

    async withdraw(userId: string, amount: number, pwd: string) {
        // 1. 驗證
        const user = await this.userRepo.findById(userId);
        if (!this.hashService.compare(pwd, user.wallet.password)) {
            throw new WrongPasswordException();
        }
        if (user.wallet.balance < amount) {
            throw new InsufficientBalanceException();
        }

        // 2. 執行交易 (採用 Unit of Work 或 DB Transaction)
        return await this.transactionRepo.runTransaction(async (manager) => {
            user.wallet.balance -= amount;
            await manager.save(user.wallet);
            
            const log = await manager.create(Transaction, {
                userId, type: 'withdrawal', amount: -amount
            });
            return log;
        });
    }
}
```

## 4. 擴充性與維護性策略

1.  **Service Interface**: 所有 Service 都應定義 Interface，方便未來進行 Unit Test 時 Mock 資料，或替換實作 (例如從本地轉帳切換為金流 API)。
2.  **Event Driven Architecture (事件驅動)**:
    *   當 `Transaction` 完成時，發送 `TransactionCreatedEvent`。
    *   `NotificationService` 監聽此事件發送 Email/App 推播。
    *   避免 WalletService 直接依賴 Email 寄送邏輯。
3.  **Error Handling**: 定義全域異常過濾器 (Global Exception Filter)，統一處理錯誤回應格式 (e.g., `{ code: 400, message: '餘額不足' }`)。
4.  **DTO Validation**: 在進 Controller 前就完成型別與格式檢查 (e.g., 金額必須大於 0)，減少 Service 層的防衛性程式碼。

## 5. Security (安全性規畫)

1.  **密碼雜湊**: 銀行密碼與登入密碼需使用 Argon2 或 Bcrypt 雜湊儲存。
2.  **Rate Limiting**: 針對 /login, /withdraw 等敏感 API 實施 IP 速率限制。
3.  **SQL Injection 防護**: 嚴格使用 ORM 或 Prepared Statements。
4.  **Audit Log (稽核紀錄)**: 紀錄管理員在後台的所有操作 (修改餘額、刪除帳號)，包含 IP、時間、操作前數值、操作後數值。
