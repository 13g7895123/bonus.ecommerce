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

    public function sendPhoneOtp()
    {
        $data  = $this->getJson();
        $phone = trim($data['phone'] ?? '');

        if (!$phone) {
            return $this->error('Phone number is required', 422);
        }

        $twilio = new \App\Services\TwilioService();
        $result = $twilio->sendOtp($phone);

        if (!$result['success']) {
            return $this->error($result['message'] ?? 'Failed to send OTP', 500);
        }

        return $this->success(null, 'OTP sent successfully');
    }

    public function verifyPhoneOtp()
    {
        $data  = $this->getJson();
        $phone = trim($data['phone'] ?? '');
        $code  = trim($data['code'] ?? '');

        if (!$phone || !$code) {
            return $this->error('Phone and code are required', 422);
        }

        $twilio = new \App\Services\TwilioService();
        $result = $twilio->verifyOtp($phone, $code);

        if (!$result['success']) {
            return $this->error($result['message'] ?? 'Invalid OTP', 422);
        }

        return $this->success(null, 'Phone verified successfully');
    }
}
