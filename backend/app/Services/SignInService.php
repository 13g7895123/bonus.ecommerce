<?php

namespace App\Services;

use App\Models\AppConfigModel;
use App\Models\SignInCampaignModel;
use App\Models\UserSignInModel;
use App\Repositories\MileageRecordRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserWalletRepository;
use Config\Database;

class SignInService
{
    public function __construct(
        private readonly SignInCampaignModel $campaignModel = new SignInCampaignModel(),
        private readonly UserSignInModel $recordModel = new UserSignInModel(),
        private readonly UserWalletRepository $walletRepo = new UserWalletRepository(),
        private readonly MileageRecordRepository $mileageRecordRepo = new MileageRecordRepository(),
        private readonly AppConfigModel $configModel = new AppConfigModel(),
        private readonly UserRepository $userRepo = new UserRepository(),
    ) {}

    public function getOrCreateCampaign(int $year, int $month): array
    {
        $campaign = $this->campaignModel->where('year', $year)->where('month', $month)->first();
        if ($campaign) {
            return $campaign;
        }

        $campaignId = $this->campaignModel->insert([
            'year'               => $year,
            'month'              => $month,
            'title'              => sprintf('%d年【%d】月期 簽到活動', $year, $month),
            'base_reward_miles'  => 0,
            'streak_days'        => 0,
            'streak_bonus_miles' => 0,
            'is_active'          => 1,
        ], true);

        return $this->campaignModel->find($campaignId);
    }

    public function getStatus(int $userId, ?\DateTimeImmutable $now = null): array
    {
        $now ??= new \DateTimeImmutable('now');
        $year = (int) $now->format('Y');
        $month = (int) $now->format('n');
        $today = $now->format('Y-m-d');

        $campaign = $this->getOrCreateCampaign($year, $month);
        $records = $this->recordModel
            ->where('user_id', $userId)
            ->where('campaign_id', $campaign['id'])
            ->orderBy('sign_in_date', 'ASC')
            ->findAll();

        $signedDates = array_values(array_map(static fn(array $row): string => $row['sign_in_date'], $records));
        $signedToday = in_array($today, $signedDates, true);
        $currentStreak = $this->countTrailingStreak(
            $signedDates,
            $signedToday ? $now : $now->modify('-1 day')
        );

        return [
            'campaign' => [
                'id'                 => (int) $campaign['id'],
                'year'               => (int) $campaign['year'],
                'month'              => (int) $campaign['month'],
                'title'              => $campaign['title'],
                'base_reward_miles'  => (int) ($campaign['base_reward_miles'] ?? 0),
                'streak_days'        => (int) ($campaign['streak_days'] ?? 0),
                'streak_bonus_miles' => (int) ($campaign['streak_bonus_miles'] ?? 0),
                'is_active'          => (int) ($campaign['is_active'] ?? 1),
            ],
            'today'               => $today,
            'signed_dates'        => $signedDates,
            'signed_count'        => count($signedDates),
            'has_signed_today'    => $signedToday,
            'current_streak_days' => $currentStreak,
            'can_make_up'         => false,
        ];
    }

    public function signToday(int $userId, ?\DateTimeImmutable $now = null): array
    {
        $now ??= new \DateTimeImmutable('now');
        $today = $now->format('Y-m-d');
        $year = (int) $now->format('Y');
        $month = (int) $now->format('n');

        $campaign = $this->getOrCreateCampaign($year, $month);
        if ((int) ($campaign['is_active'] ?? 1) !== 1) {
            return ['success' => false, 'message' => '本月簽到活動未啟用'];
        }

        $exists = $this->recordModel
            ->where('user_id', $userId)
            ->where('campaign_id', $campaign['id'])
            ->where('sign_in_date', $today)
            ->first();

        if ($exists) {
            return [
                'success' => true,
                'message' => '今日已簽到',
                'sign_in_date' => $today,
                'reward_miles' => (int) ($exists['awarded_miles'] ?? 0),
                'streak_days' => (int) ($exists['streak_day_count'] ?? 0),
                'is_streak_bonus' => (int) ($exists['is_streak_bonus'] ?? 0),
            ];
        }

        $records = $this->recordModel
            ->where('user_id', $userId)
            ->where('campaign_id', $campaign['id'])
            ->findAll();
        $signedDates = array_values(array_map(static fn(array $row): string => $row['sign_in_date'], $records));

        $previousStreak = $this->countTrailingStreak($signedDates, $now->modify('-1 day'));
        $streakDays = $previousStreak + 1;
        $baseReward = (int) ($campaign['base_reward_miles'] ?? 0);
        $streakTriggerDays = (int) ($campaign['streak_days'] ?? 0);
        $streakBonusMiles = (int) ($campaign['streak_bonus_miles'] ?? 0);
        $isStreakBonus = $streakTriggerDays > 0
            && $streakBonusMiles > 0
            && $streakDays % $streakTriggerDays === 0;
        $rewardMiles = $baseReward + ($isStreakBonus ? $streakBonusMiles : 0);

        $db = Database::connect();
        $db->transStart();

        $this->recordModel->insert([
            'user_id'          => $userId,
            'campaign_id'      => $campaign['id'],
            'sign_in_date'     => $today,
            'awarded_miles'    => $rewardMiles,
            'streak_day_count' => $streakDays,
            'is_streak_bonus'  => $isStreakBonus ? 1 : 0,
        ]);

        $wallet = $this->walletRepo->findByUserId($userId);
        $newMilesBalance = $rewardMiles;

        if ($wallet) {
            $newMilesBalance = (int) $wallet['miles_balance'] + $rewardMiles;
            $this->walletRepo->updateByUserId($userId, ['miles_balance' => $newMilesBalance]);
        } else {
            $this->walletRepo->create([
                'user_id' => $userId,
                'balance' => 0,
                'miles_balance' => $rewardMiles,
            ]);
        }

        if ($rewardMiles > 0) {
            $this->mileageRecordRepo->create([
                'user_id' => $userId,
                'type' => 'earn',
                'amount' => $rewardMiles,
                'source' => 'daily_sign_in',
            ]);
            $this->upgradeUserTierIfNeeded($userId, $newMilesBalance);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return ['success' => false, 'message' => '簽到失敗'];
        }

        return [
            'success' => true,
            'message' => $isStreakBonus
                ? sprintf('簽到成功，獲得 %d 里程點數（含連續簽到加碼）', $rewardMiles)
                : sprintf('簽到成功，獲得 %d 里程點數', $rewardMiles),
            'sign_in_date' => $today,
            'reward_miles' => $rewardMiles,
            'base_reward_miles' => $baseReward,
            'streak_bonus_miles' => $isStreakBonus ? $streakBonusMiles : 0,
            'streak_days' => $streakDays,
            'is_streak_bonus' => $isStreakBonus ? 1 : 0,
            'miles_balance' => $newMilesBalance,
        ];
    }

    private function countTrailingStreak(array $signedDates, \DateTimeImmutable $endDate): int
    {
        if (empty($signedDates)) {
            return 0;
        }

        $signedMap = array_fill_keys($signedDates, true);
        $cursor = $endDate;
        $count = 0;

        while (!empty($signedMap[$cursor->format('Y-m-d')])) {
            $count++;
            $cursor = $cursor->modify('-1 day');
        }

        return $count;
    }

    private function upgradeUserTierIfNeeded(int $userId, int $totalMiles): void
    {
        $silver = (int) ($this->configModel->getByKey('tier_silver_miles')['value'] ?? 25000);
        $gold = (int) ($this->configModel->getByKey('tier_gold_miles')['value'] ?? 50000);
        $platinum = (int) ($this->configModel->getByKey('tier_platinum_miles')['value'] ?? 100000);

        if ($totalMiles >= $platinum) {
            $newTier = 'platinum';
        } elseif ($totalMiles >= $gold) {
            $newTier = 'gold';
        } elseif ($totalMiles >= $silver) {
            $newTier = 'silver';
        } else {
            return;
        }

        $user = $this->userRepo->find($userId);
        if (!$user) {
            return;
        }

        $tierOrder = ['regular' => 0, 'silver' => 1, 'gold' => 2, 'platinum' => 3];
        $currentTier = $user['tier'] ?? 'regular';
        if (($tierOrder[$newTier] ?? 0) > ($tierOrder[$currentTier] ?? 0)) {
            $this->userRepo->update($userId, ['tier' => $newTier]);
        }
    }
}
