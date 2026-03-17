<?php

namespace App\Controllers\Api;

use App\Services\SkywardsBenefitService;

class SkywardsBenefitController extends BaseApiController
{
    public function index()
    {
        $items = (new SkywardsBenefitService())->getActiveItems();
        return $this->success(['items' => $items]);
    }
}
