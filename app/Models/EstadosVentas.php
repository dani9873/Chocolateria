<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadosVentas extends Model
{
    use HasFactory;
    protected $table = 'estados_ventas';

    protected $fillable = [
        'nombre', 'venta_id',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
