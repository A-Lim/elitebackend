<?php

namespace App\Imports;

use App\Order;
use App\Workflow;

use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class OrdersImport implements ToModel, WithHeadingRow, 
    WithValidation, SkipsOnFailure, 
    WithChunkReading, WithBatchInserts
{
    use Importable, SkipsFailures;

    private Workflow $workflow;

    public function __construct(Workflow $workflow) {
        $this->workflow = $workflow;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row) {
        $tableName = '_workflow_'.$this->workflow->id;
        $order = new Order();
        $order->setTable($tableName);

        $order_data = [
            'iwo' => $row['iwo_no'],
            'company' => $row['name_of_client'],
            'description' => $row['work_description'],
            'quantity' => $row['qty'],
            'remark' => $row['remark'],
            'delivery_date' => ExcelDate::excelToDateTimeObject($row['promised_delivery_date']),
            'status' => ORDER::STATUS_INPROGRESS,
            'created_by' => auth()->id()
        ];

        foreach ($this->workflow->processes as $process) {
            $order_data[$process->code] = $process->default;
        }
        
        $order->fill($order_data);

        return $order;
    }

    public function headingRow() {
        return 1;
    }

    public function rules(): array {
        return [
            'iwo_no' => 'required|unique:_workflow_'.$this->workflow->id.',iwo',
            'qty' => 'integer'
        ];
    }

    public function chunkSize(): int {
        return 100;
    }

    public function batchSize(): int {
        return 100;
    }
}
