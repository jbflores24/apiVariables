<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Auth;
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
            $token = $user->createToken('token-name')->plainTextToken;
            return ApiResponse::success('Token creado', 200,  $token);
        } else {
            return ApiResponse::error ('Credenciales inválidas', 401);
        }
    }

    public function logout (Request $request){
        $token = $request->user()->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
        return ApiResponse::success('Sesión cerrada', 200);
    }
}
