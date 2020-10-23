<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\CustomFormRequest;

class LoginRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
        // set message
        $this->setMessage('Invalid login credentials.');
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            'rememberMe' => 'nullable|boolean'
        ];
    }
}
