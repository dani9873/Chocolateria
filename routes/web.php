<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MateriaPrimaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\EstadosVentasController;
use App\Http\Controllers\EstadoVentaVentaController;
use App\Http\Controllers\ProductoMateriaPrimaController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\KPIController;
use App\Http\Controllers\VentaProductoController;

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

    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');
    Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::put('/clientes/{id}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');


    Route::get('/productos', [ProductoController::class, 'index'])->name('productos');
    Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    Route::put('/productos/{id}', [ProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('productos.destroy');

    Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
    Route::get('/compras/{id}', [CompraController::class, 'show'])->name('compras.show');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
    Route::put('/compras/{compra}', [CompraController::class, 'update'])->name('compras.update');
    Route::delete('/compras/{compra}', [CompraController::class, 'destroy'])->name('compras.destroy');

    Route::get('/estados-ventas', [EstadosVentasController::class, 'index'])->name('estados-ventas');
    Route::get('/estados-ventas/{id}', [EstadosVentasController::class, 'show'])->name('estados-ventas.show');
    Route::post('/estados-ventas', [EstadosVentasController::class, 'store'])->name('estados-ventas.store');
    Route::put('/estados-ventas/{id}', [EstadosVentasController::class, 'update'])->name('estados-ventas.update');
    Route::delete('/estados-ventas/{id}', [EstadosVentasController::class, 'destroy'])->name('estados-ventas.destroy');

    Route::get('/materiasPrimas', [MateriaPrimaController::class, 'index'])->name('materiasPrimas.index');
    Route::get('/materiasPrimas/{id}', [MateriaPrimaController::class, 'show'])->name('materiasPrimas.show');
    Route::post('/materiasPrimas', [MateriaPrimaController::class, 'store'])->name('materiasPrimas.store');
    Route::put('/materiasPrimas/{id}', [MateriaPrimaController::class, 'update'])->name('materiasPrimas.update');
    Route::delete('/materiasPrimas/{id}', [MateriaPrimaController::class, 'destroy'])->name('materiasPrimas.destroy');
    Route::post('/materias-primas/add-product-relation', [MateriaPrimaController::class, 'addProductRelation'])->name('materiasPrimas.add-product-relation');
    Route::delete('/materias-primas/{materiaPrima}/remove-product-relation/{producto}', [MateriaPrimaController::class, 'removeProductRelation'])->name('materiasPrimas.remove-product-relation');
    Route::post('/materias-primas/update-inventory', [MateriaPrimaController::class, 'updateInventory'])->name('materiasPrimas.update-inventory');

    Route::get('/estados-ventas-ventas', [EstadoVentaVentaController::class, 'index'])->name('estados-ventas-ventas.index');
    Route::get('/estados-ventas-ventas/{id}', [EstadoVentaVentaController::class, 'show'])->name('estados-ventas-ventas.show');
    Route::post('/estados-ventas-ventas', [EstadoVentaVentaController::class, 'store'])->name('estados-ventas-ventas.store');
    Route::put('/estados-ventas-ventas/{id}', [EstadoVentaVentaController::class, 'update'])->name('estados-ventas-ventas.update');
    Route::delete('/estados-ventas-ventas/{id}', [EstadoVentaVentaController::class, 'destroy'])->name('estados-ventas-ventas.destroy');

    Route::get('/productos', [ProductoController::class, 'index'])->name('productos');
    Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
    Route::post('/productos/materia-prima', [ProductoController::class, 'agregarMateriaPrima'])->name('producto.materia-prima.store');
    Route::delete('/productos/{producto}/materia-prima/{materiaPrima}', [ProductoController::class, 'eliminarMateriaPrima'])->name('producto.materia-prima.destroy');

    Route::get('/productos-materias-primas', [ProductoMateriaPrimaController::class, 'index'])->name('productos-materias-primas');
    Route::get('/productos-materias-primas/{id}', [ProductoMateriaPrimaController::class, 'show'])->name('productos-materias-primas.show');
    Route::post('/productos-materias-primas', [ProductoMateriaPrimaController::class, 'store'])->name('productos-materias-primas.store');
    Route::put('/productos-materias-primas/{id}', [ProductoMateriaPrimaController::class, 'update'])->name('productos-materias-primas.update');
    Route::delete('/productos-materias-primas/{id}', [ProductoMateriaPrimaController::class, 'destroy'])->name('productos-materias-primas.destroy');

    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/{id}', [VentaController::class, 'show'])->name('ventas.show');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::put('/ventas/{venta}', [VentaController::class, 'update'])->name('ventas.update');
    Route::put('/ventas/{id}/estado', [VentaController::class, 'updateEstado'])->name('ventas.updateEstado');
    Route::delete('/ventas/{venta}', [VentaController::class, 'destroy'])->name('ventas.destroy');

    Route::get('/ventas-productos', [VentaProductoController::class, 'index'])->name('ventas-productos');
    Route::get('/ventas-productos/{id}', [VentaProductoController::class, 'show'])->name('ventas-productos.show');
    Route::post('/ventas-productos', [VentaProductoController::class, 'store'])->name('ventas-productos.store');
    Route::put('/ventas-productos/{id}', [VentaProductoController::class, 'update'])->name('ventas-productos.update');
    Route::delete('/ventas-productos/{id}', [VentaProductoController::class, 'destroy'])->name('ventas-productos.destroy');

    Route::get('/api/kpi-data', [KPIController::class, 'getKPIData'])->name('api.kpi-data');

    Route::get('/inventarios', function () {
        return Inertia::render('Modules/Inventarios');
    })->name('inventarios');
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
});
Route::get('{any}', function () {
    return redirect()->route('login');
})->where('any', '.*');
