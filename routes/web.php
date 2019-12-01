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

use App\Btc\Orders;
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


// 下单
Route::get('placeorder', function (App\Btc\Orders $orders, Request $request) {

    $signKey = 'wangzhaoqistruggle';
    $token = $request->input('s');
    if ($token !== md5($signKey . strtotime('today'))) {
        header('HTTP/1.1 403 Forbidden');
        exit();
    }

    $clientOrderId = 'st000002';
    $account_id = 9016902;
    $amount = 20;
    $price=0.21;
    $symbol='xrpusdt';
    $type='buy-limit';

    return $orders->placeOrder($clientOrderId,$account_id,$amount,$price,$symbol,$type);
    /*
      {
        "status": "ok",
        "data": "58234589524"
      }
     */
});

// 获取订单信息
Route::get('getorder', function (App\Btc\Orders $orders, Request $request) {

    $signKey = 'wangzhaoqistruggle';
    $token = $request->input('s');
    if ($token !== md5($signKey . strtotime('today'))) {
        header('HTTP/1.1 403 Forbidden');
        exit();
    }

    return $orders->getOrder('58234589524');
    /*
      {
    "status": "ok",
    "data": {
        "id": 58234589524,
        "symbol": "xrpusdt",
        "account-id": 9016902,
        "amount": "20.000000000000000000",
        "price": "0.210000000000000000",
        "created-at": 1575213293157,
        "type": "buy-limit",
        "field-amount": "0.0",
        "field-cash-amount": "0.0",
        "field-fees": "0.0",
        "finished-at": 0,
        "source": "api",
        "state": "submitted",
        "canceled-at": 0
        }
    }
     */
});

Route::get('cancelorder', function (App\Btc\Orders $orders, Request $request) {

    $signKey = 'wangzhaoqistruggle';
    $token = $request->input('s');
    if ($token !== md5($signKey . strtotime('today'))) {
        header('HTTP/1.1 403 Forbidden');
        exit();
    }

    return $orders->cancelOrder('58235864650');
    /*
    {
        "status": "ok",
        "data": "58234589524"
    }
     */
});


# Route::any('mail/send', 'MailController@send');



