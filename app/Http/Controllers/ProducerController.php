<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Producer;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ProducerCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    public function show(Producer $producer)
    {
        try {
            $rol = new ProducerCollection(Producer::find($producer));//Role::findOrFail($id);
            return ApiResponse::success('Registro encontrado', 200, $rol);
        } catch(ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),404);
        }
    }
}
