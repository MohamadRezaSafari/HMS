<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRequest extends FormRequest
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
            'doctorImg' => 'mimes:jpeg,jpg,png,gif|max:1024',
            'doctorName' => 'required|max:255',
            'doctorLastName' => 'required|max:255',
            'doctorTime' => 'required|max:255',
            'expertiseField' => 'required|max:255',
            'academicRank' => 'max:255',
            'doctorAddress' => 'max:400',
            'clinicName' => 'max:255',
            'clinicTell' => 'max:255',
            'clinicAddress' => 'max:600',
            'fellowship' => 'max:255',
            'graduateFrom' => 'max:255',
            'specialty' => 'max:255'
        ];
    }
}
