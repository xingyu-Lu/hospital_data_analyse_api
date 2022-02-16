<?php

namespace App\Console\Commands;

use App\Models\BillingIncome;
use App\Models\ChargeProject;
use App\Models\OfficeContrast;
use App\Models\ReceiveIncome;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Incomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'incomes:incomes {year_month?},,{type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Incomes query';

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

        // $start_date = $end_date = '';

        // if ($type == 0) {
        //     $start_date = $year . '-' . $month . '-01';
        //     $end_date = $year . '-' . $month . '-15';
        // } else {
        //     $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")));
        //     $EndDate = date('j', strtotime("$BeginDate +1 month -1 day"));

        //     $start_date = $year . '-' . $month . '-16';
        //     $end_date = $year . '-' . $month . '-' . $EndDate;
        // }

        // $sql = 'WorkLoad_OrdDate, WorkLoad_Type, WorkLoad_PatDep_DR->CTLOC_Desc billing_dep, WorkLoad_ResDoc_DR->CTPCP_Code, WorkLoad_ResDoc_DR->CTPCP_Desc, WorkLoad_RecDep_DR->CTLOC_Desc receive_dep, WorkLoad_OEORE_Dr->OEORE_CTPCP_DR->CTPCP_Desc, WorkLoad_ResDep_DR->CTLOC_Desc patient_dep, WorkLoad_TarSC_Dr->TARSC_Desc charge_subclass, WorkLoad_TarItem_DR->TARI_Code, WorkLoad_TarItem_DR->TARI_Desc, sum(WorkLoad_Quantity) num, sum(WorkLoad_TotalPrice) money';

        // $query_data = DB::connection('sqlsrv')->table('DHC_WorkLoad')->selectRaw($sql)
        //     ->whereBetween('WorkLoad_OrdDate', [$start_date, $end_date])
        //     ->groupBy('WorkLoad_Type', 'WorkLoad_PatDep_DR->CTLOC_Desc', 'WorkLoad_ResDoc_DR', 'WorkLoad_ResDoc_DR->CTPCP_Desc', 'WorkLoad_RecDep_DR->CTLOC_Desc', 'WorkLoad_OEORE_Dr->OEORE_CTPCP_DR->CTPCP_Desc', 'WorkLoad_ResDep_DR->CTLOC_Desc', 'WorkLoad_TarSC_Dr->TARSC_Desc', 'WorkLoad_TarItem_DR->TARI_Code', 'WorkLoad_TarItem_DR->TARI_Desc')
        //     ->get();

        $file_path = 'app/1.xlsx';
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
        $highestColumn = 'M';
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
        $charge_subclass_all = ChargeProject::pluck('value', 'key');

        foreach ($query_data as $key => $value) {
            // $billing_dep = $value['billing_dep'];
            // $receive_dep = $value['receive_dep'];
            // $patient_dep = $value['patient_dep'];
            // $charge_subclass = $value['charge_subclass'];

            $billing_dep = $value['2'];
            $receive_dep = $value['5'];
            $patient_dep = $value['7'];
            $charge_subclass = $value['8'];

            if (strstr($patient_dep, '(手术治疗)')) {
                if ($billing_dep == 'SSS手术室') {
                    $billing_dep = $patient_dep;
                }
                if ($receive_dep == 'SSS手术室') {
                    $receive_dep = $patient_dep;
                }
            }

            // 科室名转换为最终科室名 收费子类转换为最终收费子类
            $billing_dep = $office_contrast_all[$billing_dep] ?? $billing_dep ?? '';
            $receive_dep = $office_contrast_all[$receive_dep] ?? $receive_dep ?? '';
            $patient_dep = $office_contrast_all[$patient_dep] ?? $patient_dep ?? '';
            $charge_subclass = $charge_subclass_all[$charge_subclass] ?? $charge_subclass ?? '';

            $data[] = [
                'date' => $date,
                'year' => $year,
                'month' => $month,
                'type' => $type,
                'billing_dep' => $billing_dep,
                'receive_dep' => $receive_dep,
                'patient_dep' => $patient_dep,
                'charge_subclass' => $charge_subclass,
                // 'num' => $value['num'],
                // 'money' => $value['money'],
                'num' => $value[11],
                'money' => $value[12],
            ];
        }

        unset($query_data, $office_contrast_all, $charge_subclass_all);

        $billing_data = $receive_data = [];

        // 数据汇总归集
        foreach ($data as $key => $value) {
            // 开单数据汇总归集
            $billing_key = $value['billing_dep'] . '-' . $value['patient_dep'] . '-' . $value['charge_subclass'];

            if (isset($billing_data[$billing_key])) {
                $billing_data[$billing_key]['num'] += $value['num'];
                $billing_data[$billing_key]['money'] += $value['money'];
            } else {
                $billing_data[$billing_key] = [
                    'date' => $value['date'],
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'type' => $value['type'],
                    'billing_dep' => $value['billing_dep'],
                    'patient_dep' => $value['patient_dep'],
                    'charge_subclass' => $value['charge_subclass'],
                    'num' => $value['num'],
                    'money' => $value['money'],
                ];
            }

            // 接单数据汇总归集
            $receive_key = $value['receive_dep'] . '-' . $value['patient_dep'] . '-' . $value['charge_subclass'];

            if (isset($receive_data[$receive_key])) {
                $receive_data[$receive_key]['num'] += $value['num'];
                $receive_data[$receive_key]['money'] += $value['money'];
            } else {
                $receive_data[$receive_key] = [
                    'date' => $value['date'],
                    'year' => $value['year'],
                    'month' => $value['month'],
                    'type' => $value['type'],
                    'receive_dep' => $value['receive_dep'],
                    'patient_dep' => $value['patient_dep'],
                    'charge_subclass' => $value['charge_subclass'],
                    'num' => $value['num'],
                    'money' => $value['money'],
                ];
            }
        }

        unset($data);

        // 开单数据入库
        BillingIncome::where('date', $date)->where('type', $type)->delete();
        foreach ($billing_data as $key => $value) {
            BillingIncome::create($value);
        }

        unset($billing_data);

        // 接单数据入库
        ReceiveIncome::where('date', $date)->where('type', $type)->delete();
        foreach ($receive_data as $key => $value) {
            ReceiveIncome::create($value);
        }

        unset($receive_data);

        $this->info("======end======");

        exit;
    }
}
