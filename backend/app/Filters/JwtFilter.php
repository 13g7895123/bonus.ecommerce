<?php

namespace App\Filters;

use App\Libraries\Auth;
use App\Libraries\JwtHelper;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $header = $request->getHeaderLine('Authorization');
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['code' => 401, 'message' => 'Unauthorized', 'data' => null]);
        }

        $token   = substr($header, 7);
        $payload = JwtHelper::verify($token);
        if (!$payload || empty($payload['user_id'])) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['code' => 401, 'message' => 'Token invalid or expired', 'data' => null]);
        }

        $user = model(UserModel::class)->find($payload['user_id']);
        if (!$user || $user['status'] !== 'active') {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['code' => 403, 'message' => 'Account suspended', 'data' => null]);
        }

        Auth::setUser($user);

        // Check for admin argument
        if (in_array('admin', (array) $arguments, true) && !Auth::isAdmin()) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['code' => 403, 'message' => 'Forbidden', 'data' => null]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
