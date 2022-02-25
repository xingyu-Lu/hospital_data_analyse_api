<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class IndicatorsController extends Controller
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
            ['dep', '=', $office_name]
        ];

        $indicator_data = Indicator::where($where)->orderBy('date', 'asc')->get()->toArray();
        foreach ($indicator_data as $key => &$value) {
            $value['date'] = date('Y-m', $value['date']);
        }
        unset($value);

        // 合计
        $billing_income_total = $direct_cost_total = $balance_total = $balance_rate_total = $drug_income_total = $consumable_income_total = $drug_pay_total = $consumable_pay_total = $drug_rate_total = $consumable_rate_total = $drug_profit_total = $consumable_profit_total = 0;

        $billing_income_arr = array_column($indicator_data, 'billing_income');
        foreach ($billing_income_arr as $key => $value) {
            $billing_income_total = bcadd($billing_income_total, $value, 2);
        }

        $direct_cost_arr = array_column($indicator_data, 'direct_cost');
        foreach ($direct_cost_arr as $key => $value) {
            $direct_cost_total = bcadd($direct_cost_total, $value, 2);
        }

        $balance_arr = array_column($indicator_data, 'balance');
        foreach ($balance_arr as $key => $value) {
            $balance_total = bcadd($balance_total, $value, 2);
        }

        if ($billing_income_total > 0) {
            $balance_rate_total = bcdiv($balance_total, $billing_income_total, 4)*100 . '%';
        }

        $drug_income_arr = array_column($indicator_data, 'drug_income');
        foreach ($drug_income_arr as $key => $value) {
            $drug_income_total = bcadd($drug_income_total, $value, 2);
        }

        $consumable_income_arr = array_column($indicator_data, 'consumable_income');
        foreach ($consumable_income_arr as $key => $value) {
            $consumable_income_total = bcadd($consumable_income_total, $value, 2);
        }

        $drug_pay_arr = array_column($indicator_data, 'drug_pay');
        foreach ($drug_pay_arr as $key => $value) {
            $drug_pay_total = bcadd($drug_pay_total, $value, 2);
        }

        $consumable_pay_arr = array_column($indicator_data, 'consumable_pay');
        foreach ($consumable_pay_arr as $key => $value) {
            $consumable_pay_total = bcadd($consumable_pay_total, $value, 2);
        }

        if ($billing_income_total > 0) {
            $drug_rate_total = bcdiv($drug_income_total, $billing_income_total, 4)*100 . '%';
        }

        if (($billing_income_total-$drug_income_total) > 0) {
            $consumable_rate_total = bcdiv($consumable_pay_total, ($billing_income_total-$drug_income_total), 4)*100 . '%';
        }

        $drug_profit_arr = array_column($indicator_data, 'drug_profit');
        foreach ($drug_profit_arr as $key => $value) {
            $drug_profit_total = bcadd($drug_profit_total, $value, 2);
        }

        $consumable_profit_arr = array_column($indicator_data, 'consumable_profit');
        foreach ($consumable_profit_arr as $key => $value) {
            $consumable_profit_total = bcadd($consumable_profit_total, $value, 2);
        }

        $indicator_data[] = [
            'date' => '合计',
            'year' => '',
            'month' => '',
            'dep' => $office_name,
            'billing_income' => $billing_income_total,
            'direct_cost' => $direct_cost_total,
            'balance' => $balance_total,
            'balance_rate' => $balance_rate_total,
            'drug_income' => $drug_income_total,
            'consumable_income' => $consumable_income_total,
            'drug_pay' => $drug_pay_total,
            'consumable_pay' => $consumable_pay_total,
            'drug_rate' => $drug_rate_total,
            'consumable_rate' => $consumable_rate_total,
            'drug_profit' => $drug_profit_total,
            'consumable_profit' => $consumable_profit_total,
        ];

        //实例化Excel
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
        $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle($office_name . '-重点指标');

        $head_arr = [
            '日期', '开单收入', '直接成本', '收支结余', '结余率', '药品收入', '耗材收入', '药品支出', '耗材支出', '药占比', '耗占比', '药品利润', '耗材利润'
        ];

        foreach ($head_arr as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($key+1, 1, $value);
        }

        foreach ($indicator_data as $key => $value) {
            $worksheet->setCellValueByColumnAndRow(1, $key+2, $value['date']);
            $worksheet->setCellValueByColumnAndRow(2, $key+2, $value['billing_income']);
            $worksheet->setCellValueByColumnAndRow(3, $key+2, $value['direct_cost']);
            $worksheet->setCellValueByColumnAndRow(4, $key+2, $value['balance']);
            $worksheet->setCellValueByColumnAndRow(5, $key+2, $value['balance_rate']);
            $worksheet->setCellValueByColumnAndRow(6, $key+2, $value['drug_income']);
            $worksheet->setCellValueByColumnAndRow(7, $key+2, $value['consumable_income']);
            $worksheet->setCellValueByColumnAndRow(8, $key+2, $value['drug_pay']);
            $worksheet->setCellValueByColumnAndRow(9, $key+2, $value['consumable_pay']);
            $worksheet->setCellValueByColumnAndRow(10, $key+2, $value['drug_rate']);
            $worksheet->setCellValueByColumnAndRow(11, $key+2, $value['consumable_rate']);
            $worksheet->setCellValueByColumnAndRow(12, $key+2, $value['drug_profit']);
            $worksheet->setCellValueByColumnAndRow(13, $key+2, $value['consumable_profit']);
        }

         //下载
        $filename = $office_name . '-重点指标' . '.xlsx';
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
            ['dep', '=', $office_name]
        ];

        $indicator_data = Indicator::where($where)->orderBy('date', 'asc')->get()->toArray();
        foreach ($indicator_data as $key => &$value) {
            $value['date'] = date('Y-m', $value['date']);
        }
        unset($value);

        // 合计
        $billing_income_total = $direct_cost_total = $balance_total = $balance_rate_total = $drug_income_total = $consumable_income_total = $drug_pay_total = $consumable_pay_total = $drug_rate_total = $consumable_rate_total = $drug_profit_total = $consumable_profit_total = 0;

        $billing_income_arr = array_column($indicator_data, 'billing_income');
        foreach ($billing_income_arr as $key => $value) {
            $billing_income_total = bcadd($billing_income_total, $value, 2);
        }

        $direct_cost_arr = array_column($indicator_data, 'direct_cost');
        foreach ($direct_cost_arr as $key => $value) {
            $direct_cost_total = bcadd($direct_cost_total, $value, 2);
        }

        $balance_arr = array_column($indicator_data, 'balance');
        foreach ($balance_arr as $key => $value) {
            $balance_total = bcadd($balance_total, $value, 2);
        }

        if ($billing_income_total > 0) {
            $balance_rate_total = bcdiv($balance_total, $billing_income_total, 4)*100 . '%';
        }

        $drug_income_arr = array_column($indicator_data, 'drug_income');
        foreach ($drug_income_arr as $key => $value) {
            $drug_income_total = bcadd($drug_income_total, $value, 2);
        }

        $consumable_income_arr = array_column($indicator_data, 'consumable_income');
        foreach ($consumable_income_arr as $key => $value) {
            $consumable_income_total = bcadd($consumable_income_total, $value, 2);
        }

        $drug_pay_arr = array_column($indicator_data, 'drug_pay');
        foreach ($drug_pay_arr as $key => $value) {
            $drug_pay_total = bcadd($drug_pay_total, $value, 2);
        }

        $consumable_pay_arr = array_column($indicator_data, 'consumable_pay');
        foreach ($consumable_pay_arr as $key => $value) {
            $consumable_pay_total = bcadd($consumable_pay_total, $value, 2);
        }

        if ($billing_income_total > 0) {
            $drug_rate_total = bcdiv($drug_income_total, $billing_income_total, 4)*100 . '%';
        }

        if (($billing_income_total-$drug_income_total) > 0) {
            $consumable_rate_total = bcdiv($consumable_pay_total, ($billing_income_total-$drug_income_total), 4)*100 . '%';
        }

        $drug_profit_arr = array_column($indicator_data, 'drug_profit');
        foreach ($drug_profit_arr as $key => $value) {
            $drug_profit_total = bcadd($drug_profit_total, $value, 2);
        }

        $consumable_profit_arr = array_column($indicator_data, 'consumable_profit');
        foreach ($consumable_profit_arr as $key => $value) {
            $consumable_profit_total = bcadd($consumable_profit_total, $value, 2);
        }

        $indicator_data[] = [
            'date' => '合计',
            'year' => '',
            'month' => '',
            'dep' => $office_name,
            'billing_income' => $billing_income_total,
            'direct_cost' => $direct_cost_total,
            'balance' => $balance_total,
            'balance_rate' => $balance_rate_total,
            'drug_income' => $drug_income_total,
            'consumable_income' => $consumable_income_total,
            'drug_pay' => $drug_pay_total,
            'consumable_pay' => $consumable_pay_total,
            'drug_rate' => $drug_rate_total,
            'consumable_rate' => $consumable_rate_total,
            'drug_profit' => $drug_profit_total,
            'consumable_profit' => $consumable_profit_total,
        ];

        return responder()->success($indicator_data);
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
