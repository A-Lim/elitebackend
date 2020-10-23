<?php

namespace App\Http\Requests\Workflow;

use App\Http\Requests\CustomFormRequest;

class NameExistsRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'name' => 'required|string',
            'workflowId' => 'nullable|integer|exists:workflows,id'
        ];
    }
}
