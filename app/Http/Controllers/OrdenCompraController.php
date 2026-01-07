<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bodega;
use App\Models\OrdenCompra;

class OrdenCompraController extends Controller
{
    public function index()
    {
        $ordenes =  OrdenCompra::orderBy('created_at','desc')->paginate(20);
        return view('admin.Ordenes.index', compact('ordenes'));
    }

    public function create()
    {
        $productos = Bodega::orderBy('nombre')->get();
        return view('admin.ordenes.create', compact('productos'));
    }

    public function store(Request $request)
    {
        // Validación simple
        $request->validate([
            'proveedor' => 'required|string',
            'items' => 'required|array',
            'items.*.producto_id' => 'required|exists:bodega,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio' => 'required|numeric|min:0',
        ]);

        // Crear orden simple
        $orden = OrdenCompra::create([
            'proveedor' => $request->proveedor,
            'total' => array_sum(array_map(fn($it)=> $it['precio']*$it['cantidad'], $request->items)),
        ]);

        foreach ($request->items as $it) {
            $orden->items()->create([
                'producto_id' => $it['producto_id'],
                'cantidad' => $it['cantidad'],
                'precio_unitario' => $it['precio'],
            ]);
        }

        return redirect()->route('admin.ordenescompra.index')->with('success','Orden creada.');
    }

    public function show($id)
    {
        $orden = OrdenCompra::with('items.producto')->findOrFail($id);
        return view('admin.ordenes.show', compact('orden'));
    }

    public function confirmar($id)
    {
        $orden = OrdenCompra::with('items')->findOrFail($id);

        // Ya confirmada
        if ($orden->estado === 'confirmada') {
            return back()->with('error', 'Esta orden ya fue confirmada.');
        }

        // Sumar cantidades al inventario
        foreach ($orden->items as $item) {
            $producto = \App\Models\Bodega::find($item->producto_id);

            if ($producto) {
                $producto->stock += $item->cantidad;
                $producto->save();
            }
        }

        // Marcar como confirmada
        $orden->estado = 'confirmada';
        $orden->save();

        return back()->with('success', 'Inventario actualizado y orden confirmada.');
    }

    public function destroy($id)
    {
        $orden = OrdenCompra::findOrFail($id);
    
        // Elimina también los items vinculados
        $orden->items()->delete();
    
        $orden->delete();
    
        return redirect()->route('ordenes.index')->with('success', 'Orden eliminada correctamente.');
    }
    
}
