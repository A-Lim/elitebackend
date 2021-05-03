<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\CustomFormRequest;

use App\Order;

class UpdateStatusRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'status' => 'required|string|in:'.implode(',', Order::STATUSES)
        ];
    }

    public function messages() {
        return [
        ];
    }
}
