<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoMateriaPrima extends Model
{
    use HasFactory;
    protected $table = 'producto_materia_prima';

    protected $fillable = [
        'idmateriaPrima', 'idProducto', 'cantidad_por_unidad',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idProducto');
    }

    public function materiaPrima()
    {
        return $this->belongsTo(MateriaPrima::class, 'idmateriaPrima');
    }
}
