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

    // ── Admin Panel (免登入後台) ──
    $routes->group('admin-panel', static function ($routes) {
        $routes->get('stats',                 'AdminPanelController::stats');
        $routes->get('logs',                  'AdminPanelController::logs');
        $routes->get('logs/(:num)',           'AdminPanelController::logDetail/$1');
        $routes->get('users',                 'AdminPanelController::users');
        $routes->get('users/(:num)',          'AdminPanelController::userDetail/$1');
        $routes->get('users/(:num)/logs',     'AdminPanelController::userLogs/$1');
        $routes->post('users/(:num)/deposit', 'AdminPanelController::deposit/$1');
    });

    // ── Auth (Public) ──
    $routes->group('auth', static function ($routes) {
        $routes->post('register',        'Api\AuthController::register');
        $routes->post('login',           'Api\AuthController::login');
        $routes->post('forgot-password', 'Api\AuthController::forgotPassword');
        $routes->post('reset-password',  'Api\AuthController::resetPassword');
    });

    // ── Users (JWT required) ──
    $routes->group('users', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('me',              'Api\UserController::me');
        $routes->put('me',              'Api\UserController::updateMe');
        $routes->put('me/password',     'Api\UserController::changePassword');
        $routes->post('me/verify',      'Api\UserController::verify');
        $routes->post('me/avatar',      'Api\UserController::uploadAvatar');
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
        $routes->get('history',  'Api\MileageController::history');
        $routes->post('redeem',  'Api\MileageController::redeem');
    });

    // ── Announcements (JWT required) ──
    $routes->group('announcements', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('/',          'Api\AnnouncementController::index');
        $routes->get('(:num)',     'Api\AnnouncementController::show/$1');
    });

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
