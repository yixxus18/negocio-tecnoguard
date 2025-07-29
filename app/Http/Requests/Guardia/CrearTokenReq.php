<?php

namespace App\Http\Requests\Guardia;

use Illuminate\Foundation\Http\FormRequest;

class CrearTokenReq extends FormRequest
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
            'nombre' => 'required|string|max:255|min:5',
            'tipo_token' => 'sometimes|in:servicio,visita,residente sin acceso',
            'puerta' => 'required|in:peatonal,automovil',
        ];
    }
}
