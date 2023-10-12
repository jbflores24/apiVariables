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
            $request->validate([
                'estanque_id' => 'required',
                'variable_id' => 'required',
                'user_id' => 'required',
                'valor' => 'required',
            ]);
            $rol = Register::create($request->all());
            return ApiResponse::success('Registro agregado', 201, $rol);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(),422);
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
            $request->validate([
                'estanque_id' => 'required',
                'variable_id' => 'required',
                'user_id' => 'required',
                'valor' => 'required',
            ]);
            $registro->update($request->all());
            return ApiResponse::success('Registro editado', 200, $registro);
        } catch(ModelNotFoundException $e) {
            return ApiResponse::error('No Encontrado',404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(),500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $rol = Register::findOrFail($id);
            $rol->delete();
            return ApiResponse::error('Registro eliminado',200);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error($e->getMessage(),404);
        }
    }
}
