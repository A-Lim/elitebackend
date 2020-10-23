<?php

namespace App\Http\Controllers\API\v1\Workflow;

use Illuminate\Http\Request;
use App\Workflow;
use App\Http\Controllers\ApiController;
use App\Repositories\Workflow\IWorkflowRepository;

use App\Http\Requests\Workflow\NameExistsRequest;
use App\Http\Requests\Workflow\CreateRequest;
use App\Http\Requests\Workflow\UpdateRequest;

class WorkflowController extends ApiController {

    private $workflowRepository;

    public function __construct(IWorkflowRepository $iWorkflowRepository) {
        $this->middleware('auth:api');
        $this->workflowRepository = $iWorkflowRepository;
    }

    public function exists(NameExistsRequest $request) {
        $exists = $this->workflowRepository->nameExists($request->name, $request->workflowId);
        return $this->responseWithData(200, $exists);
    }

    public function list(Request $request) {
        $this->authorize('viewAny', Workflow::class);
        $workflows = $this->workflowRepository->list($request->all(), true);
        return $this->responseWithData(200, $workflows);
    }

    public function workflow_menu(Request $request) {
        $data = $request->all();
        $data['status'] = 'equals:'.Workflow::STATUS_ACTIVE;
        $workflows = $this->workflowRepository->list($data, false);
        return $this->responseWithData(200, $workflows);
    }

    public function create(CreateRequest $request) {
        $this->authorize('create', Workflow::class);
        $workflow = $this->workflowRepository->create($request->all());
        return $this->responseWithData(201, $workflow);
    }

    public function details(Workflow $workflow) {
        $this->authorize('view', $workflow);
        $workflow = $this->workflowRepository->find($workflow->id);
        return $this->responseWithData(200, $workflow);
    }

    public function update(UpdateRequest $request, Workflow $workflow) {
        $this->authorize('update', $workflow);
        $this->workflowRepository->update($workflow, $request->all());
        return $this->responseWithMessage(200, 'Workflow updated.');
    }

    public function activate(Request $request, Workflow $workflow) {
        $this->authorize('update', $workflow);
        $data['status'] = Workflow::STATUS_ACTIVE;
        $this->workflowRepository->update_status($workflow, $data);
        return $this->responseWithMessage(200, 'Workflow activated.');
    }

    public function deactivate(Request $request, Workflow $workflow) {
        $this->authorize('update', $workflow);
        $data['status'] = Workflow::STATUS_INACTIVE;
        $this->workflowRepository->update_status($workflow, $data);
        return $this->responseWithMessage(200, 'Workflow deactivated.');
    }

    public function delete(Workflow $workflow) {
        $this->authorize('delete', $workflow);
        $this->workflowRepository->delete($workflow);
        return $this->responseWithMessage(200, 'Workflow deleted.');
    }
}
