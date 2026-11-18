<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Models\GameList;
use App\Models\User;
use Illuminate\Http\Request;

class GameApiController extends Controller
{

    function __construct()
    {

    }

    public function getSetting(Request $request)
    {
        $gameCode = $request->input('gameCode');

        $game = GameList::where('game_code', $gameCode)->first();
        if (!$game) {
            $data['code'] = '404';
            return response()->json($data);
        }
        $data['code'] = '200';
        $data['setting'] = json_decode($game->game_setting);
        return response()->json($data);
    }

    public function getBalance(Request $request) {
        $token = $request->input('token');
        $user = User::where('token', $token)->first();
        $data['balance'] = $user->credit;
        return response()->json($data);
    }

    public function spinData(Request $request) {
        $token = $request->input('token');
        $win = $request->input('betWins');
        $use = $request->input('betPrice');
        $user = User::where('token', $token)->first();
        $user->credit = $user->credit + ($win - $use);
        if ($user->credit > 0) {
            $user->save();
            $data['balance'] = $user->credit;
            return response()->json($data);
        }
        $data['balance'] = 0;
        return response()->json($data);
    }

    public function verifySession(Request $request)
    {
        $request_data = $request->all();
        $id = $request_data['id'];
        $time = $request_data['timestampMillis'];
        $productId = $request_data['productId'];
        $currency = $request_data['currency'];
        $sessionToken = $request_data['sessionToken'];
        $user_balance = User::where('token', $sessionToken)->first();
        $milliseconds = floor(microtime(true) * 1000);
        $data = array(
            'id' => $id,
            'statusCode' => 10001,
            'timestampMillis' => $milliseconds,
            'productId' => $productId
        );
        if ($user_balance) {
            $user = $user_balance;
                if ($user) {
                    $data = array(
                        'id' => $id,
                        'statusCode' => 0,
                        'timestampMillis' => $milliseconds,
                        'productId' => $productId,
                        'currency' => $currency,
                        'balance' => $user->credit,
                        'username' => $user->username
                    );
                    return response()->json($data);
                }
        } else {
            $data['statusCode'] = 30001;
        }

        return response()->json($data);
    }

    public function getBalance2(Request $request)
    {
        $request_data = $request->all();
        $id = $request_data['id'];
        $time = $request_data['timestampMillis'];
        $productId = $request_data['productId'];
        $currency = $request_data['currency'];
        $username = $request_data['username'];
        $user_balance = User::where('username', $username)->first();
        $milliseconds = floor(microtime(true) * 1000);
        $data = array(
            'id' => $id,
            'statusCode' => 10001,
            'timestampMillis' => $milliseconds,
            'productId' => $productId
        );
        if ($user_balance) {
            if (false && $user_balance->username != $username) {
                $data['statusCode'] = 10001;
            } else {
                $user = $user_balance;
                if ($user) {
                    $data = array(
                        'id' => $id,
                        'statusCode' => 0,
                        'timestampMillis' => $milliseconds,
                        'productId' => $productId,
                        'currency' => $currency,
                        'balance' => $user->credit,
                        'username' => $username
                    );
                    return response()->json($data);
                }
            }

        } else {
            $data['statusCode'] = 30001;
        }

        return response()->json($data);
    }

    public function settleBets(Request $request)
    {
        $bets = $request->all();
        $user_balance =  User::where('username', $bets['username'])->first();
        $milliseconds = floor(microtime(true) * 1000);
        $data = array(
            'id' => $bets['id'],
            'statusCode' => 10001,
            'timestampMillis' => $milliseconds,
            'productId' => $bets['productId']
        );
        if ($user_balance) {
            $user = $user_balance;
            if ($user) {
                if ($user->credit < $bets['txns'][0]['betAmount']) {
                    $data['statusCode'] = 10002;
                } else {
                    $before = $user->credit;
                    // $dup = $this->redisService->checkIsDupTransactionAMB($bets['txns'][0]['txnId'], $bets);
                    if (true) {
                        $user->credit += ($bets['txns'][0]['payoutAmount'] - $bets['txns'][0]['betAmount']);
                        $user->save();
                    }
                    $data = array(
                        'id' => $bets['id'],
                        'statusCode' => 0,
                        'timestampMillis' => $milliseconds,
                        'productId' => $bets['productId'],
                        'currency' => $bets['currency'],
                        'balanceAfter' => $user->credit,
                        'balanceBefore' => $before,
                        'username' => $bets['username']
                    );
                    return response()->json($data);
                }
            }
        } else {
            $data['statusCode'] = 10001;
        }
        return response()->json($data);
    }
}
