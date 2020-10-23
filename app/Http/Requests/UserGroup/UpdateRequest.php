<?php

namespace App\Http\Requests\UserGroup;

use App\Http\Requests\CustomFormRequest;

class UpdateRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        $id = $this->route('userGroup')->id;
        return [
            'name' => 'required|string',
            // 'code' => 'required|string|unique:usergroups,code,{$id},id,deleted_at,NULL',
            'is_admin' => 'required|boolean'
        ];
    }
}
