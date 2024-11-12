<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all();
        return Inertia::render('Modules/Clientes', ['clientes' => $clientes]);
    }

    public function show($id)
    {
        $cliente = Cliente::FindOrFail($id);
        return Inertia::render('Modules/Clientes', ['cliente' => $cliente]);
    }

    public function store(Request $request)
    {
       $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:clientes,email',
            'telefono' => 'nullable|string|max:20',
        ]);
        $cliente = Cliente::create($request->all());
        return redirect()->route('clientes')->with('success', 'Cliente creado exitosamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:clientes,email,' . $id,
            'telefono' => 'nullable|string|max:20',
        ]);

        $cliente = Cliente::findOrFail($id);
        $cliente->update($request->all());
        return redirect()->route('clientes')->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy($id)
    {
        Cliente::destroy($id);
        return redirect()->route('clientes')->with('success', 'Cliente eliminado exitosamente');
    }
}
