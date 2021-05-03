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
            'person_in_charge' => $data['person_in_charge']
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
    public function bulkCreate(Workflow $workflow, Collection $data) {
        $tableName = '_workflow_'.$workflow->id;
        // remove duplicates
        $data = $data->unique('iwo_no');

        $importIWOs = $data->pluck('iwo_no');

        $duplicates = Order::fromTable($tableName)->whereIn('iwo', $importIWOs)->get();

        if (count($duplicates) > 0) {
            $duplicateIWOs = $duplicates->pluck('iwo')->toArray();
            // remove duplicates 
            $data = $data->filter(function($row) {
                return !in_array($row['iwo'], $duplicateIWOs);
            });
        }
        dd(count($duplicates));
        // if ($duplicates->cou)
        // dd($duplicates);
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
            'person_in_charge' => $data['person_in_charge']
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
        Order::fromTable($tableName)
            ->where('id', $order->id)
            ->update(['status' => $status]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Workflow $workflow, $order_id) {
        $tableName = '_workflow_'.$workflow->id;
        Order::fromTable($tableName)
            ->where('id', $order_id)
            ->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function updateProcess(Workflow $workflow, Order $order, $data) {
        $tableName = '_workflow_'.$workflow->id;

        DB::beginTransaction();

        $tableName = '_workflow_'.$workflow->id;

        // $processes = Process::where('workflow_id', $workflow->id)->get();
        // dd($processes);


        $process = Process::find($data['process_id']);
        $process_column = $process->code;
        $from_status = $order->{$process_column};

        Order::fromTable($tableName)
            ->where('id', $order->id)
            ->update([$process_column => $data['status']]);
        $order->{$process_column} = $data['status'];
        $order->save();

        // $success_count = 0;
        // foreach ($processes as $process) {
        //     if ($order->{$process->code} == 'Completed')
        //         $success_count++;
        // }

        // update order if all processes completed
        // if (count($processes) == $success_count) {
        //     Order::fromTable($tableName)
        //         ->where('id', $order->id)
        //         ->update(['status' => Order::STATUS_COMPLETED]);
        // }
        
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
            ->agGridQuery($data)
            ->orderBy('iwo', 'DESC');
    }

    private function deleteFiles($order_files) {
        $filePaths = $order_files->pluck('path')->toArray();
        
        foreach ($filePaths as $filePath) {
            $fullPath = public_path($filePath);
            unlink($fullPath);
        }
    }

    private function saveFiles($workflow_id, $order_id, $files) {
        if ($files == null || !isset($files['uploadFiles'])) 
            return [];

        $filesData = [];
        
        $saveDirectory = 'public/iwo/'.$workflow_id.'/'.$order_id.'/';
        foreach ($files['uploadFiles'] as $file) {
            $fileName = $file->getClientOriginalName();
            Storage::putFileAs($saveDirectory, $file, $fileName);
            $fileData = [
                'workflow_id' => $workflow_id,
                'name' => $fileName,
                'path' => Storage::url($saveDirectory.$fileName),
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