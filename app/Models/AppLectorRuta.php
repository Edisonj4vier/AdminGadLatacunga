<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppLectorRuta extends Model
{
    use HasFactory;
    protected $table = 'app_lector_ruta';

    protected $fillable = ['id_usuario', 'id_ruta'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }
}
