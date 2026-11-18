<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    function __construct()
    {

    }

    public function index() {
        return view('Backoffice.Dashboard.index');
    }

    public function quickRandom($length = 16)
    {
        $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    public function login(Request $request) {
        $username = $request->input('username');
        $password = $request->input('password');

        $user = User::where('username', $username)->first();
        if ($user && Hash::check($password, $user->password)) {
            $user->token =  $this->quickRandom(4) . '-' . $this->quickRandom(4) . '-' . $this->quickRandom(4) . '-' . $this->quickRandom(4);
            $user->save();
            Auth::loginUsingId($user->id);
            return redirect(route('admin.index'));
        }
        return redirect()->back();
    }

}
