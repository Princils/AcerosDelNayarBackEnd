<?php
namespace App\Http\Controllers\Api\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmBitacorasController extends Controller
{
    /* 	Alerta de modificación de compras se basa en tabla de bicoras	 */
    public function ModificacionesDeComprasBitacoras(Request $request)
    {
        $request->validate([
            'fechainicio' => 'required|date',
            'fechafin' => 'required|date',
        ]);

        $fechainicio = $request->input('fechainicio');
        $fechafin = $request->input('fechafin');

        // Ensure fechafin includes the entire day by setting it to the end of the day
        $fechafin = date('Y-m-d 23:59:59', strtotime($fechafin));

        // Ejecuta la consulta utilizando la conexión sqlsrvComercial_dinamica
        $respuesta = DB::connection('sqlsrvComercial_dinamica')
            ->select("
                SELECT 
                    *
                FROM admBitacoras
                WHERE CTEXTOEX01 LIKE '%COMPRA%'
                AND PROCESO LIKE 'M%'
                AND FECHA >= CONVERT(datetime, ?, 21)
                AND FECHA <= CONVERT(datetime, ?, 21)
                ORDER BY IDBITACORA DESC;
            ", [$fechainicio, $fechafin]);

        return response()->json([
            'response' => true,
            'message' => $respuesta
        ]);
    }

}