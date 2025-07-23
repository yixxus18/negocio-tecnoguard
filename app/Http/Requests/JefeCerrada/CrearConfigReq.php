<?php

namespace App\Http\Requests\JefeCerrada;

use Illuminate\Foundation\Http\FormRequest;

class CrearConfigReq extends FormRequest
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
            'nombre_configuracion' => 'required|string|min:5|max:127',
            'fecha_corte' => 'required|integer|min:1|max:31',
            'pay' => 'required|integer|min:100|max:9999',
            'tiempo_prorroga' => 'required|integer|min:1|max:3'
        ];
    }
}
