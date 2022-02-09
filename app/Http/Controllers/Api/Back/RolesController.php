<?php

namespace App\Http\Controllers\Api\Back;

use App\Exceptions\BaseException;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::where('name', '<>', 'root')->get()->toArray();

        return responder()->success($roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $params = $request->all();

        //创建角色
        $role_name = Role::where('name', $params['name'])->where('guard_name', app(Admin::class)->guardName())->first();
        if ($role_name) {
            throw new BaseException(['msg' => '角色已存在']);
        }
        $role = Role::create(['guard_name' => app(Admin::class)->guardName(), 'name' => $params['name']]);

        //角色赋予权限
        $permission = Permission::whereIn('id', $params['permission'])->get();
        $role->givePermissionTo($permission);

        //角色赋予菜单
        $role->giveMenuTo($params['menu']);

        return responder()->success();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::where('id', $id)->first();

        $role->getPermissionNames();

        $permission = [];

        foreach ($role['permissions'] as $key => $value) {
            $permission[] = $value['id'];
        }
        $role['permission'] = $permission;

        $role['menus'] = $role->getMenuNames();

        $role['menu'] = array_column($role->getMenuNames(), 'id');

        return responder()->success($role);
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
        $params = $request->all();

        $role = Role::where('id', $id)->first();

        //更新角色
        $role->name = $params['name'];
        $role->save();

        //更新角色权限
        $permission = Permission::whereIn('id', $params['permission'])->get();
        $role->syncPermissions($permission);

        //更新角色菜单
        $role->syncMenus($params['menu']);

        return responder()->success();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::where('id', $id)->first();    

        //删除角色
        $role->delete();

        //删除角色菜单
        $role->menuDelete([$id]);

        return responder()->success();
    }
}
