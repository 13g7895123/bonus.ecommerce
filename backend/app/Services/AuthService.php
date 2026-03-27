<?php

namespace App\Services;

use App\Libraries\JwtHelper;
use App\Repositories\UserRepository;
use App\Repositories\UserWalletRepository;

class AuthService
{
    public function __construct(
        private readonly UserRepository       $userRepo   = new UserRepository(),
        private readonly UserWalletRepository $walletRepo = new UserWalletRepository(),
    ) {}

    public function register(array $data): array
    {
        if ($this->userRepo->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // 有填入手機號碼時，確認該號碼尚未被其他帳號使用
        if (!empty($data['phone'])) {
            $existing = (new \App\Models\UserModel())->findByPhone($data['phone']);
            if ($existing) {
                return ['success' => false, 'message' => '此手機號碼已被其他帳號使用'];
            }
        }

        $userId = $this->userRepo->create([
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'full_name'     => $data['full_name'] ?? null,
            'phone'         => $data['phone'] ?? null,
        ]);

        if (!$userId) {
            return ['success' => false, 'message' => 'Registration failed'];
        }

        $this->walletRepo->create([
            'user_id'       => $userId,
            'balance'       => 0.00,
            'miles_balance' => 0,
        ]);

        $user = $this->userRepo->find($userId);
        $token = JwtHelper::generate(['user_id' => $userId, 'role' => $user['role']]);

        return ['success' => true, 'data' => ['token' => $token, 'user' => $this->sanitize($user)]];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepo->findByEmail($email);

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

    /**
     * 根據 userId 產生 JWT token（OTP 驗證通過後使用）
     */
    public function createTokenForUserId(int $userId): ?array
    {
        $user = $this->userRepo->find($userId);
        if (!$user) return null;
        $token = JwtHelper::generate(['user_id' => $userId, 'role' => $user['role']]);
        return ['token' => $token, 'user' => $this->sanitize($user)];
    }
}
