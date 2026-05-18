<?php

namespace App\Filters;

use App\Libraries\Auth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * DeviceBindingFilter
 *
 * 確保已登入的使用者已完成設備綁定（register_device 不為空）。
 * 必須排在 JwtFilter 之後套用，因為需要 Auth::user() 已初始化。
 */
class DeviceBindingFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $user = Auth::user();

        if (!$user) {
            // JwtFilter 尚未執行或未通過，此處不做二次攔截
            return null;
        }

        if (empty($user['register_device'])) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'code'    => 403,
                    'message' => '尚未綁定設備，請聯繫客服完成設備綁定後再進行操作',
                    'data'    => null,
                ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
