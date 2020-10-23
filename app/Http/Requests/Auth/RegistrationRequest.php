<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\CustomFormRequest;

class RegistrationRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }

    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8'
        ];
    }

    public function filters() {
        return [
            'email' => 'trim|lowercase',
            'password' => 'trim'
        ];
    }
}
