<?php

namespace App\Http\Middleware;

use Closure;

class Owner
{
    /**
     * @var string
     */
    protected $role;
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->role = \Auth::user();
        
        if($this->role->roles[0]['roleName'] != "owner"){
            return redirect('AccessDenied');
        }
        
        return $next($request);
    }
}
