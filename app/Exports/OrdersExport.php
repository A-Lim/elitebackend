<?php

namespace App\Exports;

use App\Order;
use App\Workflow;
use Illuminate\Contracts\Support\Responsable;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class OrdersExport implements FromCollection, Responsable, 
    WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    use Exportable;

    private $fileName = 'orders.xlsx';
    private $writerType = Excel::XLSX;
    private $headers = [
        'Content-Type' => 'text/xlsx',
    ];

    private $workflow;
    private $orders;
    

    public function __construct(Workflow $workflow, $orders) {
        $this->workflow = $workflow;
        $this->orders = $orders;
    }

    public function collection() {
        return $this->orders;
    }

    public function headings(): array {
        $headings = [
            'ID', 
            'IWO NO',
            'NAME OF CLIENT',
            'WORK DESCRIPTION',
            'QTY',
            'DELIVERY DATE'
        ];

        foreach ($this->workflow->processes as $process) {
            array_push($headings, strtoupper($process->code));
        }

        $headings = array_merge($headings, [
            'PERSON IN CHARGE',
            'REMARK',
            'STATUS',
        ]);

        return $headings;
    }

    public function columnFormats(): array {
        return [
            'U' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function map($order): array {
        $data = [
            $order->id,
            $order->iwo,
            $order->company,
            $order->description,
            $order->quantity,
            ExcelDate::dateTimeToExcel($order->delivery_date)
        ];

        foreach ($this->workflow->processes as $process) {
            array_push($data, $order->{$process->code});
        }

        $data = array_merge($data, [
            $order->person_in_charge,
            $order->remark,
            $order->status,
        ]);

        return $data;
    }
}
