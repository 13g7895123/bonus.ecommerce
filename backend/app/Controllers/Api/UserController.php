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
        $files = $this->request->getFiles();
        $data  = $this->request->getPost();

        $result = (new UserService())->submitVerification(Auth::id(), $files, $data ?? []);
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
}
