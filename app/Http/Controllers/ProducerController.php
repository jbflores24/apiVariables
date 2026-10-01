<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Producer;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ProducerCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProducerController extends Controller
{
    public function index(Request $request){
        try {
            $producers = new ProducerCollection(Producer::visiblePara($request->user())->get());
            return ApiResponse::success ('Listado de Productores', 200, $producers);
        }catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $producer = Producer::visiblePara($request->user())->findOrFail($id);
            $producer = [
                'id'=>$producer->id,
                'user_id'=>$producer->user_id,
                'usuario'=>$producer->user,
                'calle'=>$producer->calle,
                'numero'=>$producer->numero,
                'colonia'=>$producer->colonia,
                'cp'=>$producer->cp,
                'municipio'=>$producer->municipio,
                'agencia'=>$producer->agencia,
                'estado'=>$producer->estado,
                'telPrincipal'=>$producer->telPrincipal,
                'telSecundario'=>$producer->telSecundario,
                'estanques' => $producer->estanques,
            ];
            return ApiResponse::success('Registro encontrado', 200, $producer);
        } catch(ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),404);
        }
    }

    public function store(Request $request){
        try{
            $request->validate([
                'calle' => 'required|max:50',
                'numero' => 'required|max:4',
                'colonia' => 'required|max:50',
                'cp' => 'required|max:5',
                'municipio' => 'required|max:100',
                'agencia' => 'nullable|max:50',
                'estado' => 'required|max:50',
                'telPrincipal' => 'required|max:10',
                'telSecundario' => 'nullable|max:10',
                'user_id' => 'required|exists:users,id|unique:producers',
            ]);
            $producer = Producer::create($request->all());
            return ApiResponse::success('Registro agregado correctamente',201,$producer);
        }catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(),422, $e->errors());
        }
    }

    public function destroy($id){
        try {
            $producer = Producer::findOrFail($id);
            $producer->delete();
            return ApiResponse::success('Registro eliminado', 200);
        } catch (ModelNotFoundException $e){
            return ApiResponse::error("No se ha eliminado el registro", 404);
        }
    }

    public function update (Request $request, $id){
        try {
            $producer = Producer::findOrFail($id);
            $request->validate([
                'calle' => 'required|max:50',
                'numero' => 'required|max:4',
                'colonia' => 'required|max:50',
                'cp' => 'required|max:5',
                'municipio' => 'required|max:100',
                'agencia' => 'nullable|max:50',
                'estado' => 'required|max:50',
                'telPrincipal' => 'required|max:10',
                'telSecundario' => 'nullable|max:10',
                'user_id' => ['required','exists:users,id',Rule::unique('producers')->ignore($producer)],
            ]);
            $producer->update($request->all());
            return ApiResponse::success('Registro actualizado', 200, $producer);
        } catch (ModelNotFoundException $e){
            return ApiResponse::error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(), 422, $e->errors());
        } catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function getProducerUserId (Request $request, $user_id){
        try{
            $producerUserId = new ProducerCollection(
                Producer::visiblePara($request->user())->where('user_id',$user_id)->get()
            );
            return ApiResponse::success('Registro Encontrado',200,$producerUserId);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

    }
}
