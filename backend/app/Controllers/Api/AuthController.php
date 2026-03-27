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

    public function otpProvider()
    {
        $provider = \App\Services\OtpProviderFactory::activeProvider();
        return $this->success(['provider' => $provider]);
    }

    public function sendPhoneOtp()
    {
        $data  = $this->getJson();
        $phone = trim($data['phone'] ?? '');

        if (!$phone) {
            return $this->error('Phone number is required', 422);
        }

        // 防濫用 1：手機號碼是否已被他人註冊
        if ((new \App\Models\UserModel())->findByPhone($phone)) {
            return $this->error('此手機號碼已被註冊，請使用其他號碼', 422);
        }

        // 防濫用 2：IP 速率限制
        $ip    = $this->request->getIPAddress();
        $rl    = new \App\Services\SmsRateLimiter();
        $check = $rl->checkAndRecord($ip);
        if (!$check['allowed']) {
            $headers = ['Retry-After' => (string) ($check['retry_after'] ?? 1800)];
            foreach ($headers as $k => $v) {
                $this->response->setHeader($k, $v);
            }
            return $this->error($check['message'], 429);
        }

        $provider = \App\Services\OtpProviderFactory::make();

        // Firebase 提供者需要前端 SDK 回傳的 session_info（verificationId）
        $options = [];
        if (isset($data['session_info'])) {
            $options['session_info'] = $data['session_info'];
        }

        $result = $provider->sendOtp($phone, $options);

        if (!$result['success']) {
            return $this->error($result['message'] ?? 'Failed to send OTP', 500);
        }

        return $this->success(
            ['provider' => \App\Services\OtpProviderFactory::activeProvider()],
            'OTP sent successfully'
        );
    }

    public function verifyPhoneOtp()
    {
        $data  = $this->getJson();
        $phone = trim($data['phone'] ?? '');
        $code  = trim($data['code'] ?? '');

        if (!$phone || !$code) {
            return $this->error('Phone and code are required', 422);
        }

        $provider = \App\Services\OtpProviderFactory::make();
        $result   = $provider->verifyOtp($phone, $code);

        if (!$result['success']) {
            return $this->error($result['message'] ?? 'Invalid OTP', 422);
        }

        return $this->success(null, 'Phone verified successfully');
    }
}
