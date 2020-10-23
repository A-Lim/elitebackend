<?php

namespace App\Http\Requests\Workflow;

use App\Http\Requests\CustomFormRequest;

use App\Workflow;

class UpdateRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        $workflow = $this->route('workflow');
        return [
            'name' => 'required|unique:workflows,name,'.$workflow->id,
            'status' => 'required|in:'.implode(',', Workflow::STATUSES),
            'processes.*.name' => 'required|string',
            'processes.*.default' => 'required|string',
            'processes.*.seq' => 'required|integer',
        ];
    }

    public function messages() {
        return [
            'processes.*.name.required' => 'Process name is required.',
            'processes.*.default.required' => 'Process default status is required.',
            'processes.*.seq.required' => 'Process seq is required.',
            'processes.*.seq.integer' => 'Process seq must be an integer.',
        ];
    }
}
