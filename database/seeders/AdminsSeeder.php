<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\OfficeContrast;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AdminsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "=====begin=====" . PHP_EOL;

        $office = OfficeContrast::select('value')->distinct()->get()->toArray();

        foreach ($office as $key => $value) {
            $admin = Admin::where('name', $value['value'])->first();

            if ($admin) {
                continue;
            }

            $insert_data = [
                'name' => $value['value'],
                'password' => md5(123456),
                'status' => 1,
            ];

            $admin = Admin::create($insert_data);

            //管理员关联角色
            $roles = Role::where('name', 'test')->where('guard_name', app(Admin::class)->guardName())->first();

            $admin->assignRole($roles);
        }

        echo "=====end=====" . PHP_EOL;
    }
}
