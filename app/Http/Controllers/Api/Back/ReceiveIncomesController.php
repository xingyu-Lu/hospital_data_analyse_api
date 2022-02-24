<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use App\Models\ReceiveIncome;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReceiveIncomesController extends Controller
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

        if ($office_name != '全院') {
            $where[] = ['receive_dep', '=', $office_name];
        }

        $receive_income = ReceiveIncome::where($where)->orderBy('date', 'asc')->get()->toArray();

        $res_data = $last_res_data = [];

        foreach ($receive_income as $key => $value) {
            if ($office_name == '全院') {
                $select_key = $value['date'];
            } else{
                $select_key = $value['date'] . '-' . $value['receive_dep'];
            }

            if (isset($res_data[$select_key])) {
                $res_data[$select_key]['pathology_income'] = bcadd($res_data[$select_key]['pathology_income'], $value['pathology_income'], 2);
                $res_data[$select_key]['material_income'] = bcadd($res_data[$select_key]['material_income'], $value['material_income'], 2);
                $res_data[$select_key]['ultrasound_income'] = bcadd($res_data[$select_key]['ultrasound_income'], $value['ultrasound_income'], 2);
                $res_data[$select_key]['radiation_income'] = bcadd($res_data[$select_key]['radiation_income'], $value['radiation_income'], 2);
                $res_data[$select_key]['check_income'] = bcadd($res_data[$select_key]['check_income'], $value['check_income'], 2);
                $res_data[$select_key]['checkout_income'] = bcadd($res_data[$select_key]['checkout_income'], $value['checkout_income'], 2);
                $res_data[$select_key]['surgery_income'] = bcadd($res_data[$select_key]['surgery_income'], $value['surgery_income'], 2);
                $res_data[$select_key]['xiyao_income'] = bcadd($res_data[$select_key]['xiyao_income'], $value['xiyao_income'], 2);
                $res_data[$select_key]['general_medical_income'] = bcadd($res_data[$select_key]['general_medical_income'], $value['general_medical_income'], 2);
                $res_data[$select_key]['zhongyao_income'] = bcadd($res_data[$select_key]['zhongyao_income'], $value['zhongyao_income'], 2);
                $res_data[$select_key]['total_money'] = bcadd($res_data[$select_key]['total_money'], $value['total_money'], 2);
            } else {
                $res_data[$select_key] = [
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'date' => date('Y-m', $value['date']),
                    'receive_dep' => $value['receive_dep'],
                    'patient_dep' => $value['patient_dep'],
                    'pathology_income' => $value['pathology_income'],
                    'material_income' => $value['material_income'],
                    'ultrasound_income' => $value['ultrasound_income'],
                    'radiation_income' => $value['radiation_income'],
                    'check_income' => $value['check_income'],
                    'checkout_income' => $value['checkout_income'],
                    'surgery_income' => $value['surgery_income'],
                    'xiyao_income' => $value['xiyao_income'],
                    'general_medical_income' => $value['general_medical_income'],
                    'zhongyao_income' => $value['zhongyao_income'],
                    'total_money' => $value['total_money'],
                ];
            }
        }
        $res_data = array_values($res_data);

        // 获取日期
        $date_arr = array_values(array_unique(array_column($res_data, 'date')));

        // 合计
        $pathology_income_total = $material_income_total = $ultrasound_income_total = $radiation_income_total = $check_income_total = 0;
        $checkout_income_total = $surgery_income_total = $xiyao_income_total = $general_medical_income_total = $zhongyao_income_total = $total_money_total = 0;

        $pathology_income_arr = array_column($res_data, 'pathology_income');
        foreach ($pathology_income_arr as $key => $value) {
            $pathology_income_total = bcadd($pathology_income_total, $value, 2);
        }

        $material_income_arr = array_column($res_data, 'material_income');
        foreach ($material_income_arr as $key => $value) {
            $material_income_total = bcadd($material_income_total, $value, 2);
        }

        $ultrasound_income_arr = array_column($res_data, 'ultrasound_income');
        foreach ($ultrasound_income_arr as $key => $value) {
            $ultrasound_income_total = bcadd($ultrasound_income_total, $value, 2);
        }

        $radiation_income_arr = array_column($res_data, 'radiation_income');
        foreach ($radiation_income_arr as $key => $value) {
            $radiation_income_total = bcadd($radiation_income_total, $value, 2);
        }

        $check_income_arr = array_column($res_data, 'check_income');
        foreach ($check_income_arr as $key => $value) {
            $check_income_total = bcadd($check_income_total, $value, 2);
        }

        $checkout_income_arr = array_column($res_data, 'checkout_income');
        foreach ($checkout_income_arr as $key => $value) {
            $checkout_income_total = bcadd($checkout_income_total, $value, 2);
        }

        $surgery_income_arr = array_column($res_data, 'surgery_income');
        foreach ($surgery_income_arr as $key => $value) {
            $surgery_income_total = bcadd($surgery_income_total, $value, 2);
        }

        $xiyao_income_arr = array_column($res_data, 'xiyao_income');
        foreach ($xiyao_income_arr as $key => $value) {
            $xiyao_income_total = bcadd($xiyao_income_total, $value, 2);
        }

        $general_medical_income_arr = array_column($res_data, 'general_medical_income');
        foreach ($general_medical_income_arr as $key => $value) {
            $general_medical_income_total = bcadd($general_medical_income_total, $value, 2);
        }

        $zhongyao_income_arr = array_column($res_data, 'zhongyao_income');
        foreach ($zhongyao_income_arr as $key => $value) {
            $zhongyao_income_total = bcadd($zhongyao_income_total, $value, 2);
        }

        $total_money_arr = array_column($res_data, 'total_money');
        foreach ($total_money_arr as $key => $value) {
            $total_money_total = bcadd($total_money_total, $value, 2);
        }

        $res_data[] = [
            'year' => '',
            'month' => '',
            'date' => '合计',
            'receive_dep' => '',
            'patient_dep' => '',
            'pathology_income' => $pathology_income_total,
            'material_income' => $material_income_total,
            'ultrasound_income' => $ultrasound_income_total,
            'radiation_income' => $radiation_income_total,
            'check_income' => $check_income_total,
            'checkout_income' => $checkout_income_total,
            'surgery_income' => $surgery_income_total,
            'xiyao_income' => $xiyao_income_total,
            'general_medical_income' => $general_medical_income_total,
            'zhongyao_income' => $zhongyao_income_total,
            'total_money' => $total_money_total,
        ];

        //实例化Excel
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
        $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle($office_name . '-接单收入');

        $head_arr = [
            '日期', '病理学诊断收入', '材料费收入', '超声检查收入', '放射检查收入', '检查费收入', '检验收入', '手术项目收入', '西药费收入', '一般医疗服务收入', '中药收入', '总金额'
        ];

        foreach ($head_arr as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($key+1, 1, $value);
        }

        foreach ($res_data as $key => $value) {
            $worksheet->setCellValueByColumnAndRow(1, $key+2, $value['date']);
            $worksheet->setCellValueByColumnAndRow(2, $key+2, $value['pathology_income']);
            $worksheet->setCellValueByColumnAndRow(3, $key+2, $value['material_income']);
            $worksheet->setCellValueByColumnAndRow(4, $key+2, $value['ultrasound_income']);
            $worksheet->setCellValueByColumnAndRow(5, $key+2, $value['radiation_income']);
            $worksheet->setCellValueByColumnAndRow(6, $key+2, $value['check_income']);
            $worksheet->setCellValueByColumnAndRow(7, $key+2, $value['checkout_income']);
            $worksheet->setCellValueByColumnAndRow(8, $key+2, $value['surgery_income']);
            $worksheet->setCellValueByColumnAndRow(9, $key+2, $value['xiyao_income']);
            $worksheet->setCellValueByColumnAndRow(10, $key+2, $value['general_medical_income']);
            $worksheet->setCellValueByColumnAndRow(11, $key+2, $value['zhongyao_income']);
            $worksheet->setCellValueByColumnAndRow(12, $key+2, $value['total_money']);
        }

         //下载
        $filename = $office_name . '-接单收入' . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Access-Control-Allow-Origin: *');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

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

        if ($office_name != '全院') {
            $where[] = ['receive_dep', '=', $office_name];
        }

        $receive_income = ReceiveIncome::where($where)->orderBy('date', 'asc')->get()->toArray();

        $res_data = $last_res_data = [];

        foreach ($receive_income as $key => $value) {
            if ($office_name == '全院') {
                $select_key = $value['date'];
            } else{
                $select_key = $value['date'] . '-' . $value['receive_dep'];
            }

            if (isset($res_data[$select_key])) {
                $res_data[$select_key]['pathology_income'] = bcadd($res_data[$select_key]['pathology_income'], $value['pathology_income'], 2);
                $res_data[$select_key]['material_income'] = bcadd($res_data[$select_key]['material_income'], $value['material_income'], 2);
                $res_data[$select_key]['ultrasound_income'] = bcadd($res_data[$select_key]['ultrasound_income'], $value['ultrasound_income'], 2);
                $res_data[$select_key]['radiation_income'] = bcadd($res_data[$select_key]['radiation_income'], $value['radiation_income'], 2);
                $res_data[$select_key]['check_income'] = bcadd($res_data[$select_key]['check_income'], $value['check_income'], 2);
                $res_data[$select_key]['checkout_income'] = bcadd($res_data[$select_key]['checkout_income'], $value['checkout_income'], 2);
                $res_data[$select_key]['surgery_income'] = bcadd($res_data[$select_key]['surgery_income'], $value['surgery_income'], 2);
                $res_data[$select_key]['xiyao_income'] = bcadd($res_data[$select_key]['xiyao_income'], $value['xiyao_income'], 2);
                $res_data[$select_key]['general_medical_income'] = bcadd($res_data[$select_key]['general_medical_income'], $value['general_medical_income'], 2);
                $res_data[$select_key]['zhongyao_income'] = bcadd($res_data[$select_key]['zhongyao_income'], $value['zhongyao_income'], 2);
                $res_data[$select_key]['total_money'] = bcadd($res_data[$select_key]['total_money'], $value['total_money'], 2);
            } else {
                $res_data[$select_key] = [
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'date' => date('Y-m', $value['date']),
                    'receive_dep' => $value['receive_dep'],
                    'patient_dep' => $value['patient_dep'],
                    'pathology_income' => $value['pathology_income'],
                    'material_income' => $value['material_income'],
                    'ultrasound_income' => $value['ultrasound_income'],
                    'radiation_income' => $value['radiation_income'],
                    'check_income' => $value['check_income'],
                    'checkout_income' => $value['checkout_income'],
                    'surgery_income' => $value['surgery_income'],
                    'xiyao_income' => $value['xiyao_income'],
                    'general_medical_income' => $value['general_medical_income'],
                    'zhongyao_income' => $value['zhongyao_income'],
                    'total_money' => $value['total_money'],
                ];
            }
        }
        $res_data = array_values($res_data);

        // 获取日期
        $date_arr = array_values(array_unique(array_column($res_data, 'date')));

        // 合计
        $pathology_income_total = $material_income_total = $ultrasound_income_total = $radiation_income_total = $check_income_total = 0;
        $checkout_income_total = $surgery_income_total = $xiyao_income_total = $general_medical_income_total = $zhongyao_income_total = $total_money_total = 0;

        $pathology_income_arr = array_column($res_data, 'pathology_income');
        foreach ($pathology_income_arr as $key => $value) {
            $pathology_income_total = bcadd($pathology_income_total, $value, 2);
        }

        $material_income_arr = array_column($res_data, 'material_income');
        foreach ($material_income_arr as $key => $value) {
            $material_income_total = bcadd($material_income_total, $value, 2);
        }

        $ultrasound_income_arr = array_column($res_data, 'ultrasound_income');
        foreach ($ultrasound_income_arr as $key => $value) {
            $ultrasound_income_total = bcadd($ultrasound_income_total, $value, 2);
        }

        $radiation_income_arr = array_column($res_data, 'radiation_income');
        foreach ($radiation_income_arr as $key => $value) {
            $radiation_income_total = bcadd($radiation_income_total, $value, 2);
        }

        $check_income_arr = array_column($res_data, 'check_income');
        foreach ($check_income_arr as $key => $value) {
            $check_income_total = bcadd($check_income_total, $value, 2);
        }

        $checkout_income_arr = array_column($res_data, 'checkout_income');
        foreach ($checkout_income_arr as $key => $value) {
            $checkout_income_total = bcadd($checkout_income_total, $value, 2);
        }

        $surgery_income_arr = array_column($res_data, 'surgery_income');
        foreach ($surgery_income_arr as $key => $value) {
            $surgery_income_total = bcadd($surgery_income_total, $value, 2);
        }

        $xiyao_income_arr = array_column($res_data, 'xiyao_income');
        foreach ($xiyao_income_arr as $key => $value) {
            $xiyao_income_total = bcadd($xiyao_income_total, $value, 2);
        }

        $general_medical_income_arr = array_column($res_data, 'general_medical_income');
        foreach ($general_medical_income_arr as $key => $value) {
            $general_medical_income_total = bcadd($general_medical_income_total, $value, 2);
        }

        $zhongyao_income_arr = array_column($res_data, 'zhongyao_income');
        foreach ($zhongyao_income_arr as $key => $value) {
            $zhongyao_income_total = bcadd($zhongyao_income_total, $value, 2);
        }

        $total_money_arr = array_column($res_data, 'total_money');
        foreach ($total_money_arr as $key => $value) {
            $total_money_total = bcadd($total_money_total, $value, 2);
        }

        $res_data[] = [
            'year' => '',
            'month' => '',
            'date' => '合计',
            'receive_dep' => '',
            'patient_dep' => '',
            'pathology_income' => $pathology_income_total,
            'material_income' => $material_income_total,
            'ultrasound_income' => $ultrasound_income_total,
            'radiation_income' => $radiation_income_total,
            'check_income' => $check_income_total,
            'checkout_income' => $checkout_income_total,
            'surgery_income' => $surgery_income_total,
            'xiyao_income' => $xiyao_income_total,
            'general_medical_income' => $general_medical_income_total,
            'zhongyao_income' => $zhongyao_income_total,
            'total_money' => $total_money_total,
        ];

        // 饼图
        $pie_chart = [
            'legend_data' => [
                '病理学诊断收入', '材料费收入', '超声检查收入', '放射检查收入', '检查费收入', '检验收入', '手术项目收入', '西药费收入', '一般医疗服务收入', '中药收入'
            ],
            'series_data' => [
                ['name' => '病理学诊断收入', 'value' => $pathology_income_total],
                ['name' => '材料费收入', 'value' => $material_income_total],
                ['name' => '超声检查收入', 'value' => $ultrasound_income_total],
                ['name' => '放射检查收入', 'value' => $radiation_income_total],
                ['name' => '检查费收入', 'value' => $check_income_total],
                ['name' => '检验收入', 'value' => $checkout_income_total],
                ['name' => '手术项目收入', 'value' => $surgery_income_total],
                ['name' => '西药费收入', 'value' => $xiyao_income_total],
                ['name' => '一般医疗服务收入', 'value' => $general_medical_income_total],
                ['name' => '中药收入', 'value' => $zhongyao_income_total],
            ],
        ];

        // 科室折线图
        $line_chart = [
            'legend_data' => [$office_name],
            'series_data' => $total_money_arr,
            'series_name' => $office_name,
            'series_date' => $date_arr,
        ];

        $head = $office_name . date('Y-m', $start_date) . '至' . date('Y-m', $end_date) . '接单收入';

        $last_res_data = [
            'data' => $res_data,
            'head' => $head,
            'pie_chart' => $pie_chart,
            'line_chart' => $line_chart,
        ];
        

        return responder()->success($last_res_data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_back(Request $request)
    {
        $params = $request->all();

        $start_date = strtotime($params['date'][0]);
        $end_date = strtotime($params['date'][1]);

        $office_name = $params['office_name'];
        $charge_subclass = $params['charge_subclass'];

        $where = [];

        $where = [
            ['date', '>=', $start_date],
            ['date', '<=', $end_date],
            ['receive_dep', '=', $office_name]
        ];

        if ($charge_subclass) {
            $where[] = [
                'charge_subclass', 'like', '%' . $charge_subclass . '%'
            ];
        }

        $receive_income = ReceiveIncome::where($where)->orderBy('date', 'desc')->get()->toArray();

        $res_data = [];

        foreach ($receive_income as $key => $value) {
            $select_key = $value['date'] . '-' . $value['receive_dep'] . '-' . $value['charge_subclass'];

            if (isset($res_data[$select_key])) {
                $res_data[$select_key]['num'] = bcadd($res_data[$select_key]['num'], $value['num']);
                $res_data[$select_key]['money'] = bcadd($res_data[$select_key]['money'], $value['money'], 2);
                // $res_data[$select_key]['num'] += $value['num'];
                // $res_data[$select_key]['money'] += $value['money'];
            } else {
                $res_data[$select_key] = [
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'date' => date('Y-m', $value['date']),
                    'receive_dep' => $value['receive_dep'],
                    'patient_dep' => $value['patient_dep'],
                    'charge_subclass' => $value['charge_subclass'],
                    'num' => $value['num'],
                    'money' => $value['money'],
                ];
            }
        }        

        $res_data = array_values($res_data);

        // 加同环比
        foreach ($res_data as $key => &$value) {
            // 环比
            $sequential_data = ReceiveIncome::where('date', strtotime('-1 month', strtotime($value['date'])))->where('receive_dep', $value['receive_dep'])->where('charge_subclass', $value['charge_subclass'])->sum('money');
            if ($sequential_data) {
                $sequential = bcdiv($value['money'] - $sequential_data, $sequential_data, 2);
            } else {
                $sequential = '';
            }
            $value['sequential'] = $sequential;

            // 同比
            $compare_same_data = ReceiveIncome::where('date', strtotime('-1 year', strtotime($value['date'])))->where('receive_dep', $value['receive_dep'])->where('charge_subclass', $value['charge_subclass'])->sum('money');
            if ($compare_same_data) {
                $compare_same = bcdiv($value['money'] - $compare_same_data, $compare_same_data, 2);
            } else {
                $compare_same = '';
            }
            
            $value['compare_same'] = $compare_same;            
        }
        unset($value);

        $date_arr = array_values(array_unique(array_column($res_data, 'date')));
        $title = $office_name;
        $legend_arr = array_unique(array_column($res_data, 'charge_subclass'));
        $money_arr = [];
        $series_arr = [];

        foreach ($legend_arr as $key => $value) {
            foreach ($res_data as $key_1 => $value_1) {
                if ($value == $value_1['charge_subclass']) {
                    $money_arr[$value][] = $value_1['money'];
                }  
            }
        }

        foreach ($legend_arr as $key => $value) {
            $series_arr[] = [
                'name' => $value,
                'type' => 'bar',
                'label' => [
                    'show' => true,
                    'position' => 'insideBottom',
                    'distance' => 15,
                    'align' => 'left',
                    'verticalAlign' => 'middle',
                    'rotate' => 90,
                    'formatter' => '{c} {name|{a}}',
                    'fontSize' => 16,
                    'rich' => [
                        'name' => []
                    ]
                ],
                'stack' => '',
                'areaStyle' => [],
                'emphasis' => ['focus' => 'series'],
                'data' => $money_arr[$value],
            ];
        }

        // 加合计
        $all_num = array_sum(array_column($res_data, 'num'));
        $all_money = 0;
        $all_money_all = array_column($res_data, 'money');
        foreach ($all_money_all as $key => $value) {
            $all_money = bcadd($all_money, $value, 2);
        }

        $res_data[] = [
            'year' => '',
            'month' => '',
            'date' => '合计',
            'receive_dep' => '',
            'patient_dep' => '',
            'charge_subclass' => '',
            'num' => $all_num,
            'money' => $all_money,
            'sequential' => '',
            'compare_same' => ''
        ];

        $res_data = [
            'data' => $res_data,
            'date_arr' => $date_arr,
            'title' => $title,
            'legend_arr' => $legend_arr,
            'money_arr' => $money_arr,
            'series_arr' => $series_arr,
        ];

        return responder()->success($res_data);
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
