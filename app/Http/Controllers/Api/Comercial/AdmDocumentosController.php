<?php

namespace App\Http\Controllers\Api\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\AdmDocumentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmDocumentosController extends Controller
{

    public function index()
    {
        // Especifica la conexión sqlsrvComercial_dinamica
        $respuesta = DB::connection('sqlsrvComercial_dinamica')
                        ->table('admDocumentos')
                        ->orderBy('CIDDOCUMENTO', 'desc')
                        ->paginate(10);
        
        return response()->json([
            'response' => true,
            'message' => $respuesta
        ]);
    }


    /*	Alerta de facturas vencidas de proveedores “compras” , alertar un dia antes*/
    public function FacturasProveedoresProximasAVencerCompras()
    {
        // Ejecuta la consulta utilizando la conexión sqlsrvComercial_dinamica
        $respuesta = DB::connection('sqlsrvComercial_dinamica')
                        ->select("SELECT * FROM admDocumentos WHERE CIDDOCUMENTODE = 19 AND CFECHAVENCIMIENTO = DATEADD(day, 1, CAST(GETDATE() AS DATE)) ORDER BY CIDDOCUMENTO DESC");
        
        return response()->json([
            'response' => true,
            'message' => $respuesta
        ]);
    }

}
