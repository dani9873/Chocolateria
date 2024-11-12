<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoVentaVenta extends Model
{
    use HasFactory;
    protected $table = 'estado_venta_venta';

    protected $fillable = [
        'idventa',
        'estado_venta_id',
    ];
}

