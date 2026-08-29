<?php
namespace App\Helpers;

use App\Models\TipoCombustible;
use Illuminate\Http\Request;

class Helper{

    public static function validateTipoCombustible(Request $request):bool
    {
        $tipoCombustible = TipoCombustible::where('id', $request->input('tipo_combustible_id'))
                                          ->where('centro_costo_id', $request->input('centro_costo_id'))
                                          ->exists();

        if (!$tipoCombustible) {
            return false;
        }

        return true;
    }
}
