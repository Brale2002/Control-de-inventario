<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bodega;
use App\Models\PropuestaDetalle;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BodegaController extends Controller
{
    public function index()
    {
        // productos paginados
        $productos = Bodega::orderBy('nombre')->paginate(20);

        // indicadores
        $totalProductos = Bodega::count();
        $totalStock = Bodega::sum(DB::raw('stock'));
        $valorInventario = Bodega::sum(DB::raw('stock * precio'));
        // vendidos en base a propuestas (si existe propuesta_detalles)
        $totalVendido = PropuestaDetalle::sum('cantidad');

        return view('admin.Bodega.BodegaVista', compact(
            'productos','totalProductos','totalStock','valorInventario','totalVendido'
        ));
    }

    public function Buscar(Request $request) 
    { $query = $request->input('q'); 
        
        $items = Bodega::select('id', 'codigo', 'nombre', 'stock') 
        ->when($query, function ($q) use ($query) 
        { $q->where('nombre', 'like', "%{$query}%") ->orWhere('codigo', 'like', "%{$query}%"); }) 
        ->limit(50) ->get(); 
        
        return response()->json($items); 
    
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'codigo' => 'required|string|max:50|unique:bodega,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        Bodega::create($v->validated());

        return redirect()->route('admin.Bodega.BodegaVista')->with('success','Producto creado.');
    }

    public function update(Request $request, $id)
    {
        $producto = Bodega::findOrFail($id);

        $v = Validator::make($request->all(), [
            'codigo' => 'required|string|max:50|unique:bodega,codigo,'.$producto->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $producto->update($v->validated());

        return redirect()->route('admin.Bodega.BodegaVista')->with('success','Producto actualizado.');
    }

    public function destroy($id)
    {
        $producto = Bodega::findOrFail($id);
        $producto->delete();

        return redirect()->route('admin.Bodega.BodegaVista')->with('success','Producto eliminado.');
    }

    // aumentar stock (entrada)
    public function entrada(Request $request)
    {
        $v = Validator::make($request->all(), [
            'producto_id' => 'required|exists:bodega,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'nullable|numeric|min:0',
            'nota' => 'nullable|string',
        ]);
        if ($v->fails()) return response()->json(['errors'=>$v->errors()],422);

        $p = Bodega::findOrFail($request->producto_id);
        $p->stock = ($p->stock ?? 0) + intval($request->cantidad);
        // opcional: si envían precio_unitario lo actualizamos
        if ($request->filled('precio_unitario')) $p->precio = $request->precio_unitario;
        $p->save();

        return response()->json(['success'=>true,'stock'=>$p->stock]);
    }

    // disminuir stock (salida / venta manual)
    public function salida(Request $request)
    {
        $v = Validator::make($request->all(), [
            'producto_id' => 'required|exists:bodega,id',
            'cantidad' => 'required|integer|min:1',
            'nota' => 'nullable|string',
        ]);
        if ($v->fails()) return response()->json(['errors'=>$v->errors()],422);

        $p = Bodega::findOrFail($request->producto_id);
        $cantidad = intval($request->cantidad);
        if (($p->stock ?? 0) < $cantidad) {
            return response()->json(['error'=>'Stock insuficiente'],422);
        }

        $p->stock = $p->stock - $cantidad;
        $p->save();

        return response()->json(['success'=>true,'stock'=>$p->stock]);
    }
}
