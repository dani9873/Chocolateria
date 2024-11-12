<?php

namespace App\Http\Controllers;

use App\Models\VentaProducto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentaProductoController extends Controller
{
    public function index()
    {
        $ventasProductos = VentaProducto::all();
        return Inertia::render('Modules/VentasProductos', ['ventasProductos' => $ventasProductos]);
    }

    public function show($id)
    {
        $ventaProducto = VentaProducto::find($id);
        return Inertia::render('Modules/VentasProductos', ['ventaProducto' => $ventaProducto]);
    }

    public function store(Request $request)
    {
        $ventaProducto = VentaProducto::create($request->all());
        return redirect()->route('ventasProductos')->with('success', 'Venta Producto creada exitosamente');
    }

    public function update(Request $request, $id)
    {
        $ventaProducto = VentaProducto::findOrFail($id);
        $ventaProducto->update($request->all());
        return redirect()->route('ventasProductos')->with('success', 'Venta Producto actualizada exitosamente');
    }

    public function destroy($id)
    {
        VentaProducto::destroy($id);
        return redirect()->route('ventasProductos')->with('success', 'Venta Producto eliminada exitosamente');
    }
}
