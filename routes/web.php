<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppLectorRutaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsumoLecturaController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// ---------Login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// ---------Auth
Route::middleware(['auth.token'])->group(function () {
    Route::get('/app-lector-ruta', [AppLectorRutaController::class, 'index'])->name('app-lector-ruta.index');
    Route::post('/app-lector-ruta', [AppLectorRutaController::class, 'store'])->name('app-lector-ruta.store');
    Route::get('/app-lector-ruta/{id}/edit', [AppLectorRutaController::class, 'edit'])->name('app-lector-ruta.edit');
    Route::put('/app-lector-ruta/{id}', [AppLectorRutaController::class, 'update'])->name('app-lector-ruta.update');
    Route::delete('/app-lector-ruta/{id}', [AppLectorRutaController::class, 'destroy'])->name('app-lector-ruta.destroy');
});
//------Lecturas
Route::get('/lecturas', [ConsumoLecturaController::class, 'index'])->name('lecturas.index');
Route::post('/lecturas/sincronizar', [ConsumoLecturaController::class, 'actualizarAAPPLectura'])->name('lecturas.sincronizar');
Route::delete('/lecturas/{cuenta}', [ConsumoLecturaController::class, 'destroy'])->name('lecturas.destroy');
Route::get('/lecturas/{cuenta}/edit', [ConsumoLecturaController::class, 'edit'])->name('lecturas.edit');
Route::put('/lecturas/{cuenta}', [ConsumoLecturaController::class, 'update'])->name('lecturas.update');

Route::get('/test-referrer', function () {
    return view('test-referrer');
});
