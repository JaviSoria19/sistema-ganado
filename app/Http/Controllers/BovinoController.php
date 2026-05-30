<?php

namespace App\Http\Controllers;

use App\Http\Requests\BovinoValidation;
use App\Models\Bovino;
use App\Models\Potrero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BovinoController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $potreros = (new Potrero())->get_all_potreros();
        return view('bovinos.index', [
            'head_title' => 'GESTIÓN DE BOVINOS',
            'potreros' => $potreros
        ]);
    }

    public function view_details($bovino)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $bovino = (new Bovino())->get_bovino($bovino);

        return view('bovinos.details', [
            'head_title' => 'BOVINO: ' . $bovino->identificador,
            'bovino' => $bovino,
        ]);
    }

    public function listar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso',], 403);
        }

        $id_potrero = $request->query('id_potrero');

        $origen = $request->query('origen');

        $genero = $request->query('genero');

        $estado = $request->query('estado');

        $bovinos = (new Bovino())->get_all_bovinos($id_potrero, $origen, $genero, $estado);

        return response()->json([
            'data' => $bovinos
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $bovino = (new Bovino())->get_bovino($request->bovino);
        return response()->json([
            'data' => $bovino
        ]);
    }

    public function create(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'bovinos'                              => ['required', 'array', 'min:1'],
            'bovinos.*.id_potrero'                 => ['required', 'integer', 'exists:potreros,id_potrero'],
            'bovinos.*.id_entore'                  => ['nullable', 'integer', 'exists:entores,id_entore'],
            'bovinos.*.id_padre'                   => ['nullable', 'integer', 'exists:bovinos,id_bovino'],
            'bovinos.*.id_madre'                   => ['nullable', 'integer', 'exists:bovinos,id_bovino'],
            'bovinos.*.origen'                     => ['required', Rule::in(['criollo', 'comprado', 'prestado'])],
            'bovinos.*.identificador'              => ['required', 'string', 'max:25'],
            'bovinos.*.genero'                     => ['required', Rule::in(['macho', 'hembra'])],
            'bovinos.*.tiene_identificador_oreja'  => ['required', 'boolean'],
            'bovinos.*.tiene_identificador_lomo'   => ['required', 'boolean'],
            'bovinos.*.peso_nacimiento'            => ['required', 'numeric', 'min:0', 'max:99.99'],
            'bovinos.*.peso_destete'               => ['required', 'numeric', 'min:0', 'max:999.99'],
            'bovinos.*.peso_actual'                => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'bovinos.*.color_nacimiento'           => ['required', 'string', 'max:45'],
            'bovinos.*.color_actual'               => ['required', 'string', 'max:45'],
            'bovinos.*.fecha_nacimiento'           => ['required', 'date', 'before_or_equal:today'],
            'bovinos.*.fecha_salida'               => ['nullable', 'date', 'after_or_equal:bovinos.*.fecha_nacimiento'],
            'bovinos.*.observaciones'              => ['nullable', 'string', 'max:250'],
        ]);

        // Validación de duplicados: identificador + fecha_nacimiento
        // Se valida en dos niveles: contra la BD y contra el mismo payload (por si vienen duplicados en la misma petición)
        $errors = [];

        foreach ($request->bovinos as $index => $b) {
            $existeEnBD = Bovino::where('identificador', $b['identificador'])
                ->whereDate('fecha_nacimiento', $b['fecha_nacimiento'])
                ->exists();

            if ($existeEnBD) {
                $errors["bovinos.{$index}.identificador"][] =
                    "Ya existe un bovino con el identificador '{$b['identificador']}' y fecha de nacimiento '{$b['fecha_nacimiento']}'.";
            }
        }

        // Detectar duplicados dentro del mismo payload
        $vistos = [];
        foreach ($request->bovinos as $index => $b) {
            $clave = $b['identificador'] . '|' . $b['fecha_nacimiento'];
            if (isset($vistos[$clave])) {
                $errors["bovinos.{$index}.identificador"][] =
                    "El identificador '{$b['identificador']}' con fecha '{$b['fecha_nacimiento']}' está duplicado en la misma solicitud.";
            }
            $vistos[$clave] = true;
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Existen duplicados en los bovinos.',
                'errors'  => $errors,
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->bovinos as $b) {
                $bovino = new Bovino();
                $bovino->id_potrero               = $b['id_potrero'];
                $bovino->id_entore                = $b['id_entore'] ?? null;
                $bovino->id_padre                 = $b['id_padre'] ?? null;
                $bovino->id_madre                 = $b['id_madre'] ?? null;
                $bovino->origen                   = $b['origen'];
                $bovino->identificador            = $b['identificador'];
                $bovino->genero                   = $b['genero'];
                $bovino->tiene_identificador_oreja = $b['tiene_identificador_oreja'] ? '1' : '0';
                $bovino->tiene_identificador_lomo  = $b['tiene_identificador_lomo'] ? '1' : '0';
                $bovino->peso_nacimiento          = $b['peso_nacimiento'];
                $bovino->peso_destete             = $b['peso_destete'];
                $bovino->peso_actual              = $b['peso_actual'];
                $bovino->color_nacimiento         = $b['color_nacimiento'];
                $bovino->color_actual             = $b['color_actual'];
                $bovino->fecha_nacimiento         = $b['fecha_nacimiento'];
                $bovino->observaciones            = $b['observaciones'] ?? null;
                $bovino->creado_por               = session('id_usuario');
                $bovino->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bovinos registrados correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(BovinoValidation $request, $id_bovino)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $bovino = (new Bovino())->get_bovino($id_bovino);

        $bovino->id_potrero               = $request->id_potrero;
        $bovino->id_entore                = $request->id_entore;
        $bovino->id_padre                 = $request->id_padre;
        $bovino->id_madre                 = $request->id_madre;
        $bovino->origen                   = $request->origen;
        $bovino->identificador            = $request->identificador;
        $bovino->genero                   = $request->genero;
        $bovino->tiene_identificador_oreja = $request->tiene_identificador_oreja ? '1' : '0';
        $bovino->tiene_identificador_lomo  = $request->tiene_identificador_lomo ? '1' : '0';
        $bovino->peso_nacimiento          = $request->peso_nacimiento;
        $bovino->peso_destete             = $request->peso_destete;
        $bovino->peso_actual              = $request->peso_actual;
        $bovino->color_nacimiento         = $request->color_nacimiento;
        $bovino->color_actual             = $request->color_actual;
        $bovino->fecha_nacimiento         = $request->fecha_nacimiento;
        $bovino->observaciones            = $request->observaciones;
        $bovino->modificado_por           = session('id_usuario');
        $bovino->save();

        return response()->json([
            'success' => true,
            'message' => 'Bovino actualizado correctamente',
            'bovino'  => $bovino,
        ]);
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_bovino' => ['required', 'numeric', 'integer']
        ]);

        $bovino = (new Bovino())->get_bovino($request->id_bovino);

        if($bovino->estado === 'vendido'){
            return response()->json([
                'success' => false,
                'message' => 'No se puede modificar el estado de un bovino que fue vendido.'
            ], 400);
        }

        $bovino->estado = $bovino->estado == 'activo' ? 'inactivo' : 'activo';
        $bovino->fecha_eliminacion = $bovino->estado == 'inactivo' ? now() : null;
        $bovino->eliminado_por = $bovino->estado == 'inactivo' ? session('id_usuario') : null;
        $bovino->save();

        return response()->json([
            'success' => true,
            'message' => $bovino->estado == 'activo' ? 'El bovino fue restaurado con éxito.' : 'El bovino fue archivado con éxito.',
            'bovino' => $bovino
        ]);
    }
}
