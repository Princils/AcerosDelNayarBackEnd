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


/**********************************************************************************************************************************************************************************************/
/*		                    	Alerta si se capturo una compra sin gastos sobre compra “campo gastos sobre compra”, si este vacío alertará                                 					  */
/*														Compra sin gasto sobre compra																				  */
/**********************************************************************************************************************************************************************************************/	
public function ComprasSinGastosSobreComprasDocumentos(Request $request)
{
    // Si no existen, asigna la fecha actual a fechainicio y fechafin
    $hoy = now()->format('Y-m-d');

    $fechainicio = $request->input('fechainicio', $hoy);
    $fechafin = $request->input('fechafin', $hoy);

    // Realiza la validación de las fechas
    $request->validate([
        'fechainicio' => 'required|date',
        'fechafin' => 'required|date',
    ]);

    // Asegura que fechafin incluya el final del día
    $fechafin = date('Y-m-d 23:59:59', strtotime($fechafin));

    // Ejecuta la consulta utilizando la conexión sqlsrvComercial_dinamica
    $respuesta = DB::connection('sqlsrvComercial_dinamica')
        ->select("
            SELECT 
                CSERIEDOCUMENTO as Serie, 
                CFOLIO as Folio, FORMAT(CONVERT(DATETIME, CFECHA, 103), 'yyyy/MM/dd') as Fecha, 
                CRAZONSOCIAL as cliente, 
                CUSUARIO as usuario
            FROM admDocumentos 
            WHERE 
                CIDDOCUMENTODE = 19  
                AND (CGASTO1 = 0  AND CGASTO2 = 0 AND CGASTO3 = 0)
                AND CCANCELADO = 0 AND CDEVUELTO = 0 
                AND CFECHA >= CONVERT(datetime,?,21)
                AND CFECHA <= CONVERT(datetime,?,21)
            ORDER BY CFECHA DESC
        ", [$fechainicio, $fechafin]);


    return response()->json([
        'response' => true,
        'message' => $respuesta
    ]);
}

/**********************************************************************************************************************************************************************************************/
/*		                    	Alerta si se capturo y cerro una compra sin fecha descuento pronto pago “fecha extra 1”                                 					  */
/*														Compras sin fecha descuento por pronto pago																				  */
/**********************************************************************************************************************************************************************************************/	
public function ComprasSinFechaDescuentoProntoPagoDocumentos(Request $request)
{
    // Si no existen, asigna la fecha actual a fechainicio y fechafin
    $hoy = now()->format('Y-m-d');

    $fechainicio = $request->input('fechainicio', $hoy);
    $fechafin = $request->input('fechafin', $hoy);

    // Realiza la validación de las fechas
    $request->validate([
        'fechainicio' => 'required|date',
        'fechafin' => 'required|date',
    ]);

    // Asegura que fechafin incluya el final del día
    $fechafin = date('Y-m-d 23:59:59', strtotime($fechafin));

    // Ejecuta la consulta utilizando la conexión sqlsrvComercial_dinamica
    $respuesta = DB::connection('sqlsrvComercial_dinamica')
        ->select("
            SELECT 
                CSERIEDOCUMENTO as Serie, 
                CFOLIO as Folio, FORMAT(CONVERT(DATETIME, CFECHA, 103), 'yyyy/MM/dd') as Fecha, 
                CRAZONSOCIAL as cliente, 
                CUSUARIO as usuario,
                FORMAT(CONVERT(DATETIME, CFECHAEXTRA, 103), 'yyyy/MM/dd') as FechaExtra,
                FORMAT(CONVERT(DATETIME, CFECHAPRONTOPAGO, 103), 'yyyy/MM/dd') as FechaProntoPago
            FROM admDocumentos 
            WHERE 
                CIDDOCUMENTODE = 19  
                AND (CFECHAEXTRA = '' OR CFECHAEXTRA < CONVERT(datetime,'2000-01-01',21)) 
                AND CCANCELADO = 0 AND CDEVUELTO = 0
                AND CFECHA >= CONVERT(datetime,?,21)
                AND CFECHA <= CONVERT(datetime,?,21)
            ORDER BY CFECHA DESC
        ", [$fechainicio, $fechafin]);

    return response()->json([
        'response' => true,
        'message' => $respuesta
    ]);
}


}
