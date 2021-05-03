<?php

namespace App\Http\Requests\UserGroup;

use App\Http\Requests\CustomFormRequest;

class AddUsersRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'userIds' => 'required|array',
            'userIds.*' => 'integer'
        ];
    }
}
