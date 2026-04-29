<?php

namespace App\Services;

use App\Repositories\MileageRewardOrderRepository;
use App\Repositories\MileageRewardProductRepository;
use App\Repositories\MileageRecordRepository;
use App\Repositories\UserWalletRepository;

class MileageRewardOrderService
{
    public function __construct(
        private readonly MileageRewardOrderRepository   $orderRepo   = new MileageRewardOrderRepository(),
        private readonly MileageRewardProductRepository $productRepo = new MileageRewardProductRepository(),
        private readonly UserWalletRepository           $walletRepo  = new UserWalletRepository(),
        private readonly MileageRecordRepository        $mileageRepo = new MileageRecordRepository(),
    ) {}

    /**
     * 購買驗證 + 建立訂單（pending_review）
     * 規則：
      *  - 此時先扣商品金額並鎖定庫存
      *  - 里程扣除與現金回饋延後到 reviewOrder() 通過時
     */
    public function purchase(int $userId, int $productId, int $quantity): array
    {
        if ($quantity < 1) {
            return ['success' => false, 'message' => '數量必須大於 0'];
        }

        $product = $this->productRepo->find($productId);
        if (!$product || !$product['is_active']) {
            return ['success' => false, 'message' => '商品不存在或已下架'];
        }

        if ($product['stock'] < $quantity) {
            return ['success' => false, 'message' => '庫存不足'];
        }

        $wallet = $this->walletRepo->findByUserId($userId);
        if (!$wallet) {
            return ['success' => false, 'message' => '找不到錢包資訊'];
        }

        $totalPrice       = (float) $product['price']        * $quantity;
        $totalMilesPoints = (int)   $product['miles_points'] * $quantity;
        // 現金回饋金額 = 售價 × 回饋% × 數量
        $cashReward       = round((float) $product['price'] * (float) $product['mileage_amount'] / 100 * $quantity, 2);

        if ((float) $wallet['balance'] < $totalPrice) {
            return ['success' => false, 'message' => '帳戶餘額不足，無法購買', 'code' => 'insufficient_balance'];
        }

        $this->walletRepo->updateByUserId($userId, [
            'balance' => (float) $wallet['balance'] - $totalPrice,
        ]);

        // 鎖定庫存（避免併發超賣）
        $this->productRepo->update($productId, [
            'stock' => (int) $product['stock'] - $quantity,
        ]);

        // 建立訂單（待審核狀態，現金已先扣，里程尚未異動）
        $orderId = $this->orderRepo->create([
            'user_id'              => $userId,
            'product_id'           => $productId,
            'product_name'         => $product['name'],
            'product_image_url'    => $product['image_url'] ?? null,
            'quantity'             => $quantity,
            'unit_price'           => $product['price'],
            'unit_miles_points'    => $product['miles_points'],
            'total_price'          => $totalPrice,
            'total_miles_points'   => $totalMilesPoints,
            'mileage_reward_amount'=> 0,
            'cash_reward_amount'   => $cashReward,
            'status'               => 'pending_review',
        ]);

        return [
            'success'  => true,
            'order_id' => $orderId,
            'message'  => '已送出，訂單審核中',
        ];
    }

    public function getUserOrders(int $userId): array
    {
        return $this->orderRepo->getByUserId($userId);
    }

    public function getUserPendingOrders(int $userId): array
    {
        return $this->orderRepo->getPendingByUserId($userId);
    }

    public function getAllOrders(string $status = ''): array
    {
        return $this->orderRepo->getAll($status);
    }

    public function reviewOrder(int $orderId, string $action, ?string $note = null): array
    {
        $order = $this->orderRepo->find($orderId);
        if (!$order) {
            return ['success' => false, 'message' => '訂單不存在'];
        }

        if (!in_array($action, ['approve', 'reject'], true)) {
            return ['success' => false, 'message' => 'action 必須為 approve 或 reject'];
        }

        $newStatus = $action === 'approve' ? 'approved' : 'rejected';

        // 審核通過：扣里程、加回「商品金額 + 現金回饋」
        if ($action === 'approve') {
            $wallet = $this->walletRepo->findByUserId($order['user_id']);
            if (!$wallet) {
                return ['success' => false, 'message' => '找不到使用者錢包', 'code' => 'wallet_not_found'];
            }

            $totalMilesPoints = (int)   $order['total_miles_points'];
            $totalPrice       = (float) $order['total_price'];
            $cashReward       = (float) ($order['cash_reward_amount'] ?? 0);

            // 里程不足直接擋下，不變更狀態
            if ((int) $wallet['miles_balance'] < $totalMilesPoints) {
                return [
                    'success' => false,
                    'message' => '使用者里程點數不足，無法核准此訂單',
                    'code'    => 'insufficient_miles',
                    'status'  => 422,
                ];
            }

            $this->walletRepo->updateByUserId($order['user_id'], [
                'balance'       => (float) $wallet['balance']       + $totalPrice + $cashReward,
                'miles_balance' => (int)   $wallet['miles_balance'] - $totalMilesPoints,
            ]);

            // 紀錄里程扣減
            if ($totalMilesPoints > 0) {
                $this->mileageRepo->create([
                    'user_id' => $order['user_id'],
                    'type'    => 'spend',
                    'amount'  => $totalMilesPoints,
                    'source'  => 'reward_purchase',
                ]);
            }
        }

        // 拒絕：退回購買時先扣的商品金額，並還回庫存
        if ($action === 'reject') {
            $wallet = $this->walletRepo->findByUserId($order['user_id']);
            if (!$wallet) {
                return ['success' => false, 'message' => '找不到使用者錢包', 'code' => 'wallet_not_found'];
            }

            $this->walletRepo->updateByUserId($order['user_id'], [
                'balance' => (float) $wallet['balance'] + (float) $order['total_price'],
            ]);

            $product = $this->productRepo->find($order['product_id']);
            if ($product) {
                $this->productRepo->update($order['product_id'], [
                    'stock' => (int) $product['stock'] + (int) $order['quantity'],
                ]);
            }
        }

        $this->orderRepo->update($orderId, [
            'status'      => $newStatus,
            'review_note' => $note,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'message' => $action === 'approve' ? '已審核通過並入帳' : '已拒絕並退款'];
    }
}
