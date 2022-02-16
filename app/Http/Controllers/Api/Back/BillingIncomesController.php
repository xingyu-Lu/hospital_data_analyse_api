<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use App\Models\BillingIncome;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BillingIncomesController extends Controller
{
    public function export(Request $request)
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
            ['billing_dep', '=', $office_name]
        ];

        if ($charge_subclass) {
            $where[] = [
                'charge_subclass', 'like', '%' . $charge_subclass . '%'
            ];
        }

        $billing_income = BillingIncome::where($where)->orderBy('date', 'desc')->get()->toArray();

        $res_data = [];

        foreach ($billing_income as $key => $value) {
            $select_key = $value['date'] . '-' . $value['billing_dep'] . '-' . $value['charge_subclass'];

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
                    'billing_dep' => $value['billing_dep'],
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
            $sequential_data = BillingIncome::where('date', strtotime('-1 month', strtotime($value['date'])))->where('billing_dep', $value['billing_dep'])->where('charge_subclass', $value['charge_subclass'])->sum('money');
            if ($sequential_data) {
                $sequential = bcdiv($value['money'] - $sequential_data, $sequential_data, 2);
            } else {
                $sequential = '';
            }
            $value['sequential'] = $sequential;

            // 同比
            $compare_same_data = BillingIncome::where('date', strtotime('-1 year', strtotime($value['date'])))->where('billing_dep', $value['billing_dep'])->where('charge_subclass', $value['charge_subclass'])->sum('money');
            if ($compare_same_data) {
                $compare_same = bcdiv($value['money'] - $compare_same_data, $compare_same_data, 2);
            } else {
                $compare_same = '';
            }
            
            $value['compare_same'] = $compare_same;            
        }
        unset($value);

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
            'billing_dep' => '',
            'patient_dep' => '',
            'charge_subclass' => '',
            'num' => $all_num,
            'money' => $all_money,
            'sequential' => '',
            'compare_same' => ''
        ];

        //实例化Excel
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
        $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle($office_name . '-开单收入');

        $head_arr = [
            '日期', '开单科室', '病人科室', '收费项目', '数量', '金额', '环比', '同比'
        ];

        foreach ($head_arr as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($key+1, 1, $value);
        }

        foreach ($res_data as $key => $value) {
            $worksheet->setCellValueByColumnAndRow(1, $key+2, $value['date']);
            $worksheet->setCellValueByColumnAndRow(2, $key+2, $value['billing_dep']);
            $worksheet->setCellValueByColumnAndRow(3, $key+2, $value['patient_dep']);
            $worksheet->setCellValueByColumnAndRow(4, $key+2, $value['charge_subclass']);
            $worksheet->setCellValueByColumnAndRow(5, $key+2, $value['num']);
            $worksheet->setCellValueByColumnAndRow(6, $key+2, $value['money']);
            $worksheet->setCellValueByColumnAndRow(7, $key+2, $value['sequential']);
            $worksheet->setCellValueByColumnAndRow(8, $key+2, $value['compare_same']);
        }

         //下载
        $filename = $office_name . '-开单收入' . '.xlsx';
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
        $charge_subclass = $params['charge_subclass'];

        $where = [];

        $where = [
            ['date', '>=', $start_date],
            ['date', '<=', $end_date],
            ['billing_dep', '=', $office_name]
        ];

        if ($charge_subclass) {
            $where[] = [
                'charge_subclass', 'like', '%' . $charge_subclass . '%'
            ];
        }

        $billing_income = BillingIncome::where($where)->orderBy('date', 'desc')->get()->toArray();

        $res_data = [];

        foreach ($billing_income as $key => $value) {
            $select_key = $value['date'] . '-' . $value['billing_dep'] . '-' . $value['charge_subclass'];

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
                    'billing_dep' => $value['billing_dep'],
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
            $sequential_data = BillingIncome::where('date', strtotime('-1 month', strtotime($value['date'])))->where('billing_dep', $value['billing_dep'])->where('charge_subclass', $value['charge_subclass'])->sum('money');
            if ($sequential_data) {
                $sequential = bcdiv($value['money'] - $sequential_data, $sequential_data, 2);
            } else {
                $sequential = '';
            }
            $value['sequential'] = $sequential;

            // 同比
            $compare_same_data = BillingIncome::where('date', strtotime('-1 year', strtotime($value['date'])))->where('billing_dep', $value['billing_dep'])->where('charge_subclass', $value['charge_subclass'])->sum('money');
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
            'billing_dep' => '',
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
