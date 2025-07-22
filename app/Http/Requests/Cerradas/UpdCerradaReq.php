<?php

namespace App\Http\Requests\Cerradas;

use Illuminate\Foundation\Http\FormRequest;

class UpdCerradaReq extends FormRequest
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
            'nombre' => 'sometimes|string|unique:cerradas,group_name|max:127|min:5',
            'latitud'=> 'sometimes|numeric|between:-90,90',
            'longitud'=>'sometimes|numeric|between:-180,180',
        ];
    }
}
