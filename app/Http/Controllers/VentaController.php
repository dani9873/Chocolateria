<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        return Venta::all();
    }

    public function show($id)
    {
        return Venta::find($id);
    }

    public function store(Request $request)
    {
        return Venta::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);
        $venta->update($request->all());
        return $venta;
    }

    public function destroy($id)
    {
        Venta::destroy($id);
        return response()->json(['message' => 'Venta eliminada']);
    }
}
