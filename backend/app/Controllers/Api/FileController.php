<?php

namespace App\Controllers\Api;

use App\Libraries\Auth;
use App\Services\FileService;

/**
 * FileController — 提供統一的檔案上傳與查詢 API
 *
 * POST   /api/v1/files/upload             (JWT)    — 上傳檔案，回傳檔案資訊
 * GET    /api/v1/files/{id}               (public) — 取得指定檔案 JSON 資訊
 * GET    /api/v1/files/by-uuid/{uuid}     (public) — 透過 UUID 取得 JSON 資訊
 * GET    /api/v1/files/{uuid}/serve       (public) — 直接提供檔案內容（用於 <img src>）
 * GET    /api/v1/files/mine              (JWT)    — 取得自己上傳的所有檔案
 * DELETE /api/v1/files/{id}               (JWT)    — 刪除自己上傳的檔案
 */
class FileController extends BaseApiController
{
    private FileService $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    /**
     * POST /api/v1/files/upload
     *
     * Form fields:
     *   file  — 上傳的檔案（必要）
     *   type  — 檔案類型 avatar|kyc|cs_message|general（選用，預設 general）
     */
    public function upload(): mixed
    {
        $file = $this->request->getFile('file');
        $type = $this->request->getPost('type') ?? 'general';

        $result = $this->fileService->upload(Auth::id(), $file, $type);

        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }

        return $this->success($result['file'], $result['message'], 201);
    }

    /**
     * GET /api/v1/files/(:num)
     * 公開端點（已上傳的公開檔案不需驗證即可查詢資訊）
     */
    public function show(int $id): mixed
    {
        $file = $this->fileService->getById($id);
        if (!$file) {
            return $this->error('File not found', 404);
        }
        return $this->success($file);
    }

    /**
     * GET /api/v1/files/by-uuid/(:segment)
     */
    public function showByUuid(string $uuid): mixed
    {
        $file = $this->fileService->getByUuid($uuid);
        if (!$file) {
            return $this->error('File not found', 404);
        }
        return $this->success($file);
    }

    /**
     * GET /api/v1/files/mine
     * 取得目前登入使用者自己上傳的所有檔案
     */
    public function mine(): mixed
    {
        $type  = $this->request->getGet('type');
        $files = $this->fileService->getByUserId(Auth::id(), $type ?: null);
        return $this->success($files);
    }

    /**
     * GET /api/v1/files/(:segment)/serve
     * 直接輸出檔案內容（供 <img src="..."> 使用）
     * 公開端點（is_public=1 的檔案不需驗證）
     * 支援以整數 ID 或 UUID 查詢
     */
    public function serve(string $idOrUuid): mixed
    {
        // 若全部為數字則以整數 ID 查詢，否則以 UUID 查詢
        $file = ctype_digit($idOrUuid)
            ? $this->fileService->getById((int) $idOrUuid)
            : $this->fileService->getByUuid($idOrUuid);

        if (!$file) {
            return $this->error('File not found', 404);
        }

        $absPath = ROOTPATH . 'public/' . $file['path'];
        if (!is_file($absPath)) {
            return $this->error('File not found on disk', 404);
        }

        $mime = $file['mime_type'] ?: mime_content_type($absPath) ?: 'application/octet-stream';

        // 設定快取 Header，避免瀏覽器重複請求
        $this->response->setHeader('Cache-Control', 'public, max-age=31536000, immutable');
        $this->response->setHeader('ETag', md5($uuid));
        $this->response->setContentType($mime);
        $this->response->setBody(file_get_contents($absPath));
        return $this->response;
    }

    /**
     * DELETE /api/v1/files/(:num)
     */
    public function destroy(int $id): mixed
    {
        $result = $this->fileService->delete($id, Auth::id());
        if (!$result['success']) {
            return $this->error($result['message'], 403);
        }
        return $this->success(null, $result['message']);
    }
}
