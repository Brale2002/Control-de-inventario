<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        return response()->json(Cliente::all());
    }

    public function show($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'documento' => 'required|string|max:50|unique:clientes',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'es_temporal' => 'boolean',
        ]);

        $cliente = Cliente::create($validated);

        return response()->json($cliente, 201);
    }
}
