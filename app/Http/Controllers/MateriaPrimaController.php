<?php

namespace App\Http\Controllers;

use App\Models\MateriaPrima;
use Illuminate\Http\Request;

class MateriaPrimaController extends Controller
{
    public function index()
    {
        return MateriaPrima::all();
    }

    public function show($id)
    {
        return MateriaPrima::find($id);
    }

    public function store(Request $request)
    {
        return MateriaPrima::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $materiaPrima = MateriaPrima::findOrFail($id);
        $materiaPrima->update($request->all());
        return $materiaPrima;
    }

    public function destroy($id)
    {
        MateriaPrima::destroy($id);
        return response()->json(['message' => 'Materia Prima eliminada']);
    }
}
