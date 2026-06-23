<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// =====================================================================
// PUBLIC ROUTES (Frontend)
// =====================================================================
$routes->get('/', 'Home::index');

// Redirect /login to backend if already logged in (or let Myth/Auth handle it)
// Myth/Auth automatically handles /login, /register, /logout, /forgot, /reset-password

// =====================================================================
// BACKEND ROUTES (Protected by Myth/Auth)
// =====================================================================
$routes->group('backend', ['filter' => 'login', 'namespace' => 'App\Controllers\Backend'], static function($routes) {
    
    // Dashboard
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    
    // Groups & Members
    $routes->group('groups', static function($routes) {
        $routes->get('/', 'Groups::index');
        $routes->get('create', 'Groups::create');
        $routes->post('store', 'Groups::store');
        $routes->get('detail/(:num)', 'Groups::detail/$1');
        $routes->post('update/(:num)', 'Groups::update/$1');
        $routes->get('delete-preview/(:num)', 'Groups::deletePreview/$1');
        $routes->post('delete/(:num)', 'Groups::delete/$1');
        $routes->post('add-member/(:num)', 'Groups::addMember/$1');
        $routes->get('update-role/(:num)/(:num)/(:alpha)', 'Groups::updateRole/$1/$2/$3');
        $routes->get('remove-member/(:num)/(:num)', 'Groups::removeMember/$1/$2');
    });

    // Trips & Periods
    $routes->group('trips', static function($routes) {
        $routes->get('/', 'Trips::index');
        $routes->get('create', 'Trips::create');
        $routes->post('store', 'Trips::store');
        $routes->get('detail/(:num)', 'Trips::detail/$1');
        $routes->post('update/(:num)', 'Trips::update/$1');
        $routes->get('delete-preview/(:num)', 'Trips::deletePreview/$1');
        $routes->post('delete/(:num)', 'Trips::delete/$1');
        $routes->post('add-period/(:num)', 'Trips::addPeriod/$1');
        $routes->post('update-period/(:num)', 'Trips::updatePeriod/$1');
        $routes->get('delete-period-preview/(:num)', 'Trips::deletePeriodPreview/$1');
        $routes->post('delete-period/(:num)', 'Trips::deletePeriod/$1');
        $routes->post('save-active-members/(:num)', 'Trips::saveActiveMembers/$1'); // period_id
        $routes->post('toggle-period-status/(:num)', 'Trips::togglePeriodStatus/$1'); // period_id
    });

    // Transactions & Adjustments
    $routes->group('transactions', static function($routes) {
        $routes->get('/', 'Transactions::index');
        $routes->post('store', 'Transactions::store');
        $routes->get('get/(:num)', 'Transactions::get/$1');
        $routes->post('update/(:num)', 'Transactions::update/$1');
        $routes->get('delete/(:num)', 'Transactions::delete/$1');
    });

    // Settlements
    $routes->group('settlements', static function($routes) {
        $routes->get('/', 'Settlements::index');
        $routes->post('pay', 'Settlements::pay');
        $routes->get('approve/(:num)', 'Settlements::approve/$1');
    });

    // Profil Akun
    $routes->get('profil', 'Profil::index');
    $routes->post('profil/update', 'Profil::update');
    $routes->post('profil/update-password', 'Profil::updatePassword');
});
