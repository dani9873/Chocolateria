<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;
    protected $table = 'venta';

    protected $fillable = [
        'fecha', 'total', 'cliente_id', 'usuario_id', 'venta_id',
    ];

    public function productos()
    {
        return $this->hasMany(VentaProducto::class, 'idventa');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function estado()
    {
        return $this->hasOne(EstadosVentas::class, 'venta_id');
    }
}
