<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permission = Permission::getPermissions()->toArray();

        return responder()->success($permission);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function saveApiPermission()
    {
        $routes = Route::getRoutes()->get();

        foreach ($routes as $key => $value) {
            $action = $value->action;
            $middleware = $action['middleware'];
            if (in_array('auth:api', $middleware) && in_array('permission', $middleware)) {
                $name = $action['as'];
                try {
                    $permission_name = Permission::findByName($name);
                    if ($permission_name) {
                        continue;
                    }
                } catch (\Exception $e) {
                    //权限不存在创建权限
                    Permission::create(['guard_name' => app(Admin::class)->guardName(), 'name' => $name]);
                }
            }
        }
        
        return responder()->success();
    }
}
