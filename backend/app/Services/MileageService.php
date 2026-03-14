<?php

namespace App\Services;

use App\Models\MileageRecordModel;
use App\Models\UserWalletModel;

class MileageService
{
    public function getHistory(int $userId, int $page = 1, int $limit = 20): array
    {
        $result = model(MileageRecordModel::class)->getByUserId($userId, $page, $limit);
        $wallet = model(UserWalletModel::class)->findByUserId($userId);

        $result['currentMiles'] = $wallet ? (int) $wallet['miles_balance'] : 0;
        return $result;
    }
}
