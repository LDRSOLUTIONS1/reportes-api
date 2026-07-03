<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login($collaborator_number)
    {
        try {
            $user = User::where('collaborator_number', $collaborator_number)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'Empleado no encontrado.'
                ], 404);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Autenticación exitosa',
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al autenticar empleado',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function loginn(Request $request)
    {
        $jwt = $request->cookie('token');

        if (!$jwt) {

            return response()->json([
                'message' => 'No existe token'
            ], 401);
        }

        try {

            $decoded = JWT::decode(
                $jwt,
                new Key(config('jwt.secret_rh'), 'HS256')
                //new Key(env('JWT_SECRET'), 'HS256')
            );
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Token inválido'
            ], 401);
        }

        $user = User::where(
            'external_rh_id',
            $decoded->id_colaborador
        )->first();

        if (!$user) {

            $user = User::where(
                'email',
                $decoded->correo
            )->first();
        }

        if ($user) {

            $user->update([

                'external_rh_id' => $decoded->id_colaborador,
                'name' => $decoded->nombre,
                'email' => $decoded->correo,
                'brand' => $decoded->marca,
                'location_name' => $decoded->nombre_sede

            ]);
        } else {

            $user = User::create([

                'external_rh_id' => $decoded->id_colaborador,
                'name' => $decoded->nombre,
                'email' => $decoded->correo,
                'brand' => $decoded->marca,
                'location_name' => $decoded->nombre_sede,
                'password' => Hash::make(Str::random(40))

            ]);
        }

        $token = $user->createToken('reportes')->plainTextToken;

        return response()->json([

            'token' => $token,
            'user' => $user

        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'message' => 'Usuario autenticado',
            'user' => [
                'id' => $user->id,
                'external_rh_id' => $user->external_rh_id,
                'name' => $user->name,
                'email' => $user->email,
                'brand' => $user->brand,
                'location_name' => $user->location_name,
                'estado' => $user->estado,
            ]
        ], 200);
    }
}
