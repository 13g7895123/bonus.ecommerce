<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\UserWalletRepository;

class UserService
{
    public function __construct(
        private readonly UserRepository       $userRepo   = new UserRepository(),
        private readonly UserWalletRepository $walletRepo = new UserWalletRepository(),
        private readonly FileService          $fileService = new FileService(),
    ) {}

    public function getProfile(int $userId): ?array
    {
        $user = $this->userRepo->find($userId);
        if (!$user) {
            return null;
        }
        unset($user['password_hash']);
        $wallet = $this->walletRepo->findByUserId($userId);
        $user['wallet'] = $wallet ? [
            'balance'           => (float) $wallet['balance'],
            'miles_balance'     => (int) $wallet['miles_balance'],
            'has_bank_account'  => !empty($wallet['bank_account']),
            'has_withdrawal_pw' => !empty($wallet['withdrawal_password_hash']),
        ] : null;
        return $user;
    }

    public function updateProfile(int $userId, array $data): array
    {
        $allowed = array_intersect_key($data, array_flip(['full_name', 'phone', 'dob', 'country']));
        if (empty($allowed)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        $this->userRepo->update($userId, $allowed);
        return ['success' => true, 'data' => $this->getProfile($userId)];
    }

    public function uploadAvatar(int $userId, $file): array
    {
        $result = $this->fileService->upload($userId, $file, 'avatar');
        if (!$result['success']) {
            return $result;
        }

        $avatarUrl = $result['file']['url'];
        $this->userRepo->update($userId, [
            'avatar'         => $avatarUrl,
            'avatar_file_id' => $result['file']['id'],
        ]);

        return ['success' => true, 'avatar_url' => $avatarUrl, 'file' => $result['file']];
    }

    public function submitVerification(int $userId, array $files, array $data): array
    {
        $fileIds = [];
        foreach ($files as $key => $file) {
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $result = $this->fileService->upload($userId, $file, 'kyc');
                if ($result['success']) {
                    $fileIds[$key] = $result['file']['id'];
                }
            }
        }

        $verificationData = array_merge($data, ['file_ids' => $fileIds]);
        $this->userRepo->update($userId, [
            'verify_status'     => 'pending',
            'verification_data' => json_encode($verificationData),
        ]);

        return ['success' => true, 'message' => 'Verification submitted'];
    }

    public function changePassword(int $userId, string $newPassword): array
    {
        if (strlen($newPassword) < 6 || strlen($newPassword) > 12) {
            return ['success' => false, 'message' => '密碼須為 6-12 位'];
        }

        $this->userRepo->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);

        return ['success' => true, 'message' => '密碼已更新'];
    }
}
