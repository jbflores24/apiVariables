<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\RoleUser;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            $request->validate($this->rules($request));
            $role_user = RoleUser::create($request->only(['role_id', 'user_id']));
            return ApiResponse::success('Registro agregado', 201, $role_user);
        } catch (ValidationException $e){
            return ApiResponse::error ($e->getMessage(),422, $e->errors());
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
            $request->validate($this->rules($request, $role_user));
            $role_user->update($request->only(['role_id', 'user_id']));
            return ApiResponse::success('registro editado',200,$role_user);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),404);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(), 422, $e->errors());
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

    /**
     * El rol y el usuario deben existir y el usuario no puede tener el mismo rol dos veces.
     */
    private function rules(Request $request, $role_user = null)
    {
        $unico = Rule::unique('role_user')->where('role_id', $request->input('role_id'));
        if ($role_user) {
            $unico->ignore($role_user);
        }
        return [
            'role_id' => 'required|exists:roles,id',
            'user_id' => ['required', 'exists:users,id', $unico],
        ];
    }
}
