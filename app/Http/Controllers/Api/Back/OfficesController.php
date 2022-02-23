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
            $office = OfficeContrast::select('value')->distinct()->get()->toArray();

            if (isset($params['type']) && $params['type'] == 1) {
                $arr = ['value' => '全院'];
                $arr_1 = ['value' => '全院(临床)'];

                array_unshift($office, $arr, $arr_1);
                array_pop($office);
            } else {
                $arr = ['value' => '全院'];

                array_unshift($office, $arr);
                array_pop($office);
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
