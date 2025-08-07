<?php

namespace App\Http\Requests\JefeCerrada;

use Illuminate\Foundation\Http\FormRequest;

class CrearPagoReq extends FormRequest
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
            'membership_id' => 'required|integer|exists:memberships,id',
            'amount' => 'required|integer|min:1',
            'date_pay' => 'required|date',
            'ticket' => 'sometimes|file|max:5120|mimes:jpg,png',
            'status' => 'required|string',
        ];
    }
}
