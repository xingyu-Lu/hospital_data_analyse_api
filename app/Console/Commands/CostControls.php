<?php

namespace App\Console\Commands;

use App\Models\BillingIncome;
use App\Models\CostControl;
use App\Models\OfficeContrast;
use App\Models\Pay;
use Illuminate\Console\Command;

class CostControls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'costControls:costControls {year_month?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CostControls query';

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

        $billing_data = $billing_tmp_data = $pay_data = $pay_tmp_data = $insert_data = [];

        // 获取临床科室
        $office = OfficeContrast::distinct()->where('type', 1)->pluck('value');

        foreach ($office as $key => $value) {
            // 获取开单收入
            $billing_tmp_data = BillingIncome::where('date', $date)->where('billing_dep', $value)->get()->toArray();
            foreach ($billing_tmp_data as $key_1 => $value_1) {
                $billing_select_key = $value_1['billing_dep'];
                if (isset($billing_data[$billing_select_key])) {
                    $billing_data[$value_1['billing_dep']]['pathology_income'] = bcadd($billing_data[$billing_select_key]['pathology_income'], $value_1['pathology_income'], 2);
                    $billing_data[$value_1['billing_dep']]['material_income'] = bcadd($billing_data[$billing_select_key]['material_income'], $value_1['material_income'], 2);
                    $billing_data[$value_1['billing_dep']]['ultrasound_income'] = bcadd($billing_data[$billing_select_key]['ultrasound_income'], $value_1['ultrasound_income'], 2);
                    $billing_data[$value_1['billing_dep']]['radiation_income'] = bcadd($billing_data[$billing_select_key]['radiation_income'], $value_1['radiation_income'], 2);
                    $billing_data[$value_1['billing_dep']]['check_income'] = bcadd($billing_data[$billing_select_key]['check_income'], $value_1['check_income'], 2);
                    $billing_data[$value_1['billing_dep']]['checkout_income'] = bcadd($billing_data[$billing_select_key]['checkout_income'], $value_1['checkout_income'], 2);
                    $billing_data[$value_1['billing_dep']]['surgery_income'] = bcadd($billing_data[$billing_select_key]['surgery_income'], $value_1['surgery_income'], 2);
                    $billing_data[$value_1['billing_dep']]['xiyao_income'] = bcadd($billing_data[$billing_select_key]['xiyao_income'], $value_1['xiyao_income'], 2);
                    $billing_data[$value_1['billing_dep']]['general_medical_income'] = bcadd($billing_data[$billing_select_key]['general_medical_income'], $value_1['general_medical_income'], 2);
                    $billing_data[$value_1['billing_dep']]['zhongyao_income'] = bcadd($billing_data[$billing_select_key]['zhongyao_income'], $value_1['zhongyao_income'], 2);
                    $billing_data[$value_1['billing_dep']]['total_money'] = bcadd($billing_data[$billing_select_key]['total_money'], $value_1['total_money'], 2);
                } else {
                    $billing_data[$billing_select_key] = [
                        'date' => $value_1['date'],
                        'dep' => $value_1['billing_dep'],
                        'pathology_income' => $value_1['pathology_income'],
                        'material_income' => $value_1['material_income'],
                        'ultrasound_income' => $value_1['ultrasound_income'],
                        'radiation_income' => $value_1['radiation_income'],
                        'check_income' => $value_1['check_income'],
                        'checkout_income' => $value_1['checkout_income'],
                        'surgery_income' => $value_1['surgery_income'],
                        'xiyao_income' => $value_1['xiyao_income'],
                        'general_medical_income' => $value_1['general_medical_income'],
                        'zhongyao_income' => $value_1['zhongyao_income'],
                        'total_money' => $value_1['total_money'],
                    ];
                }
            }
            unset($billing_tmp_data);
            $billing_data = array_values($billing_data);

            // 获取支出数据
            $pay_tmp_data = Pay::where('date', $date)->where('dep', $value)->get()->toArray();
            foreach ($pay_tmp_data as $key_2 => $value_2) {
                $pay_select_key = $value_2['dep'];
                if (isset($pay_data[$pay_select_key])) {
                    $pay_data[$pay_select_key]['personnel_pay'] = bcadd($pay_data[$pay_select_key]['personnel_pay'], $value_2['personnel_pay'], 2);
                    $pay_data[$pay_select_key]['fixed_asset_pay'] = bcadd($pay_data[$pay_select_key]['fixed_asset_pay'], $value_2['fixed_asset_pay'], 2);
                    $pay_data[$pay_select_key]['material_pay'] = bcadd($pay_data[$pay_select_key]['material_pay'], $value_2['material_pay'], 2);
                    $pay_data[$pay_select_key]['medicine_pay'] = bcadd($pay_data[$pay_select_key]['medicine_pay'], $value_2['medicine_pay'], 2);
                    $pay_data[$pay_select_key]['other_pay'] = bcadd($pay_data[$pay_select_key]['other_pay'], $value_2['other_pay'], 2);
                    $pay_data[$pay_select_key]['total_money'] = bcadd($pay_data[$pay_select_key]['total_money'], $value_2['total_money'], 2);
                } else {
                    $pay_data[$pay_select_key] = [
                        'date' => $value_2['date'],
                        'dep' => $value_2['dep'],
                        'personnel_pay' => $value_2['personnel_pay'],
                        'fixed_asset_pay' => $value_2['fixed_asset_pay'],
                        'material_pay' => $value_2['material_pay'],
                        'medicine_pay' => $value_2['medicine_pay'],
                        'other_pay' => $value_2['other_pay'],
                        'total_money' => $value_2['total_money'],
                    ];
                }
            }
            unset($pay_tmp_data);
            $pay_data = array_values($pay_data);

            // 准备写入成本控制数据
            $personnel_cost = $consumable_cost = $drug_cost = $fixed_asset_cost = $other_cost = 0;

            $billing_income = $billing_data[0]['total_money'] ?? 0;

            if ($billing_income > 0) {
                $personnel_pay = $pay_data[0]['personnel_pay'] ?? 0;
                $personnel_cost = bcdiv($personnel_pay, $billing_income, 2);

                $material_pay = $pay_data[0]['material_pay'] ?? 0;
                $consumable_cost = bcdiv($material_pay, $billing_income, 2);
                
                $medicine_pay = $pay_data[0]['medicine_pay'] ?? 0;
                $drug_cost = bcdiv($medicine_pay, $billing_income, 2);

                $fixed_asset_pay = $pay_data[0]['fixed_asset_pay'] ?? 0;
                $fixed_asset_cost = bcdiv($fixed_asset_pay, $billing_income, 2);

                $other_pay = $pay_data[0]['other_pay'] ?? 0;
                $other_cost = bcdiv($other_pay, $billing_income, 2);
            }

            $total_cost = $personnel_cost + $consumable_cost + $drug_cost + $fixed_asset_cost + $other_cost;
            
            $insert_data = [
                'date' => $date,
                'year' => $year,
                'month' => $month,
                'dep' => $value,
                'personnel_cost' => $personnel_cost,
                'consumable_cost' => $consumable_cost,
                'drug_cost' => $drug_cost,
                'fixed_asset_cost' => $fixed_asset_cost,
                'other_cost' => $other_cost
            ];

            CostControl::where('date', $date)->where('dep', $value)->delete();
            CostControl::create($insert_data);
        }
        
        $this->info("======end======");
    }
}
