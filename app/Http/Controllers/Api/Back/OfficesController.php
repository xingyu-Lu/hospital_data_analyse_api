<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\OfficeContrast;
use App\Models\Role;
use Illuminate\Http\Request;

class OfficesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = $request->all();

        $user = auth('api')->user();

        $root = $user->hasRole(Role::ROOT, app(Admin::class)->guardName());

        if ($root) {
            $where = [];
            if (isset($params['is_lc']) && $params['is_lc'] == 1) {
                $where[] = [
                    'type', '=', 1
                ];
            }
            $office = OfficeContrast::select('value')->distinct()->where($where)->get()->toArray();

            if (isset($params['type']) && $params['type'] == 1) {
                $arr = ['value' => '全院(临床)'];
                array_push($office, $arr);
            }
        } else {
            if (isset($params['type']) && $params['type'] == 1) {
                $office = [
                    ['value' => '全院'],
                    ['value' => '全院(临床)'],
                    ['value' => $user['name']]
                ];
            } else {
                $office = [
                    ['value' => '全院'],
                    ['value' => $user['name']]
                ];
            }

            if (isset($params['indicator']) && $params['indicator'] == 1) {
                $office = [
                    ['value' => $user['name']]
                ];
            }

            if (isset($params['cost_control']) && $params['cost_control'] == 1) {
                $office = [
                    ['value' => '全院'],
                    ['value' => $user['name']]
                ];
            }
        }

        return responder()->success($office);
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
}
