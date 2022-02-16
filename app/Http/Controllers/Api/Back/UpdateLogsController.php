<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UpdateLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            [
                'content' => '导入数据，开单科室，接收科室，列表，柱状图，导出优化，等等',
                'timestamp' => '2022-02-16',
            ],
            [
                'content' => '开单科室、接收科室，列表，柱状图，导出，升级element puls等等',
                'timestamp' => '2022-02-15',
            ],
            [
                'content' => '获取数据改为从excel里获取，后端接口编写，前端页面编写等等',
                'timestamp' => '2022-02-14',
            ],
            [
                'content' => '初步编写导入数据脚本其他等等',
                'timestamp' => '2022-02-11',
            ],
            [
                'content' => '了解了查询统计语句，建修正对照表，并导入excel数据等等',
                'timestamp' => '2022-02-10',
            ],
            [
                'content' => '前后端建代码仓库，系统基础搭建，前后端部署我服务器等等',
                'timestamp' => '2022-02-09',
            ],
        ];

        return responder()->success($data);
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
