<?php

namespace App\Console\Commands;

use App\Models\FinancialSpend;
use App\Models\OfficeContrast;
use App\Models\Pay;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Pays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pays:pays {year_month?},,{type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pays query';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("======begin======");

        ini_set("memory_limit", '-1');

        $arguments = $this->arguments();

        $date = $arguments['year_month'] ?? date("Y-m");
        
        $year = date('Y', strtotime($date));
        $month = date('n', strtotime($date));

        $date = strtotime($date);

        $day = date("j");

        $type_now = 0;

        if ($day <= 15) {
            $type_now = 0; // 上午
        } else {
            $type_now = 1; // 下午
        }

        $type = $arguments['type'] ?? $type_now;

         $file_path = 'app/2.xlsx';
        $file_path = storage_path($file_path);
        //读Excel
        $reader = IOFactory::createReader('Xlsx');
        // 载入Excel表格
        $spreadsheet = $reader->load($file_path);
        $worksheet = $spreadsheet->getSheet(0);
        // 总行数
        $highestRow = $worksheet->getHighestRow();
        // $highestRow = 3;
        //总列数
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumn = 'G';
        $highestColumn = Coordinate::columnIndexFromString($highestColumn);

        $query_data = [];

        for ($row=2; $row <= $highestRow; $row++) { 
            for ($column=0; $column < $highestColumn; $column++) { 
                $value = $worksheet->getCellByColumnAndRow($column+1, $row)->getValue();
                $query_data[$row][] = $value;
            }
        }

        $data = [];

        $office_contrast_all = OfficeContrast::pluck('value', 'key');
        $financial_spend_all = FinancialSpend::pluck('value', 'key');

        foreach ($query_data as $key => $value) {
            $money = $value[3];
            $dep = $value[4];
            $pay_subclass = $value[5];

            // 科室名转换为最终科室名 付费子类转换为最终付费子类
            $dep = $office_contrast_all[$dep] ?? $dep ?? '';
            $pay_subclass = $financial_spend_all[$pay_subclass] ?? $pay_subclass ?? '';

            $data[] = [
                'date' => $date,
                'year' => $year,
                'month' => $month,
                'type' => $type,
                'money' => $money,
                'dep' => $dep,
                'pay_subclass' => $pay_subclass,
            ];
        }

        unset($query_data);

        $pay_data = [];

        // 数据归集汇总
        foreach ($data as $key => $value) {
            $select_key = $value['dep'] . '-' . $value['pay_subclass'];

            if (isset($pay_data[$select_key])) {
                $pay_data[$select_key]['money'] += $value['money'];
            } else {
                $pay_data[$select_key] = [
                    'date' => $value['date'],
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'type' => $value['type'],
                    'money' => $value['money'],
                    'dep' => $value['dep'],
                    'pay_subclass' => $value['pay_subclass'],
                ];
            }
        }

        unset($data);

        $pay_insert_data = [];

        // 准备支出入库数据
        foreach ($pay_data as $key => $value) {
            $pay_insert_key = $value['dep'];

            if (isset($pay_insert_data[$pay_insert_key])) {
                switch ($value['pay_subclass']) {
                    case '人员经费':
                        $pay_insert_data[$pay_insert_key]['personnel_pay'] = $value['money'];
                        break;
                    case '固定资产折旧费':
                        $pay_insert_data[$pay_insert_key]['fixed_asset_pay'] = $value['money'];
                        break;
                    case '卫生材料费':
                        $pay_insert_data[$pay_insert_key]['material_pay'] = $value['money'];
                        break;
                    case '药品费':
                        $pay_insert_data[$pay_insert_key]['medicine_pay'] = $value['money'];
                        break;
                    case '其他费用':
                        $pay_insert_data[$pay_insert_key]['other_pay'] = $value['money'];
                        break;
                }
                $pay_insert_data[$pay_insert_key]['total_money'] += $value['money'];
            } else {
                $pay_insert_data[$pay_insert_key] = [
                    'date' => $value['date'],
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'type' => $value['type'],
                    'dep' => $value['dep'],
                    'total_money' => 0
                ];
                switch ($value['pay_subclass']) {
                    case '人员经费':
                        $pay_insert_data[$pay_insert_key]['personnel_pay'] = $value['money'];
                        break;
                    case '固定资产折旧费':
                        $pay_insert_data[$pay_insert_key]['fixed_asset_pay'] = $value['money'];
                        break;
                    case '卫生材料费':
                        $pay_insert_data[$pay_insert_key]['material_pay'] = $value['money'];
                        break;
                    case '药品费':
                        $pay_insert_data[$pay_insert_key]['medicine_pay'] = $value['money'];
                        break;
                    case '其他费用':
                        $pay_insert_data[$pay_insert_key]['other_pay'] = $value['money'];
                        break;
                }
                $pay_insert_data[$pay_insert_key]['total_money'] += $value['money'];
            }
        }

        unset($pay_data);

        // 支出数据入库
        Pay::where('date', $date)->where('type', $type)->delete();
        foreach ($pay_insert_data as $key => $value) {
            Pay::create($value);
        }

        unset($pay_insert_data);

        $this->info("======end======");

        exit;
    }
}
