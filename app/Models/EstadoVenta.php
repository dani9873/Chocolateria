<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoVenta extends Model
{
    use HasFactory;
    protected $table = 'estados_ventas';

    protected $fillable = [
        'nombre',
    ];

    public function ventas()
    {
        return $this->belongsToMany(Venta::class, 'estado_venta_venta', 'estado_venta_id', 'idventa')
            ->withTimestamps();
    }
    
}
