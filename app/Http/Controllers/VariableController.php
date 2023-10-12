<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\VariableCollection;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VariableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $variable = new VariableCollection(Variable::all());
            return ApiResponse::success('Listado de variables', 200, $variable);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:variables'
            ]);
            $rol = Variable::create($request->all());
            return ApiResponse::success('Registro agregado', 201, $rol);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(),422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Variable $variable)
    {
        try {
            $rol = new VariableCollection(Variable::find($variable));//Role::findOrFail($id);
            return ApiResponse::success('Registro encontrado', 200, $rol);
        } catch(ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {               //return response()->json([$request->nombre]);
        try {
            $variable = Variable::findOrFail($id);
            $request->validate([
                'nombre' => [
                    'required',
                    Rule::unique('variables')->ignore($variable)
                ]
            ]);
            $variable->update($request->all());
            return ApiResponse::success('Registro actualizado',200,$variable);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (Exception $e) {
            return ApiResponse::error ($e->getMessage(), 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $variable = Variable::findOrFail($id);
            $variable->delete();
            return ApiResponse::error('Registro eliminado',200);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error($e->getMessage(),404);
        }
    }
}
