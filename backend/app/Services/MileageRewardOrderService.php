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
     * 購買驗證 + 建立訂單
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

        $totalPrice      = (float) $product['price']         * $quantity;
        $totalMilesPoints = (int)  $product['miles_points']  * $quantity;
        $mileageReward   = round((float) $product['price'] * (float) $product['mileage_amount'] / 100 * $quantity);

        // 驗證帳戶餘額
        if ((float) $wallet['balance'] < $totalPrice) {
            return ['success' => false, 'message' => '帳戶餘額不足，無法購買', 'code' => 'insufficient_balance'];
        }

        // 驗證里程點數
        if ((int) $wallet['miles_balance'] < $totalMilesPoints) {
            return ['success' => false, 'message' => '里程點數不足，無法購買', 'code' => 'insufficient_miles'];
        }

        // 扣除餘額與里程點數
        $newBalance      = (float) $wallet['balance']       - $totalPrice;
        $newMilesBalance = (int)   $wallet['miles_balance'] - $totalMilesPoints;

        $this->walletRepo->updateByUserId($userId, [
            'balance'       => $newBalance,
            'miles_balance' => $newMilesBalance,
        ]);

        // 減少庫存
        $this->productRepo->update($productId, [
            'stock' => (int) $product['stock'] - $quantity,
        ]);

        // 建立訂單（待審核狀態）
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
            'mileage_reward_amount'=> $mileageReward,
            'status'               => 'pending_review',
        ]);

        return [
            'success'  => true,
            'order_id' => $orderId,
            'message'  => '購買成功，等待審核',
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

        // 若審核通過，發放里程回饋
        if ($action === 'approve') {
            $rewardAmount = (int) $order['mileage_reward_amount'];
            if ($rewardAmount > 0) {
                $wallet = $this->walletRepo->findByUserId($order['user_id']);
                if ($wallet) {
                    $this->walletRepo->updateByUserId($order['user_id'], [
                        'miles_balance' => (int) $wallet['miles_balance'] + $rewardAmount,
                    ]);
                }
                $this->mileageRepo->create([
                    'user_id' => $order['user_id'],
                    'type'    => 'earn',
                    'amount'  => $rewardAmount,
                    'source'  => 'reward_purchase',
                ]);
            }
        }

        // 若拒絕，退還餘額與里程點數
        if ($action === 'reject') {
            $wallet = $this->walletRepo->findByUserId($order['user_id']);
            if ($wallet) {
                $this->walletRepo->updateByUserId($order['user_id'], [
                    'balance'       => (float) $wallet['balance']       + (float) $order['total_price'],
                    'miles_balance' => (int)   $wallet['miles_balance'] + (int)   $order['total_miles_points'],
                ]);
            }
            // 退還庫存
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

        return ['success' => true, 'message' => $action === 'approve' ? '已審核通過' : '已拒絕並退款'];
    }
}
