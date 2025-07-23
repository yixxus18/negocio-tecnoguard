<?php

namespace App\Http\Requests\JefeCerrada;

use Illuminate\Foundation\Http\FormRequest;

class AsignarGuardiaReq extends FormRequest
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
            'guardia_id' =>'required|number|exist:users,id',
        ];
    }
}
