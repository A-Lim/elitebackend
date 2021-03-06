<?php
namespace App\Repositories\Order;

use DB;
use App\Order;
use App\OrderLog;
use App\Workflow;
use App\Process;
use App\OrderFile;

use File;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class OrderRepository implements IOrderRepository {
    const STATUS_INPROGRESS = 'in progress';
    const STATUS_COMPLETED = 'completed';

    /**
     * {@inheritdoc}
     */
    public function iwoExists(Workflow $workflow, $iwo, $orderId = null) {
        $tableName = '_workflow_'.$workflow->id;
        $conditions = [['iwo', '=', $iwo]];
        if ($orderId != null)
            array_push($conditions, ['id', '<>', $orderId]);

        return Order::fromTable($tableName)
            ->where($conditions)->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function list(Workflow $workflow, $data, $withFiles = false, $paginate = false) {
        $query = $this->listQuery($workflow, $data);

        if ($paginate) {
            $limit = isset($data['limit']) ? $data['limit'] : 10;
            $paginated = $query->paginate($limit);
            return $this->withFiles($workflow, $paginated);
        }

        $orders = $query->get();
        return $this->withFiles($workflow, $orders);
    }

    /**
     * {@inheritdoc}
     */
    public function find(Workflow $workflow, $order_id, $withFiles = false, $withOrderLogs = false) {
        $tableName = '_workflow_'.$workflow->id;
        $order = Order::fromTable($tableName)
            ->where('id', $order_id)
            ->first();

        if ($order == null)
            return null;

        if ($withFiles) {
            $files = OrderFile::where('workflow_id', $workflow->id)
                ->where('order_id', $order_id)
                ->get();

            $order->setAttribute('files', $files);
        }
        
        if ($withOrderLogs) {
            $orderLogs = OrderLog::with(['process', 'user'])
                ->where('workflow_id', $workflow->id)
                ->where('order_id', $order_id)
                ->orderBy('created_at', 'DESC')
                ->get();

            $order->setAttribute('orderLogs', $orderLogs);
        }
        
        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Workflow $workflow, $data, $files = null) {
        $tableName = '_workflow_'.$workflow->id;

        $insert_data = [
            'iwo' => $data['iwo'],
            'company' => $data['company'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'delivery_date' => Carbon::createFromFormat(env('DATE_FORMAT'), $data['delivery_date']),
            'remark' => $data['remark'] ?? null,
            'status' => $data['status'],
            'person_in_charge' => isset($data['person_in_charge']) ? $data['person_in_charge'] : null
        ];

        foreach ($workflow->processes as $process) {
            $insert_data[$process->code] = $data[$process->code] ?? $process->default;
        }
        
        $insert_data['created_by'] = auth()->id();
        $insert_data['created_at'] = Carbon::now();

        DB::beginTransaction();
        // insert order
        $order = Order::fromTable($tableName)->create($insert_data);
        // save file to disk
        $filesData = $this->saveFiles($workflow->id, $order->id, $files);
        // save file data to db
        $order->files()->createMany($filesData);
        DB::commit();
    }

    /**
     * {@inheritdoc}
     */
    public function bulkCreate(Workflow $workflow, array $data) {
        $tableName = '_workflow_'.$workflow->id;
        $count = 0;

        $orders = [];
        $processes = [];

        foreach ($workflow->processes as $process) {
            $processes[$process->code] = $process->default;
        }

        foreach ($data as $row) {
            $order = [
                'iwo' => $row['iwo_no'],
                'company' => $row['name_of_client'],
                'description' => $row['work_description'],
                'quantity' => $row['qty'],
                'remark' => $row['remark'],
                'delivery_date' => ExcelDate::excelToDateTimeObject($row['promised_delivery_date']),
                'status' => ORDER::STATUS_INPROGRESS,
                'created_by' => auth()->id(),
                'created_at' => Carbon::now()
            ];

            $order = array_merge($order, $processes);
            array_push($orders, $order);
        }

        DB::beginTransaction();
        DB::table($tableName)->insert($orders);
        DB::commit();

        return count($orders);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Workflow $workflow, Order $order, $data, $files = null) {
        $tableName = '_workflow_'.$workflow->id;

        $data['files'] = $data['files'] ?? [];

        $update_data = [
            'iwo' => $data['iwo'],
            'company' => $data['company'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'delivery_date' => Carbon::createFromFormat(env('DATE_FORMAT'), $data['delivery_date']),
            'remark' => $data['remark'] ?? null,
            'status' => $data['status'] ?? self::STATUS_INPROGRESS,
            'person_in_charge' => isset($data['person_in_charge']) ? $data['person_in_charge'] : null
        ];

        $update_data['updated_by'] = auth()->id();
        $update_data['updated_at'] = Carbon::now();

        foreach ($workflow->processes as $process) {
            $update_data[$process->code] = $data[$process->code] ?? $process->default;
        }

        $update_data['created_by'] = auth()->id();
        $update_data['created_at'] = Carbon::now();

        // retrieve files that is not listed 
        $toBeDeleted = OrderFile::where('workflow_id', $workflow->id)
            ->where('order_id', $order->id)
            ->whereNotIn('id', $data['files'])
            ->toBase()
            ->get();
        
        DB::beginTransaction();
        // update order
        Order::fromTable($tableName)
            ->where('id', $order->id)
            ->update($update_data);
        // save file to disk
        $filesData = $this->saveFiles($workflow->id, $order->id, $files);
        // save file data to db
        $order->files()->createMany($filesData);
        // delete disk files
        $this->deleteFiles($toBeDeleted);
        // delete db files 
        $order->files()
            ->whereIn('id', $toBeDeleted->pluck('id')->toArray())
            ->delete();
        DB::commit();
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatus(Workflow $workflow, Order $order, $status) {
        $tableName = '_workflow_'.$workflow->id;

        $data = ['status' => $status];
        $from_status = $order->status;

        if ($status === Order::STATUS_COMPLETED)
            $data['completed_at'] = Carbon::now();

        Order::fromTable($tableName)
            ->where('id', $order->id)
            ->update($data);

        OrderLog::create([
            'workflow_id' => $workflow->id,
            'order_id' => $order->id,
            'from_status' => $from_status,
            'to_status' => $status,
            'created_by' => auth()->id(),
            'created_at' => Carbon::now()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Workflow $workflow, $order_id) {
        $tableName = '_workflow_'.$workflow->id;
        $order = Order::fromTable($tableName)
            ->where('id', $order_id)
            ->first();

        $this->deleteFiles($order->files);
        $order->files()->delete();
        $order->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function updateProcess(Workflow $workflow, Order $order, $data) {
        $tableName = '_workflow_'.$workflow->id;

        DB::beginTransaction();

        $tableName = '_workflow_'.$workflow->id;

        $process = Process::find($data['process_id']);
        $process_column = $process->code;
        $from_status = $order->{$process_column};

        Order::fromTable($tableName)
            ->where('id', $order->id)
            ->update([$process_column => $data['status']]);
        $order->{$process_column} = $data['status'];
        $order->save();

        OrderLog::create([
            'workflow_id' => $workflow->id,
            'order_id' => $order->id,
            'process_id' => $process->id,
            'from_status' => $from_status,
            'to_status' => $data['status'],
            'created_by' => auth()->id(),
            'created_at' => Carbon::now()
        ]);

        DB::commit();
    }

    private function listQuery(Workflow $workflow, $data) {
        $tableName = '_workflow_'.$workflow->id;
        return Order::fromTable($tableName)
            ->agGridQuery($data);
    }

    private function deleteFiles($order_files) {
        $filePaths = [];
        foreach ($order_files as $file) {
            array_push($filePaths, $file->getRawOriginal('path'));
        }

        Storage::disk('public')->delete($filePaths);
    }

    private function saveFiles($workflow_id, $order_id, $files) {
        if ($files == null || !isset($files['uploadFiles'])) 
            return [];

        $filesData = [];
        
        $saveDirectory = 'iwo/'.$workflow_id.'/'.$order_id;
        foreach ($files['uploadFiles'] as $file) {
            $fileName = $file->getClientOriginalName();
            $path = Storage::disk('public')->putFileAs($saveDirectory, $file, $fileName);
            $fileData = [
                'workflow_id' => $workflow_id,
                'name' => $fileName,
                'path' => $path,
                'type' => $file->getClientOriginalExtension(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => Carbon::now()
            ];
            array_push($filesData, $fileData);
        }

        return $filesData;
    }

    private function withFiles(Workflow $workflow, $orderData) {
        $orders = $orderData;
        if ($orderData instanceof LengthAwarePaginator) {
            $orders = collect($orderData->items());
        }
        
        $order_ids = $orders->pluck('id')->toArray();
        $orderFiles = OrderFile::where('workflow_id', $workflow->id)
            ->whereIn('order_id', $order_ids)
            ->get();

        foreach ($orders as $order) {
            $order_files = $orderFiles->filter(function($file) use ($order) {
                return $file->order_id == $order->id;
            })->values()->toArray();
            $order->setAttribute('files', $order_files);
        }

        if ($orderData instanceof LengthAwarePaginator) {
            $orderData->files = $orders->toArray();
            return $orderData;
        }

        return $orders;
    }
}