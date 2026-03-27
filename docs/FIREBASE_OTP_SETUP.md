# Firebase Authentication Phone OTP 申請與設定流程

## 目錄

1. [建立 Firebase 專案](#1-建立-firebase-專案)
2. [啟用電話號碼驗證](#2-啟用電話號碼驗證)
3. [取得 Web API 金鑰](#3-取得-web-api-金鑰)
4. [設定授權網域](#4-設定授權網域)
5. [填入環境變數](#5-填入環境變數)
6. [在後台切換 SMS 提供者](#6-在後台切換-sms-提供者)
7. [測試驗證流程](#7-測試驗證流程)
8. [正式上線前注意事項](#8-正式上線前注意事項)
9. [費用說明](#9-費用說明)
10. [常見問題](#10-常見問題)

---

## 1. 建立 Firebase 專案

1. 前往 [Firebase Console](https://console.firebase.google.com/)，使用 Google 帳號登入。

2. 點擊右上角 **「新增專案」**。

3. 輸入專案名稱（例如：`ecommerce-otp`），點擊 **「繼續」**。

4. Google Analytics 可選擇啟用或停用，依需求決定，點擊 **「建立專案」**。

5. 等待專案建立完成後，點擊 **「繼續」** 進入專案控制台。

---

## 2. 啟用電話號碼驗證

1. 在左側選單中，點擊 **「Build」→「Authentication」**。

2. 點擊 **「開始使用」**（若為第一次設定）。

3. 切換到 **「Sign-in method」** 分頁。

4. 在提供者清單中找到 **「電話」**，點擊右側鉛筆圖示（編輯）。

5. 將右上角的開關切換為 **「啟用」**，點擊 **「儲存」**。

   > 啟用後，清單中「電話」旁邊會顯示綠色勾勾。

---

## 3. 取得 Web API 金鑰

1. 點擊左上角 **齒輪圖示（⚙）→「專案設定」**。

2. 在 **「一般」** 分頁，向下捲動到 **「您的應用程式」** 區塊。

3. 若尚未新增應用程式，點擊 **「新增應用程式」**，選擇 **Web（`</>`）** 圖示。
   - 輸入應用程式暱稱（例如：`ecommerce-web`）
   - **不需要**勾選「同時為此應用程式設定 Firebase Hosting」
   - 點擊 **「註冊應用程式」**

4. 完成後，在應用程式設定區塊中可看到 `firebaseConfig` 物件，複製其中的 **`apiKey`** 值。

   ```
   apiKey: "AIzaSy__________________________"
   ```

   > 這就是 `FIREBASE_WEB_API_KEY`。

5. 同一頁面最上方也可在 **「專案設定 → 一般」** 看到 **「網路 API 金鑰」**，兩者相同。

---

## 4. 設定授權網域

Firebase 電話驗證使用 reCAPTCHA 保護，必須將網站網域加入授權清單，否則前端會報錯。

1. 前往 **「Authentication → 設定 → 授權網域」**。

2. 預設已包含 `localhost`，若要在正式環境使用，點擊 **「新增網域」**，輸入你的網域（例如：`yourdomain.com`）。

3. 點擊 **「新增」** 儲存。

   > 若使用 IP 位址存取（例如開發環境的 `192.168.x.x`），同樣需要加入。

---

## 5. 填入環境變數

開啟專案的環境設定檔，填入取得的 API 金鑰：

### 開發環境

檔案：`docker/envs/.env.development`（若有）或直接修改 `docker/envs/.env.production`

```dotenv
# Firebase Authentication Phone（https://console.firebase.google.com/ → 專案設定 → Web API 金鑰）
FIREBASE_WEB_API_KEY=AIzaSy__________________________
```

### 正式環境

檔案：`docker/envs/.env.production`

```dotenv
FIREBASE_WEB_API_KEY=AIzaSy__________________________
```

填入後，重新啟動容器讓設定生效：

```bash
cd docker
docker compose down && docker compose up -d
```

---

## 6. 在後台切換 SMS 提供者

1. 登入後台管理系統（預設路徑：`/admin`）。

2. 在左側選單找到 **「SMS 提供者設定」**，或直接前往 `/admin/sms-provider`。

3. 頁面會顯示目前使用的提供者（`twilio` 或 `firebase`）。

4. 選擇 **「Firebase Phone Auth」**，點擊 **「儲存設定」**。

5. 切換後立即生效，新的 OTP 發送請求將使用 Firebase 驗證。

   > 若要切換回 Twilio，同樣在此頁面選擇 **「Twilio Verify」** 並儲存即可。

---

## 7. 測試驗證流程

### 使用測試號碼（不消耗簡訊配額）

Firebase 提供測試電話號碼功能，可在不實際發送簡訊的情況下測試流程：

1. 前往 **「Authentication → Sign-in method → 電話」**，點擊 **「電話號碼用於測試」** 展開。

2. 點擊 **「新增電話號碼以進行測試」**，輸入測試電話號碼與固定驗證碼。

   範例：
   ```
   電話號碼：+886900000001
   驗證碼：123456
   ```

3. 儲存後，在前端用此電話號碼註冊，輸入指定驗證碼即可通過，不會實際發送簡訊。

### 實際發送測試

1. 前往專案前端的 `/register` 頁面。
2. 輸入真實手機號碼（格式：`+886912345678`）。
3. 點擊 **「發送驗證碼」**，等待 Firebase 簡訊。
4. 輸入收到的 6 位數驗證碼完成驗證。

---

## 8. 正式上線前注意事項

| 項目 | 說明 |
|------|------|
| **授權網域** | 確認正式網域已加入 Firebase Authentication → 授權網域 |
| **reCAPTCHA** | 前端 reCAPTCHA 容器 `<div id="recaptcha-container">` 必須存在於頁面中 |
| **HTTPS** | Firebase 電話驗證在生產環境**必須使用 HTTPS**，HTTP 僅限 localhost |
| **API 金鑰限制** | 建議在 Google Cloud Console 對 `FIREBASE_WEB_API_KEY` 設定 HTTP 參照來源限制，防止濫用 |
| **配額監控** | 定期至 Firebase Console 查看 Authentication 使用量，避免超出免費配額 |
| **驗證碼有效期** | Firebase OTP 預設有效期為 **5 分鐘** |

### 設定 API 金鑰限制（建議）

1. 前往 [Google Cloud Console](https://console.cloud.google.com/) → 選擇對應專案。
2. 導航至 **「API 和服務 → 憑證」**。
3. 找到對應的 API 金鑰，點擊編輯。
4. 在 **「應用程式限制」** 選擇 **「HTTP 參照來源（網站）」**，填入你的網域。
5. 儲存即可。

---

## 9. 費用說明

Firebase Authentication 電話驗證費用（截至 2025 年）：

| 方案 | 免費配額 | 超出後費用 |
|------|----------|-----------|
| **Spark（免費方案）** | 每月 10 次驗證（**僅限測試號碼，不支援真實簡訊**） | 不可超出 |
| **Blaze（隨用隨付）** | 每月 10,000 次（台灣地區） | 每次約 $0.01 USD |

> **重要**：若要發送真實簡訊給使用者，**必須啟用 Blaze 方案**（需綁定信用卡）。  
> 免費 Spark 方案僅能使用測試電話號碼功能，無法實際發送簡訊。

### 升級至 Blaze 方案

1. 在 Firebase Console 左下角點擊 **「升級」**。
2. 選擇 **「Blaze - 隨用隨付」**，依引導綁定付款方式。
3. 建議設定 **預算快訊**，避免費用超出預期。

---

## 10. 常見問題

### Q1：前端出現 `auth/invalid-app-credential` 錯誤

**原因**：reCAPTCHA 驗證失敗，通常是網域未加入授權清單。  
**解決**：至 Firebase Console → Authentication → 設定 → 授權網域，加入目前使用的網域或 IP。

### Q2：前端出現 `auth/too-many-requests` 錯誤

**原因**：短時間內對同一號碼發送過多請求，Firebase 自動封鎖。  
**解決**：等待一段時間後再試，或在 Firebase Console 解除封鎖。

### Q3：簡訊收不到

**可能原因**：
- 使用的是 Spark 免費方案（無法發送真實簡訊）
- 電話號碼格式錯誤（必須含國碼，如 `+886912345678`）
- 號碼已被 Firebase 列入黑名單

**解決**：確認 Blaze 方案已啟用，電話號碼格式正確。

### Q4：後台切換後前端還是使用舊的提供者

**原因**：前端有快取 OTP 提供者的 API 結果。  
**解決**：重新整理頁面，或清除 localStorage / sessionStorage。

### Q5：`FIREBASE_WEB_API_KEY` 填入後重啟容器仍無效

**解決**：確認已重新 build 後端映像：
```bash
cd docker
docker compose down
docker compose build backend
docker compose up -d
```

---

*最後更新：2026-03-27*
