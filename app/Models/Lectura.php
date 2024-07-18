<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lectura extends Model
{
    protected $table = 'aapplectura';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'numcuenta', 'anio', 'mes', 'lectura', 'observacion',
        'lecturaanterior', 'consumo', 'nromedidor', 'ciu'
    ];
}
