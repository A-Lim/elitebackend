<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\CustomFormRequest;

use App\Workflow;

class UpdateProcessRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'process_id' => 'required|integer|exists:processes,id',
            'status' => 'required|string'
        ];
    }
}
