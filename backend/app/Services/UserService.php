<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\UserWalletRepository;

class UserService
{
    public function __construct(
        private readonly UserRepository       $userRepo   = new UserRepository(),
        private readonly UserWalletRepository $walletRepo = new UserWalletRepository(),
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
        $uploadDir = ROOTPATH . 'public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return ['success' => false, 'message' => 'Invalid file'];
        }

        $name = $userId . '_' . $file->getRandomName();
        $file->move($uploadDir, $name);

        $avatarUrl = base_url('uploads/avatars/' . $name);
        $this->userRepo->update($userId, ['avatar' => $avatarUrl]);

        return ['success' => true, 'avatar_url' => $avatarUrl];
    }

    public function submitVerification(int $userId, array $files, array $data): array
    {
        $uploadDir = WRITEPATH . 'uploads/kyc/' . $userId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $paths = [];
        foreach ($files as $key => $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $name = $file->getRandomName();
                $file->move($uploadDir, $name);
                $paths[$key] = 'kyc/' . $userId . '/' . $name;
            }
        }

        $verificationData = array_merge($data, ['files' => $paths]);
        $this->userRepo->update($userId, [
            'verify_status'     => 'pending',
            'verification_data' => json_encode($verificationData),
        ]);

        return ['success' => true, 'message' => 'Verification submitted'];
    }
}
