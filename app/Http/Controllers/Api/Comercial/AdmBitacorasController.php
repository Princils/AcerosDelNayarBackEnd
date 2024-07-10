<?php
namespace App\Http\Controllers\Api\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmBitacorasController extends Controller
{
    /**Alerta de modificación de compras se basa en tabla de bicoras */ 		
    public function ModificacionesDeComprasBitacoras(Request $request)
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
                    IDBITACORA, 
                    FORMAT(CONVERT(DATETIME, FECHA, 103), 'yyyy/MM/dd') as FECHA,
                    RIGHT('0' + LEFT(HORA, LEN(HORA) - 2), 2) + ':' + RIGHT(HORA, 2) AS Hora,
                    USUARIO, 
                    PROCESO, DATOS
                FROM admBitacoras
                WHERE CTEXTOEX01 LIKE '%COMPRA%'
                AND PROCESO LIKE 'M%'
                AND FECHA >= CONVERT(datetime, ?, 21)
                AND FECHA <= CONVERT(datetime, ?, 21)
                ORDER BY FECHA DESC;
            ", [$fechainicio, $fechafin]);


        return response()->json([
            'response' => true,
            'message' => $respuesta
        ]);
    }

    /**Alerta de modificación de compras se basa en tabla de bicoras */ 		
    public function CancelacionesDeComprasBitacora(Request $request)
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
                    USUARIO,
                    FORMAT(CONVERT(DATETIME, FECHA, 103), 'yyyy/MM/dd') as Fecha,
                    RIGHT('0' + LEFT(HORA, LEN(HORA) - 2), 2) + ':' + RIGHT(HORA, 2) AS Hora,
                    DATOS
                FROM admBitacoras  
                where 
                    CTEXTOEX01 LIKE '%COMPRA%' 
                    AND PROCESO LIKE '%cancelado%'  
                    AND FECHA >= CONVERT(datetime,?,21)
                    AND FECHA <= CONVERT(datetime,?,21)
                ORDER BY FECHA DESC
            ", [$fechainicio, $fechafin]);


        return response()->json([
            'response' => true,
            'message' => $respuesta
        ]);
    }


/**********************************************************************************************************************************************************************************************/
/*			Un historial de cancelaciones, Pero con la alerta de quien lo realice, y la notificación de la observación de los documentos (factura, remisión, nota de venta)					  */
/*														consultar desde el edo del documento o bien la botacora																				  */
/*																	CANCELACIONES DE FACTURA-REMISION-NOTAD DE VENTA																												*/
/**********************************************************************************************************************************************************************************************/	
    public function CancelacionesDeFacturasRemisionNotadeventaBitacora(Request $request)
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
                    IDBITACORA, 
                    FORMAT(CONVERT(DATETIME, FECHA, 103), 'yyyy/MM/dd') as Fecha,
                    CONVERT(varchar(5), HORA, 108) as Hora,
                    USUARIO, 
                    PROCESO, 
                    DATOS
                FROM admBitacoras
                WHERE PROCESO LIKE '%CANCELA%'
                    AND (DATOS LIKE '%REMISION%' OR DATOS LIKE '%NOTA%' OR DATOS LIKE '%factura%' ) 
                    AND FECHA >= CONVERT(datetime,?,21)
                    AND FECHA <= CONVERT(datetime,?,21)
                ORDER BY FECHA DESC
            ", [$fechainicio, $fechafin]);


        return response()->json([
            'response' => true,
            'message' => $respuesta
        ]);
    }
    
/**********************************************************************************************************************************************************************************************/
/*			Historial de eliminación de documentos, datos de bitácora detallado, y notificación de alerta. Notificar si eliminaron notas, o remisiones “desde la bitácora					  */
/*																ELIMINACION DE DOCUMENTOS NOTAS REMISIONES																												*/
/**********************************************************************************************************************************************************************************************/	
public function EliminacionDeDocumentosNotasRemisionesBitacora(Request $request)
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
                IDBITACORA as ID,
                FORMAT(CONVERT(DATETIME, FECHA, 103), 'yyyy/MM/dd') as Fecha,
                CONVERT(varchar(5), HORA, 108) as Hora,
                USUARIO as Usuario,
                DATOS as Datos,
                PROCESO as proceso
            FROM admBitacoras
            WHERE PROCESO LIKE '%BORRADO%'
                AND (DATOS LIKE '%REMISION%' OR DATOS LIKE '%NOTA%' )
                AND FECHA >= CONVERT(datetime,?,21)
                AND FECHA <= CONVERT(datetime,?,21)
            ORDER BY FECHA DESC
        ", [$fechainicio, $fechafin]);


    return response()->json([
        'response' => true,
        'message' => $respuesta
    ]);
}

   
}