<?php

namespace App\Http\Requests\User;

use App\Http\Requests\CustomFormRequest;

class UploadAvatarRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'avatar' => 'required|image|max:2000',
        ];
    }

    public function messages() {
        return [
            'avatar.max' => 'Maximum limit allowed for :attribute is 1mb.'
        ];
    }
}
