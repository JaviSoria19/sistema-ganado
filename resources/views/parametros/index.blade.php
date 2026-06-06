@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-sliders"></i> {{ $head_title }}</h1>

    <div class="card p-3 mb-3">
        <div class="row">
            <div class="col-4"></div>

            <div class="col-4">
                <form id="form-crear-o-editar">
                    <!-- input de idEmpleado en caso de editar -->
                    <input type="hidden" name="idEmpleado" value="0">

                    <div class="mb-3">
                        <label for="unidad_animal" class="form-label">Unidad animal: <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="unidad_animal" name="unidad_animal"
                            value="{{ $parametro->unidad_animal }}" required>
                    </div>
                </form>
                <p class="fw-bold">
                    <i class="fa-solid fa-duotone fa-circle-info"></i> Última modificación: <span class="text-info"
                        id="modificado">{{ $parametro->modificado?->usuario ? $parametro->modificado->usuario : '-' }} {{$parametro->fecha_actualizacion ? "el " . date('d/m/Y H:i:s', strtotime($parametro->fecha_actualizacion)) : '-' }}</span>
                </p>
                <button type="button" id="btnGuardar" class="btn btn-primary"><i class="fa-solid fa-duotone fa-save"></i>
                    Guardar</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary"><i
                        class="fa-solid fa-duotone fa-dashboard"></i>
                    Regresar al panel</a>
            </div>

            <div class="col-4"></div>
        </div>
    </div>
    <div class="mb-3"></div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $(document).on('click', '#btnGuardar', function() {
                const url = "{{ route('parametros.index') . '/' }}" + 1;
                $.ajax({
                    url: "{{ route('parametros.index') . '/' }}" + 1,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: $('#form-crear-o-editar').serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Éxito', response.message, 'success');
                            document.getElementById("modificado").textContent = `${response.parametro.modificado?.usuario} el ${new Date(response.parametro.fecha_actualizacion).toLocaleString()}`;
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let respuesta = {};
                        try {
                            respuesta = JSON.parse(xhr.responseText);
                        } catch (e) {
                            respuesta = {
                                message: "Error desconocido"
                            };
                        }

                        let htmlError = "";

                        if (respuesta.errors) {
                            // Errores de validación (422)
                            htmlError = Object.values(respuesta.errors)
                                .flat()
                                .join("<br>");
                        } else if (respuesta.message) {
                            // Errores manuales (400, 403, 500...)
                            htmlError = respuesta.message;
                        } else {
                            htmlError = "Ocurrió un error inesperado.";
                        }

                        Swal.fire({
                            title: 'Error',
                            html: htmlError,
                            icon: 'error'
                        });
                    }
                });
            });
        });
    </script>
@endsection
