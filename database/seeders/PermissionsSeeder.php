<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "=====begin=====" . PHP_EOL;

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

        echo "=====end=====" . PHP_EOL;

        exit;
    }
}
