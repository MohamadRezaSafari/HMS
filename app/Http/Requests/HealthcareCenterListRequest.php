<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HealthcareCenterListRequest extends FormRequest
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
            'healthcareCenterListName' => 'required|max:255',
            'tell' => 'required|max:255',
            'address' => 'required|max:255',
            'expertise' => 'required|max:800',
            'description' => 'required',
            'map' => 'max:255',
            'time' => 'max:250',
            'img' => 'max:2000|mimes:jpeg,jpg,png,gif'

        ];
    }
}
