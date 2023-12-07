<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Producer;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ProducerCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProducerController extends Controller
{
    public function index(){
        try {
            $producers = new ProducerCollection(Producer::all());
            return ApiResponse::success ('Listado de Productores', 200, $producers);
        }catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $producer = Producer::findOrFail($id);
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
                'agencia' => 'max:50',
                'estado' => 'required|max:50',
                'telPrincipal' => 'required|max:10',
                'telSecundario' => 'max:10',
                'user_id' => 'required|unique:producers|max:5',
            ]);
            $producer = Producer::create($request->all());
            return ApiResponse::success('Registro agregado correctamente',202,$producer);
        }catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(),422);
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
                'agencia' => 'max:50',
                'estado' => 'required|max:50',
                'telPrincipal' => 'required|max:10',
                'telSecundario' => 'max:10',
                'user_id' => ['required',Rule::unique('producers')->ignore($producer),'max:5'],
            ]);
            $producer->update($request->all());
            return ApiResponse::success('Registro actualizado', 200, $producer);
        } catch (ModelNotFoundException $e){
            return ApiResponse::error($e->getMessage(), 404);
        } catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function getProducerUserId ($user_id){
        try{
            /*$producerUserId = DB::table('producers')
                ->where('producers.user_id',$user_id)
                ->get();*/
            /*$producerUserId = DB::table('producers')
                ->join ('estanques', 'producers.id','=','estanques.producer_id')
                ->select ('*')
                ->where ('producers.user_id',$user_id)
                ->get();*/
            $producerUserId = new ProducerCollection(Producer::query()->where('user_id',$user_id)->get());
            return ApiResponse::success('Registro Encontrado',200,$producerUserId);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

    }
}
