<?php

namespace App\Controllers\Api;

use App\Libraries\Auth;
use App\Services\CustomerServiceService;

class CustomerServiceController extends BaseApiController
{
    public function messages()
    {
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 50);

        $result = (new CustomerServiceService())->getMessages(Auth::id(), $page, $limit);
        return $this->success($result);
    }

    public function sendMessage()
    {
        // Support both JSON and multipart
        $contentType = $this->request->getHeaderLine('Content-Type');
        if (str_contains($contentType, 'multipart')) {
            $content   = $this->request->getPost('content');
            $imageFile = $this->request->getFile('image');
        } else {
            $body      = $this->getJson();
            $content   = $body['content'] ?? null;
            $imageFile = null;
        }

        if (!$content && !$imageFile) {
            return $this->error('Message content or image is required');
        }

        $result = (new CustomerServiceService())->sendMessage(Auth::id(), $content, $imageFile);
        return $this->success($result['data'], 'Message sent', 201);
    }
}
