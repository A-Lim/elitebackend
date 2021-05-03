<?php

namespace App\Http\Requests\Order;

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
        $tableName = '_workflow_'.$workflow->id;
        $order_id = $this->route('id');
        return [
            'iwo' => 'required|string|unique:'.$tableName.',iwo,'.$order_id,
            'company' => 'required|string',
            'remark' => 'nullable|string',
            'delivery_date' => 'required|date_format:d/m/Y',
            'processes.*.name' => 'required|string',
            'processes.*.default' => 'required|string',
        ];
    }

    public function messages() {
        return [
            'processes.*.name.required' => 'Process name is required.',
            'processes.*.default.required' => 'Process default status is required.',
        ];
    }
}
