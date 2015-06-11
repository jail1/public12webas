<?php
require 'coupons/coupons.php';
require 'coupons/categories.php';
require 'merchants/merchants.php';
require 'contracts/contracts.php';
require 'users/check.php';
require 'leads/leads.php';

// Coupons
$app->get('/coupons', 'GetCoupons');
$app->post('/coupons', 'PostCoupon');
$app->get('/coupons/latest', 'GetCouponsLatest');
$app->get('/coupons/redeems', 'GetCouponsRedeems');
$app->get('/coupons/:id', 'GetCoupon');
$app->post('/coupons/:id', 'UpdateCoupon');
$app->delete('/coupons/:id', 'DeleteCoupon');
$app->post('/coupons/:id/redeem', 'RedeemCoupon');
$app->post('/coupons/:id/rate', 'RateCoupon');

$app->get('/live', 'LiveSearch');

$app->get('/ads', 'GetAds');
// Categories
$app->get('/categories', 'GetCategories');

// Merchants
$app->get('/merchants', 'GetMerchants');
$app->get('/merchants/:id', 'GetMerchant');
$app->get('/merchants/:id/coupons', 'GetMerchantCoupons');

//Campaigns & Punchcard
$app->post('/setPunchCard', 'setPunchCard');

//send mail to mechant
$app->post('/mailToMerchant', 'mailToMerchant');

// Contracts
$app->get('/contracts/:id', 'GetContract');

// User

$app->get('/getAPPConfig', 'getAPPConfig');
$app->get('/getUserPreferences', 'getUserPreferences');
$app->post('/setUserPreferences', 'setUserPreferences');
$app->get('/user/check', 'GetUserCheck');

// Call-in leads
$app->post('/leads', 'PostLead');
