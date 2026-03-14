<?php

namespace App\Services;

use App\Repositories\FileRepository;

/**
 * FileService — 統一管理所有檔案上傳與存取
 *
 * 公開路徑 (public/uploads/{type}/) 由 nginx 直接提供靜態檔案，
 * 可透過 base_url('uploads/{type}/{filename}') 存取。
 *
 * 各類型存放目錄：
 *   avatar      → public/uploads/avatars/
 *   kyc         → public/uploads/kyc/
 *   cs_message  → public/uploads/cs/
 *   general     → public/uploads/general/
 */
class FileService
{
    private const ALLOWED_MIME = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'application/pdf',
    ];

    private const MAX_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB

    private const TYPE_DIR = [
        'avatar'     => 'uploads/avatars',
        'kyc'        => 'uploads/kyc',
        'cs_message' => 'uploads/cs',
        'general'    => 'uploads/general',
    ];

    public function __construct(
        private readonly FileRepository $fileRepo = new FileRepository(),
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // 上傳
    // ─────────────────────────────────────────────────────────────────────

    /**
     * 上傳單一檔案並寫入 files 表。
     *
     * @param  int    $userId  上傳使用者 ID
     * @param  mixed  $file    CI4 UploadedFile 物件
     * @param  string $type    檔案類型：avatar / kyc / cs_message / general
     * @return array  ['success', 'message', 'file'] — file 包含 id, uuid, url, path, mime_type, size, original_name
     */
    public function upload(int $userId, $file, string $type = 'general'): array
    {
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return ['success' => false, 'message' => 'Invalid or missing file'];
        }

        // MIME 白名單驗證
        $mime = $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return ['success' => false, 'message' => 'Unsupported file type: ' . $mime];
        }

        // 檔案大小驗證
        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            return ['success' => false, 'message' => 'File size exceeds 5 MB limit'];
        }

        $subDir  = self::TYPE_DIR[$type] ?? self::TYPE_DIR['general'];
        $absDir  = ROOTPATH . 'public/' . $subDir . '/';
        if (!is_dir($absDir)) {
            mkdir($absDir, 0755, true);
        }

        $uuid       = $this->generateUuid();
        $ext        = strtolower($file->getExtension());
        $storedName = $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        $file->move($absDir, $storedName);

        $relativePath = $subDir . '/' . $storedName;
        // URL 統一走 API 路徑，由 /api/v1/files/{uuid}/serve 提供檔案
        $fullUrl      = base_url('api/v1/files/' . $uuid . '/serve');

        $fileId = $this->fileRepo->create([
            'uuid'          => $uuid,
            'user_id'       => $userId,
            'type'          => $type,
            'original_name' => $file->getClientName(),
            'stored_name'   => $storedName,
            'path'          => $relativePath,
            'url'           => $fullUrl,
            'mime_type'     => $mime,
            'size'          => $file->getSize(),
            'is_public'     => 1,
        ]);

        $record = $this->fileRepo->find($fileId);

        return [
            'success' => true,
            'message' => 'File uploaded successfully',
            'file'    => $this->formatRecord($record),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // 查詢
    // ─────────────────────────────────────────────────────────────────────

    public function getById(int $id): ?array
    {
        $record = $this->fileRepo->find($id);
        return $record ? $this->formatRecord($record) : null;
    }

    public function getByUuid(string $uuid): ?array
    {
        $record = $this->fileRepo->findByUuid($uuid);
        return $record ? $this->formatRecord($record) : null;
    }

    public function getByUserId(int $userId, ?string $type = null): array
    {
        return array_map(
            fn($r) => $this->formatRecord($r),
            $this->fileRepo->findByUserId($userId, $type),
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // 刪除
    // ─────────────────────────────────────────────────────────────────────

    public function delete(int $id, int $requestUserId): array
    {
        $record = $this->fileRepo->find($id);
        if (!$record) {
            return ['success' => false, 'message' => 'File not found'];
        }
        if ((int) $record['user_id'] !== $requestUserId) {
            return ['success' => false, 'message' => 'Forbidden'];
        }

        $absPath = ROOTPATH . 'public/' . $record['path'];
        if (is_file($absPath)) {
            unlink($absPath);
        }
        $this->fileRepo->delete($id);

        return ['success' => true, 'message' => 'File deleted'];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────

    private function formatRecord(array $record): array
    {
        // url 統一以 API serve 路徑呈現，確保路徑一致性
        $apiUrl = base_url('api/v1/files/' . $record['uuid'] . '/serve');

        return [
            'id'            => (int) $record['id'],
            'uuid'          => $record['uuid'],
            'user_id'       => $record['user_id'] ? (int) $record['user_id'] : null,
            'type'          => $record['type'],
            'original_name' => $record['original_name'],
            'url'           => $apiUrl,
            'path'          => $record['path'],
            'mime_type'     => $record['mime_type'],
            'size'          => $record['size'] ? (int) $record['size'] : null,
            'is_image'      => str_starts_with($record['mime_type'] ?? '', 'image/'),
            'created_at'    => $record['created_at'],
        ];
    }

    private function generateUuid(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
