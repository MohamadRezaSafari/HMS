<?php

namespace App\Http\Middleware;

use Closure;

class Role
{
    /**
     * @var string
     */
    private $user;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        $this->user = \Auth::user();
//
//        if($this->user->roles[0]['roleName'] == "default"){
//            return redirect('/AccessDenied');
//        }



        return $next($request);
    }
}
