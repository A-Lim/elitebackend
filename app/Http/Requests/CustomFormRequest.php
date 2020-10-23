<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class CustomFormRequest extends FormRequest {
    protected $message = 'The given data was invalid.';

    public function setMessage($message) {
        $this->message = $message;
    }

    protected function failedValidation(Validator $validator) {
        // if request has accept application/json
        if (request()->wantsJson()) {
            $data = [
                'message' => $this->message,
                'errors' => $validator->errors()
            ];
            
            throw new HttpResponseException(response()->json($data, Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        throw (new ValidationException($validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
    }
}