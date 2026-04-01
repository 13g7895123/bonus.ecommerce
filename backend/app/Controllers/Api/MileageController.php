<?php

namespace App\Controllers\Api;

use App\Libraries\Auth;
use App\Services\MileageRedemptionItemService;
use App\Services\MileageRewardOrderService;
use App\Services\MileageRewardProductService;
use App\Services\MileageService;

class MileageController extends BaseApiController
{
    public function history()
    {
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 20);

        $result = (new MileageService())->getHistory(Auth::id(), $page, $limit);
        return $this->success($result);
    }

    public function redeem()
    {
        $data = $this->getJson();
        $code = trim($data['code'] ?? '');

        if (!$code) {
            return $this->error('請輸入里程代碼', 422);
        }

        $result = (new MileageService())->redeem(Auth::id(), $code);
        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }
        return $this->success([
            'miles_earned'  => $result['miles_earned'],
            'miles_balance' => $result['miles_balance'],
        ], $result['message']);
    }

    public function redemptionItems()
    {
        $items = (new MileageRedemptionItemService())->getActiveItems();
        return $this->success(['items' => $items]);
    }

    public function rewardProducts()
    {
        $itemId = (int) ($this->request->getGet('item_id') ?? 0);
        $svc    = new MileageRewardProductService();
        $items  = $itemId > 0
            ? $svc->getActiveByItemId($itemId)
            : $svc->getActiveProducts();
        return $this->success(['items' => $items]);
    }

    public function myRewardOrders()
    {
        $orders = (new MileageRewardOrderService())->getUserOrders(Auth::id());
        return $this->success(['items' => $orders]);
    }

    public function myPendingRewardOrders()
    {
        $orders = (new MileageRewardOrderService())->getUserPendingOrders(Auth::id());
        return $this->success(['items' => $orders]);
    }

    public function purchaseRewardProduct(int $productId)
    {
        $data     = $this->getJson();
        $quantity = (int) ($data['quantity'] ?? 1);

        $result = (new MileageRewardOrderService())->purchase(Auth::id(), $productId, $quantity);
        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }
        return $this->success(['order_id' => $result['order_id']], $result['message']);
    }
}
