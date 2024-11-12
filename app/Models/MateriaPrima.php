<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaPrima extends Model
{
    use HasFactory;
    protected $table = 'materia_primas';

    protected $fillable = [
        'nombre',
        'cantidadDisponible',
        'unidadMedida',
        'costoUnitario',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_materia_prima', 'idmateriaPrima', 'idProducto')
                    ->withPivot('cantidad_por_unidad')
                    ->withTimestamps();
    }
    public function compras()
    {
        return $this->hasMany(Compra::class);
    }
}
