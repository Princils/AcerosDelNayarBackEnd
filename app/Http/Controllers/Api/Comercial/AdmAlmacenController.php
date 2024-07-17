<?php

namespace App\Http\Controllers\Api\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\AdmDocumentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmAlmacenController extends Controller
{
    /**********************************************************************************************************************************************************************************************/
    /*		      Alerta de máximos y mínimos cuando llegan al mínimo de un almacén siempre y cuando, se alerta por almacen.                                					  */
    /*														PRODUCTOS CON ALMACEN AL MINIMO																					  */
    /**********************************************************************************************************************************************************************************************/	
    public function ProductosConAlmacenAlMinimoAlmacen(Request $request)
    {

        // Ejecuta la consulta utilizando la conexión sqlsrvComercial_dinamica
        $respuesta = DB::connection('sqlsrvComercial_dinamica')
            ->select("
                SELECT 
                    p.CCODIGOPRODUCTO as 'Codigo', p.CNOMBREPRODUCTO as 'Producto', alm.CNOMBREALMACEN,
                    ISNULL(ROUND((
                        select
                            SUM(CASE m2.CAFECTAEXISTENCIA WHEN 1 THEN m2.CUNIDADES WHEN 2 THEN (m2.CUNIDADES * (-1)) ELSE 0 END)
                        from admProductos p2  
                            inner join admMovimientos m2 on m2.CIDPRODUCTO = p2.CIDPRODUCTO AND M2.CAFECTADOINVENTARIO != 0  
                            inner join admAlmacenes as alm1 on M2.CIDALMACEN = alm1.CIDALMACEN
                        where 
                            p2.CTIPOPRODUCTO = 1  
                            AND M2.CUNIDADES > 0.01  
                            AND (alm1.CCODIGOALMACEN in (alm.CCODIGOALMACEN))
                            AND M2.CFECHA <= CONVERT(DATETIME, CONVERT(VARCHAR, GETDATE(), 103), 103)
                            and p2.CCODIGOPRODUCTO = P.CCODIGOPRODUCTO 
                        group by p2.CIDPRODUCTO, p2.CCODIGOPRODUCTO, p2.CNOMBREPRODUCTO  
                    ),2),0)	as 'existencia_actual',
                    ISNULL((select SUM(CEXISTENCIAMINBASE) from admMaximosMinimos as mm INNER JOIN admAlmacenes AS alm1 on alm1.CIDALMACEN = mm.CIDALMACEN  where p.CIDPRODUCTO = mm.CIDPRODUCTO AND (alm1.CCODIGOALMACEN in (alm.CCODIGOALMACEN))),0) as 'min',
                    ISNULL((select SUM(CEXISTENCIAMAXBASE) from admMaximosMinimos as mm INNER JOIN admAlmacenes AS alm1 on alm1.CIDALMACEN = mm.CIDALMACEN where p.CIDPRODUCTO = mm.CIDPRODUCTO AND (alm1.CCODIGOALMACEN in (alm.CCODIGOALMACEN))),0) as 'max'
                FROM admProductos as p  
                    INNER JOIN admMovimientos as m  ON p.CIDPRODUCTO = m.CIDPRODUCTO 
                    inner join admAlmacenes as alm on M.CIDALMACEN = alm.CIDALMACEN
                    INNER JOIN admUnidadesMedidaPeso as um ON um.CIDUNIDAD = p.CIDUNIDADBASE  
                    INNER JOIN admDocumentos as d ON d.ciddocumento = m.ciddocumento 
                    INNER JOIN admConceptos con ON d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO AND con.CSISTORIG <> 101 AND con.CCARTAPOR = 0
                WHERE 
                    p.CTIPOPRODUCTO <> 3 AND P.CSTATUSPRODUCTO = 1 
                    AND
                    ISNULL(ROUND((
                        select
                            SUM(CASE m2.CAFECTAEXISTENCIA WHEN 1 THEN m2.CUNIDADES WHEN 2 THEN (m2.CUNIDADES * (-1)) ELSE 0 END)
                        from admProductos p2  
                            inner join admMovimientos m2 on m2.CIDPRODUCTO = p2.CIDPRODUCTO AND M2.CAFECTADOINVENTARIO != 0  
                            inner join admAlmacenes as alm1 on M2.CIDALMACEN = alm1.CIDALMACEN
                        where 
                            p2.CTIPOPRODUCTO = 1  
                            AND M2.CUNIDADES > 0.01  
                            AND (alm1.CCODIGOALMACEN in (alm.CCODIGOALMACEN))
                            AND M2.CFECHA <= CONVERT(DATETIME, CONVERT(VARCHAR, GETDATE(), 103), 103)
                            and p2.CCODIGOPRODUCTO = P.CCODIGOPRODUCTO 
                        group by p2.CIDPRODUCTO, p2.CCODIGOPRODUCTO, p2.CNOMBREPRODUCTO  ),2),0) <
                            ISNULL((select SUM(CEXISTENCIAMINBASE) from admMaximosMinimos as mm INNER JOIN admAlmacenes AS alm1 on alm1.CIDALMACEN = mm.CIDALMACEN  where p.CIDPRODUCTO = mm.CIDPRODUCTO AND (alm1.CCODIGOALMACEN in (alm.CCODIGOALMACEN))),0)
                        GROUP BY um.CABREVIATURA,p.CIMPORTEEXTRA1, p.CIDPRODUCTO, p.CCODIGOPRODUCTO, p.CNOMBREPRODUCTO,alm.CNOMBREALMACEN,alm.CCODIGOALMACEN
                ORDER BY P.CCODIGOPRODUCTO

            ");

        return response()->json([
            'response' => true,
            'message' => $respuesta
        ]);
    }


}
