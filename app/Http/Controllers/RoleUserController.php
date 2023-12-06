<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\RoleUser;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role_user = RoleUser::all();
        return ApiResponse::success('Lista de Roles y usuarios', 200, $role_user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'role_id' => 'required',
                'user_id' => 'required',
            ]);
            $role_user = RoleUser::create($request->all());
            return ApiResponse::success('Registro agregado', 201, $role_user);
        } catch (ValidationException $e){
            return ApiResponse::error ($e->getMessage(),404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($user_id)
    {
        try{
            $user = DB::table('role_user')->where('user_id',$user_id)->get();
            return ApiResponse::success('Listado de roles del usuario '. $user_id , 200, $user);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $role_user = RoleUser::findOrFail($id);
            $request->validate(
                [
                    'role_id' => 'required',
                    'user_id' => 'required'
                ]
            );
            $role_user->update($request->all());
            return ApiResponse::success('registro editado',200,$role_user);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(),422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $role_user = RoleUser::findOrFail($id);
            $role_user->delete();
            return ApiResponse::success("Registro eliminado",200);
        } catch (ModelNotFoundException $e){
            return ApiResponse::error($e->getMessage(),404);
        }
    }
}
