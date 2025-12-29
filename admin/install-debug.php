<?php
// Disable all output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Immediate output
echo "1";
flush();
echo "2";
flush();

// Set handlers
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL) {
        echo "<h1>Fatal Error Detected</h1>";
        echo "<pre>";
        print_r($error);
        echo "</pre>";
    }
});

echo "3";
flush();

echo "<!DOCTYPE html><html><head><title>Debug</title></head><body>";
echo "<h1>YOURLS Install Debug</h1>";
echo "<p>If you see this, PHP is working.</p>";
flush();

echo "<h2>Step 1: Constants</h2>";
flush();
try {
    define('YOURLS_ADMIN', true);
    define('YOURLS_INSTALLING', true);
    echo "<p style='color:green'>✓ Constants defined</p>";
    flush();
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    flush();
    die();
}

echo "<h2>Step 2: File Check</h2>";
flush();
$load_file = dirname(__DIR__) . '/includes/load-yourls.php';
echo "<p>File: <code>$load_file</code></p>";
flush();

if (!file_exists($load_file)) {
    die("<p style='color:red'>File not found!</p>");
}
echo "<p style='color:green'>✓ File exists</p>";
flush();

echo "<h2>Step 3: Loading YOURLS</h2>";
flush();

// Try to load with output at each step
echo "<p>Starting require_once...</p>";
flush();

try {
    require_once($load_file);
    echo "<p style='color:green'>✓ YOURLS loaded!</p>";
    flush();
} catch (ParseError $e) {
    echo "<h2 style='color:red'>Parse Error!</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    flush();
    die();
} catch (Error $e) {
    echo "<h2 style='color:red'>Fatal Error!</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    flush();
    die();
} catch (Throwable $e) {
    echo "<h2 style='color:red'>Throwable Error!</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    flush();
    die();
}

echo "<h2>Step 4: Checking Functions</h2>";
flush();

if (function_exists('yourls_check_PDO')) {
    echo "<p style='color:green'>✓ yourls_check_PDO exists</p>";
} else {
    echo "<p style='color:red'>✗ yourls_check_PDO NOT found</p>";
}
flush();

echo "<hr><p><strong>If you see this message, YOURLS loaded successfully!</strong></p>";
echo "<p><a href='install.php'>Continue to Install Page</a></p>";
flush();

echo "</body></html>";

