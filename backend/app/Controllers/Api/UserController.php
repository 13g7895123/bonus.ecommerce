<?php

namespace App\Controllers\Api;

use App\Libraries\Auth;
use App\Services\UserService;

class UserController extends BaseApiController
{
    public function me()
    {
        $profile = (new UserService())->getProfile(Auth::id());
        if (!$profile) {
            return $this->error('User not found', 404);
        }
        return $this->success($profile);
    }

    public function updateMe()
    {
        $data   = $this->getJson();
        $result = (new UserService())->updateProfile(Auth::id(), $data);
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success($result['data'], 'Profile updated');
    }

    public function verify()
    {
        // 前端傳 JSON（file ID 已預先上傳），用 getJson() 讀取
        $json = $this->getJson(true);  // true = assoc array
        $result = (new UserService())->submitVerification(Auth::id(), [], $json ?? []);
        return $this->success(null, $result['message']);
    }

    public function changePassword()
    {
        $data    = $this->getJson();
        $new     = $data['new_password']     ?? '';
        $confirm = $data['confirm_password'] ?? '';

        if (!$new || !$confirm) {
            return $this->error('請填寫所有欄位', 422);
        }

        if ($new !== $confirm) {
            return $this->error('新密碼與確認密碼不一致', 422);
        }

        $result = (new UserService())->changePassword(Auth::id(), $new);
        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }
        return $this->success(null, $result['message']);
    }

    public function uploadAvatar()
    {
        $file   = $this->request->getFile('avatar');
        $result = (new UserService())->uploadAvatar(Auth::id(), $file);
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success(['avatar_url' => $result['avatar_url']], 'Avatar updated');
    }

    public function sendVerificationEmail()
    {
        // TODO: 串接寄信功能（SMTP / 第三方 mail service）
        // 目前直接回傳成功，待日後實作以下流程：
        //   1. 產生 email_verify_token(uuid) 並寫入 users 表
        //   2. 寄送驗證連結至使用者信箱
        //   3. 另建 GET /users/me/verify-email?token=xxx 驗證 token 並更新 is_verified=1
        return $this->success(null, '驗證信已發送，請查收電子郵件');
    }
}
