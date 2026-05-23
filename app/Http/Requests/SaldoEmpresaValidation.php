<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaldoEmpresaValidation extends FormRequest
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
            'idEmpresa' => ['required', 'numeric', 'integer'],
            'montoUSD' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'pagoUSD' => ['required', 'numeric', 'min:0', 'max:99999.99','lte:montoUSD'],
            // el saldoUSD se calcula desde el Front-end.
        ];
    }
}
