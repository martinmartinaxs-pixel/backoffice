<?php

namespace App\Http\Controllers\Rent;

use App\Models\SeamlessSetting;
use App\Models\User;
use App\Models\WebContent;
use App\Services\RedisService;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LoginLineController extends Controller
{
    function __construct()
    {

    }

    public function loginByToken($token) {
        $user = User::where('login_token', $token)->first();
        if ($user) {
            return redirect()->route('admin.index', ['game' => 'pg']);
        }
        return redirect()->route('welcome');
    }
}
