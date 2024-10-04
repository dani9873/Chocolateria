<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function index()
    {
        return Compra::all();
    }

    public function show($id)
    {
        return Compra::find($id);
    }

    public function store(Request $request)
    {
        return Compra::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $compra = Compra::findOrFail($id);
        $compra->update($request->all());
        return $compra;
    }

    public function destroy($id)
    {
        Compra::destroy($id);
        return response()->json(['message' => 'Compra eliminada']);
    }
}
