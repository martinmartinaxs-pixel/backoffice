<?php

namespace App\Http\Controllers\Main;

use App\Models\User;
use App\Models\WebContent;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{
    protected $mainUrl;
    protected $mainStaticUrl;
    function __construct()
    {
    }
}
