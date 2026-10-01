<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login (Request $request){
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (Auth::attempt($credentials)){
            $user = Auth::user();
            $token = $user->createToken('app')->plainTextToken;
            return ApiResponse::success('Token creado', 200, [
                'token' => $token,
                'user' => $user->perfil(),
            ]);
        } else {
            return ApiResponse::error ('Credenciales inválidas', 401);
        }
    }

    public function me (Request $request){
        return ApiResponse::success('Usuario autenticado', 200, $request->user()->perfil());
    }

    public function cambiarPassword (Request $request){
        $request->validate([
            'password_actual' => 'required',
            'password' => 'required|min:4|confirmed',
        ]);
        $user = $request->user();
        if (!Hash::check($request->password_actual, $user->password)) {
            return ApiResponse::error('La contraseña actual no es correcta', 422, [
                'password_actual' => ['La contraseña actual no es correcta'],
            ]);
        }
        $user->update(['password' => $request->password]);

        // Se cierran las demás sesiones; la actual sigue activa
        $actual = $user->currentAccessToken();
        $user->tokens()
            ->when($actual instanceof PersonalAccessToken, fn ($q) => $q->where('id', '!=', $actual->id))
            ->delete();

        return ApiResponse::success('Contraseña actualizada', 200);
    }

    public function logout (Request $request){
        $token = $request->user()->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
        return ApiResponse::success('Sesión cerrada', 200);
    }
}
