<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\RoleCollection;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $roles = new RoleCollection(Role::all());
            return ApiResponse::success ('Listado de roles', 200, $roles);
        }catch (Exception $e){
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
                'nombre' => 'required|unique:roles'
            ]);
            $rol = Role::create($request->all());
            return ApiResponse::success('Registro agregado', 201, $rol);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(),422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        try {
            $rol = new RoleCollection(Role::find($role));//Role::findOrFail($id);
            return ApiResponse::success('Registro encontrado', 200, $rol);
        } catch(ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {               //return response()->json([$request->nombre]);
        try {
            $rol = Role::findOrFail($id);
            $request->validate([
                'nombre' => [
                    'required',
                    Rule::unique('roles')->ignore($rol)
                ]
            ]);
            $rol->update($request->all());
            return ApiResponse::success('Registro actualizado',200,$rol);
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
            $rol = Role::findOrFail($id);
            $rol->delete();
            return ApiResponse::error('Registro eliminado',200);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error($e->getMessage(),404);
        }
    }
}
