<?php

namespace App\Http\Requests\Cerradas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StrCerradaReq extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|unique:cerradas,group_name|max:127|min:5',
            'description' => 'sometimes|string|max:255|min:10',
            'jefe_cerrada_id' => 'sometimes|nullable|integer|exists:users,id',
            'guard_id' => 'sometimes|nullable|integer|exists:users,id',
            'configuration_pay_date' => 'sometimes|integer|exist:configuation_pay_date,id',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ];
    }

}
