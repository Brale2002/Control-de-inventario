<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Propuesta extends Model
{
    use HasFactory;

    protected $table = 'propuestas';

    protected $fillable = [
        'cliente_id',
        'codigo',
        'estado',
        'fecha',
        'fecha_entrega_estimada',
        'iva_total',
        'subtotal',
        'total',
        'nota',
        'creada',
        'actualizada'
    ];

    public $timestamps = true; 
    
    const CREATED_AT = 'creada';
    const UPDATED_AT = 'actualizada';

    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Relación con PropuestaDetalle
    public function detalles()
    {
        return $this->hasMany(PropuestaDetalle::class, 'codigo', 'codigo');
    }

    public function trazabilidad()
    {
        return $this->hasMany(TrazabilidadPedido::class, 'codigo', 'codigo');
    }

    public function ultimoPaso()
    {
        return $this->hasOne(TrazabilidadPedido::class, 'codigo', 'codigo')->orderByDesc('paso');
    }

}
