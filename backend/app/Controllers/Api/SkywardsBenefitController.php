<?php

namespace App\Controllers\Api;

use App\Libraries\Auth;
use App\Models\UserModel;
use App\Services\SkywardsBenefitService;

class SkywardsBenefitController extends BaseApiController
{
    public function index()
    {
        $user = model(UserModel::class)->find(Auth::id());
        $tier = $user['tier'] ?? 'regular';
        $item = (new SkywardsBenefitService())->getActiveItemForTier($tier);

        return $this->success([
            'tier'  => $tier,
            'item'  => $item,
            'items' => $item ? [$item] : [],
        ]);
    }
}
