<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use App\Models\BillingChargeName;
use App\Models\BillingIncome;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BillingRanksController extends Controller
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
            ['billing_dep', '=', $office_name]
        ];

        $data = BillingChargeName::where($where)->orderBy('money', 'desc')->get();

        $res_data = [];

        $i = 0;

        foreach ($data as $key => $value) {
            $select_key = $value['charge_name'];
            if (isset($res_data[$select_key])) {
                $res_data[$select_key]['num'] += $value['num'];
                $res_data[$select_key]['money'] += $value['money'];
            } else {
                $res_data[$select_key] = [
                    'rank' => ++$i,
                    'billing_dep' => $value['billing_dep'],
                    'charge_name' => $value['charge_name'],
                    'money' => $value['money'],
                    'num' => $value['num'],
                ];
            }
        }

        $total_money = BillingIncome::where($where)->sum('total_money');
        foreach ($res_data as $key => &$value) {
            $value['ratio'] = bcmul(bcdiv($value['money'], $total_money, 4), 100) . '%';
        }
        unset($value);
        $res_data = array_values($res_data);

        $res_data = array_slice($res_data, 0, 20);

        // 合计
        $total_money_total = 0;
        $total_money_arr = array_column($res_data, 'money');
        foreach ($total_money_arr as $key => $value) {
            $total_money_total = bcadd($total_money_total, $value, 2);
        }

        $res_data[] = [
            'rank' => '合计',
            'billing_dep' => '',
            'charge_name' => '',
            'money' => $total_money_total,
            'num' => '',
            'ratio' => bcmul(bcdiv($total_money_total, $total_money, 4), 100) . '%'
        ];

        //实例化Excel
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
        $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle($office_name . '-开单排名');

        $head_arr = [
            '金额排名', '收费项目名称', '金额(元)', '占科室收入比例'
        ];

        foreach ($head_arr as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($key+1, 1, $value);
        }

        foreach ($res_data as $key => $value) {
            $worksheet->setCellValueByColumnAndRow(1, $key+2, $value['rank']);
            $worksheet->setCellValueByColumnAndRow(2, $key+2, $value['charge_name']);
            $worksheet->setCellValueByColumnAndRow(3, $key+2, $value['money']);
            $worksheet->setCellValueByColumnAndRow(4, $key+2, $value['ratio']);
        }

         //下载
        $filename = $office_name . '-加单排名' . '.xlsx';
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
            ['billing_dep', '=', $office_name]
        ];

        $data = BillingChargeName::where($where)->orderBy('money', 'desc')->get();

        $res_data = [];

        $i = 0;

        foreach ($data as $key => $value) {
            $select_key = $value['charge_name'];
            if (isset($res_data[$select_key])) {
                $res_data[$select_key]['num'] += $value['num'];
                $res_data[$select_key]['money'] += $value['money'];
            } else {
                $res_data[$select_key] = [
                    'rank' => ++$i,
                    'billing_dep' => $value['billing_dep'],
                    'charge_name' => $value['charge_name'],
                    'money' => $value['money'],
                    'num' => $value['num'],
                ];
            }
        }

        $total_money = BillingIncome::where($where)->sum('total_money');
        foreach ($res_data as $key => &$value) {
            $value['ratio'] = bcmul(bcdiv($value['money'], $total_money, 4), 100) . '%';
        }
        unset($value);
        $res_data = array_values($res_data);
        $res_data = array_slice($res_data, 0, 20);

        // 合计
        $total_money_total = 0;
        $total_money_arr = array_column($res_data, 'money');
        foreach ($total_money_arr as $key => $value) {
            $total_money_total = bcadd($total_money_total, $value, 2);
        }

        $res_data[] = [
            'rank' => '合计',
            'billing_dep' => '',
            'charge_name' => '',
            'money' => $total_money_total,
            'num' => '',
            'ratio' => bcmul(bcdiv($total_money_total, $total_money, 4), 100) . '%'
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
