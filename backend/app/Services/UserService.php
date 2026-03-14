<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserWalletModel;

class UserService
{
    public function getProfile(int $userId): ?array
    {
        $user = model(UserModel::class)->find($userId);
        if (!$user) {
            return null;
        }
        unset($user['password_hash']);
        $wallet = model(UserWalletModel::class)->findByUserId($userId);
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
        $allowed = array_intersect_key($data, array_flip(['full_name', 'phone']));
        if (empty($allowed)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        model(UserModel::class)->update($userId, $allowed);
        return ['success' => true, 'data' => $this->getProfile($userId)];
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
        model(UserModel::class)->update($userId, [
            'verify_status'     => 'pending',
            'verification_data' => json_encode($verificationData),
        ]);

        return ['success' => true, 'message' => 'Verification submitted'];
    }
}
