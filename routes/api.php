<?php

use App\Http\Controllers\Api\Comercial\AdmAlmacenController;
use App\Http\Controllers\Api\Comercial\AdmBitacorasController;
use App\Http\Controllers\Api\Comercial\AdmDocumentosController;
use App\Http\Controllers\Api\Configuracion\ConexionDinamica;
use App\Models\Comercial\AdmDocumentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/*
|--------------------------------------------------------------------------
| CONFIGURACION CAMBIO DE BD
|--------------------------------------------------------------------------
*/
Route::post('conexionDinamica',[ConexionDinamica::class,'CambiarConexion']);


/*
|--------------------------------------------------------------------------
| EXTRACCCION COMPLETA DE TABLAS
|--------------------------------------------------------------------------
*/
Route::get('documentos',[AdmDocumentosController::class,'index']);


/*
|--------------------------------------------------------------------------
| EXTRACCION DE ALERTAS COMPLETAS
|--------------------------------------------------------------------------
*/
Route::get('alert/FacturasProveedoresProximasAVencerCompras',[AdmDocumentosController::class,'FacturasProveedoresProximasAVencerCompras']);


/*
|--------------------------------------------------------------------------
| EXTRACCION DE ALERTAS PARA REPORTES DE BITACORA
|--------------------------------------------------------------------------
*/
Route::post('alert/ModificacionesDeComprasBitacoras',[AdmBitacorasController::class,'ModificacionesDeComprasBitacoras']);
Route::post('alert/CancelacionesDeComprasBitacora',[AdmBitacorasController::class,'CancelacionesDeComprasBitacora']);
Route::post('alert/CancelacionesDeFacturasRemisionNotadeventaBitacora',[AdmBitacorasController::class,'CancelacionesDeFacturasRemisionNotadeventaBitacora']);
Route::post('alert/EliminacionDeDocumentosNotasRemisionesBitacora',[AdmBitacorasController::class,'EliminacionDeDocumentosNotasRemisionesBitacora']);



/*
|--------------------------------------------------------------------------
| EXTRACCION DE ALERTAS PARA REPORTES DE DOCUMENTOS
|--------------------------------------------------------------------------
*/
Route::post('alert/documentos/ComprasSinGastosSobreCompras',[AdmDocumentosController::class,'ComprasSinGastosSobreComprasDocumentos']);
Route::post('alert/documentos/ComprasSinFechaDescuentoProntoPagoDocumentos',[AdmDocumentosController::class,'ComprasSinFechaDescuentoProntoPagoDocumentos']);
Route::post('alert/documentos/FacturasVencidasDiaAntes',[AdmDocumentosController::class,'FacturasVencidasDiaAntes']);

/*
|--------------------------------------------------------------------------
| EXTRACCION DE ALERTAS PARA REPORTES DE ALMACEN
|--------------------------------------------------------------------------
*/

Route::post('alert/Almacen/ProductosConAlmacenAlMinimoAlmacen',[AdmAlmacenController::class,'ProductosConAlmacenAlMinimoAlmacen']);
