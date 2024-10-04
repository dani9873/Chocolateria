<?php

namespace App\Http\Controllers;

use App\Models\VentaProducto;
use Illuminate\Http\Request;

class VentaProductoController extends Controller
{
    public function index()
    {
        return VentaProducto::all();
    }

    public function show($id)
    {
        return VentaProducto::find($id);
    }

    public function store(Request $request)
    {
        return VentaProducto::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $ventaProducto = VentaProducto::findOrFail($id);
        $ventaProducto->update($request->all());
        return $ventaProducto;
    }

    public function destroy($id)
    {
        VentaProducto::destroy($id);
        return response()->json(['message' => 'VentaProducto eliminado']);
    }
}
