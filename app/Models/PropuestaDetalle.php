<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropuestaDetalle extends Model
{
    use HasFactory;

    protected $table = 'propuesta_detalles';

    protected $fillable = [
        'codigo',
        'referencia',
        'cantidad',
        'talla',
        'observacion',
        'precio_unitario',
        'subtotal',
        'iva_porcentaje',
        'iva_valor',
        'total_item',
        'referencia',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

    // Relación con la propuesta
    public function propuesta()
{
    return $this->belongsTo(Propuesta::class, 'codigo', 'codigo');
}

public function bodega()
{
    return $this->belongsTo(Bodega::class, 'referencia', 'codigo');
}

}
