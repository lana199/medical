<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeEnum;
use App\Models\Doctor;
use App\Models\Patient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $guards = empty($guards) ? [null] : $guards;
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check() && Auth::user()->role != UserTypeEnum::getValue($role)) {
                return abort(403);
            }
            $user = Auth::user();

          if(isset($user)){
              if ($user) {
                  if ($user->role == UserTypeEnum::Doctor) {
                      $doctor = Doctor::whereId($user->id)->first();
                      if ($doctor->is_active == 0) {
                          Auth::logout();
                          return redirect()->route('accountNotActive');
                      }
                  }
                  if ($user->role == UserTypeEnum::Patient) {
                      $patient = Patient::whereId($user->id)->first();
                      if ($patient->is_active == 0) {
                          Auth::logout();
                          return redirect()->route('accountNotActive');
                      }
                  }
              }
          }else{
              return redirect()->route('login');
          }
        }

        return $next($request);
    }
}
