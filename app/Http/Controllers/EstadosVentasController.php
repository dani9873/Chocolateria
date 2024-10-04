<?php

namespace App\Http\Controllers;

use App\Models\EstadosVentas;
use Illuminate\Http\Request;

class EstadosVentasController extends Controller
{
    public function index()
    {
        return EstadosVentas::all();
    }

    public function show($id)
    {
        return EstadosVentas::find($id);
    }

    public function store(Request $request)
    {
        return EstadosVentas::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $estadoVenta = EstadosVentas::findOrFail($id);
        $estadoVenta->update($request->all());
        return $estadoVenta;
    }

    public function destroy($id)
    {
        EstadosVentas::destroy($id);
        return response()->json(['message' => 'Estado de Venta eliminado']);
    }
}
