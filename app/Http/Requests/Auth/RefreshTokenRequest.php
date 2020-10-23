<?php
namespace App\Http\Requests\Auth;

use App\Http\Requests\CustomFormRequest;

class RefreshTokenRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'refreshToken' => 'required|string'
        ];
    }
}
