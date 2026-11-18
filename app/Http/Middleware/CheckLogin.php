<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\RequestException;

class CheckLogin
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected $agent_api;
    protected $ufaService;
    protected $jokerService;
    protected $jokerNewService;
    protected $nikiService;
    protected $hengService;
    protected $agentGameService;
    protected $redisService;
    function __construct() {
    }
    public function handle($request, Closure $next)
    {
        if (Auth::user()) {
            $timestamp = time();
            if (Auth::user()->valid_until === null || Auth::user()->valid_until >= $timestamp) {
                return $next($request);
            }
            Auth::logout();
        }
        return redirect()->route('welcome');
    }
}
