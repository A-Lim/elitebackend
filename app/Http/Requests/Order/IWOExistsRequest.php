<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\CustomFormRequest;

class IWOExistsRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        $workflow = $this->route('workflow');
        $tableName = '_workflow_'.$workflow->id;
        return [
            'iwo' => 'required|string',
            'orderId' => 'nullable|integer|exists:'.$tableName.',id'
        ];
    }
}
