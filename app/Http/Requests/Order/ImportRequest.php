<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\CustomFormRequest;

use App\Workflow;

class ImportRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'file' => 'required|mimes:xlsx'
        ];
    }

    public function messages() {
        return [
        ];
    }
}
