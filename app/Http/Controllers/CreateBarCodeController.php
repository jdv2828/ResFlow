<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Milon\Barcode\DNS2D;

class CreateBarCodeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $d = new DNS2D();
        $d->setStorPath(__DIR__ . '/cache/');
        $barcode = $d->getBarcodeHTML('https://github.com/milon/barcod', 'PDF417');

        // Pasa el código de barras y cualquier otra información relevante a la vista
        return response('<html><body><img src="data:image/png,' . $barcode . '"></body></html>');
    }
}
