<?php

namespace Database\Seeders;

use App\Models\ChargeProject;
use App\Models\FinancialSpend;
use App\Models\OfficeContrast;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ContrastsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        echo "=====begin=====" . PHP_EOL;

        $data = [];

        // 科室名称对应表
        $file_path = 'app/office_contrast.xlsx';
        $file_path = storage_path($file_path);
        //读Excel
        $reader = IOFactory::createReader('Xlsx');
        // 载入Excel表格
        $spreadsheet = $reader->load($file_path);
        $worksheet = $spreadsheet->getSheet(0);
        // 总行数
        $highestRow = $worksheet->getHighestRow();
        //总列数
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumn = 'C';
        $highestColumn = Coordinate::columnIndexFromString($highestColumn);

        // 清空数据
        DB::table('office_contrasts')->truncate();

        for ($row=2; $row <= $highestRow; $row++) { 
            for ($column=0; $column < $highestColumn; $column++) { 
                $value = $worksheet->getCellByColumnAndRow($column+1, $row)->getValue();
                $data[$row][] = $value;
            }
        }

        foreach ($data as $key => $value) {
            if ($value[2] == '非临床科室') {
                $type = 0;
            } else {
                $type = 1;
            }

            $insert_data = [
                'key' => $value[0],
                'value' => $value[1],
                'type' => $type,
            ];

            OfficeContrast::create($insert_data);
        }
        unset($data);


        // 收费项目子类对应表
        $file_path = 'app/charge_project.xlsx';
        $file_path = storage_path($file_path);
        //读Excel
        $reader = IOFactory::createReader('Xlsx');
        // 载入Excel表格
        $spreadsheet = $reader->load($file_path);
        $worksheet = $spreadsheet->getSheet(0);
        // 总行数
        $highestRow = $worksheet->getHighestRow();
        //总列数
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumn = 'B';
        $highestColumn = Coordinate::columnIndexFromString($highestColumn);        

        DB::table('charge_projects')->truncate();

        for ($row=2; $row <= $highestRow; $row++) { 
            for ($column=0; $column < $highestColumn; $column++) { 
                $value = $worksheet->getCellByColumnAndRow($column+1, $row)->getValue();
                $data[$row][] = $value;
            }
        }

        foreach ($data as $key => $value) {
            $insert_data = [
                'key' => $value[0],
                'value' => $value[1],
            ];

            ChargeProject::create($insert_data);
        }
        unset($data);        

        // 财务支出科目对应表
        $file_path = 'app/financial_spend.xlsx';
        $file_path = storage_path($file_path);
        //读Excel
        $reader = IOFactory::createReader('Xlsx');
        // 载入Excel表格
        $spreadsheet = $reader->load($file_path);
        $worksheet = $spreadsheet->getSheet(0);
        // 总行数
        $highestRow = $worksheet->getHighestRow();
        //总列数
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumn = 'B';
        $highestColumn = Coordinate::columnIndexFromString($highestColumn);

        DB::table('financial_spends')->truncate();

        for ($row=1; $row <= $highestRow; $row++) { 
            for ($column=0; $column < $highestColumn; $column++) { 
                $value = $worksheet->getCellByColumnAndRow($column+1, $row)->getValue();
                $data[$row][] = $value;
            }
        }

        foreach ($data as $key => $value) {
            $insert_data = [
                'key' => $value[0],
                'value' => $value[1],
            ];

            FinancialSpend::create($insert_data);
        }
        unset($data);

        echo "=====end=====" . PHP_EOL;        
        exit;
    }
}
