<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConsumoLecturaController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/consumos', [ConsumoLecturaController::class, 'index']);
Route::get('/consumos/{cuenta}', [ConsumoLecturaController::class, 'show']);


Route::get('/test-referrer-policy', function () {
    return response()->json(['message' => 'Referrer Policy is set to strict-origin-when-cross-origin']);
});
