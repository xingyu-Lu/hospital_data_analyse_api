<?php

namespace App\Http\Controllers\Api\Back;

use App\Exceptions\BaseException;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminsController extends Controller
{
    public function info()
    {
        $user = auth('api')->user();

        return responder()->success($user);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page_size = 10;

        $admins = Admin::orderBy('id', 'desc')->where('id', '<>', 1)->paginate($page_size);;

        foreach ($admins as $key => $value) {
            $value->getRoleNames();
        }

        return responder()->success($admins);
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

        if ($params['new_password']) {
            $params['password'] = md5($params['new_password']);
        } else {
            $params['password'] = md5($params['password']);
        }

        unset($params['new_password']);

        $admin = Admin::where('name', $params['name'])->first();

        if ($admin) {
            throw new BaseException(['msg' => '账号名已存在']);
        }

        $role_ids = $params['role_ids'];
        unset($params['role_ids']);

        $admin = Admin::create($params);

        //管理员关联角色
        $roles = Role::whereIn('id', $role_ids)->where('guard_name', app(Admin::class)->guardName())->get();

        $admin->assignRole($roles);

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
        $admin = Admin::where('id', $id)->first();

        $role = [];

        $admin->getRoleNames();

        foreach ($admin['roles'] as $key => $value) {
            $role[] = $value['id'];
        }

        $admin['role'] = $role;

        return responder()->success($admin);
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

        if ($params['new_password']) {
            $params['password'] = md5($params['new_password']);
        } else {
            $params['password'] = md5($params['password']);
        }

        unset($params['new_password']);

        $role_ids = $params['role_ids'];
        unset($params['role_ids']);

        $admin = Admin::updateOrCreate(['id' => $id], $params);

        // 更新角色
        $roles = Role::whereIn('id', $role_ids)->where('guard_name', app(Admin::class)->guardName())->get();
        $admin->syncRoles($roles);

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

    }

    public function status(Request $request)
    {
        $params = $request->all();

        $id = $params['id'];
        $status = $params['status'];

        Admin::updateOrCreate(['id' => $id], ['status' => $status]);

        return responder()->success();
    }

    public function changepwd(Request $request)
    {
        $user = auth('api')->user();

        $params = $request->all();

        $old_pwd = $params['old_password'];

        $new_pwd = $params['new_password'];

        if (md5($old_pwd) != $user['password']) {
            throw new BaseException(['msg' => '原密码错误']);
        }

        $user->password = md5($new_pwd);

        $user->save();

        return responder()->success();
    }
}
