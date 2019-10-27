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
    return $timestamp->getCommonTimestamp();
});

Route::get('accounts', function (App\Btc\Account $account) {
    return $account->getAccountAccounts();
});

Route::get('balance', function (App\Btc\Account $account) {
    return $account->getBalance();
});

Route::any('mail/send','MailController@send');



