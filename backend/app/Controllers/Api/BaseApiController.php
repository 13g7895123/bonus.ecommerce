<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class BaseApiController extends Controller
{
    protected function success(mixed $data = null, string $message = 'OK', int $code = 200): ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON([
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    protected function error(string $message = 'Error', int $code = 400): ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON([
            'code'    => $code,
            'message' => $message,
            'data'    => null,
        ]);
    }

    protected function getJson(): array
    {
        $body = $this->request->getBody();
        return json_decode($body, true) ?? [];
    }
}
