<?php

namespace App\Http\Controllers\Api\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\File;


class ConexionDinamica extends Controller
{
    public function CambiarConexion(Request $request)
    {
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'server' => 'required|string',
            'puerto' => 'required|string',
            'database' => 'required|string',
            'userbd' => 'required|string',
            'password' => 'required|string',
        ]);
    
        $validatedData['server'] = urldecode($validatedData['server']);
        $validatedData['puerto'] = ($validatedData['puerto'] != '' ? '55632' : $validatedData['puerto']);
    
        try {
            // Intenta conectar con los nuevos datos
            $pdo = new \PDO("sqlsrv:Server={$validatedData['server']},{$validatedData['puerto']};Database={$validatedData['database']}", $validatedData['userbd'], $validatedData['password']);
    
            // Si la conexión tiene éxito, actualiza la configuración
            $configPath = config_path('database.php');
            $config = File::get($configPath);
    
            $newConfig = preg_replace('/\'sqlsrvComercial_dinamica\' => \[\s*(.*?)\s*\]/s', "'sqlsrvComercial_dinamica' => [
                'driver' => 'sqlsrv',
                'url' => env('DATABASE_URL'),
                'host' => '{$validatedData['server']}',
                'port' => '{$validatedData['puerto']}',
                'database' => '{$validatedData['database']}',
                'username' => '{$validatedData['userbd']}',
                'password' => '{$validatedData['password']}',
                'charset' => 'utf8',
                'prefix' => '',
                'prefix_indexes' => true,
            ]", $config);
    
            File::put($configPath, $newConfig);
    
            // Retorna una respuesta exitosa
            return response()->json([
                'response' => true,
                'message' => 'Configuración actualizada correctamente',
                'pdo' => $pdo,
            ], 200);
        } catch (\Exception $e) {
            // Si hay un error al conectar, devuelve un error
            return response()->json([
                'response' => false,
                'errors' => 'Error al verificar el servidor: ' . $e->getMessage()
            ], 400);
        }
    }
    
}
