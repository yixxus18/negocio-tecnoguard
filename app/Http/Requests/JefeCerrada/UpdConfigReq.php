<?php

namespace App\Http\Requests\JefeCerrada;

use Illuminate\Foundation\Http\FormRequest;

class UpdConfigReq extends FormRequest
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
            'nombre_configuracion' => 'sometimes|string|min:5|max:127',
            'fecha_corte' => 'sometimes|integer|min:1|max:31',
            'pay' => 'sometimes|integer|min:100|max:9999',
            'tiempo_prorroga' => 'sometimes|integer|min:1|max:3'
        ];
    }
}
