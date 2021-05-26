<?php

namespace App\Http\Middleware;

use Closure;

class RoleHospitalAdmin
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
            $this->role->roles[0]['roleName'] == "doctor"
            ||
            $this->role->roles[0]['roleName'] == "default"
            ||
            $this->role->roles[0]['roleName'] == "clerk"
            ||
            $this->role->roles[0]['roleName'] == "hospital"
        ){
            return redirect('AccessDenied');
        }

        return $next($request);
    }
}
