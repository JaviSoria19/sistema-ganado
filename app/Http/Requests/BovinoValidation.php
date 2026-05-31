<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BovinoValidation extends FormRequest
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
        $bovinoId = $this->route('bovino'); // para el caso de update

        return [
            'id_potrero'                  => ['required', 'integer', 'exists:potreros,id_potrero'],
            'id_entore'                   => ['nullable', 'integer', 'exists:entores,id_entore'],
            'id_padre'                    => ['nullable', 'integer', 'exists:bovinos,id_bovino'],
            'id_madre'                    => ['nullable', 'integer', 'exists:bovinos,id_bovino'],
            'origen'                      => ['required', Rule::in(['criollo', 'comprado', 'prestado'])],
            'identificador' => [
                'required',
                'string',
                'max:25',
                $bovinoId
                    ? Rule::unique('bovinos')->where(fn($query) => $query->whereDate('fecha_nacimiento', $this->input('fecha_nacimiento')))->ignore($bovinoId, 'id_bovino')
                    : Rule::unique('bovinos')->where(fn($query) => $query->whereDate('fecha_nacimiento', $this->input('fecha_nacimiento'))),
            ],
            'genero'                      => ['required', Rule::in(['macho', 'hembra'])],
            'tiene_identificador_oreja'   => ['nullable', 'boolean'],
            'tiene_identificador_lomo'    => ['nullable', 'boolean'],
            'peso_nacimiento'             => ['required', 'numeric', 'min:0', 'max:99.99'],
            'peso_destete'                => ['required', 'numeric', 'min:0', 'max:999.99'],
            'peso_actual'                 => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'color_nacimiento'            => ['required', 'string', 'max:45'],
            'color_actual'                => ['required', 'string', 'max:45'],
            'fecha_nacimiento'            => ['required', 'date', 'before_or_equal:today'],
            'fecha_salida'                => ['nullable', 'date', 'after_or_equal:fecha_nacimiento'],
            'observaciones'               => ['nullable', 'string', 'max:250'],
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
            'id_potrero.required'                => 'El potrero es obligatorio.',
            'id_potrero.exists'                  => 'El potrero seleccionado no existe.',
            'id_entore.exists'                   => 'El entore seleccionado no existe.',
            'id_padre.exists'                    => 'El padre seleccionado no existe.',
            'id_madre.exists'                    => 'La madre seleccionada no existe.',
            'origen.required'                    => 'El origen es obligatorio.',
            'origen.in'                          => 'El origen debe ser: criollo, comprado o prestado.',
            'identificador.required'             => 'El identificador es obligatorio.',
            'identificador.max'                  => 'El identificador no puede superar los 25 caracteres.',
            'identificador.unique' => 'Ya existe un bovino con ese identificador y año de nacimiento.',
            'genero.required'                    => 'El género es obligatorio.',
            'genero.in'                          => 'El género debe ser macho o hembra.',
            'tiene_identificador_oreja.boolean'  => 'El identificador de oreja debe ser verdadero o falso.',
            'tiene_identificador_lomo.boolean'   => 'El identificador de lomo debe ser verdadero o falso.',
            'peso_nacimiento.required'           => 'El peso al nacimiento es obligatorio.',
            'peso_nacimiento.numeric'            => 'El peso al nacimiento debe ser un número.',
            'peso_nacimiento.min'                => 'El peso al nacimiento no puede ser negativo.',
            'peso_nacimiento.max'                => 'El peso al nacimiento no puede superar 99.99 kg.',
            'peso_destete.required'              => 'El peso al destete es obligatorio.',
            'peso_destete.numeric'               => 'El peso al destete debe ser un número.',
            'peso_destete.min'                   => 'El peso al destete no puede ser negativo.',
            'peso_destete.max'                   => 'El peso al destete no puede superar 999.99 kg.',
            'peso_actual.required'               => 'El peso actual es obligatorio.',
            'peso_actual.numeric'                => 'El peso actual debe ser un número.',
            'peso_actual.min'                    => 'El peso actual no puede ser negativo.',
            'peso_actual.max'                    => 'El peso actual no puede superar 9999.99 kg.',
            'color_nacimiento.required'          => 'El color al nacimiento es obligatorio.',
            'color_nacimiento.max'               => 'El color al nacimiento no puede superar los 45 caracteres.',
            'color_actual.required'              => 'El color actual es obligatorio.',
            'color_actual.max'                   => 'El color actual no puede superar los 45 caracteres.',
            'fecha_nacimiento.required'          => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date'              => 'La fecha de nacimiento no tiene un formato válido.',
            'fecha_nacimiento.before_or_equal'   => 'La fecha de nacimiento no puede ser futura.',
            'fecha_salida.date'                  => 'La fecha de salida no tiene un formato válido.',
            'fecha_salida.after_or_equal'        => 'La fecha de salida debe ser posterior o igual a la fecha de nacimiento.',
            'observaciones.max'                  => 'Las observaciones no pueden superar los 250 caracteres.',
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
            'id_potrero'                  => 'potrero',
            'id_entore'                   => 'entore',
            'id_padre'                    => 'padre',
            'id_madre'                    => 'madre',
            'origen'                      => 'origen',
            'identificador'               => 'identificador',
            'genero'                      => 'género',
            'tiene_identificador_oreja'   => 'identificador de oreja',
            'tiene_identificador_lomo'    => 'identificador de lomo',
            'peso_nacimiento'             => 'peso al nacimiento',
            'peso_destete'                => 'peso al destete',
            'peso_actual'                 => 'peso actual',
            'color_nacimiento'            => 'color al nacimiento',
            'color_actual'                => 'color actual',
            'fecha_nacimiento'            => 'fecha de nacimiento',
            'fecha_salida'                => 'fecha de salida',
            'observaciones'               => 'observaciones',
        ];
    }
}
