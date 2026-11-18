<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    function __construct()
    {

    }

    public function index() {
        return view('Backoffice.User.userList');
    }

    public function logout() {
        if (Auth::user()) {
            Auth::logout();
        }
        return redirect()->route('welcome');
    }
}
