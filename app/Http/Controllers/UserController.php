<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserCollection;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(){
        //return new UserCollection(User::all());
        $usuarios = new UserCollection(User::all());//User::all();
        return ApiResponse::success('Lista de usuarios',200,$usuarios);
    }

    public function show (User $user){
        try {
            $rol = new UserCollection(User::find($user));//Role::findOrFail($id);
            return ApiResponse::success('Registro encontrado', 200, $rol);
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
}
