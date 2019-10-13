<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('timestamp', function (App\Btc\Account $timestamp) {
    return $timestamp->get_common_timestamp();
});

Route::get('accounts', function (App\Btc\Account $account) {
    return $account->get_account_accounts();
});



