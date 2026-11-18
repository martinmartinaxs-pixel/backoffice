<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\GameList;
use App\Models\SeamlessSetting;
use App\Models\SettingHistory;
use App\Models\UserGameSetting;
use App\Services\RedisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    protected $redisService;
    function __construct(RedisService $redisService)
    {
        $this->redisService = $redisService;
    }

    public function index($game)
    {
        $data['game_code'] = $game;
        $data['games'] = GameList::where('id', '>', 0)->where('game_status', 1)->where('game_type', $game)->get()->toArray();
        $settings = UserGameSetting::where('user_id', Auth::user()->id)->get();
        $game_s = array();
        foreach ($settings as $setting) {
            $game_s[$setting->game_id] = $setting;
        }
        $data['game_s'] = $game_s;
        $data['operator'] = SeamlessSetting::where('user_id', Auth::user()->id)->first();
        return view('Backoffice.Game.gamelist', $data);
    }

    public function saveGameSetting($game, $request)
    {
        $keys = ['range1', 'range2', 'range3', 'range4', 'free_max', 'free_min', 'max', 'min', 'buy_free_min', 'buy_free_max'];
        $game_setting = UserGameSetting::where('user_id', Auth::user()->id)->where('game_id', $game->id)->where('game_code', $game->game_code)->first();
        if (!$game_setting) {
            $game_setting = new UserGameSetting();
            $game_setting->user_id = Auth::user()->id;
            $game_setting->game_id = $game->id;
            $game_setting->game_code = $game->game_code;
        }
        $setting = array();
        foreach ($keys as $k) {
            $setting[$k] = $request->input($k);
        }
        $setting_ex['value'] = $request->input('ex_val', 0);
        foreach ($keys as $k) {
            $setting_ex[$k] = $request->input('ex_'.$k);
        }
        $setting['ex'] = $setting_ex;
        // dd($game_setting);
        $game_setting->game_user_setting = json_encode($setting);
        $game_setting->save();
        return $game_setting;
    }
    public function saveSetting(Request $request)
    {

        $save_type = $request->input('save_type', 1);
        $setting = SeamlessSetting::where('user_id', Auth::user()->id)->first();
        $game_id = 0;
        if ($save_type == 1) {
            $game_id = $request->input('game_id');
            $game = GameList::find($game_id);
            if ($game) {
                $game_setting = $this->saveGameSetting($game, $request);
                // $setting = SeamlessSetting::where('user_id', Auth::user()->id)->first();
                $this->redisService->setPgGameSetting($setting->operator_token, $game->game_code, $game_setting);
            }
        } else {
            $games = GameList::where('game_status', 1)->get();
            $game_setting = UserGameSetting::where('user_id', Auth::user()->id)->get();


            foreach ($games as $game) {
                if (count($games) != count($game_setting)) {

                }
                $stt = $this->saveGameSetting($game, $request);
                $this->redisService->setPgGameSetting($setting->operator_token, $game->game_code, $stt);
            }
        }

        $log = new SettingHistory();
        $log->user_id = Auth::user()->id;
        $log->game_setting = $save_type == 1 ? $game_id : 0;
        $log->setting_value = json_encode($request->all());
        $log->type_setting = $save_type;
        $log->save();

        return redirect()->back();
    }

    public function saveCredit(Request $request) {
        $credit = $request->input('credit');
        $user = Auth::user();
        $user->credit = $credit;
        $user->save();
        return redirect()->back();
    }
}
