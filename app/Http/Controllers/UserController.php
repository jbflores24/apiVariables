<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserCollection;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function index(){
        //return new UserCollection(User::all());
        $usuarios = new UserCollection(User::all());//User::all();
        return ApiResponse::success('Lista de usuarios',200,$usuarios);
    }

    public function show ($id){
        try {
            $usuario = User::findOrFail($id);
            $usuario = [
                'id' => $usuario->id,
                'rfc' => $usuario->rfc,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'rol'=> $usuario->roles,
                'producer'=> $usuario->producer,
            ];
            return ApiResponse::success('Registro encontrado', 200, $usuario);
        } catch(ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),404);
        }
    }

    public function store (Request $request){
        try {
            $request->validate([
                'name' => 'required|max:30',
                'rfc' => 'required|unique:users|min:10|max:13',
                'email' => 'required|unique:users|email|max:60',
                'password' => 'required',
            ]);
            //$request->input('password') = Hash::make($request->password);
            $user = User::create($request->all());
            return ApiResponse::success('Registro agregado', 201, $user);
        }catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    public function destroy ($id){
        try{
            $user = User::findOrFail($id);
            $user->delete();
            return ApiResponse::success('Registro eliminado',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error($e->getMessage(),404);
        }
    }

    public function update (Request $request, $id){
        try {
            $user = User::findOrFail($id);
            $request->validate([
                'name' => 'required|max:30',
                'rfc' => ['required', Rule::unique('users')->ignore($user),'min:10','max:13'],
                'email' => ['required',Rule::unique('users')->ignore($user),'email','max:60'],
                //'password' => 'required',
            ]);
            $user->update($request->all());
            return ApiResponse::success ('Registro editado',200, $user);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error ($e->getMessage(), 404);
        } catch (Exception $e) {
            return ApiResponse::error ($e->getMessage(), 422);
        }
    }

    public function getProducer(){
        try{
            $users_rol_producer = DB::table('users')
                ->join('role_user','users.id','=','role_user.user_id')
                ->select('*')
                ->where('role_user.role_id',3)
                ->orderBy('users.id')
                ->get();
            return ApiResponse::success('Listado de usuarios con el rol de productor', 200, $users_rol_producer);
        } catch (ModelNotFoundException $e) {
            ApiResponse::error($e->getMessage(),404);
        }

    }
}
