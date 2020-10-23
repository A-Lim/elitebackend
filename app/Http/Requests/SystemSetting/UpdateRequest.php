<?php

namespace App\Http\Requests\SystemSetting;

use App\SystemSetting;
use App\Http\Requests\CustomFormRequest;

class UpdateRequest extends CustomFormRequest {

    public function __construct() {
        parent::__construct();
    }
    
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
          'verification_type' => 'required|in:'.implode(',', SystemSetting::VTYPES),
          'allow_public_registration' => 'boolean'
        ];
    }
}
