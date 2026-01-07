<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Cliente;
use App\Models\Propuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FacturaController extends Controller
{
    // Muestra listado simple (opcional)
    public function index()
    {
        $facturas = Factura::with('cliente')->latest()->paginate(20);
        return view('facturas.index', compact('facturas'));
    }

    // Muestra el formulario de creación (opcional)
    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        return view('facturas.create', compact('clientes'));
    }

    // Guarda la factura y sus items
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => ['required', Rule::exists('clientes', 'id')],
            'fecha' => 'required|date',
            'numero' => 'required|string|max:50|unique:facturas,numero',
            'items' => 'required|array|min:1',
            'items.*.descripcion' => 'required|string|max:1000',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.impuesto' => 'nullable|numeric|min:0',
            'notas' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            // Calcular totales
            $subtotal = 0;
            $total_impuestos = 0;

            foreach ($request->items as $it) {
                $lineTotal = floatval($it['cantidad']) * floatval($it['precio_unitario']);
                $subtotal += $lineTotal;
                if (isset($it['impuesto'])) {
                    $total_impuestos += floatval($it['impuesto']);
                }
            }

            $total = $subtotal + $total_impuestos;

            // Crear factura
            $factura = Factura::create([
                'cliente_id' => $request->cliente_id,
                'fecha' => $request->fecha,
                'numero' => $request->numero,
                'subtotal' => $subtotal,
                'impuestos' => $total_impuestos,
                'total' => $total,
                'notas' => $request->notas ?: null,
                // agrega aquí más campos de tu modelo si los tienes (p.ej. estado, resolucion_dian, etc.)
            ]);

            // Crear items
            foreach ($request->items as $it) {
                FacturaItem::create([
                    'factura_id' => $factura->id,
                    'descripcion' => $it['descripcion'],
                    'cantidad' => $it['cantidad'],
                    'precio_unitario' => $it['precio_unitario'],
                    'impuesto' => $it['impuesto'] ?? 0,
                ]);
            }

            DB::commit();

            // Redirige a la vista HTML (no PDF)
            return redirect()->route('factura.ver', $factura->id)
                             ->with('success', 'Factura creada correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            // Log::error($e); // descomenta si quieres loguear
            return back()->withInput()->withErrors(['error' => 'Error al guardar la factura: ' . $e->getMessage()]);
        }
    }

    public function facturar($codigo)
{
    // Buscar la propuesta
    $propuesta = Propuesta::with('detalles', 'cliente')
        ->where('codigo', $codigo)
        ->firstOrFail();

    // 1️⃣ Verificar si YA existe una factura asociada
    $facturaExistente = Factura::where('propuesta_id', $propuesta->codigo)->first();

    if ($facturaExistente) {
        return redirect()->route('factura.ver', $facturaExistente->id);
    }

    // 2️⃣ Si no existe → crear la factura
    $factura = Factura::create([
        'cliente_id' => $propuesta->cliente->id,
        'propuesta_id' => $propuesta->codigo,  // ← IMPORTANTE
        'fecha' => now(),
        'numero' => 'FAC-' . time(),
        'subtotal' => $propuesta->subtotal,
        'impuestos' => $propuesta->iva_total ?? 0,
        'total' => $propuesta->total,
        'notas' => 'Generada automáticamente desde propuesta '.$propuesta->codigo,
    ]);

    // 3️⃣ Crear items desde PropuestaDetalle
    foreach ($propuesta->detalles as $pItem) {

        $descripcion = $pItem->referencia
                        . ($pItem->talla ? ' - Talla: ' . $pItem->talla : '')
                        . ($pItem->observacion ? ' - ' . $pItem->observacion : '');

        FacturaItem::create([
            'factura_id' => $factura->id,
            'descripcion' => $descripcion,
            'cantidad' => $pItem->cantidad,
            'precio_unitario' => $pItem->precio_unitario,
            'impuesto' => $pItem->iva_valor ?? 0,
        ]);
    }

    // 4️⃣ Redirigir a la vista
    return redirect()->route('factura.ver', $factura->id);
}


    public function ver($id)
{
    $factura = Factura::with('items', 'cliente')->findOrFail($id);

    // Buscar la propuesta asociada a la factura
    $propuesta = Propuesta::where('id', $factura->propuesta_id ?? null)->first();

    return view('facturas.facturar', compact('factura', 'propuesta'));
}

    // Editar (opcional)
    public function edit($id)
    {
        $factura = Factura::with('items')->findOrFail($id);
        $clientes = Cliente::orderBy('nombre')->get();
        return view('facturas.edit', compact('factura', 'clientes'));
    }

    // Actualizar (opcional)
    public function update(Request $request, $id)
    {
        $request->validate([
            'cliente_id' => ['required', Rule::exists('clientes', 'id')],
            'fecha' => 'required|date',
            'numero' => ['required','string','max:50', Rule::unique('facturas','numero')->ignore($id)],
            'items' => 'required|array|min:1',
            'items.*.descripcion' => 'required|string|max:1000',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $factura = Factura::findOrFail($id);

            // recalcular totales
            $subtotal = 0;
            $total_impuestos = 0;
            foreach ($request->items as $it) {
                $lineTotal = floatval($it['cantidad']) * floatval($it['precio_unitario']);
                $subtotal += $lineTotal;
                if (isset($it['impuesto'])) $total_impuestos += floatval($it['impuesto']);
            }
            $total = $subtotal + $total_impuestos;

            $factura->update([
                'cliente_id' => $request->cliente_id,
                'fecha' => $request->fecha,
                'numero' => $request->numero,
                'subtotal' => $subtotal,
                'impuestos' => $total_impuestos,
                'total' => $total,
                'notas' => $request->notas ?? null,
            ]);

            // Para simplicidad: eliminar items viejos y crear los nuevos
            $factura->items()->delete();
            foreach ($request->items as $it) {
                FacturaItem::create([
                    'factura_id' => $factura->id,
                    'descripcion' => $it['descripcion'],
                    'cantidad' => $it['cantidad'],
                    'precio_unitario' => $it['precio_unitario'],
                    'impuesto' => $it['impuesto'] ?? 0,
                ]);
            }

            DB::commit();
            return redirect()->route('factura.ver', $factura->id)->with('success', 'Factura actualizada.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error al actualizar: '.$e->getMessage()]);
        }
    }

    // Eliminar factura (opcional)
    public function destroy($id)
    {
        $factura = Factura::findOrFail($id);
        DB::transaction(function () use ($factura) {
            $factura->items()->delete();
            $factura->delete();
        });
        return redirect()->route('factura.index')->with('success', 'Factura eliminada.');
    }

    /**
     * Ejemplo: función auxiliar para generar un QR como DataURI usando una librería
     * (esto es solo referencia; para usarla instala una librería de QR como simplesoftwareio/simple-qrcode o bacon/bacon-qr-code)
     *
     * protected function generarQrDataUri(Factura $factura)
     * {
     *     // Si instalas "simple-qrcode" podrías hacer:
     *     // $svg = \QrCode::format('svg')->size(200)->generate('Factura: '.$factura->numero);
     *     // return 'data:image/svg+xml;base64,' . base64_encode($svg);
     *     return null;
     * }
     */
}
