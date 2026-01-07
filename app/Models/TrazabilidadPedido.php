<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrazabilidadPedido extends Model
{
    use HasFactory;

    // Nombre exacto de la tabla (opcional si sigue convención)
    protected $table = 'trazabilidad_pedido';

    // Campos que se pueden llenar con create() o update()
    protected $fillable = [
        'codigo',
        'paso',
        'nombre_paso',
        'descripcion',
        'responsable',
        'TallerAsignado',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'creada',
        'actualizada'
    ];

    const CREATED_AT = 'creada';
    const UPDATED_AT = 'actualizada';

    public $timestamps = true;

    public function propuesta()
    {
        return $this->belongsTo(Propuesta::class, 'codigo', 'codigo');
    }
}
