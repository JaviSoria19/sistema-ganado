<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteValidation extends FormRequest
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
            'nombreCliente' => ['required', 'string', 'min:3', 'max:255'],
            'celular' => ['required', 'string', 'min:1', 'max:20'],
            'cedulaIdentidad' => ['required', 'string', 'min:1', 'max:30'],
            'procedencia' => ['required', 'string', 'min:1', 'max:100'],     
        ];
    }
}
