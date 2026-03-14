<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ─────────────────────────────────────────
// Admin Panel (免登入後台)
// ─────────────────────────────────────────
$routes->group('admin-panel', static function ($routes) {
    $routes->get('/',              'AdminPanelController::index');
    $routes->get('api/stats',      'AdminPanelController::stats');
    $routes->get('api/logs',       'AdminPanelController::logs');
    $routes->get('api/logs/(:num)','AdminPanelController::logDetail/$1');
    $routes->get('api/users',      'AdminPanelController::users');
    $routes->get('api/users/(:num)',     'AdminPanelController::userDetail/$1');
    $routes->get('api/users/(:num)/logs','AdminPanelController::userLogs/$1');
});

// ─────────────────────────────────────────
// API v1
// ─────────────────────────────────────────
$routes->group('api/v1', static function ($routes) {

    // ── Auth (Public) ──
    $routes->group('auth', static function ($routes) {
        $routes->post('register',        'Api\AuthController::register');
        $routes->post('login',           'Api\AuthController::login');
        $routes->post('forgot-password', 'Api\AuthController::forgotPassword');
        $routes->post('reset-password',  'Api\AuthController::resetPassword');
    });

    // ── Users (JWT required) ──
    $routes->group('users', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('me',            'Api\UserController::me');
        $routes->put('me',            'Api\UserController::updateMe');
        $routes->post('me/verify',    'Api\UserController::verify');
        $routes->post('me/avatar',    'Api\UserController::uploadAvatar');
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
        $routes->get('history', 'Api\MileageController::history');
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
