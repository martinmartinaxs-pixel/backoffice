<?php

namespace App\Http\Controllers\Api;

use App\Models\SeamlessSetting;
use App\Models\User;
use App\Models\WebContent;
use App\Services\RedisService;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ApiController extends Controller
{
    protected $redisService;
    function __construct(RedisService $redisService)
    {
        $this->redisService = $redisService;
    }

    public function callSeamless($url, $data)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            )
        );
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);

        curl_close($curl);
        
        return (json_decode($response));
    }

    public function callHttp($url, $data)
    {

        $curl = curl_init();
        if (isset($data['du'])) {
            // unset($data['du']);
            $data['du'] = 'https://static.pgf-asw0zz.com';
        }
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            )
        );

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        // $info = curl_getinfo($curl);
        // $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // dd($info, $httpcode);
        // dd($response);
        curl_close($curl);
        // echo $response;

        try {
            $decoded = json_decode($response);
            if ($decoded && $decoded->dt && $decoded->dt->tk) {
                // if (isset($data['os'])) {
                //     $decoded->dt->tk = $data['os'];
                //     $response = json_encode($decoded);
                // } else if (isset($data['tk'])) {
                //     $decoded->dt->tk = $data['tk'];
                //     $response = json_encode($decoded);
                // }
            }
        } catch (\Exception $ex) {

        }


        return response()->json(json_decode($response));
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function verifyOperatorPlayerSession(Request $request)
    {
        $trace_id = $request->input('traceId');
        $otk = $request->input('otk');
        $pass = false;
        $data = $request->all();
        $gid = $request->input('gi');
        $tk = $request->input('tk');
        $buy_free = true;
        if ($tk) {
            $user_data = $this->redisService->getUserData($tk);
            $otk = $user_data->os;
        }
        $username = 'PGUSER';
        // return response()->json(json_decode('{"dt":null,"err":{"cd":"1302","msg":"Invalid player session","tid":"' . $trace_id . '"}}'));
        if ($otk) {
            $setting = $this->redisService->getSeamlessSetting($otk);
            if (!$setting) {
                $setting = SeamlessSetting::where('operator_token', $otk)->first();
                $this->redisService->setSeamlessSetting($otk, $setting);
            }
            if ($setting) {
                $data_verify = '{
                    "timestampMillis": 123,
                    "id": "xyz",
                    "productId": "pg",
                    "currency": "thb",
                    "gameId": "' . $gid . '",
                    "sessionToken": "' . $request->input('os') . '",
                    "operator_token": "' . $otk . '"
                }';
                $varify = $this->callSeamless($setting->setting_url . '/verifySession', $data_verify);
                if ($varify && $varify->statusCode == 0) {
                    $user['username'] = $varify->username;
                    $username = $varify->username;
                    $buy_free = isset($varify->free_spin) ? $varify->free_spin : true;
                    $user['operator'] = $otk;
                    $user['os'] = $data['os'];
                    $token = $this->generateRandomString(10);
                    $this->redisService->setUserData($token, $user);
                    $player['token'] = $token;
                    $player['operator'] = $otk;
                    $this->redisService->setPlayerNameData($user['username'], $player);
                    $pass = true;
                }
            }
        }

        if ($pass) {


            $key = 'web-api/auth/session/v2/verifyOperatorPlayerSession/';
            if (isset($data['gi'])) {
                $key = 'web-api/auth/session/v2/verifyOperatorPlayerSession/' . $data['gi'];
                $content = WebContent::where('url', $key)->first();
                if ($content) {
                    $content = json_decode($content->contents)->original;
                    $content->dt->tk = $token;
                    $content->dt->pcd = $username;
                    $content->dt->nkn = $username;
                    if (!$buy_free) {
                        $content->dt->uiogc = json_decode('{ "dt": { "oj": { "jid": 1 }, "pid": "lWNgn05YBs", "pcd": "b11pir9u46960oy", "tk": "bYFoFu46lz", "st": 1, "geu": "game-api\/thai-river\/", "lau": "game-api\/lobby\/", "bau": "web-api\/game-proxy\/", "cc": "THB", "cs": "\u0e3f", "nkn": "b11pir9u46960oy", "gm": [ { "gid": 92, "msdt": 1599101546000, "medt": 1599101546000, "st": 1, "amsg": "" } ], "uiogc": { "bb": 1, "grtp": 0, "gec": 1, "cbu": 0, "cl": 0, "bf": 0, "mr": 0, "phtr": 0, "vc": 0, "bfbsi": 2, "bfbli": 2, "il": 0, "rp": 0, "gc": 0, "ign": 0, "tsn": 0, "we": 0, "gsc": 1, "bu": 0, "pwr": 0, "hd": 0, "et": 0, "np": 0, "igv": 0, "as": 0, "asc": 0, "std": 0, "hnp": 0, "ts": 0, "smpo": 0, "grt": 0, "ivs": 0, "ir": 0, "gvs": 0, "hn": 1 }, "ec": [], "occ": { "rurl": "", "tcm": "", "tsc": 0, "ttp": 0, "tlb": "", "trb": "" }, "gcv": "1.2.0.0-rc.2", "ioph": "d38863515ae2" }, "err": null }');
                    }
                    return response()->json($content);
                }
            }

            $content = new WebContent();
            $content->url = $key;
            $content->contents = '{"headers":{},"original":{},"exception":null}';
            $content->type = 'api';
            $content->save();

            return response()->json([]);
        }

        return response()->json(json_decode('{"dt":null,"err":{"cd":"1302","msg":"Invalid player session","tid":"' . $trace_id . '"}}'));
       
    }

    public function GameName(Request $request)
    {
        $trace_id = $request->input('traceId');
        $key = 'web-api/game-proxy/v2/GameName/Get';
        $content = WebContent::where('url', $key)->first();
        if ($content) {
            return response()->json(json_decode($content->contents)->original);
        }

        if (!$content) {
            $content = new WebContent();
            $content->url = $key;
            $content->contents = '{"headers":{},"original":{},"exception":null}';
            $content->type = 'api';
            $content->save();
        }

        return response()->json([]);
       
    }

    public function GetByResourcesTypeIds(Request $request)
    {
        $key = 'web-api/game-proxy/v2/Resources/GetByResourcesTypeIds';
        $content = WebContent::where('url', $key)->first();
        if ($content) {
            return response()->json(json_decode($content->contents)->original);
        }

        $trace_id = $request->input('traceId');

        $content = new WebContent();
        $content->url = 'web-api/game-proxy/v2/Resources/GetByResourcesTypeIds';
        $content->contents = '{"headers":{},"original":{},"exception":null}';
        $content->type = 'api';
        $content->save();

        return response()->json([]);
       
    }

    public function verifySession(Request $request)
    {
        $trace_id = $request->input('traceId');
        $otk = $request->input('otk');
        $os = $request->input('os');
        $pass = false;
        $data = $request->all();
        $gid = $request->input('gi');
        $tk = $request->input('tk');
        $buy_free = true;
        if ($tk) {
            $user_data = $this->redisService->getUserData($tk);
            $os = $user_data->os;
        }
        $username = 'PGUSER';
        // return response()->json(json_decode('{"dt":null,"err":{"cd":"1302","msg":"Invalid player session","tid":"' . $trace_id . '"}}'));
        if ($otk && $os) {
            $setting = $this->redisService->getSeamlessSetting($otk);
            if (!$setting) {
                $setting = SeamlessSetting::where('operator_token', $otk)->first();
                $this->redisService->setSeamlessSetting($otk, $setting);
            }
            if ($setting) {
                $data_verify = '{
                    "timestampMillis": 123,
                    "id": "xyz",
                    "productId": "pg",
                    "currency": "thb",
                    "gameId": "' . $gid . '",
                    "sessionToken": "' . $os . '",
                    "operator_token": "' . $otk . '"
                }';
                $varify = $this->callSeamless($setting->setting_url . '/verifySession', $data_verify);
                if ($varify && $varify->statusCode == 0) {
                    $user['username'] = $varify->username;
                    $username = $varify->username;
                    $buy_free = isset($varify->free_spin) ? $varify->free_spin : true;
                    $user['operator'] = $otk;
                    $user['os'] = $os;
                    $token = $os;
                    $this->redisService->setUserData($token, $user);
                    $player['token'] = $token;
                    $player['operator'] = $otk;
                    $this->redisService->setPlayerNameData($user['username'], $player);
                    $pass = true;
                }
            }
        }

        if ($pass) {


            $key = 'web-api/auth/session/v2/verifyOperatorPlayerSession/';
            if (isset($data['gi'])) {
                $key = 'web-api/auth/session/v2/verifyOperatorPlayerSession/' . $data['gi'];
                $content = WebContent::where('url', $key)->first();
                if ($content) {
                    $content = json_decode($content->contents)->original;
                    $content->dt->tk = $token;
                    $content->dt->pcd = $username;
                    $content->dt->nkn = $username;
                    if (!$buy_free) {
                        $content->dt->uiogc = json_decode('{ "dt": { "oj": { "jid": 1 }, "pid": "lWNgn05YBs", "pcd": "b11pir9u46960oy", "tk": "bYFoFu46lz", "st": 1, "geu": "game-api\/thai-river\/", "lau": "game-api\/lobby\/", "bau": "web-api\/game-proxy\/", "cc": "THB", "cs": "\u0e3f", "nkn": "b11pir9u46960oy", "gm": [ { "gid": 92, "msdt": 1599101546000, "medt": 1599101546000, "st": 1, "amsg": "" } ], "uiogc": { "bb": 1, "grtp": 0, "gec": 1, "cbu": 0, "cl": 0, "bf": 0, "mr": 0, "phtr": 0, "vc": 0, "bfbsi": 2, "bfbli": 2, "il": 0, "rp": 0, "gc": 0, "ign": 0, "tsn": 0, "we": 0, "gsc": 1, "bu": 0, "pwr": 0, "hd": 0, "et": 0, "np": 0, "igv": 0, "as": 0, "asc": 0, "std": 0, "hnp": 0, "ts": 0, "smpo": 0, "grt": 0, "ivs": 0, "ir": 0, "gvs": 0, "hn": 1 }, "ec": [], "occ": { "rurl": "", "tcm": "", "tsc": 0, "ttp": 0, "tlb": "", "trb": "" }, "gcv": "1.2.0.0-rc.2", "ioph": "d38863515ae2" }, "err": null }');
                    }
                    return response()->json($content);
                }
            }



            $content = new WebContent();
            $content->url = $key;
            $content->contents = '{"headers":{},"original":{},"exception":null}';
            $content->type = 'api';
            $content->save();


            return response()->json([]);
        }

        return response()->json(json_decode('{"dt":null,"err":{"cd":"1302","msg":"Invalid player session","tid":"' . $trace_id . '"}}'));
    }

    public function GameInfo($game_name, Request $request)
    {
        $key = 'game-api/' . $game_name . '/v2/GameInfo/Get';
        $trace_id = $request->input('traceId');
        $atk = $request->input('atk');
        $user_data = $this->redisService->getUserData($atk);
        if ($user_data) {
            $user_data = (array) $user_data;
            $os = $user_data['os'];
            $otk = $user_data['operator'];
            $pass = false;
            if ($otk) {
                $setting = $this->redisService->getSeamlessSetting($otk);
                if (!$setting) {
                    $setting = SeamlessSetting::where('operator_token', $otk)->first();
                    $this->redisService->setSeamlessSetting($otk, $setting);
                }
                if ($setting) {
                    $data_balance = '{
                        "timestampMillis": 123,
                        "id": "xyz",
                        "productId": "pg",
                        "currency": "thb",
                        "username": "' . $user_data['username'] . '",
                        "operator_token": "' . $otk . '"
                    }';
                    $balance = $this->callSeamless($setting->setting_url . '/checkBalance', $data_balance);
                    if ($balance && $balance->statusCode == 0) {
                        try {
                            $content = WebContent::where('url', $key)->first();
                            if ($content) {
                                $data_return = json_decode($content->contents)->original;


                                $last_spin_data = $request->all();
                                $last_spin_data['player_name'] = $user_data['username'];
                                $data_return->dt->bl = $balance->balance;
                                $pass = true;

                                $last_spin = $this->callSeamless('http://localhost:5555/game-api/' . $game_name . '/old/spin', json_encode($last_spin_data));
                                // dd($last_spin);
                                if ($last_spin && $last_spin->success) {
                                    $data_return->dt->ls->si = $last_spin->ls;
                                }

                                return response()->json($data_return);
                            }
                            $trace_id = $request->input('traceId');

                            $content = new WebContent();
                            $content->url = 'game-api/' . $game_name . '/v2/GameInfo/Get';
                            $content->contents = '{"headers":{},"original":{},"exception":null}';
                            $content->type = 'api';
                            $content->save();

                            return response()->json([]);
                        } catch (\Exception $e) {

                        }
                    }
                }
            }
        }

        return response()->json(json_decode('{"dt":null,"err":{"cd":"1302","msg":"Invalid player session","tid":"' . $trace_id . '"}}'));
    }

    public function GameRule(Request $request)
    {
        // dd(1);
        $gid = $request->input('gid');
        $key = '/web-api/game-proxy/v2/GameRule/Get/' . $gid;

        $content = WebContent::where('url', $key)->first();
        if ($content) {
            return response()->json(json_decode($content->contents)->original);
        }
        $trace_id = $request->input('traceId');
        
        $content = new WebContent();
        $content->url = $key;
        $content->contents = '{"headers":{},"original":{},"exception":null}';
        $content->type = 'api';
        $content->save();

        return response()->json([]);
    }

    public function BetHistory(Request $request)
    {
        $gid = $request->input('gid');
        $key = '/game-proxy/v2/GameRule/Get/' . $gid;

        $content = WebContent::where('url', $key)->first();
        if ($content) {
            return response()->json(json_decode($content->contents)->original);
        }
        $trace_id = $request->input('traceId');
        
        $content = new WebContent();
        $content->url = $key;
        $content->contents = '{"headers":{},"original":{},"exception":null}';
        $content->type = 'api';
        $content->save();

        return response()->json([]);
    }

    public function GameWallet(Request $request)
    {
        $atk = $request->input('atk');
        $user_data = $this->redisService->getUserData($atk);
        if ($user_data) {
            $user_data = (array) $user_data;
            $otk = $user_data['operator'];
            if ($otk) {
                $setting = $this->redisService->getSeamlessSetting($otk);
                if (!$setting) {
                    $setting = SeamlessSetting::where('operator_token', $otk)->first();
                    $this->redisService->setSeamlessSetting($otk, $setting);
                }
                $data_balance = '{
            "timestampMillis": 123,
            "id": "xyz",
            "productId": "pg",
            "currency": "thb",
            "username": "' . $user_data['username'] . '",
            "operator_token": "' . $otk . '"
        }';

                $balance = $this->callSeamless($setting->setting_url . '/checkBalance', $data_balance);
                if ($balance && $balance->statusCode == 0) {
                    $data_obj = json_decode('{ "dt": { "cc": "PGC", "tb": 100000.00, "pb": 0.00, "cb": 100000.00, "tbb": 0.00, "tfgb": 0.00, "rfgc": 0, "inbe": false, "infge": false, "iebe": false, "iefge": false, "ch": { "k": "0_C", "cid": 0, "cb": 100000.00 }, "p": null, "ocr": null }, "err": null }');
                    $data_obj->dt->tb = $balance->balance;
                    $data_obj->dt->cb = $balance->balance;
                    $data_obj->dt->ch->cb = $balance->balance;
                    return response()->json($data_obj);
                }
            }
        }
        return response()->json(json_decode('{}'));
    }


    public function GameSpin($game_name, Request $request)
    {
        // dd(1);
        $trace_id = $request->input('traceId');
        $data = $request->all();
        $link = 'http://127.0.0.1:5555/';
        $user_data = $this->redisService->getUserData($data['atk']);
        if ($user_data) {
            $user_data = (array) $user_data;
            $os = $user_data['os'];
            $otk = $user_data['operator'];
            $setting = $this->redisService->getSeamlessSetting($otk);
            if (!$setting) {
                $setting = SeamlessSetting::where('operator_token', $otk)->first();
            }
            if ($setting) {
                $this->redisService->setSeamlessSetting($otk, $setting);
                $data['url'] = $setting->setting_url;
                $data['player_name'] = $user_data['username'];
                $data['operator_token'] = $otk;
                return $this->callHttp($link . 'game-api/' . $game_name . '/v2/spin?traceId=' . $trace_id, $data);
            }
        }
        return response()->json(json_decode('{"dt":null,"err":{"cd":"1302","msg":"Invalid player session","tid":"' . $trace_id . '"}}'));
    }

    public function GameBonus($game_name, Request $request)
    {
        $trace_id = $request->input('traceId');
        $data = $request->all();
        $link = 'http://localhost:5555/';
        
        // return $link;
        return $this->callSeamless($link . 'game-api/' . $game_name . '/v2/Spin/Bonus?traceId=' . $trace_id, $request->all());
    }
    // /game-api/fortune-tree/v2/GameInfo/Update

    public function Update($game_name, Request $request)
    {
        $key = 'game-api/' . $game_name . '/v2/GameInfo/Update';
        $content = WebContent::where('url', $key)->first();
        if ($content) {
            return response()->json(json_decode($content->contents)->original);
        }
        $trace_id = $request->input('traceId');
        
        $content = new WebContent();
        $content->url = $key;
        $content->contents = '{"headers":{},"original":{},"exception":null}';
        $content->type = 'api';
        $content->save();

        return response()->json([]);
    }

    public function getGameDetail(Request $request)
    {
        $key = 'game-api/lobby/gameInfo/v1/getGameDetails';
        $content = WebContent::where('url', $key)->first();
        if ($content) {
            return response()->json(json_decode($content->contents));
        }
        $trace_id = $request->input('traceId');
        
        $content = new WebContent();
        $content->url = $key;
        $content->contents = json_encode([]);
        $content->type = 'api';
        $content->save();

        return response()->json([]);
    }

    public function getGameWallet(Request $request)
    {
        $atk = $request->input('atk');
        $user_data = $this->redisService->getUserData($atk);
        if ($user_data) {
            $user_data = (array) $user_data;
            $otk = $user_data['operator'];
            if ($otk) {
                $setting = $this->redisService->getSeamlessSetting($otk);
                if (!$setting) {
                    $setting = SeamlessSetting::where('operator_token', $otk)->first();
                    $this->redisService->setSeamlessSetting($otk, $setting);
                }
                $data_balance = '{
            "timestampMillis": 123,
            "id": "xyz",
            "productId": "pg",
            "currency": "thb",
            "username": "' . $user_data['username'] . '",
            "operator_token": "' . $otk . '"
        }';

                $balance = $this->callSeamless($setting->setting_url . '/checkBalance', $data_balance);
                if ($balance && $balance->statusCode == 0) {
                    $data_obj = json_decode('{"dt":{"bl":{"bbl":0.00,"cbl":0.00,"cc":"THB","cs":"฿","tbl":0.00}},"err":null}');
                    $data_obj->dt->bl->bbl = $balance->balance;
                    $data_obj->dt->bl->cbl = $balance->balance;
                    $data_obj->dt->bl->tbl = $balance->balance;
                    return response()->json($data_obj);
                }
            }
        }
        return response()->json(json_decode('{}'));
    }

    public function getAllGames(Request $request)
    {
        $key = 'game-api/lobby/gameInfo/v1/getAllGames';
        $content = WebContent::where('url', $key)->first();
        if ($content) {
            return response()->json(json_decode($content->contents));
        }
        $trace_id = $request->input('traceId');

        $content = new WebContent();
        $content->url = $key;
        $content->contents = json_encode([]);
        $content->type = 'api';
        $content->save();

        return response()->json([]);
    }

    public function initialize(Request $request)
    {
        $jsonPath = base_path('app/Http/Controllers/Api/initialize.json');
        $jsonData = File::get($jsonPath);
        $jsonArray = json_decode($jsonData, true);

        return response()->json($jsonArray);
    }

    public function getContentLastUpdated(Request $request)
    {
        $key = 'game-api/lobby/gameInfo/v1/getContentLastUpdated';
        $content = WebContent::where('url', $key)->first();
        if ($content) {
            return response()->json(json_decode($content->contents));
        }
        $trace_id = $request->input('traceId');
        
        $content = new WebContent();
        $content->url = $key;
        $content->contents = json_encode([]);
        $content->type = 'api';
        $content->save();

        return response()->json([]);
    }

    public function GameUI(Request $request)
    {
        $key = 'web-api/game-proxy/v2/GameUI/Get';
        $content = WebContent::where('url', $key)->first();
        if ($content) {
            return response()->json(json_decode($content->contents));
        }
        $trace_id = $request->input('traceId');
        
        $content = new WebContent();
        $content->url = $key;
        $content->contents = json_encode([]);
        $content->type = 'api';
        $content->save();

        return response()->json([]);
    }
}
