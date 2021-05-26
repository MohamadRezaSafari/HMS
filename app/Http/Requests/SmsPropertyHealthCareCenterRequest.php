<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SmsPropertyHealthCareCenterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sms_message' => 'required|max:100',
            'value' => 'max:50',
            'status' => 'required'
        ];
    }
}
