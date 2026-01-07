<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaItem extends Model
{
    use HasFactory;

    protected $table = 'factura_items';

    protected $fillable = [
        'factura_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'impuesto',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'precio_unitario' => 'float',
        'impuesto' => 'float',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    // TOTAL POR LÍNEA
    public function getTotalAttribute()
    {
        return ($this->cantidad * $this->precio_unitario) + $this->impuesto;
    }
}
