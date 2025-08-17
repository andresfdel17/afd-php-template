<?php

/**
 * Health Check Controller
 * Endpoint para verificar el estado de la aplicación
 * Útil para CI/CD y monitoreo
 */
class Health extends Controller
{
    public function index()
    {
        header('Content-Type: application/json');
        
        $status = [
            'status' => 'ok',
            'timestamp' => date('c'),
            'version' => '1.0.0',
            'framework' => 'PHP MVC Template',
            'checks' => []
        ];
        
        // Check database connection
        try {
            \App\Models\Users::count();
            $status['checks']['database'] = 'ok';
        } catch (Exception $e) {
            $status['checks']['database'] = 'error';
            $status['status'] = 'error';
            $status['errors'][] = 'Database connection failed';
        }
        
        // Check disk space
        try {
            $freeSpace = disk_free_space('.');
            $totalSpace = disk_total_space('.');
            $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
            
            $status['checks']['disk'] = $usagePercent < 90 ? 'ok' : 'warning';
            $status['disk_usage'] = round($usagePercent, 2) . '%';
            
            if ($usagePercent >= 95) {
                $status['status'] = 'error';
                $status['errors'][] = 'Disk space critically low';
            }
        } catch (Exception $e) {
            $status['checks']['disk'] = 'error';
        }
        
        // Check uploads directory
        $uploadsWritable = is_writable(RUTA_UPLOAD);
        $status['checks']['uploads'] = $uploadsWritable ? 'ok' : 'error';
        
        if (!$uploadsWritable) {
            $status['status'] = 'error';
            $status['errors'][] = 'Uploads directory not writable';
        }
        
        // Check cache directory
        $cacheDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'cache';
        $cacheWritable = is_writable($cacheDir);
        $status['checks']['cache'] = $cacheWritable ? 'ok' : 'error';
        
        if (!$cacheWritable) {
            $status['status'] = 'error';
            $status['errors'][] = 'Cache directory not writable';
        }
        
        // Check PHP version
        $phpVersion = PHP_VERSION;
        $status['checks']['php_version'] = version_compare($phpVersion, '8.0.0', '>=') ? 'ok' : 'warning';
        $status['php_version'] = $phpVersion;
        
        // Check configuration
        $config = config();
        $status['checks']['config'] = 'ok';
        
        // In production, debug should be false
        if (isset($config->APP_DEBUG) && $config->APP_DEBUG === 'true' && 
            isset($config->APP_URL) && strpos($config->APP_URL, 'localhost') === false) {
            $status['checks']['config'] = 'warning';
            $status['warnings'][] = 'Debug mode enabled in production';
        }
        
        // Response code based on status
        http_response_code($status['status'] === 'ok' ? 200 : 503);
        
        echo json_encode($status, JSON_PRETTY_PRINT);
    }
    
    /**
     * Health check específico para base de datos
     */
    public function database()
    {
        header('Content-Type: application/json');
        
        try {
            $start = microtime(true);
            
            // Test basic connection
            $userCount = \App\Models\Users::count();
            $officeCount = \App\Models\Offices::count();
            $assignmentCount = \App\Models\OfficeUser::count();
            
            $end = microtime(true);
            $queryTime = round(($end - $start) * 1000, 2);
            
            $response = [
                'status' => 'ok',
                'query_time_ms' => $queryTime,
                'counts' => [
                    'users' => $userCount,
                    'offices' => $officeCount,
                    'assignments' => $assignmentCount
                ],
                'timestamp' => date('c')
            ];
            
            http_response_code(200);
            
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ];
            
            http_response_code(503);
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}
