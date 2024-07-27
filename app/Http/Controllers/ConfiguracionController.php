<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configuracion;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $rangoUnidades = Configuracion::where('clave', 'rango_unidades')->first()->valor ?? 2;
        return view('configuracion.index', compact('rangoUnidades'));
    }

    public function updateRango(Request $request)
    {
        $request->validate([
            'rango_unidades' => 'required|numeric|min:0',
        ]);

        Configuracion::updateOrCreate(
            ['clave' => 'rango_unidades'],
            ['valor' => $request->rango_unidades]
        );

        return redirect()->route('configuracion.index')->with('success', 'Rango de unidades actualizado con éxito.');
    }
}
