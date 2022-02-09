<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class InitRolesPermissionsAdminsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "=====begin=====" . PHP_EOL;

        // 重置角色和权限的缓存
        app()['cache']->forget('spatie.permission.cache');

        // 创建root角色
        Role::create(['guard_name' => app(Admin::class)->guardName(), 'name' => Role::ROOT]);

        //初始化权限
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

        //初始化超级管理员
        $admin = Admin::get();
        foreach ($admin as $key => $value) {
            $role = Role::where('name', Role::ROOT)->where('guard_name', app(Admin::class)->guardName())->first();
            $value->assignRole($role);
        }

        echo "=====end=====" . PHP_EOL;

        exit;
    }
}
