<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    protected $fillable = [
        'cliente_id',
        'propuesta_id',
        'fecha',
        'numero',
        'subtotal',
        'impuestos',
        'total',
        'notas',
        // añade campos si usas DIAN (CUFE, QR, estado_dian, etc.)
    ];

    protected $casts = [
        'fecha' => 'date',
        'subtotal' => 'float',
        'impuestos' => 'float',
        'total' => 'float',
    ];

    // RELACIONES
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function items()
    {
        return $this->hasMany(FacturaItem::class);
    }

    public function propuesta()
    {
        return $this->belongsTo(Propuesta::class);
    }
}
