<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use App\Models\CostControl;
use App\Models\OfficeContrast;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CostControlsController extends Controller
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

        if ($office_name != '全院(临床)') {
            $where[] = ['dep', '=', $office_name];
        }

        // 临床科室
        $office_lc = OfficeContrast::distinct()->where('type', 1)->pluck('value');

        $cost_control = CostControl::where($where);

        if ($office_name == '全院(临床)') {
            $cost_control = $cost_control->whereIn('dep', $office_lc);
        }

        $cost_control = $cost_control->orderBy('date', 'asc')->get()->toArray();
        $cost_control_data = [];

        foreach ($cost_control as $key => $value) {
            $select_key = $value['date'];

            if (isset($cost_control_data[$select_key])) {
                $cost_control_data[$select_key]['personnel_cost'] = bcadd($cost_control_data[$select_key]['personnel_cost'], $value['personnel_cost'], 2);
                $cost_control_data[$select_key]['consumable_cost'] = bcadd($cost_control_data[$select_key]['consumable_cost'], $value['consumable_cost'], 2);
                $cost_control_data[$select_key]['drug_cost'] = bcadd($cost_control_data[$select_key]['drug_cost'], $value['drug_cost'], 2);
                $cost_control_data[$select_key]['fixed_asset_cost'] = bcadd($cost_control_data[$select_key]['fixed_asset_cost'], $value['fixed_asset_cost'], 2);
                $cost_control_data[$select_key]['other_cost'] = bcadd($cost_control_data[$select_key]['other_cost'], $value['other_cost'], 2);
                $cost_control_data[$select_key]['total_cost'] = bcadd($cost_control_data[$select_key]['total_cost'], $value['total_cost'], 2);
            } else {
                $cost_control_data[$select_key] = [
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'date' => date('Y-m', $value['date']),
                    'dep' => $value['dep'],
                    'personnel_cost' => $value['personnel_cost'],
                    'consumable_cost' => $value['consumable_cost'],
                    'drug_cost' => $value['drug_cost'],
                    'fixed_asset_cost' => $value['fixed_asset_cost'],
                    'other_cost' => $value['other_cost'],
                    'total_cost' => $value['total_cost'],
                ];
            }
        }

        // 获取日期
        $date_arr = array_values(array_unique(array_column($cost_control_data, 'date')));

        $month_num = count($cost_control_data);

        $personnel_cost_total = $consumable_cost_total = $drug_cost_total = $fixed_asset_cost_total = $other_cost_total = $total_cost_total = 0;
        $personnel_cost_avg = $consumable_cost_avg = $drug_cost_avg = $fixed_asset_cost_avg = $other_cost_avg = $total_cost_avg = 0;

        // 求平均
        $personnel_cost_arr = array_values($cost_control_data, 'personnel_cost');
        foreach ($personnel_cost_arr as $key => $value) {
            $personnel_cost_total = bcadd($personnel_cost_total, $value, 2);
        }
        $personnel_cost_avg = bcdiv($personnel_cost_total, $month_num, 2);

        $consumable_cost_arr = array_values($cost_control_data, 'consumable_cost');
        foreach ($consumable_cost_arr as $key => $value) {
            $consumable_cost_total = bcadd($consumable_cost_total, $value, 2);
        }
        $consumable_cost_avg = bcdiv($consumable_cost_total, $month_num, 2);

        $drug_cost_arr = array_values($cost_control_data, 'drug_cost');
        foreach ($drug_cost_arr as $key => $value) {
            $drug_cost_total = bcadd($drug_cost_total, $value, 2);
        }
        $drug_cost_avg = bcdiv($drug_cost_total, $month_num, 2);

        $fixed_asset_cost_arr = array_values($cost_control_data, 'fixed_asset_cost');
        foreach ($fixed_asset_cost_arr as $key => $value) {
            $fixed_asset_cost_total = bcadd($fixed_asset_cost_total, $value, 2);
        }
        $fixed_asset_cost_avg = bcdiv($fixed_asset_cost_total, $month_num, 2);  
        
        $other_cost_arr = array_values($cost_control_data, 'other_cost');
        foreach ($other_cost_arr as $key => $value) {
            $other_cost_total = bcadd($other_cost_total, $value, 2);
        }
        $other_cost_avg = bcdiv($other_cost_total, $month_num, 2);

        $total_cost_arr = array_values($cost_control_data, 'total_cost');
        foreach ($total_cost_arr as $key => $value) {
            $total_cost_total = bcadd($total_cost_total, $value, 2);
        }
        $total_cost_avg = bcdiv($total_cost_total, $month_num, 2);

        $cost_control_data[] = [
            'date' => '合计',
            'year' => '',
            'month' => '',
            'dep' => $office_name,
            'personnel_cost' => $personnel_cost_avg,
            'consumable_cost' => $consumable_cost_avg,
            'drug_cost' => $drug_cost_avg,
            'fixed_asset_cost' => $fixed_asset_cost_avg,
            'other_cost' => $other_cost_avg,
            'total_cost' => $total_cost_avg,
        ];
        unset($cost_control);

        //实例化Excel
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
        $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle($office_name . '-重点指标');

        $head_arr = [
            '日期', '人员经费', '耗材支出', '药品费', '固定资产折旧费', '其他支出', '合计'
        ];

        foreach ($head_arr as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($key+1, 1, $value);
        }

        foreach ($res_data as $key => $value) {
            $worksheet->setCellValueByColumnAndRow(1, $key+2, $value['date']);
            $worksheet->setCellValueByColumnAndRow(2, $key+2, $value['personnel_cost']);
            $worksheet->setCellValueByColumnAndRow(3, $key+2, $value['consumable_cost']);
            $worksheet->setCellValueByColumnAndRow(4, $key+2, $value['drug_cost']);
            $worksheet->setCellValueByColumnAndRow(5, $key+2, $value['fixed_asset_cost']);
            $worksheet->setCellValueByColumnAndRow(6, $key+2, $value['other_cost']);
            $worksheet->setCellValueByColumnAndRow(7, $key+2, $value['total_cost']);
        }

         //下载
        $filename = $office_name . '-成本控制' . '.xlsx';
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

        if ($office_name != '全院(临床)') {
            $where[] = ['dep', '=', $office_name];
        }

        // 临床科室
        $office_lc = OfficeContrast::distinct()->where('type', 1)->pluck('value');

        $cost_control = CostControl::where($where);

        if ($office_name == '全院(临床)') {
            $cost_control = $cost_control->whereIn('dep', $office_lc);
        }

        $cost_control = $cost_control->orderBy('date', 'asc')->get()->toArray();
        $cost_control_data = [];

        foreach ($cost_control as $key => $value) {
            $select_key = $value['date'];

            if (isset($cost_control_data[$select_key])) {
                $cost_control_data[$select_key]['personnel_cost'] = bcadd($cost_control_data[$select_key]['personnel_cost'], $value['personnel_cost'], 2);
                $cost_control_data[$select_key]['consumable_cost'] = bcadd($cost_control_data[$select_key]['consumable_cost'], $value['consumable_cost'], 2);
                $cost_control_data[$select_key]['drug_cost'] = bcadd($cost_control_data[$select_key]['drug_cost'], $value['drug_cost'], 2);
                $cost_control_data[$select_key]['fixed_asset_cost'] = bcadd($cost_control_data[$select_key]['fixed_asset_cost'], $value['fixed_asset_cost'], 2);
                $cost_control_data[$select_key]['other_cost'] = bcadd($cost_control_data[$select_key]['other_cost'], $value['other_cost'], 2);
                $cost_control_data[$select_key]['total_cost'] = bcadd($cost_control_data[$select_key]['total_cost'], $value['total_cost'], 2);
            } else {
                $cost_control_data[$select_key] = [
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'date' => date('Y-m', $value['date']),
                    'dep' => $value['dep'],
                    'personnel_cost' => $value['personnel_cost'],
                    'consumable_cost' => $value['consumable_cost'],
                    'drug_cost' => $value['drug_cost'],
                    'fixed_asset_cost' => $value['fixed_asset_cost'],
                    'other_cost' => $value['other_cost'],
                    'total_cost' => $value['total_cost'],
                ];
            }
        }

        // 获取日期
        $date_arr = array_values(array_unique(array_column($cost_control_data, 'date')));

        $month_num = count($cost_control_data);

        $personnel_cost_total = $consumable_cost_total = $drug_cost_total = $fixed_asset_cost_total = $other_cost_total = $total_cost_total = 0;
        $personnel_cost_avg = $consumable_cost_avg = $drug_cost_avg = $fixed_asset_cost_avg = $other_cost_avg = $total_cost_avg = 0;

        // 求平均
        $personnel_cost_arr = array_values($cost_control_data, 'personnel_cost');
        foreach ($personnel_cost_arr as $key => $value) {
            $personnel_cost_total = bcadd($personnel_cost_total, $value, 2);
        }
        $personnel_cost_avg = bcdiv($personnel_cost_total, $month_num, 2);

        $consumable_cost_arr = array_values($cost_control_data, 'consumable_cost');
        foreach ($consumable_cost_arr as $key => $value) {
            $consumable_cost_total = bcadd($consumable_cost_total, $value, 2);
        }
        $consumable_cost_avg = bcdiv($consumable_cost_total, $month_num, 2);

        $drug_cost_arr = array_values($cost_control_data, 'drug_cost');
        foreach ($drug_cost_arr as $key => $value) {
            $drug_cost_total = bcadd($drug_cost_total, $value, 2);
        }
        $drug_cost_avg = bcdiv($drug_cost_total, $month_num, 2);

        $fixed_asset_cost_arr = array_values($cost_control_data, 'fixed_asset_cost');
        foreach ($fixed_asset_cost_arr as $key => $value) {
            $fixed_asset_cost_total = bcadd($fixed_asset_cost_total, $value, 2);
        }
        $fixed_asset_cost_avg = bcdiv($fixed_asset_cost_total, $month_num, 2);  
        
        $other_cost_arr = array_values($cost_control_data, 'other_cost');
        foreach ($other_cost_arr as $key => $value) {
            $other_cost_total = bcadd($other_cost_total, $value, 2);
        }
        $other_cost_avg = bcdiv($other_cost_total, $month_num, 2);

        $total_cost_arr = array_values($cost_control_data, 'total_cost');
        foreach ($total_cost_arr as $key => $value) {
            $total_cost_total = bcadd($total_cost_total, $value, 2);
        }
        $total_cost_avg = bcdiv($total_cost_total, $month_num, 2);

        $cost_control_data[] = [
            'date' => '合计',
            'year' => '',
            'month' => '',
            'dep' => $office_name,
            'personnel_cost' => $personnel_cost_avg,
            'consumable_cost' => $consumable_cost_avg,
            'drug_cost' => $drug_cost_avg,
            'fixed_asset_cost' => $fixed_asset_cost_avg,
            'other_cost' => $other_cost_avg,
            'total_cost' => $total_cost_avg,
        ];
        unset($cost_control);

        // 科室折线图
        $line_chart = [
            'legend_data' => [$office_name, '全院(临床)'],
            'series_data' => $total_cost_arr,
            'series_name' => $office_name,
            'series_date' => $date_arr,
        ];

        // 全员临床折线图
        $where_qy_lc = [];

        $where_qy_lc = [
            ['date', '>=', $start_date],
            ['date', '<=', $end_date],
        ];

        $cost_control_qy_lc = Pay::where($where_qy_lc)->whereIn('dep', $office_lc)->orderBy('date', 'asc')->get()->toArray();
        $cost_control_data_qy_lc = [];

        foreach ($cost_control_qy_lc as $key => $value) {
            $select_key = $value['date'];

            if (isset($cost_control_data_qy_lc[$select_key])) {
                $cost_control_data_qy_lc[$select_key]['personnel_cost'] = bcadd($cost_control_data_qy_lc[$select_key]['personnel_cost'], $value['personnel_cost'], 2);
                $cost_control_data_qy_lc[$select_key]['consumable_cost'] = bcadd($cost_control_data_qy_lc[$select_key]['consumable_cost'], $value['consumable_cost'], 2);
                $cost_control_data_qy_lc[$select_key]['drug_cost'] = bcadd($cost_control_data_qy_lc[$select_key]['drug_cost'], $value['drug_cost'], 2);
                $cost_control_data_qy_lc[$select_key]['fixed_asset_cost'] = bcadd($cost_control_data_qy_lc[$select_key]['fixed_asset_cost'], $value['fixed_asset_cost'], 2);
                $cost_control_data_qy_lc[$select_key]['other_cost'] = bcadd($cost_control_data_qy_lc[$select_key]['other_cost'], $value['other_cost'], 2);
                $cost_control_data_qy_lc[$select_key]['total_cost'] = bcadd($cost_control_data_qy_lc[$select_key]['total_cost'], $value['total_cost'], 2);
            } else {
                $cost_control_data_qy_lc[$select_key] = [
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'date' => date('Y-m', $value['date']),
                    'dep' => $value['dep'],
                    'personnel_cost' => $value['personnel_cost'],
                    'consumable_cost' => $value['consumable_cost'],
                    'drug_cost' => $value['drug_cost'],
                    'fixed_asset_cost' => $value['fixed_asset_cost'],
                    'other_cost' => $value['other_cost'],
                    'total_cost' => $value['total_cost'],
                ];
            }
        }

        unset($cost_control_qy_lc);

        $cost_control_data_qy_lc = array_values($cost_control_data_qy_lc);

        $total_cost_arr_qy_lc = array_column($cost_control_data_qy_lc, 'total_cost');
        $line_chart_qy_lc = [
            'series_data' => $total_cost_arr_qy_lc,
            'series_name' => '全院(临床)',
            'series_date' => $date_arr,
        ];

        $head = $office_name . date('Y-m', $start_date) . '至' . date('Y-m', $end_date) . '成本控制';

        $last_res_data = [
            'data' => $cost_control_data,
            'head' => $head,
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
