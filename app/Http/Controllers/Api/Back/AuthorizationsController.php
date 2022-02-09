<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Exceptions\BaseException;

class AuthorizationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

        $name = $params['name'] ?? '';
        $password = md5($params['password'] ?? '');

        $admin = Admin::where('name', $name)->first();

        if ($admin) {
            if ($admin['status'] == 0) {
                throw new BaseException(['msg' => '已禁用']);
            }

            if ($password != $admin['password']) {
                throw new BaseException(['msg' => '密码错误']);
            }

            $token = Auth::guard('api')->login($admin);

            return responder()->success(['token' => $token])->respond();
        } else {
            throw new BaseException(['msg' => '账号不存在']);
        }
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
