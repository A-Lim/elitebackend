<?php

namespace App\Http\Controllers\API\v1\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

use App\Workflow;
use App\Order;
use App\Imports\OrdersImport;
use App\Exports\OrdersExport;
use App\Http\Requests\Order\IWOExistsRequest;
use App\Http\Requests\Order\CreateRequest;
use App\Http\Requests\Order\UpdateRequest;
use App\Http\Requests\Order\ImportRequest;
use App\Http\Requests\Order\UpdateStatusRequest;
use App\Http\Requests\Order\UpdateProcessRequest;

use App\Http\Controllers\ApiController;
use App\Repositories\Order\IOrderRepository;

class OrderController extends ApiController {

    private $orderRepository;

    public function __construct(IOrderRepository $iOrderRepository) {
        $this->middleware('auth:api');
        $this->orderRepository = $iOrderRepository;
    }

    public function exists(IWOExistsRequest $request, Workflow $workflow) {
        $exists = $this->orderRepository->iwoExists($workflow, $request->iwo, $request->orderId);
        return $this->responseWithData(200, $exists);
    }

    public function list(Request $request, Workflow $workflow) {
        $this->authorize('viewAny', Order::class);
        $orders = $this->orderRepository->list($workflow, $request->all(), true, true);
        return $this->responseWithData(200, $orders);
    }

    public function listInProgress(Request $request, Workflow $workflow) {
        $this->authorize('viewAny', Order::class);
        $data = $request->all();
        $data['status'] = 'equals:'.Order::STATUS_INPROGRESS;
        $orders = $this->orderRepository->list($workflow, $data, true, true);
        return $this->responseWithData(200, $orders);
    }

    public function listCompleted(Request $request, Workflow $workflow) {
        $this->authorize('viewAny', Order::class);
        $data = $request->all();
        $data['status'] = 'equals:'.Order::STATUS_COMPLETED;
        $orders = $this->orderRepository->list($workflow, $data, true, true);
        return $this->responseWithData(200, $orders);
    }

    public function create(CreateRequest $request, Workflow $workflow) {
        $this->authorize('create', Order::class);
        $this->orderRepository->create($workflow, $request->all(), $request->files->all());
        return $this->responseWithMessage(201, 'Order created.');
    }

    public function update(UpdateRequest $request, Workflow $workflow, $id) {
        $order = $this->orderRepository->find($workflow, $id);
        if ($order == null) 
            throw new ModelNotFoundException();
            
        $this->authorize('update', $order);
        $this->orderRepository->update($workflow, $order, $request->all(), $request->files->all());
        return $this->responseWithMessage(200, 'Order updated.');
    }

    public function updateStatus(UpdateStatusRequest $request, Workflow $workflow, $id) {
        $order = $this->orderRepository->find($workflow, $id);
        if ($order == null) 
            throw new ModelNotFoundException();
            
        $this->authorize('update', $order);
        $this->orderRepository->updateStatus($workflow, $order, $request->status);
        return $this->responseWithMessage(200, 'Order updated.');
    }

    public function details(Workflow $workflow, $id) {
        $order = $this->orderRepository->find($workflow, $id, true, true);
        if ($order == null) 
            throw new ModelNotFoundException();
        
        $this->authorize('view', $order);
        return $this->responseWithData(200, $order);
    }

    public function updateProcess(UpdateProcessRequest $request, Workflow $workflow, $id) {
        $order = $this->orderRepository->find($workflow, $id);
        if ($order == null) 
            throw new ModelNotFoundException();

        $this->orderRepository->updateProcess($workflow, $order, $request->all());
        return $this->responseWithMessage(200, 'Order Process updated.');
    }

    public function delete(Workflow $workflow, $id) {
        $order = $this->orderRepository->find($workflow, $id);
        if ($order == null) 
            throw new ModelNotFoundException();
        
        $this->authorize('delete', $order);
        $this->orderRepository->delete($workflow, $id);
        return $this->responseWithMessage(200, 'Order Deleted.');
    }

    public function import(ImportRequest $request, Workflow $workflow) {
        $this->authorize('create', Order::class);

        $import = new OrdersImport();
        $rows = $import->toArray(request()->file('file'))[0];

        // validate
        $this->validateExcelRows($workflow, $rows);
        $count = $this->orderRepository->bulkCreate($workflow, $rows);

        return $this->responseWithMessage(200, $count. ' orders imported.');
    }

    public function export(Request $request, Workflow $workflow) {
        // $this->authorize('export', Order::class);
        $data['status'] = 'equals:'.Order::STATUS_COMPLETED;
        $orders = $this->orderRepository->list($workflow, $data);

        return new OrdersExport($workflow, $orders);
    }

    private function validateExcelRows(Workflow $workflow, $data) {
        // convert dates 
        foreach ($data as $index => $row) {
            $data[$index]['promised_delivery_date'] = ExcelDate::excelToDateTimeObject($row['promised_delivery_date']);
        }

        $rules = [
            '*.iwo_no' => 'required|distinct|unique:_workflow_'.$workflow->id.',iwo',
            '*.qty' => 'required|integer',
            '*.promised_delivery_date' => 'required'
        ];

        $messages = [];

        foreach ($data as $index => $row) {
            $messages[$index.'.iwo_no.required'] = 'Row '.((int)$index + 2).' IWO NO. is empty.';
            $messages[$index.'.iwo_no.unique'] = 'Row '.((int)$index + 2).' IWO NO. already exist.';
            $messages[$index.'.iwo_no.distinct'] = 'Row '.((int)$index + 2).' IWO NO. has duplicates.';
            $messages[$index.'.qty.required'] = 'Row '.((int)$index + 2).' QTY is empty.';
            $messages[$index.'.qty.integer'] = 'Row '.((int)$index + 2).' QTY is not a number.';
            $messages[$index.'.promised_delivery_date.required'] = 'Row '.((int)$index + 2).' Promised Delivery Date is empty.';
        }

        Validator::make($data, $rules, $messages)->validate();
    }
}
