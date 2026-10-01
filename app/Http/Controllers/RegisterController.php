<?php

namespace App\Http\Controllers;

use App\Models\Estanque;
use App\Models\Register;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\RegisterResource;

class RegisterController extends Controller
{
    /**
     * Listado paginado de registros visibles para el usuario.
     * Filtros opcionales: estanque_id, variable_id, desde, hasta (Y-m-d), per_page.
     */
    public function index(Request $request)
    {
        $request->validate($this->reglasFiltros() + [
            'per_page' => 'nullable|integer|min:1|max:200',
            'page' => 'nullable|integer|min:1',
        ]);

        $registros = $this->consultaFiltrada($request)
            ->with(['estanque', 'variable', 'user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::success('Registro de valores', 200, [
            'registros' => RegisterResource::collection($registros->items()),
            'paginacion' => [
                'pagina_actual' => $registros->currentPage(),
                'por_pagina' => $registros->perPage(),
                'total' => $registros->total(),
                'ultima_pagina' => $registros->lastPage(),
            ],
        ]);
    }

    /**
     * Resumen por variable (total, mínimo, máximo, promedio y último valor).
     * Acepta los mismos filtros que el listado.
     */
    public function estadisticas(Request $request)
    {
        $request->validate($this->reglasFiltros());

        $resumen = $this->consultaFiltrada($request)
            ->select('variable_id')
            ->selectRaw('COUNT(*) as total, MIN(valor) as minimo, MAX(valor) as maximo, AVG(valor) as promedio')
            ->groupBy('variable_id')
            ->get()
            ->keyBy('variable_id');

        $variables = Variable::orderBy('id')->get()->map(function ($variable) use ($request, $resumen) {
            $fila = $resumen->get($variable->id);
            $ultimo = $fila ? $this->consultaFiltrada($request)
                ->where('variable_id', $variable->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->first() : null;
            return [
                'variable_id' => $variable->id,
                'nombre' => $variable->nombre,
                'total' => $fila ? (int) $fila->total : 0,
                'minimo' => $fila ? (float) $fila->minimo : null,
                'maximo' => $fila ? (float) $fila->maximo : null,
                'promedio' => $fila ? round((float) $fila->promedio, 2) : null,
                'ultimo_valor' => $ultimo ? $ultimo->valor : null,
                'ultima_fecha' => $ultimo ? $ultimo->created_at : null,
            ];
        });

        return ApiResponse::success('Estadísticas de variables', 200, $variables);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datos = $request->validate($this->rules($request));
        $datos['user_id'] = $request->user()->id;
        $registro = Register::create($datos);
        return ApiResponse::success('Registro agregado', 201, $registro);
    }

    /**
     * Guarda en una sola operación los valores de varias variables de un estanque.
     * Body: { estanque_id, valores: [ { variable_id, valor }, ... ] }
     */
    public function lote(Request $request)
    {
        $request->validate([
            'estanque_id' => $this->reglaEstanque($request),
            'valores' => 'required|array|min:1',
            'valores.*.variable_id' => 'required|distinct|exists:variables,id',
            'valores.*.valor' => 'required|numeric',
        ]);

        $registros = DB::transaction(function () use ($request) {
            return collect($request->input('valores'))->map(function ($valor) use ($request) {
                return Register::create([
                    'estanque_id' => $request->input('estanque_id'),
                    'variable_id' => $valor['variable_id'],
                    'valor' => $valor['valor'],
                    'user_id' => $request->user()->id,
                ]);
            });
        });

        return ApiResponse::success('Registros agregados', 201, $registros);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $registro = Register::visiblePara($request->user())->findOrFail($id);
        return ApiResponse::success('Registro encontrado', 200, new RegisterResource($registro));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $registro = Register::visiblePara($request->user())->findOrFail($id);
        if (!$this->puedeModificar($request, $registro)) {
            return ApiResponse::error('No autorizado', 403);
        }
        $datos = $request->validate($this->rules($request));
        $registro->update($datos);
        return ApiResponse::success('Registro editado', 200, $registro);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $registro = Register::visiblePara($request->user())->findOrFail($id);
        if (!$this->puedeModificar($request, $registro)) {
            return ApiResponse::error('No autorizado', 403);
        }
        $registro->delete();
        return ApiResponse::success('Registro eliminado',200);
    }

    private function rules(Request $request)
    {
        return [
            'estanque_id' => $this->reglaEstanque($request),
            'variable_id' => 'required|exists:variables,id',
            'valor' => 'required|numeric',
        ];
    }

    /**
     * El estanque debe existir y, para un productor, ser suyo.
     */
    private function reglaEstanque(Request $request)
    {
        $user = $request->user();
        return ['required', 'integer', function ($attribute, $value, $fail) use ($user) {
            if (!Estanque::visiblePara($user)->whereKey($value)->exists()) {
                $fail('El estanque no existe o no pertenece al productor.');
            }
        }];
    }

    private function reglasFiltros()
    {
        return [
            'estanque_id' => 'nullable|integer',
            'variable_id' => 'nullable|integer',
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde',
        ];
    }

    private function consultaFiltrada(Request $request)
    {
        return Register::visiblePara($request->user())
            ->when($request->filled('estanque_id'), fn ($q) => $q->where('estanque_id', $request->input('estanque_id')))
            ->when($request->filled('variable_id'), fn ($q) => $q->where('variable_id', $request->input('variable_id')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('hasta')));
    }

    /**
     * Solo el usuario que capturó el registro o un administrador pueden modificarlo.
     */
    private function puedeModificar(Request $request, Register $registro)
    {
        $user = $request->user();
        return (int) $registro->user_id === (int) $user->id || $user->isAdmin();
    }
}
