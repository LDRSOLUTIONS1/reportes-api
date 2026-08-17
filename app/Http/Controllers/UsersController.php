<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index()
    {
        $usuarios = User::with([
            'role:id,name'
        ])->select(
            'id',
            'collaborator_number',
            'role_id',
            'name',
            'email',
            'estado',
            'created_at',
        )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($usuarios, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateUsers($request);

        $validated['password'] = Hash::make('password');

        $usuario = User::create($validated);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data'    => $usuario
        ], 201);
    }

    public function show($id)
    {
        $usuario = User::with([
            'role:id,name'
        ])->select(
            'id',
            'collaborator_number',
            'role_id',
            'name',
            'email',
            'estado',
            'created_at',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($usuario, 200);
    }

    public function update(Request $request, $id)
    {
        $usuario = User::activos()
            ->findOrFail($id);

        $validated = $this->validateUsers($request, $id);

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data'    => $usuario
        ], 200);
    }

    public function validateUsers(Request $request, $id = null)
    {
        return $request->validate(
            [
                'collaborator_number' => 'nullable|numeric',
                'role_id'             => 'required|exists:roles,id',
                'name'                => 'required|string|max:255' . $id,
                'email'               => 'nullable|email|unique:users,email,' . $id,
                'estado'              => 'nullable|in:0,1,2',
            ],
            [
                'collaborator_number.numeric' => 'El número de colaborador debe ser un número',
                'role_id.required'            => 'El rol es obligatorio',
                'role_id.exists'              => 'El rol no existe',
                'name.required'               => 'El nombre es obligatorio',
                'name.max'                    => 'El nombre no puede tener más de 255 caracteres',
                'email.email'                 => 'El correo electrónico debe ser válido',
                'email.unique'                => 'El correo electrónico ya existe',
                'estado.in'                   => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }
}
