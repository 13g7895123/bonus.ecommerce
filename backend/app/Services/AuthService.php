<?php

namespace App\Services;

use App\Libraries\JwtHelper;
use App\Models\UserModel;
use App\Models\UserWalletModel;

class AuthService
{
    public function register(array $data): array
    {
        $userModel = model(UserModel::class);

        if ($userModel->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        $userId = $userModel->insert([
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'full_name'     => $data['full_name'] ?? null,
            'phone'         => $data['phone'] ?? null,
        ]);

        if (!$userId) {
            return ['success' => false, 'message' => 'Registration failed'];
        }

        model(UserWalletModel::class)->insert([
            'user_id'  => $userId,
            'balance'  => 0.00,
            'miles_balance' => 0,
        ]);

        $user  = $userModel->find($userId);
        $token = JwtHelper::generate(['user_id' => $userId, 'role' => $user['role']]);

        return ['success' => true, 'data' => ['token' => $token, 'user' => $this->sanitize($user)]];
    }

    public function login(string $email, string $password): array
    {
        $userModel = model(UserModel::class);
        $user      = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Account suspended'];
        }

        $token = JwtHelper::generate(['user_id' => $user['id'], 'role' => $user['role']]);
        return ['success' => true, 'data' => ['token' => $token, 'user' => $this->sanitize($user)]];
    }

    private function sanitize(array $user): array
    {
        unset($user['password_hash']);
        return $user;
    }
}
