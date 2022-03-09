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
                'content' => '导入数据脚本优化等等',
                'timestamp' => '2022-03-09',
            ],
            [
                'content' => '升级element-plus等等',
                'timestamp' => '2022-03-07',
            ],
            [
                'content' => 'ico优化等等',
                'timestamp' => '2022-03-04',
            ],
            [
                'content' => '标记金额元单位等等',
                'timestamp' => '2022-03-03',
            ],
            [
                'content' => '接单排名接口及页面编写，联调,升级element-pus等等',
                'timestamp' => '2022-03-02',
            ],
            [
                'content' => '开单排名接口及页面编写，联调等等',
                'timestamp' => '2022-03-01',
            ],
            [
                'content' => '升级element-pus，成本控制，排名需求分析建表等等',
                'timestamp' => '2022-02-28',
            ],
            [
                'content' => '重点指标任务脚本修改，菜单优化，默认科室优化，加重点指标等等',
                'timestamp' => '2022-02-25',
            ],
            [
                'content' => '重点指标任务脚本，成本控制任务脚本等等',
                'timestamp' => '2022-02-24',
            ],
            [
                'content' => '指标篇需求分析，建表，重点指标任务脚本部分等等',
                'timestamp' => '2022-02-23',
            ],
            [
                'content' => '支出明细导数据，加支出明细，科室区分临床与非临床等等',
                'timestamp' => '2022-02-22',
            ],
            [
                'content' => '开单收入，接单收入重构，加饼图、折线图，等等',
                'timestamp' => '2022-02-21',
            ],
            [
                'content' => '了解需求，重构数据库，重构导入数据，等等',
                'timestamp' => '2022-02-21',
            ],
            [
                'content' => '导入数据，开单科室，接收科室，列表，柱状图，导出优化，加同环比，等等',
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
