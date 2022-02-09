<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\Role;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $current_user = auth('api')->user();

        $root = $current_user->hasRole(Role::ROOT, app(Admin::class)->guardName());
        
        if ($root) {
            return $next($request);
        }

        // 获取当前路由名称
        $currentRouteName = Route::currentRouteName();

        // 当路由不为 null 时，验证权限
        if (!is_null($currentRouteName)) {
            Gate::authorize($currentRouteName);
        }

        return $next($request);
    }
}
