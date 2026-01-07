<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Propuesta;
use App\Models\PropuestaDetalle;
use App\Models\Cliente;
use App\Models\Bodega;
use App\Models\TrazabilidadPedido;
use App\Models\ClienteNuevo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PropuestaController extends Controller
{
    public function cotizacion()
{
    // Trae todas las propuestas con su cliente asociado
    $propuestas = Propuesta::with('cliente')->orderBy('id', 'desc')->get();
    $propuestas = Propuesta::with(['cliente', 'ultimoPaso'])
    ->orderBy('id', 'desc')
    ->get();

return view('propuestas.cotizacion', compact('propuestas'));
}

    public function create()
{
    $clientes = Cliente::orderBy('nombre', 'asc')->get();
    $productos = Bodega::all();

    return view('propuestas.create', compact('clientes', 'productos'));
}

public function store(Request $request)
{
    
    // Validación base
    $request->validate([
        'items' => 'required|string', // JSON con los ítems
        'fecha_entrega_estimada' => 'nullable|date',
    ]);

    $cliente_id = null;

    if ($request->boolean('cliente_externo')) {
        
        $request->validate([
            'nombre_externo' => 'required|string|max:255',
            'cedula_externo' => 'required|string|max:20',
        ]);
    
        //Calcular el siguiente ID global (sin tocar los autoincrementales)
        $ultimoIdClientes = DB::table('clientes')->max('id') ?? 0;
        $ultimoIdNuevos = DB::table('clientes_nuevos')->max('id_cliente') ?? 0;
        $siguienteIdGlobal = max($ultimoIdClientes, $ultimoIdNuevos) + 1;

        $request->validate([
            'telefono_externo' => [
                'required',
                'digits:10'
            ],
        ]);
        // 1) Crear cliente en clientes_nuevos
        ClienteNuevo::create([
            'nombre'    => $request->nombre_externo,
            'cedula'    => $request->cedula_externo,
            'telefono'  => $request->telefono_externo,
            'direccion' => $request->direccion_externo,
            'futuro'    => 1,
        ]);

        // 2) Crear cliente también en la tabla clientes
        $clienteBase = Cliente::create([
            'nombre'    => $request->nombre_externo,
            'documento' => $request->cedula_externo,
            'telefono'  => $request->telefono_externo,
            'direccion'     => $request->direccion_externo,
            'creacion' => now(),
            'actualizacion' => now(),
        ]);

        // 3) Usar ese ID para la propuesta
        $cliente_id = $clienteBase->id;
    } else {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id'
        ]);
        $cliente_id = $request->cliente_id;
    }

    // Crear el código único personalizado
    $fecha = now()->format('Ymdhs');
    $codigo = 'PRO' . str_pad($cliente_id, 3, '0', STR_PAD_LEFT) . $fecha;
    
    // Crear la propuesta (encabezado)
    $propuesta = Propuesta::create([
        'codigo' => $codigo,
        'cliente_id' => $cliente_id,
        'estado' => 'pendiente',
        'fecha' => now(),
        'fecha_entrega_estimada' => $request->fecha_entrega_estimada,
        'subtotal' => 0,
        'iva_total' => 0,
        'total' => 0,
    ]);

    // Decodificar los ítems del JSON
    $bloques = json_decode($request->items, true);

    $subtotal_general = 0;
    $iva_general = 0;

    // Recorremos cada bloque (cada observación con sus productos)
    foreach ($bloques as $bloque) {
        $observacionBloque = $bloque['observacion'] ?? null;
        $itemsBloque = $bloque['items'] ?? [];

        foreach ($itemsBloque as $item) {
            $producto = \App\Models\Bodega::find($item['id']);
            if (!$producto) continue;

            $precio = $producto->precio ?? 0;
            $iva_porcentaje = $producto->iva ?? 0;
            $cantidad = intval($item['cantidad']);
            $subtotal = $precio * $cantidad;
            $iva_valor = $subtotal * ($iva_porcentaje / 100);

            PropuestaDetalle::create([
                'codigo' => $codigo,
                'referencia' => $producto->codigo ?? '',
                'cantidad' => $cantidad,
                'talla' => $item['talla'] ?? null,
                'observacion' => $observacionBloque,
                'precio_unitario' => $precio,
                'iva_porcentaje' => $iva_porcentaje,
                'iva_valor' => $iva_valor,
            ]);

            $subtotal_general += $subtotal;
            $iva_general += $iva_valor;
        }
    }

    // Actualizar totales de la propuesta
    $propuesta->update([
        'subtotal' => $subtotal_general,
        'iva_total' => $iva_general,
        'total' => $subtotal_general + $iva_general,
    ]);

    // Crear trazabilidad
    TrazabilidadPedido::create([
        'codigo' => $propuesta->codigo,
        'paso' => 1,
        'nombre_paso' => 'Cotización creada',
        'descripcion' => 'Propuesta comercial registrada y enviada al cliente.',
        'estado' => 'completado',
        'fecha_inicio' => now(),
        'fecha_fin' => now(),
        'responsable' => Auth::user()->nombre ?? 'Sistema',
        'TallerAsignado' => '0'
    ]);
    
    // Redirigir al listado
    return redirect()->route('propuestas.cotizacion')
        ->with('success', 'Cotización creada exitosamente.');
}


public function aprobar($codigo)
{
    // Buscar la propuesta por su código
    $propuesta = Propuesta::where('codigo', $codigo)->firstOrFail();

    // Actualizar su estado
    $propuesta->update([
        'estado' => 'aprobada',
    ]);

    // Crear registro en trazabilidad (PASO 2)
    \App\Models\TrazabilidadPedido::create([
        'codigo' => $propuesta->codigo,
        'paso' => 2,
        'nombre_paso' => 'Orden de Pedido generada',
        'descripcion' => 'Cliente aprobó la cotización y se generó la orden de pedido.',
        'estado' => 'completado',
        'fecha_inicio' => now(),
        'fecha_fin' => now(),
        'responsable' => Auth::user()->nombre ?? 'Sistema',
        'TallerAsignado' => '0'
    ]);

    return redirect()->back()->with('success', 'Propuesta aprobada y paso 2 registrado.');
}

//Ver el detalle de la propuesta

public function detalle($codigo)
{
    $propuesta = Propuesta::with(['cliente', 'detalles'])
        ->where('codigo', $codigo)
        ->firstOrFail();

    return view('propuestas.partials.detalle', compact('propuesta'));
}


//Actualizar la propuesta
public function update(Request $request, $codigo)
{
    $propuesta = Propuesta::where('codigo', $codigo)->firstOrFail();

    // Validación básica
    $request->validate([
        'fecha_entrega_estimada' => 'nullable|date',
        'nota' => 'nullable|string|max:1000',
        'cantidades' => 'nullable|array'
    ]);

    // Actualiza campos principales
    $propuesta->fecha_entrega_estimada = $request->fecha_entrega_estimada;
    $propuesta->nota = $request->nota ?? ''; 
    $propuesta->save();

    // Actualizar cantidades de los detalles
    if ($request->has('cantidades')) {
        foreach ($request->cantidades as $detalleId => $cantidad) {
            $detalle = \App\Models\PropuestaDetalle::find($detalleId);
            if ($detalle) {
                $detalle->cantidad = $cantidad;
                $detalle->save();
            }
        }
    }

    return redirect()
        ->route('propuestas.cotizacion')
        ->with('success', 'Cotización actualizada correctamente.');
}


public function edit($codigo)
{
    $propuesta = Propuesta::with(['cliente', 'detalles.bodega'])
    ->where('codigo', $codigo)
    ->firstOrFail();
    
    $clientes = Cliente::all();
    $productos = Bodega::all();

    return view('propuestas.edit', compact('propuesta', 'clientes', 'productos'));
}

//Eliminar Detalle
public function eliminarDetalle($id)
{
    $detalle = PropuestaDetalle::find($id);

    if (!$detalle) {
        return response()->json(['error' => 'Detalle no encontrado'], 404);
    }

    $detalle->delete();

    return response()->json(['success' => true]);
}



//Eliminar la propuesta
public function destroy($codigo)
{
    // Buscar la propuesta
    $propuesta = Propuesta::where('codigo', $codigo)->first();

    if (!$propuesta) {
        return redirect()->route('propuestas.cotizacion')->with('error', 'No se encontró la propuesta.');
    }

    // Eliminar trazabilidad asociada
    TrazabilidadPedido::where('codigo', $codigo)->delete();

    // Eliminar los detalles asociados
    PropuestaDetalle::where('codigo', $codigo)->delete();

    // Finalmente eliminar la propuesta
    $propuesta->delete();

    return redirect()->route('propuestas.cotizacion')->with('success', 'Propuesta y trazabilidad eliminadas correctamente.');
}

public function dashboard()
{
    // Puedes traer algunos datos resumidos si quieres mostrar estadísticas
    $totalPropuestas = \App\Models\Propuesta::count();
    $pendientes = \App\Models\Propuesta::where('estado', 'pendiente')->count();
    $aprobadas = \App\Models\Propuesta::where('estado', 'aprobada')->count();

    return view('propuestas.dashboard', compact('totalPropuestas', 'pendientes', 'aprobadas'));
}

}
