<?php

namespace App\Http\Controllers;

use App\Models\Usuario;

class AdminUsuariosController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::all();
        return view('admin.usuarios', compact('usuarios'));
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return back()->with('success', 'Usuario eliminado correctamente');
    }
}
