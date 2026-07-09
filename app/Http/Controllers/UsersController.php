<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index()
    {
        $usuarios = User::select(
            'id',
            'name',
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

        $usuario = User::create($validated);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data'    => $usuario
        ], 201);
    }

    public function show($id)
    {
        $usuario = User::select(
            'id',
            'name',
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
                'name' => 'required|string|max:255|unique:users,name,' . $id,
                'estado' => 'nullable|in:0,1,2',
            ],
            [
                'name.required' => 'El nombre es obligatorio',
                'name.max'      => 'El nombre no puede tener más de 255 caracteres',
                'name.unique'   => 'El nombre ya existe',
                'estado.in'     => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }
}
