<?php
/**
 * Simple test runner for the PHP MVC Template
 */

require_once __DIR__ . '/../vendor/autoload.php';

class TestRunner
{
    private $passed = 0;
    private $failed = 0;
    
    public function test($name, $callback)
    {
        echo "Testing: {$name}... ";
        
        try {
            $result = $callback();
            if ($result) {
                echo "✅ PASSED\n";
                $this->passed++;
            } else {
                echo "❌ FAILED\n";
                $this->failed++;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            $this->failed++;
        }
    }
    
    public function assert($condition, $message = "Assertion failed")
    {
        if (!$condition) {
            throw new Exception($message);
        }
        return true;
    }
    
    public function results()
    {
        echo "\n--- Test Results ---\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Total: " . ($this->passed + $this->failed) . "\n";
        
        return $this->failed === 0;
    }
}

// Configurar entorno de pruebas
$_ENV['APP_DEBUG'] = 'true';
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_NAME'] = 'test_db';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = 'root';

$test = new TestRunner();

// Test 1: Verificar carga de archivos principales
$test->test("Core files exist", function() use ($test) {
    $test->assert(file_exists(__DIR__ . '/../App/Lib/Core.php'), "Core.php not found");
    $test->assert(file_exists(__DIR__ . '/../App/Config/Config.php'), "Config.php not found");
    $test->assert(file_exists(__DIR__ . '/../Public/index.php'), "index.php not found");
    return true;
});

// Test 2: Verificar modelos
$test->test("Models load correctly", function() use ($test) {
    $test->assert(class_exists('App\Models\Users'), "Users model not found");
    $test->assert(class_exists('App\Models\Offices'), "Offices model not found");
    $test->assert(class_exists('App\Models\OfficeUser'), "OfficeUser model not found");
    return true;
});

// Test 3: Verificar conexión a base de datos
$test->test("Database connection", function() use ($test) {
    try {
        $user = new \App\Models\Users();
        $columns = $user->getColumns();
        $test->assert(is_array($columns), "getColumns should return array");
        $test->assert(in_array('id', $columns), "id column should exist");
        return true;
    } catch (Exception $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
});

// Test 4: Verificar helpers
$test->test("Helper functions", function() use ($test) {
    $test->assert(function_exists('config'), "config() function not found");
    $test->assert(function_exists('redirect'), "redirect() function not found");
    $test->assert(function_exists('RandomString'), "RandomString() function not found");
    
    // Test RandomString
    $random1 = RandomString(10);
    $random2 = RandomString(10);
    $test->assert(strlen($random1) === 10, "RandomString should return 10 chars");
    $test->assert($random1 !== $random2, "RandomString should return different values");
    
    return true;
});

// Test 5: Verificar configuración de producción
$test->test("Production configuration", function() use ($test) {
    if (file_exists(__DIR__ . '/../.env')) {
        $env = file_get_contents(__DIR__ . '/../.env');
        
        // En CI, debería tener configuración de prueba
        if (getenv('CI')) {
            $test->assert(strpos($env, 'APP_DEBUG=true') !== false, "Should have debug enabled in CI");
        }
    }
    return true;
});

// Ejecutar tests y mostrar resultados
$success = $test->results();

// Exit code para CI/CD
exit($success ? 0 : 1);
