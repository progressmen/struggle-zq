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

use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('timestamp', function (App\Btc\Account $timestamp, Request $request) {

    $signKey = 'wangzhaoqistruggle';
    $token = $request->input('s');
    if ($token !== md5($signKey . strtotime('today'))) {
        header('HTTP/1.1 403 Forbidden');
        exit();
    }

    return $timestamp->getCommonTimestamp();
});

Route::get('accounts', function (App\Btc\Account $account, Request $request) {

    $signKey = 'wangzhaoqistruggle';
    $token = $request->input('s');
    if ($token !== md5($signKey . strtotime('today'))) {
        header('HTTP/1.1 403 Forbidden');
        exit();
    }
    return $account->getAccountAccounts();

});

Route::get('balance', function (App\Btc\Account $account, Request $request) {

    $signKey = 'wangzhaoqistruggle';
    $token = $request->input('s');
    if ($token !== md5($signKey . strtotime('today'))) {
        header('HTTP/1.1 403 Forbidden');
        exit();
    }

    return $account->getBalance();
});

Route::get('actionOne', function (App\Action\ActionOne $actionOne, Request $request) {

    $signKey = 'wangzhaoqistruggle';
    $token = $request->input('s');
    if ($token !== md5($signKey . strtotime('today'))) {
        header('HTTP/1.1 403 Forbidden');
        exit();
    }

    return $actionOne->exec();
});


# Route::any('mail/send', 'MailController@send');



