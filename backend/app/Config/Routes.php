<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ─────────────────────────────────────────
// API v1
// ─────────────────────────────────────────
$routes->group('api/v1', static function ($routes) {

    // ── Sadmin (Super Admin — All API Logs) ──
    $routes->group('sadmin', static function ($routes) {
        $routes->get('stats',                        'SadminController::stats');
        $routes->get('api-logs',                     'SadminController::apiLogs');
        $routes->get('api-logs/(:num)',              'SadminController::apiLogDetail/$1');
        $routes->get('third-party-logs',             'SadminController::thirdPartyLogs');
        $routes->get('third-party-logs/(:num)',      'SadminController::thirdPartyLogDetail/$1');
        // SMS Provider
        $routes->get('sms-provider',                 'SadminController::smsProviderInfo');
        $routes->post('sms-provider',                'SadminController::setSmsProvider');
        $routes->delete('sms-provider',              'SadminController::resetSmsProvider');
        // TopMessage Config
        $routes->get('topmessage-config',            'SadminController::topmessageConfig');
        $routes->post('topmessage-config',           'SadminController::saveTopmessageConfig');
        // SMS Logs
        $routes->get('sms-logs',                     'SadminController::smsLogs');
        // SMS Security Settings
        $routes->get('sms-security',                 'SadminController::smsSecurityConfig');
        $routes->post('sms-security',                'SadminController::saveSmsSecurityConfig');
        $routes->get('sms-security/blocked-ips',     'SadminController::smsBlockedIps');
        $routes->post('sms-security/unblock',        'SadminController::smsUnblockIp');
        $routes->delete('sms-security/rate-limits',  'SadminController::smsClearRateLimits');
        // SMS Verification Mode (全域開關)
        $routes->get('sms-verification-mode',        'SadminController::getSmsVerificationMode');
        $routes->post('sms-verification-mode',       'SadminController::setSmsVerificationMode');
    });

    // ── Admin Panel (免登入後台) ──
    $routes->group('admin-panel', static function ($routes) {
        $routes->get('stats',                 'AdminPanelController::stats');
        $routes->get('logs',                  'AdminPanelController::logs');
        $routes->get('logs/(:num)',           'AdminPanelController::logDetail/$1');
        $routes->get('users',                 'AdminPanelController::users');
        $routes->post('users',                'AdminPanelController::createUser');
        $routes->get('users/(:num)',          'AdminPanelController::userDetail/$1');
        $routes->get('users/(:num)/logs',     'AdminPanelController::userLogs/$1');
        $routes->post('users/(:num)/deposit', 'AdminPanelController::deposit/$1');
        $routes->post('users/(:num)/change-password',            'AdminPanelController::changePassword/$1');
        $routes->post('users/(:num)/change-withdrawal-password', 'AdminPanelController::changeWithdrawalPassword/$1');
        // Mileage Redemption Items
        $routes->get('mileage-items',           'AdminPanelController::mileageItems');
        $routes->post('mileage-items',          'AdminPanelController::createMileageItem');
        $routes->put('mileage-items/(:num)',    'AdminPanelController::updateMileageItem/$1');
        $routes->delete('mileage-items/(:num)', 'AdminPanelController::deleteMileageItem/$1');
        // Skywards Benefits
        $routes->get('skywards-benefits',           'AdminPanelController::skywardsBenefits');
        $routes->post('skywards-benefits',          'AdminPanelController::createSkywardsBenefit');
        $routes->put('skywards-benefits/(:num)',    'AdminPanelController::updateSkywardsBenefit/$1');
        $routes->delete('skywards-benefits/(:num)', 'AdminPanelController::deleteSkywardsBenefit/$1');
        // Mileage Reward Products
        $routes->get('mileage-reward-products',           'AdminPanelController::mileageRewardProducts');
        $routes->post('mileage-reward-products',          'AdminPanelController::createMileageRewardProduct');
        $routes->put('mileage-reward-products/(:num)',    'AdminPanelController::updateMileageRewardProduct/$1');
        $routes->delete('mileage-reward-products/(:num)', 'AdminPanelController::deleteMileageRewardProduct/$1');
        // Mileage Reward Orders
        $routes->get('reward-orders',                'AdminPanelController::rewardOrders');
        $routes->post('reward-orders/(:num)/review', 'AdminPanelController::reviewRewardOrder/$1');
        // Mileage Codes
        $routes->get('mileage-codes',           'AdminPanelController::mileageCodes');
        $routes->get('mileage-code-records',    'AdminPanelController::mileageCodeRecords');
        $routes->post('mileage-codes',          'AdminPanelController::createMileageCode');
        $routes->put('mileage-codes/(:num)',    'AdminPanelController::updateMileageCode/$1');
        $routes->delete('mileage-codes/(:num)', 'AdminPanelController::deleteMileageCode/$1');
        // App Config
        $routes->get('config/(:segment)',   'AdminPanelController::getConfig/$1');
        $routes->post('config/(:segment)',  'AdminPanelController::setConfig/$1');
        // KYC Management
        $routes->get('kyc',                 'AdminPanelController::kycList');
        $routes->post('kyc/(:num)/review',  'AdminPanelController::kycReview/$1');
        // Customer Service
        $routes->get('cs/conversations',                        'AdminPanelController::csConversations');
        $routes->get('cs/conversations/(:segment)',             'AdminPanelController::csMessages/$1');
        $routes->post('cs/conversations/(:segment)/reply',      'AdminPanelController::csSendMessage/$1');
        // Phone Verifications
        $routes->get('phone-verifications', 'AdminPanelController::phoneVerifications');
        // SMS Logs
        $routes->get('sms-logs',            'AdminPanelController::smsLogs');
        // Mails
        $routes->get('mails',               'AdminPanelController::mailList');
        $routes->post('mails',              'AdminPanelController::createMail');
        $routes->put('mails/(:num)',         'AdminPanelController::updateMail/$1');
        $routes->delete('mails/(:num)',      'AdminPanelController::deleteMail/$1');
        // Announcements
        $routes->get('announcements',              'AdminPanelController::announcementList');
        $routes->post('announcements',             'AdminPanelController::createAnnouncement');
        $routes->put('announcements/(:num)',        'AdminPanelController::updateAnnouncement/$1');
        $routes->delete('announcements/(:num)',     'AdminPanelController::deleteAnnouncement/$1');
    });

    // ── Auth (Public) ──
    $routes->group('auth', static function ($routes) {
        $routes->post('register',          'Api\AuthController::register');
        $routes->post('login',             'Api\AuthController::login');
        $routes->post('forgot-password',   'Api\AuthController::forgotPassword');
        $routes->post('reset-password',    'Api\AuthController::resetPassword');
        $routes->get('otp-provider',       'Api\AuthController::otpProvider');
        $routes->post('send-phone-otp',    'Api\AuthController::sendPhoneOtp');
        $routes->post('verify-phone-otp',  'Api\AuthController::verifyPhoneOtp');
    });

    // ── Users (JWT required) ──
    $routes->group('users', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('me',              'Api\UserController::me');
        $routes->put('me',              'Api\UserController::updateMe');
        $routes->put('me/password',     'Api\UserController::changePassword');
        $routes->post('me/verify',         'Api\UserController::verify');
        $routes->post('me/verify-email',    'Api\UserController::sendVerificationEmail');
        $routes->post('me/avatar',          'Api\UserController::uploadAvatar');
    });

    // ── Files ──
    $routes->group('files', static function ($routes) {
        // 公開查詢（不需 JWT）
        $routes->get('(:num)',                    'Api\FileController::show/$1');
        $routes->get('by-uuid/(:segment)',        'Api\FileController::showByUuid/$1');
        $routes->get('(:segment)/serve',          'Api\FileController::serve/$1');  // 直接提供檔案內容

        // 需要 JWT
        $routes->post('upload',   'Api\FileController::upload',      ['filter' => 'jwt']);
        $routes->get('mine',      'Api\FileController::mine',         ['filter' => 'jwt']);
        $routes->delete('(:num)', 'Api\FileController::destroy/$1',   ['filter' => 'jwt']);
    });

    // ── Wallet (JWT required) ──
    $routes->group('wallet', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('info',         'Api\WalletController::info');
        $routes->post('password',    'Api\WalletController::setPassword');
        $routes->post('bank',        'Api\WalletController::bindBank');
        $routes->post('withdraw',    'Api\WalletController::withdraw');
        $routes->get('transactions', 'Api\WalletController::transactions');
    });

    // ── Mileage (JWT required) ──
    $routes->group('mileage', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('history',                          'Api\MileageController::history');
        $routes->post('redeem',                          'Api\MileageController::redeem');
        $routes->get('redemption-items',                 'Api\MileageController::redemptionItems');
        $routes->get('reward-products',                  'Api\MileageController::rewardProducts');
        $routes->get('reward-orders/my',                 'Api\MileageController::myRewardOrders');
        $routes->get('reward-orders/my-pending',         'Api\MileageController::myPendingRewardOrders');
        $routes->post('reward-products/(:num)/purchase', 'Api\MileageController::purchaseRewardProduct/$1');
    });

    // ── Announcements (Public) ──
    $routes->get('announcements',       'Api\AnnouncementController::index');
    $routes->get('announcements/(:num)', 'Api\AnnouncementController::show/$1');

    // ── Skywards (JWT required) ──
    $routes->group('skywards', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('benefits', 'Api\SkywardsBenefitController::index');
    });

    // ── App Config (Public) ──
    $routes->get('config/(:segment)', 'AdminPanelController::getConfig/$1');

    // ── Mails (Public listing) ──
    $routes->get('mails', 'AdminPanelController::publicMailList');

    // ── Customer Service (JWT required) ──
    $routes->group('cs', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('messages',  'Api\CustomerServiceController::messages');
        $routes->post('messages', 'Api\CustomerServiceController::sendMessage');
    });

    // ── Admin (JWT + admin role required) ──
    $routes->group('admin', ['filter' => 'jwt:admin'], static function ($routes) {
        $routes->get('users',                        'Api\AdminController::users');
        $routes->post('users/(:num)/balance',        'Api\AdminController::adjustBalance/$1');
        $routes->post('users/(:num)/verify',         'Api\AdminController::reviewVerification/$1');
    });
});
