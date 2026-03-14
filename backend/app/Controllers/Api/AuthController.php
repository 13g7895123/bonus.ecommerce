<?php

namespace App\Controllers\Api;

use App\Services\AuthService;

class AuthController extends BaseApiController
{
    public function register()
    {
        $data = $this->getJson();
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return $this->error('Email and password are required');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('Invalid email format');
        }
        if (strlen($password) < 6) {
            return $this->error('Password must be at least 6 characters');
        }

        $result = (new AuthService())->register([
            'email'     => $email,
            'password'  => $password,
            'full_name' => $data['full_name'] ?? null,
            'phone'     => $data['phone'] ?? null,
        ]);

        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success($result['data'], 'Registration successful', 201);
    }

    public function login()
    {
        $data     = $this->getJson();
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return $this->error('Email and password are required');
        }

        $result = (new AuthService())->login($email, $password);
        if (!$result['success']) {
            return $this->error($result['message'], 401);
        }
        return $this->success($result['data'], 'Login successful');
    }

    public function forgotPassword()
    {
        // Placeholder — email sending not implemented in this demo
        $data  = $this->getJson();
        $email = trim($data['email'] ?? '');
        if (!$email) {
            return $this->error('Email is required');
        }
        return $this->success(null, 'If the email exists, a reset link has been sent');
    }

    public function resetPassword()
    {
        // Placeholder — token-based reset not implemented in this demo
        return $this->success(null, 'Password reset feature coming soon');
    }
}
