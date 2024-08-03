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
// ---------Auth
Route::middleware(['auth.token'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'getUser'])->name('user');
    //------App Lector Ruta
    Route::get('/app-lector-ruta', [AppLectorRutaController::class, 'index'])->name('app-lector-ruta.index');
    Route::post('/app-lector-ruta', [AppLectorRutaController::class, 'store'])->name('app-lector-ruta.store');
    Route::get('/app-lector-ruta/{username}/{id_ruta}/edit', [AppLectorRutaController::class, 'edit'])->name('app-lector-ruta.edit');
    Route::put('/app-lector-ruta/{username}/{id_ruta}', [AppLectorRutaController::class, 'update'])->name('app-lector-ruta.update');
    Route::delete('/app-lector-ruta/{username}/{id_ruta}', [AppLectorRutaController::class, 'destroy'])->name('app-lector-ruta.destroy');
//------Lecturas
    Route::get('/lecturas', [ConsumoLecturaController::class, 'index'])->name('lecturas.index');
    Route::post('/lecturas/actualizar', [ConsumoLecturaController::class, 'actualizarLecturas']);
    Route::delete('/lecturas/{cuenta}', [ConsumoLecturaController::class, 'destroy'])->name('lecturas.destroy');
    Route::get('/lecturas/{cuenta}/edit', [ConsumoLecturaController::class, 'edit'])->name('lecturas.edit');
    Route::put('/lecturas/{cuenta}', [ConsumoLecturaController::class, 'update'])->name('lecturas.update');
    Route::get('/lecturas/{cuenta}', [ConsumoLecturaController::class, 'show']);
    Route::post('/lecturas/copiar-evidencias', [ConsumoLecturaController::class, 'copiarEvidencias']);
    //----------Configuracion


});
// Ruta de fallback para manejar 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
