<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use App\Models\OfficeContrast;
use App\Models\Pay;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PaysController extends Controller
{
    public function export(Request $request)
    {
        $params = $request->all();

        $start_date = strtotime($params['date'][0]);
        $end_date = strtotime($params['date'][1]);      

        $office_name = $params['office_name'];

        $where = [];

        $where = [
            ['date', '>=', $start_date],
            ['date', '<=', $end_date],
        ];

        if ($office_name != '全院' && $office_name != '全院(临床)') {
            $where[] = ['dep', '=', $office_name];
        }

        // 临床科室
        $office_lc = OfficeContrast::distinct()->where('type', 1)->pluck('value');

        $pay = Pay::where($where);

        if ($office_name == '全院(临床)') {
            $pay = Pay::where($where)->whereIn('dep', $office_lc);
        }

        $pay = $pay->orderBy('date', 'asc')->get()->toArray();

        $res_data = $res_data_qy_lc = $last_res_data = [];

        foreach ($pay as $key => $value) {
            if ($office_name == '全院' || $office_name == '全院(临床)') {
                $select_key = $value['date'];
            } else{
                $select_key = $value['date'] . '-' . $value['dep'];
            }

            if (isset($res_data[$select_key])) {
                $res_data[$select_key]['personnel_pay'] = bcadd($res_data[$select_key]['personnel_pay'], $value['personnel_pay'], 2);
                $res_data[$select_key]['fixed_asset_pay'] = bcadd($res_data[$select_key]['fixed_asset_pay'], $value['fixed_asset_pay'], 2);
                $res_data[$select_key]['material_pay'] = bcadd($res_data[$select_key]['material_pay'], $value['material_pay'], 2);
                $res_data[$select_key]['medicine_pay'] = bcadd($res_data[$select_key]['medicine_pay'], $value['medicine_pay'], 2);
                $res_data[$select_key]['other_pay'] = bcadd($res_data[$select_key]['other_pay'], $value['other_pay'], 2);
                $res_data[$select_key]['total_money'] = bcadd($res_data[$select_key]['total_money'], $value['total_money'], 2);
            } else {
                $res_data[$select_key] = [
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'date' => date('Y-m', $value['date']),
                    'dep' => $value['dep'],
                    'personnel_pay' => $value['personnel_pay'],
                    'fixed_asset_pay' => $value['fixed_asset_pay'],
                    'material_pay' => $value['material_pay'],
                    'medicine_pay' => $value['medicine_pay'],
                    'other_pay' => $value['other_pay'],
                    'total_money' => $value['total_money'],
                ];
            }
        }

        $res_data = array_values($res_data);

        // 获取日期
        $date_arr = array_values(array_unique(array_column($res_data, 'date')));

        // 合计
        $personnel_pay_total = $fixed_asset_pay_total = $material_pay_total = $medicine_pay_total = $other_pay_total = $total_money_total = 0;

        $personnel_pay_arr = array_column($res_data, 'personnel_pay');
        foreach ($personnel_pay_arr as $key => $value) {
            $personnel_pay_total = bcadd($personnel_pay_total, $value, 2);
        }

        $fixed_asset_pay_arr = array_column($res_data, 'fixed_asset_pay');
        foreach ($fixed_asset_pay_arr as $key => $value) {
            $fixed_asset_pay_total = bcadd($fixed_asset_pay_total, $value, 2);
        }

        $material_pay_arr = array_column($res_data, 'material_pay');
        foreach ($material_pay_arr as $key => $value) {
            $material_pay_total = bcadd($material_pay_total, $value, 2);
        }

        $medicine_pay_arr = array_column($res_data, 'medicine_pay');
        foreach ($medicine_pay_arr as $key => $value) {
            $medicine_pay_total = bcadd($medicine_pay_total, $value, 2);
        }

        $other_pay_arr = array_column($res_data, 'other_pay');
        foreach ($other_pay_arr as $key => $value) {
            $other_pay_total = bcadd($other_pay_total, $value, 2);
        }

        $total_money_arr = array_column($res_data, 'total_money');
        foreach ($total_money_arr as $key => $value) {
            $total_money_total = bcadd($total_money_total, $value, 2);
        }

        $res_data[] = [
            'year' => '',
            'month' => '',
            'date' => '合计',
            'dep' => '',
            'personnel_pay' => $personnel_pay_total,
            'fixed_asset_pay' => $fixed_asset_pay_total,
            'material_pay' => $material_pay_total,
            'medicine_pay' => $medicine_pay_total,
            'other_pay' => $other_pay_total,
            'total_money' => $total_money_total,
        ];

        //实例化Excel
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
        $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle($office_name . '-支出明细');

        $head_arr = [
            '日期', '人员经费', '固定资产折旧费', '卫生材料费', '药品费', '其他费用', '总金额'
        ];

        foreach ($head_arr as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($key+1, 1, $value);
        }

        foreach ($res_data as $key => $value) {
            $worksheet->setCellValueByColumnAndRow(1, $key+2, $value['date']);
            $worksheet->setCellValueByColumnAndRow(2, $key+2, $value['personnel_pay']);
            $worksheet->setCellValueByColumnAndRow(3, $key+2, $value['fixed_asset_pay']);
            $worksheet->setCellValueByColumnAndRow(4, $key+2, $value['material_pay']);
            $worksheet->setCellValueByColumnAndRow(5, $key+2, $value['medicine_pay']);
            $worksheet->setCellValueByColumnAndRow(6, $key+2, $value['other_pay']);
            $worksheet->setCellValueByColumnAndRow(7, $key+2, $value['total_money']);
        }

         //下载
        $filename = $office_name . '-支出明细' . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Access-Control-Allow-Origin: *');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = $request->all();

        $start_date = strtotime($params['date'][0]);
        $end_date = strtotime($params['date'][1]);      

        $office_name = $params['office_name'];

        $where = [];

        $where = [
            ['date', '>=', $start_date],
            ['date', '<=', $end_date],
        ];

        if ($office_name != '全院' && $office_name != '全院(临床)') {
            $where[] = ['dep', '=', $office_name];
        }

        // 临床科室
        $office_lc = OfficeContrast::distinct()->where('type', 1)->pluck('value');

        $pay = Pay::where($where);

        if ($office_name == '全院(临床)') {
            $pay = Pay::where($where)->whereIn('dep', $office_lc);
        }

        $pay = $pay->orderBy('date', 'asc')->get()->toArray();

        $res_data = $res_data_qy_lc = $last_res_data = [];

        foreach ($pay as $key => $value) {
            if ($office_name == '全院' || $office_name == '全院(临床)') {
                $select_key = $value['date'];
            } else{
                $select_key = $value['date'] . '-' . $value['dep'];
            }

            if (isset($res_data[$select_key])) {
                $res_data[$select_key]['personnel_pay'] = bcadd($res_data[$select_key]['personnel_pay'], $value['personnel_pay'], 2);
                $res_data[$select_key]['fixed_asset_pay'] = bcadd($res_data[$select_key]['fixed_asset_pay'], $value['fixed_asset_pay'], 2);
                $res_data[$select_key]['material_pay'] = bcadd($res_data[$select_key]['material_pay'], $value['material_pay'], 2);
                $res_data[$select_key]['medicine_pay'] = bcadd($res_data[$select_key]['medicine_pay'], $value['medicine_pay'], 2);
                $res_data[$select_key]['other_pay'] = bcadd($res_data[$select_key]['other_pay'], $value['other_pay'], 2);
                $res_data[$select_key]['total_money'] = bcadd($res_data[$select_key]['total_money'], $value['total_money'], 2);
            } else {
                $res_data[$select_key] = [
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'date' => date('Y-m', $value['date']),
                    'dep' => $value['dep'],
                    'personnel_pay' => $value['personnel_pay'],
                    'fixed_asset_pay' => $value['fixed_asset_pay'],
                    'material_pay' => $value['material_pay'],
                    'medicine_pay' => $value['medicine_pay'],
                    'other_pay' => $value['other_pay'],
                    'total_money' => $value['total_money'],
                ];
            }
        }

        $res_data = array_values($res_data);

        // 获取日期
        $date_arr = array_values(array_unique(array_column($res_data, 'date')));

        // 合计
        $personnel_pay_total = $fixed_asset_pay_total = $material_pay_total = $medicine_pay_total = $other_pay_total = $total_money_total = 0;

        $personnel_pay_arr = array_column($res_data, 'personnel_pay');
        foreach ($personnel_pay_arr as $key => $value) {
            $personnel_pay_total = bcadd($personnel_pay_total, $value, 2);
        }

        $fixed_asset_pay_arr = array_column($res_data, 'fixed_asset_pay');
        foreach ($fixed_asset_pay_arr as $key => $value) {
            $fixed_asset_pay_total = bcadd($fixed_asset_pay_total, $value, 2);
        }

        $material_pay_arr = array_column($res_data, 'material_pay');
        foreach ($material_pay_arr as $key => $value) {
            $material_pay_total = bcadd($material_pay_total, $value, 2);
        }

        $medicine_pay_arr = array_column($res_data, 'medicine_pay');
        foreach ($medicine_pay_arr as $key => $value) {
            $medicine_pay_total = bcadd($medicine_pay_total, $value, 2);
        }

        $other_pay_arr = array_column($res_data, 'other_pay');
        foreach ($other_pay_arr as $key => $value) {
            $other_pay_total = bcadd($other_pay_total, $value, 2);
        }

        $total_money_arr = array_column($res_data, 'total_money');
        foreach ($total_money_arr as $key => $value) {
            $total_money_total = bcadd($total_money_total, $value, 2);
        }

        $res_data[] = [
            'year' => '',
            'month' => '',
            'date' => '合计',
            'dep' => '',
            'personnel_pay' => $personnel_pay_total,
            'fixed_asset_pay' => $fixed_asset_pay_total,
            'material_pay' => $material_pay_total,
            'medicine_pay' => $medicine_pay_total,
            'other_pay' => $other_pay_total,
            'total_money' => $total_money_total,
        ];

        // 饼图
        $pie_chart = [
            'legend_data' => [
                '人员经费', '固定资产折旧费', '卫生材料费', '药品费', '其他费用',
            ],
            'series_data' => [
                ['name' => '人员经费', 'value' => $personnel_pay_total],
                ['name' => '固定资产折旧费', 'value' => $fixed_asset_pay_total],
                ['name' => '卫生材料费', 'value' => $material_pay_total],
                ['name' => '药品费', 'value' => $medicine_pay_total],
                ['name' => '其他费用', 'value' => $other_pay_total],
            ],
        ];

        // 科室折线图
        $line_chart = [
            'legend_data' => [$office_name, '全院(临床)'],
            'series_data' => $total_money_arr,
            'series_name' => $office_name,
            'series_date' => $date_arr,
        ];

        // 全员临床折线图
        $where_qy_lc = [];

        $where_qy_lc = [
            ['date', '>=', $start_date],
            ['date', '<=', $end_date],
        ];
        $pay_qy_lc = Pay::where($where_qy_lc)->whereIn('dep', $office_lc)->orderBy('date', 'asc')->get()->toArray();

        foreach ($pay_qy_lc as $key => $value) {
            $select_key = $value['date'];

            if (isset($res_data_qy_lc[$select_key])) {
                $res_data_qy_lc[$select_key]['personnel_pay'] = bcadd($res_data_qy_lc[$select_key]['personnel_pay'], $value['personnel_pay'], 2);
                $res_data_qy_lc[$select_key]['fixed_asset_pay'] = bcadd($res_data_qy_lc[$select_key]['fixed_asset_pay'], $value['fixed_asset_pay'], 2);
                $res_data_qy_lc[$select_key]['material_pay'] = bcadd($res_data_qy_lc[$select_key]['material_pay'], $value['material_pay'], 2);
                $res_data_qy_lc[$select_key]['medicine_pay'] = bcadd($res_data_qy_lc[$select_key]['medicine_pay'], $value['medicine_pay'], 2);
                $res_data_qy_lc[$select_key]['other_pay'] = bcadd($res_data_qy_lc[$select_key]['other_pay'], $value['other_pay'], 2);
                $res_data_qy_lc[$select_key]['total_money'] = bcadd($res_data_qy_lc[$select_key]['total_money'], $value['total_money'], 2);
            } else {
                $res_data_qy_lc[$select_key] = [
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'date' => date('Y-m', $value['date']),
                    'dep' => $value['dep'],
                    'personnel_pay' => $value['personnel_pay'],
                    'fixed_asset_pay' => $value['fixed_asset_pay'],
                    'material_pay' => $value['material_pay'],
                    'medicine_pay' => $value['medicine_pay'],
                    'other_pay' => $value['other_pay'],
                    'total_money' => $value['total_money'],
                ];
            }
        }

        $res_data_qy_lc = array_values($res_data_qy_lc);

        $total_money_arr_qy_lc = array_column($res_data_qy_lc, 'total_money');
        $line_chart_qy_lc = [
            'series_data' => $total_money_arr_qy_lc,
            'series_name' => '全院(临床)',
            'series_date' => $date_arr,
        ];

        $head = $office_name . date('Y-m', $start_date) . '至' . date('Y-m', $end_date) . '支出';

        $last_res_data = [
            'data' => $res_data,
            'head' => $head,
            'pie_chart' => $pie_chart,
            'line_chart' => $line_chart,
            'line_chart_qy_lc' => $line_chart_qy_lc,
        ];

        return responder()->success($last_res_data);
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
