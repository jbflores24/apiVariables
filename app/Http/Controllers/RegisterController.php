<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Register;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\RegisterCollection;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $registers = new RegisterCollection(Register::all());
            return ApiResponse::success('Registro de valores', 200, $registers);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $datos = $request->validate($this->rules());
            $datos['user_id'] = $request->user()->id;
            $rol = Register::create($datos);
            return ApiResponse::success('Registro agregado', 201, $rol);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(),422, $e->errors());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $registro = Register::findOrFail($id);
            $registro = [
                'id' => $registro->id,
                'valor'=>$registro->valor,
                'estanque_id'=>$registro->estanque_id,
                'estanque' => $registro->estanque,
                'variable_id'=>$registro->variable_id,
                'variable'=>$registro->variable,
                'user_id'=> $registro->user_id,
                'registro'=> $registro->user,
            ];
            return ApiResponse::success('Registro encontrado', 200, $registro);
        } catch(ModelNotFoundException $e) {
            return ApiResponse::error('No Encontrado',404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $registro = Register::findOrFail($id);
            if (!$this->puedeModificar($request, $registro)) {
                return ApiResponse::error('No autorizado', 403);
            }
            $datos = $request->validate($this->rules());
            $registro->update($datos);
            return ApiResponse::success('Registro editado', 200, $registro);
        } catch(ModelNotFoundException $e) {
            return ApiResponse::error('No Encontrado',404);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(),422, $e->errors());
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(),500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $rol = Register::findOrFail($id);
            if (!$this->puedeModificar($request, $rol)) {
                return ApiResponse::error('No autorizado', 403);
            }
            $rol->delete();
            return ApiResponse::success('Registro eliminado',200);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error($e->getMessage(),404);
        }
    }

    private function rules()
    {
        return [
            'estanque_id' => 'required|exists:estanques,id',
            'variable_id' => 'required|exists:variables,id',
            'valor' => 'required|numeric',
        ];
    }

    /**
     * Solo el usuario que capturó el registro o un administrador pueden modificarlo.
     */
    private function puedeModificar(Request $request, Register $registro)
    {
        $user = $request->user();
        return (int) $registro->user_id === (int) $user->id || $user->hasRole('Administrador');
    }
}
