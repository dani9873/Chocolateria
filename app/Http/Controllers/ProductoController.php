<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::all();
        return response()->json($productos);
    }

    public function create()
    {
        // Retornar vista de creación
        return view('productos.create');
    }

    public function show($id)
    {
        return Producto::find($id);
    }

    public function store(Request $request)
    {
        return Producto::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update($request->all());
        return $producto;
    }

    public function destroy($id)
    {
        Producto::destroy($id);
        return response()->json(['message' => 'Producto eliminado']);
    }
}
