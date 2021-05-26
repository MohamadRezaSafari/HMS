<?php

namespace App\Http\Middleware;

use Closure;

class RoleDoctor
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


        if(
            $this->role->roles[0]['roleName'] == "hospital"
            ||
            $this->role->roles[0]['roleName'] == "hospitalAdmin"
            ||
            $this->role->roles[0]['roleName'] == "default"
            ||
            $this->role->roles[0]['roleName'] == "clerk"
        ){
            return redirect('AccessDenied');
        }

        return $next($request);
    }
}
