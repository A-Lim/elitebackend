<?php

namespace App\Http\Controllers\API\v1\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Repositories\Order\IOrderRepository;

use App\Workflow;
use App\Http\Requests\Order\IWOExistsRequest;
use App\Http\Requests\Order\CreateRequest;
use App\Http\Requests\Order\UpdateRequest;
use App\Http\Requests\Order\UpdateProcessRequest;

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
        // $this->authorize('viewAny', Workflow::class);
        $orders = $this->orderRepository->list($workflow, $request->all(), true, true);
        return $this->responseWithData(200, $orders);
    }

    public function create(CreateRequest $request, Workflow $workflow) {
        // $this->authorize('create', Workflow::class);
        $this->orderRepository->create($workflow, $request->all(), $request->files->all());
        return $this->responseWithMessage(201, 'Order created.');
    }

    public function details(Workflow $workflow, $id) {
        // $this->authorize('view', $workflow);
        $order = $this->orderRepository->find($workflow, $id);
        if ($order == null) 
            throw new ModelNotFoundException();
        
        return $this->responseWithData(200, $order);
    }

    public function updateProcess(UpdateProcessRequest $request, Workflow $workflow, $id) {
        $this->orderRepository->updateProcess($workflow, $id, $request->all());
        return $this->responseWithMessage(200, 'Order Process updated.');
    }

    public function delete(Workflow $workflow, $id) {
        $this->orderRepository->delete($workflow, $id);
        return $this->responseWithMessage(200, 'Order Deleted.');
    }

    // public function update(UpdateRequest $request, Workflow $workflow) {
    //     $this->authorize('update', $workflow);
    //     $this->workflowRepository->update($workflow, $request->all());
    //     return $this->responseWithMessage(200, 'Workflow updated.');
    // }

    // public function activate(Request $request, Workflow $workflow) {
    //     $this->authorize('update', $workflow);
    //     $data['status'] = Workflow::STATUS_ACTIVE;
    //     $this->workflowRepository->update_status($workflow, $data);
    //     return $this->responseWithMessage(200, 'Workflow activated.');
    // }

    // public function deactivate(Request $request, Workflow $workflow) {
    //     $this->authorize('update', $workflow);
    //     $data['status'] = Workflow::STATUS_INACTIVE;
    //     $this->workflowRepository->update_status($workflow, $data);
    //     return $this->responseWithMessage(200, 'Workflow deactivated.');
    // }

    // public function delete(Workflow $workflow) {
    //     $this->authorize('delete', $workflow);
    //     $this->workflowRepository->delete($workflow);
    //     return $this->responseWithMessage(200, 'Workflow deleted.');
    // }
}
