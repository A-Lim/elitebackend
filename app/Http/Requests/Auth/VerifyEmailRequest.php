<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\CustomFormRequest;

class VerifyEmailRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
        $this->setMessage('Invalid url.');
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'id' => 'required',
            'hash' => 'required',
            'email' => 'required|email|exists:users,email',
        ];
    }
}
