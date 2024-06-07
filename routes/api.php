<?php

use App\Http\Controllers\Api\Comercial\AdmBitacorasController;
use App\Http\Controllers\Api\Comercial\AdmDocumentosController;
use App\Http\Controllers\Api\Configuracion\ConexionDinamica;
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
| EXTRACCION DE ALERTAS POR FILTRO DE FECHAS
|--------------------------------------------------------------------------
*/
Route::post('alert/ModificacionesDeComprasBitacoras',[AdmBitacorasController::class,'ModificacionesDeComprasBitacoras']);
