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
                CFOLIO as Folio, FORMAT(CONVERT(DATETIME, CFECHA, 103), 'dd/MM/yyyy') as Fecha, 
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
                CFOLIO as Folio, FORMAT(CONVERT(DATETIME, CFECHA, 103), 'dd/MM/yyyy') as Fecha, 
                CRAZONSOCIAL as cliente, 
                CUSUARIO as usuario,
                FORMAT(CONVERT(DATETIME, CFECHAEXTRA, 103), 'dd/MM/yyyy') as FechaExtra,
                FORMAT(CONVERT(DATETIME, CFECHAPRONTOPAGO, 103), 'dd/MM/yyyy') as FechaProntoPago
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


/***************************************************************************************************************/
/*	Alerta de facturas vencidas de proveedores “compras” , campo de fecha de vencimiento , alertar un dia antes*/
/*								   		Facturas_Vencidas_Dia_Antes											   */
/***************************************************************************************************************/

public function FacturasVencidasDiaAntes(Request $request)
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
                FORMAT(CONVERT(DATETIME, CFECHA, 103), 'dd/MM/yyyy') as Alta,
                CSERIEDOCUMENTO, CFOLIO, CRAZONSOCIAL, 
                FORMAT(CONVERT(DATETIME,CFECHAVENCIMIENTO, 103), 'dd/MM/yyyy') AS Vencimiento, ROUND(CTOTAL,2) as Total, ROUND(CPENDIENTE,2) as Pendiente
            FROM admDocumentos WHERE CIDDOCUMENTODE = 19  AND CPENDIENTE > 0  AND CCANCELADO = 0   AND CDEVUELTO = 0
                AND CFECHAVENCIMIENTO >= CONVERT(datetime,?,21)
                AND CFECHAVENCIMIENTO <= CONVERT(datetime,?,21)
            ORDER BY CFECHAVENCIMIENTO DESC
        ", [$fechainicio, $fechafin]);

    return response()->json([
        'response' => true,
        'message' => $respuesta
    ]);
}

/**********************************************************************************************************************************************************************************************/
/*										Revisión de que todas las facturas esten pagadas que tengan la forma de pago distinta a 99 de lo contrario alerta											  */
/*                                                                  FacturasConPago99YConPPD                                                                                              */    
/**********************************************************************************************************************************************************************************************/

public function FacturasConPago99YConPPD(Request $request)
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
                FORMAT(CONVERT(DATETIME, CFECHA, 103), 'dd/MM/yyyy') as Alta,
                CSERIEDOCUMENTO, CFOLIO, CRAZONSOCIAL, 
                FORMAT(CONVERT(DATETIME,CFECHAVENCIMIENTO, 103), 'dd/MM/yyyy') AS Vencimiento, 
                ROUND(CTOTAL,2) as Total, 
                ROUND(CPENDIENTE,2) as Pendiente
            FROM admDocumentos 
            WHERE 
                CMETODOPAG = 99 
                --ME FALTO AGREGARLE EL METODO PAGO PERO NO PUDE ENCONTRAR EL CAMPO PARA EL PPD
                AND CCANCELADO = 0 AND CDEVUELTO = 0 AND CPENDIENTE != 0 --NOSE SI CPENDIENTE APLIQUE YA QUE SE ESTA REVISANDO FACTURAS QUE TENGAS LOS 2 CAMPOS
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
/*										Revisión de que todas las facturas esten pagadas que tengan la forma de pago distinta a 99 de lo contrario alerta											  */
/*											FACTURAS PAGADAS CON FORMA DE PAGO DISTINTA A 99                                                                        */
/**********************************************************************************************************************************************************************************************/


public function FacturasConFormaPagoDistintaA99(Request $request)
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
                FORMAT(CONVERT(DATETIME, CFECHA, 103), 'dd/MM/yyyy') as Alta,
                CSERIEDOCUMENTO, CFOLIO, CRAZONSOCIAL, 
                FORMAT(CONVERT(DATETIME,CFECHAVENCIMIENTO, 103), 'dd/MM/yyyy') AS Vencimiento, 
                ROUND(CTOTAL,2) as Total, 
                ROUND(CPENDIENTE,2) as Pendiente
            FROM admDocumentos 
            WHERE 
                CMETODOPAG != 99 
                AND CIDDOCUMENTODE = 4 --CIDDOCUMENTO 4 = FACTURA
                AND CCANCELADO = 0 AND CDEVUELTO = 0 AND CPENDIENTE != 0 
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
