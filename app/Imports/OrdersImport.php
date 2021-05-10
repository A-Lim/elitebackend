<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrdersImport implements ToArray, WithHeadingRow, WithChunkReading
{
    use Importable;

    public function array(array $rows) {
    }

    public function headingRow() {
        return 1;
    }

    public function chunkSize(): int {
        return 10;
    }
}
