<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    const ROOT = 'root';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        if (static::where('name', $attributes['name'])->where('guard_name', $attributes['guard_name'])->first()) {
            throw RoleAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    public function giveMenuTo($menu)
    {
        foreach ($menu as $key => $value) {
            DB::table('role_has_menus')->insert(['role_id' => $this->id, 'menu_id' => $value]);
        }

        return true;
    }

    public function getMenuNames()
    {
        $menu_id = DB::table('role_has_menus')->select('menu_id')->where('role_id', $this->id)->get()->toArray();

        $menu_id = array_column($menu_id, 'menu_id');
        
        $menus = Menu::whereIn('id', $menu_id)->get()->toArray();

        return $menus;
    }

    public function syncMenus($menu)
    {
        DB::table('role_has_menus')->where('role_id', $this->id)->delete();

        foreach ($menu as $key => $value) {
            $query = DB::table('role_has_menus')->where('role_id', $this->id)->where('menu_id', $value)->first();

            if ($query) {
                continue;
            }

            DB::table('role_has_menus')->insert(['role_id' => $this->id, 'menu_id' => $value]);
        }

        return true;
    }

    public function menuDelete($ids)
    {
        foreach ($ids as $key => $value) {
            DB::table('role_has_menus')->where('role_id', $value)->delete();
        }
        
        return true;
    }
}
