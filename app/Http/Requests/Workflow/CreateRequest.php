<?php

namespace App\Http\Requests\Workflow;

use App\Http\Requests\CustomFormRequest;

use App\Workflow;

class CreateRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'name' => 'required|unique:workflows,name,NULL,id,deleted_at,NULL',
            'status' => 'required|in:'.implode(',', Workflow::STATUSES),
            'processes.*.name' => 'required|string|max:100|regex:/^[\/a-z\d\-_\s]+$/i',
            'processes.*.default' => 'required|string',
            'processes.*.seq' => 'required|integer',
            'processes.*.pinned' => 'required',
            'processes.*.width' => 'required|integer',
        ];
    }

    public function messages() {
        return [
            'processes.*.name.required' => 'Process name is required.',
            'processes.*.name.regex' => 'Process name does not allow special characters.',
            'processes.*.default.required' => 'Process default status is required.',
            'processes.*.pinned.required' => 'Process pinned is required.',
            'processes.*.seq.required' => 'Process seq is required.',
            'processes.*.seq.integer' => 'Process seq must be an integer.',
            'processes.*.width.required' => 'Process width is required.',
            'processes.*.width.integer' => 'Process width must be an integer.',
        ];
    }
}
