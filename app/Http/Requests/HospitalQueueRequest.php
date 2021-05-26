<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HospitalQueueRequest extends FormRequest
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
            'hospital_id' => 'required|integer',
            'doctor_id' => 'required|integer',
            'nationalCode' => 'required|integer|max:50',
            'name' => 'required|string|max:250',
            'mobile' => 'required|integer|max:50',
            'ip' => 'required|max:100',
            'forDate' => 'required',
            'innings' => 'required|integer',
            'trackingCode' => 'required|integer|unique'
        ];
    }
}
