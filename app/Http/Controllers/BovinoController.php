<?php

namespace App\Http\Controllers;

use App\Http\Requests\BovinoValidation;
use App\Models\Bovino;
use App\Models\Potrero;
use App\Models\PesajeHistorico;
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

    public function view_importar()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $potreros = (new Potrero())->get_all_potreros();

        return view('bovinos.importar', [
            'head_title' => 'IMPORTAR BOVINOS',
            'potreros' => $potreros
        ]);
    }

    public function view_details($bovino)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $bovino = (new Bovino())->get_bovino($bovino);

        $carimbo = date('Y', strtotime($bovino->fecha_nacimiento));

        return view('bovinos.details', [
            'head_title' => "BOVINO: C{$carimbo} {$bovino->identificador}",
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

    public function create(BovinoValidation $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $bovino = new Bovino();

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
        $bovino->estado_corporal          = $request->estado_corporal;
        $bovino->seleccion                = $request->seleccion;
        $bovino->observaciones            = $request->observaciones;
        $bovino->creado_por           = session('id_usuario');

        if ($request->id_madre && $request->id_padre) {
            $bovino->id_entore = null; // Si se especifican padre y madre, se asume que no es un entore
        }

        if ($request->id_madre && $request->id_entore) {
            $bovino->id_padre = null; // Si se especifican padre y entore, se asume que no se conoce el padre específico (multitoro o inseminación) y se deja nulo el id_padre
        }

        // Si se especifica madre pero no padre ni entore, buscar el entore correspondiente
        if ($request->id_madre && !$request->id_padre && !$request->id_entore) {
            $entore = DB::table('entores_detalles as ed')
                ->join('entores as e', 'ed.id_entore', '=', 'e.id_entore')
                ->where('ed.id_hembra', $request->id_madre)
                ->where('e.estado', 'activo')
                ->orderBy('e.fecha_inicio', 'desc')
                ->select('e.id_entore', 'e.tipo_entore', 'e.id_macho')
                ->first();

            if ($entore) {
                $entoreMessage = ", se encontró un entore activo para la madre especificada: ";
                if ($entore->tipo_entore === 'unitoro') {
                    $bovino->id_padre  = $entore->id_macho;
                    $bovino->id_entore = null;
                    $entoreMessage = $entoreMessage . "Se asignó el padre automáticamente por ser un entore unitoro.";
                } else {
                    // inseminacion o multitoro
                    $bovino->id_entore = $entore->id_entore;
                    $bovino->id_padre  = null;
                    $entoreMessage = $entoreMessage . "Se asignó el entore automáticamente por ser un entore de tipo '{$entore->tipo_entore}'.";
                }
            }
        }

        $bovino->save();

        return response()->json([
            'success' => true,
            'message' => 'Bovino creado correctamente' . ($entoreMessage ?? ''),
            'bovino'  => $bovino,
        ]);
    }

    public function import(Request $request)
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
            'bovinos.*.tiene_identificador_oreja'  => ['required', Rule::in([true, false, '1', '0', 1, 0])],
            'bovinos.*.tiene_identificador_lomo'   => ['required', Rule::in([true, false, '1', '0', 1, 0])],
            'bovinos.*.fecha_nacimiento'           => ['required', 'date', 'before_or_equal:today'],
            'bovinos.*.peso_nacimiento'            => ['required', 'numeric', 'min:0', 'max:99.99'],
            'bovinos.*.fecha_destete'              => ['nullable', 'date', 'before_or_equal:today'],
            'bovinos.*.peso_destete'               => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'bovinos.*.peso_actual'                => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'bovinos.*.color_nacimiento'           => ['required', 'string', 'max:45'],
            'bovinos.*.color_actual'               => ['required', 'string', 'max:45'],
            'bovinos.*.fecha_salida'               => ['nullable', 'date', 'after_or_equal:bovinos.*.fecha_nacimiento'],
            'bovinos.*.estado_corporal'            => ['nullable', 'integer', 'min:0', 'max:15'],
            'bovinos.*.seleccion'                  => ['nullable', 'string', 'max:100'],
            'bovinos.*.observaciones'              => ['nullable', 'string', 'max:250'],
        ]);

        /* Validación de duplicados: identificador + fecha_nacimiento, se valida en dos niveles: contra la BD y contra el mismo payload (por si vienen duplicados en la misma petición)*/

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
                $bovino->fecha_nacimiento         = $b['fecha_nacimiento'];
                $bovino->peso_nacimiento          = $b['peso_nacimiento'];
                $bovino->fecha_destete            = $b['fecha_destete'] ?? null;
                $bovino->peso_destete             = $b['peso_destete'] ?? null;
                $bovino->peso_actual              = $b['peso_actual'];
                $bovino->color_nacimiento         = $b['color_nacimiento'];
                $bovino->color_actual             = $b['color_actual'];
                $bovino->estado_corporal          = $b['estado_corporal'] ?? null;
                $bovino->seleccion                = $b['seleccion'] ?? null;
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

        if ($bovino->estado === 'vendido') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede modificar un bovino que fue vendido.'
            ], 400);
        }

        // Si el peso actual ha cambiado, se registra un nuevo registro en la tabla de pesaje histórica.
        if ($bovino->peso_actual != $request->peso_actual) {
            $pesaje_historico = new PesajeHistorico();
            $pesaje_historico->id_bovino = $bovino->id_bovino;
            $pesaje_historico->peso = $request->peso_actual;
            $pesaje_historico->fecha = date('Y-m-d');
            $pesaje_historico->creado_por = session('id_usuario');
            $pesaje_historico->save();
        }

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
        $bovino->estado_corporal          = $request->estado_corporal;
        $bovino->seleccion                = $request->seleccion;
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

        if ($bovino->estado === 'vendido') {
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
