<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Estanque;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\EstanqueCollection;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EstanqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $estanques = new EstanqueCollection(Estanque::all());
            return ApiResponse::success ('Listado de estanques', 200, $estanques);
        }catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 500);
        }
        $usuarios = Estanque::all();
        return ApiResponse::success('Lista de estanques',200,$usuarios);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|min:1|max:255',
                'descripcion'=>'max:255',
                'producer_id'=>'required'
            ]);
            $rol = Estanque::create($request->all());
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
            //$est = new EstanqueCollection(Estanque::find($estanque));//Role::findOrFail($id);
            $estanque = Estanque::findOrFail($id);
            $estanque = [
                'id' => $estanque->id,
                'nombre'=> $estanque->nombre,
                'descripcion'=> $estanque->descripcion,
                'producer_id'=>$estanque->producer_id,
                'productor'=>$estanque->producer,
                'usuario' => $estanque->producer->user
            ];
            return ApiResponse::success('Registro encontrado', 200, $estanque);
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
            $estanque = Estanque::findOrFail($id);
            $request->validate([
                'nombre' => 'required|min:1|max:255',
                'descripcion' => 'max:255',
                'producer_id'=>'required'
            ]);
            $estanque->update($request->all());
            return ApiResponse::success('Registro actualizado',200,$estanque);
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
            $estanque = Estanque::findOrFail($id);
            $estanque->delete();
            return ApiResponse::error('Registro eliminado',200);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error($e->getMessage(),404);
        }
    }
}
