<?php

namespace App\Http\Controllers\API\v1\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Repositories\Order\IOrderRepository;

use App\Workflow;
use App\Imports\OrdersImport;
use App\Http\Requests\Order\IWOExistsRequest;
use App\Http\Requests\Order\CreateRequest;
use App\Http\Requests\Order\UpdateRequest;
use App\Http\Requests\Order\ImportRequest;
use App\Http\Requests\Order\UpdateStatusRequest;
use App\Http\Requests\Order\UpdateProcessRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $import = new OrdersImport($workflow);
        $rows = $import->toCollection(request()->file('file'))->first();
        // dd($rows->toArray());

        // validate
        $this->_validateExcelRows($workflow, $rows->toArray());
        // Validator::make($rows->toArray(), [
        //     '*.iwo_no' => 'required|unique:_workflow_'.$workflow->id.',iwo',
        //     '*.qty' => 'required|integer',
        // ])->validate();

        // $this->orderRepository->bulkCreate($workflow, $rows->first());
        // dd($rows);
        // Validator::make($rows, [
        //     '*.0' => 'required|unique:_workflow_'.$workflow->id.',iwo',
        //     '*.5' => 'required|integer',
        // ])->validate();
        // dd($validate);
    }

    private function _validateExcelRows(Workflow $workflow, $data) {
        $rules = [
            '*.iwo_no' => 'required|unique:_workflow_'.$workflow->id.',iwo',
            '*.qty' => 'required|integer',
        ];

        $messages = [
            '*.iwo_no' => 'The :index :attribute field is required 123.',
        ];
        // dd(get_class(Validator));
        Validator::make($data, $rules, $messages)->validate();
    }
}
