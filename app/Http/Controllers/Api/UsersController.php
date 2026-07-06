<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index()
    {
        $roles = User::select(
            'id',
            'name',
            'estado',
            'created_at',
        )
            ->where('estado', '!=', 0)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($roles, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateUsers($request);

        $role = User::create($validated);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data'    => $role
        ], 201);
    }

    public function show($id)
    {
        $role = User::select(
            'id',
            'name',
            'estado',
            'created_at',
        )
            ->where('id', $id)
            ->where('estado', '!=', 0)
            ->firstOrFail();

        return response()->json($role, 200);
    }

    public function update(Request $request, $id)
    {
        $role = User::where('id', $id)
            ->where('estado', '!=', 0)
            ->firstOrFail();

        $validated = $this->validateUsers($request, $id);

        $role->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data'    => $role
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
