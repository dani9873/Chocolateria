<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\MateriaPrimaController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\ComprasController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    /* Route::resource('usuarios', UsuariosController::class);
    Route::resource('productos', ProductosController::class);
    Route::resource('materias-primas', MateriaPrimaController::class);
    Route::resource('clientes', ClientesController::class);
    Route::resource('ventas', VentasController::class);
    Route::resource('compras', ComprasController::class); */
    Route::get('{any}', function () {
        return redirect()->route('dashboard');
    })->where('any', '.*');
});
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('Auth/Login');
    })->name('login');
    Route::get('/register', function () {
        return Inertia::render('Auth/Register');
    })->name('register');

Route::get('{any}', function () {
    return redirect()->route('login');
})->where('any', '.*');
});
