<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PotreroValidation extends FormRequest
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
            'nombre' => [
                'required', 'string', 'min:3', 'max:100',
                Rule::unique('potreros', 'nombre')->ignore($this->route('potrero'), 'id_potrero'),
            ],
            'ubicacion' => 'required|string|max:45',
            'superficie' => 'required|numeric|between:0,9999.99',
            'tipo_pasto' => 'required|string|max:45',
            'estado_potrero' => 'required|string|max:45',
            'disponibilidad_agua' => 'required|string|max:65',
            // Número decimal que mide Unidad Animal (UA) con un máximo de 9999.99 UA.
            'capacidad_carga_actual' => 'required|numeric|between:0,9999.99',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del potrero es obligatorio.',
            'nombre.string' => 'El nombre del potrero debe ser una cadena de texto.',
            'nombre.min' => 'El nombre del potrero debe tener al menos :min caracteres.',
            'nombre.max' => 'El nombre del potrero no puede exceder de :max caracteres.',
            'nombre.unique' => 'Ya existe un potrero con ese nombre. Por favor, elija otro nombre.',

            'ubicacion.required' => 'La ubicación del potrero es obligatoria.',
            'ubicacion.string' => 'La ubicación del potrero debe ser una cadena de texto.',
            'ubicacion.max' => 'La ubicación del potrero no puede exceder de :max caracteres.',

            'superficie.required' => 'La superficie del potrero es obligatoria.',
            'superficie.numeric' => 'La superficie del potrero debe ser un número.',
            'superficie.between' => 'La superficie del potrero debe estar entre :min y :max.',

            'tipo_pasto.required' => 'El tipo de pasto del potrero es obligatorio.',
            'tipo_pasto.string' => 'El tipo de pasto del potrero debe ser una cadena de texto.',
            'tipo_pasto.max' => 'El tipo de pasto del potrero no puede exceder de :max caracteres.',

            'estado_potrero.required' => 'El estado del potrero es obligatorio.',
            'estado_potrero.string' => 'El estado del potrero debe ser una cadena de texto.',
            'estado_potrero.max' => 'El estado del potrero no puede exceder de :max caracteres.',

            'disponibilidad_agua.required' => 'La disponibilidad de agua en el potrero es obligatoria.',
            'disponibilidad_agua.string' => 'La disponibilidad de agua en el potrero debe ser una cadena de texto.',
            'disponibilidad_agua.max' => 'La disponibilidad de agua en el potrero no puede exceder de :max caracteres.',

            'capacidad_carga_actual.required' => 'La capacidad de carga actual del potrero es obligatoria.',
            'capacidad_carga_actual.numeric' => 'La capacidad de carga actual del potrero debe ser un número.',
            'capacidad_carga_actual.between' => 'La capacidad de carga actual del potrero debe estar entre :min y :max.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre del potrero',
            'ubicacion' => 'ubicación del potrero',
            'superficie' => 'superficie del potrero',
            'tipo_pasto' => 'tipo de pasto del potrero',
            'estado_potrero' => 'estado del potrero',
            'disponibilidad_agua' => 'disponibilidad de agua en el potrero',
            'capacidad_carga_actual' => 'capacidad de carga actual del potrero',
        ];
    }
}
