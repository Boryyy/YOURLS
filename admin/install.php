<?php
// Enable error reporting for debugging - MUST be first
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Set error handler to catch everything
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<div style='background:#ffcccc;padding:20px;border:2px solid red;margin:20px;'>";
    echo "<h2>PHP Error Caught</h2>";
    echo "<p><strong>Error:</strong> $errstr</p>";
    echo "<p><strong>File:</strong> $errfile</p>";
    echo "<p><strong>Line:</strong> $errline</p>";
    echo "</div>";
    return false; // Let PHP handle it normally too
});

// Set exception handler
set_exception_handler(function($exception) {
    echo "<div style='background:#ffcccc;padding:20px;border:2px solid red;margin:20px;'>";
    echo "<h2>Uncaught Exception</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    echo "</div>";
});

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

echo "<!DOCTYPE html><html><head><title>YOURLS Install Debug</title></head><body>";
echo "<h1>YOURLS Installation Debug</h1>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current file: " . __FILE__ . "</p>";
echo "<hr>";

echo "<h2>Step 1: Defining constants</h2>";
try {
    define( 'YOURLS_ADMIN', true );
    echo "<p style='color:green'>✓ YOURLS_ADMIN defined</p>";
    define( 'YOURLS_INSTALLING', true );
    echo "<p style='color:green'>✓ YOURLS_INSTALLING defined</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error defining constants: " . htmlspecialchars($e->getMessage()) . "</p>";
    die();
}

echo "<h2>Step 2: Checking load-yourls.php file</h2>";
$load_file = dirname( __DIR__ ).'/includes/load-yourls.php';
echo "<p>Looking for: <code>$load_file</code></p>";

if (!file_exists($load_file)) {
    die("<p style='color:red;font-size:18px;'><strong>ERROR: Cannot find load-yourls.php at: $load_file</strong></p>");
}
echo "<p style='color:green'>✓ File exists</p>";

echo "<h2>Step 3: Loading YOURLS</h2>";
echo "<p>Attempting to require: <code>$load_file</code></p>";

try {
    require_once( $load_file );
    echo "<p style='color:green'>✓ YOURLS loaded successfully</p>";
} catch (ParseError $e) {
    echo "<p style='color:red;font-size:18px;'><strong>Parse Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    die();
} catch (Error $e) {
    echo "<p style='color:red;font-size:18px;'><strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    die();
} catch (Throwable $e) {
    echo "<p style='color:red;font-size:18px;'><strong>Throwable Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    die();
} catch (Exception $e) {
    echo "<p style='color:red;font-size:18px;'><strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    die();
}

echo "<hr><h2>Step 4: YOURLS loaded, continuing...</h2>";

echo "<!-- DEBUG: Initializing arrays -->\n";
$error   = array();
$warning = array();
$success = array();

// Check pre-requisites
echo "<!-- DEBUG: Checking prerequisites -->\n";
try {
    if ( !function_exists('yourls_check_PDO') ) {
        $error[] = 'Function yourls_check_PDO() not found';
        echo "<!-- DEBUG: ERROR - yourls_check_PDO function not found -->\n";
    } else {
        if ( !yourls_check_PDO() ) {
            $error[] = yourls__( 'PHP extension for PDO not found' );
            yourls_debug_log( 'PHP PDO extension not found' );
            echo "<!-- DEBUG: PDO check failed -->\n";
        } else {
            echo "<!-- DEBUG: PDO check passed -->\n";
        }
    }
} catch (Exception $e) {
    $error[] = 'Error checking PDO: ' . $e->getMessage();
    echo "<!-- DEBUG: Exception checking PDO: " . htmlspecialchars($e->getMessage()) . " -->\n";
}

try {
    if ( !function_exists('yourls_check_database_version') ) {
        $error[] = 'Function yourls_check_database_version() not found';
        echo "<!-- DEBUG: ERROR - yourls_check_database_version function not found -->\n";
    } else {
        if ( !yourls_check_database_version() ) {
            $error[] = yourls_s( '%s version is too old. Ask your server admin for an upgrade.', 'MySQL' );
            yourls_debug_log( 'MySQL version: ' . yourls_get_database_version() );
            echo "<!-- DEBUG: Database version check failed -->\n";
        } else {
            echo "<!-- DEBUG: Database version check passed -->\n";
        }
    }
} catch (Exception $e) {
    $error[] = 'Error checking database version: ' . $e->getMessage();
    echo "<!-- DEBUG: Exception checking database version: " . htmlspecialchars($e->getMessage()) . " -->\n";
}

try {
    if ( !function_exists('yourls_check_php_version') ) {
        $error[] = 'Function yourls_check_php_version() not found';
        echo "<!-- DEBUG: ERROR - yourls_check_php_version function not found -->\n";
    } else {
        if ( !yourls_check_php_version() ) {
            $error[] = yourls_s( '%s version is too old. Ask your server admin for an upgrade.', 'PHP' );
            yourls_debug_log( 'PHP version: ' . PHP_VERSION );
            echo "<!-- DEBUG: PHP version check failed -->\n";
        } else {
            echo "<!-- DEBUG: PHP version check passed -->\n";
        }
    }
} catch (Exception $e) {
    $error[] = 'Error checking PHP version: ' . $e->getMessage();
    echo "<!-- DEBUG: Exception checking PHP version: " . htmlspecialchars($e->getMessage()) . " -->\n";
}

// Is YOURLS already installed ?
echo "<!-- DEBUG: Checking if YOURLS is installed -->\n";
try {
    if ( !function_exists('yourls_is_installed') ) {
        echo "<!-- DEBUG: ERROR - yourls_is_installed function not found -->\n";
    } else {
        $is_installed = yourls_is_installed();
        echo "<!-- DEBUG: yourls_is_installed() returned: " . ($is_installed ? 'true' : 'false') . " -->\n";
        if ( $is_installed ) {
            $error[] = yourls__( 'YOURLS already installed.' );
            // check if .htaccess exists, recreate otherwise. No error checking.
            if( !file_exists( YOURLS_ABSPATH.'/.htaccess' ) ) {
                yourls_create_htaccess();
            }
        }
    }
} catch (Exception $e) {
    $error[] = 'Error checking installation status: ' . $e->getMessage();
    echo "<!-- DEBUG: Exception checking installation: " . htmlspecialchars($e->getMessage()) . " -->\n";
}

// Start install if possible and needed
echo "<!-- DEBUG: Checking install request -->\n";
echo "<!-- DEBUG: _REQUEST['install'] = " . (isset($_REQUEST['install']) ? 'set' : 'not set') . " -->\n";
echo "<!-- DEBUG: Error count = " . count($error) . " -->\n";

if ( isset($_REQUEST['install']) && count( $error ) == 0 ) {
    echo "<!-- DEBUG: Starting installation process -->\n";
    
    // Create/update .htaccess file
    echo "<!-- DEBUG: Creating .htaccess file -->\n";
    try {
        if ( !function_exists('yourls_create_htaccess') ) {
            echo "<!-- DEBUG: ERROR - yourls_create_htaccess function not found -->\n";
            $warning[] = 'Function yourls_create_htaccess() not found';
        } else {
            if ( yourls_create_htaccess() ) {
                $success[] = yourls__( 'File <tt>.htaccess</tt> successfully created/updated.' );
                echo "<!-- DEBUG: .htaccess created successfully -->\n";
            } else {
                $warning[] = yourls__( 'Could not write file <tt>.htaccess</tt> in YOURLS root directory. You will have to do it manually. See <a href="http://yourls.org/htaccess">how</a>.' );
                echo "<!-- DEBUG: .htaccess creation failed -->\n";
            }
        }
    } catch (Exception $e) {
        $warning[] = 'Error creating .htaccess: ' . $e->getMessage();
        echo "<!-- DEBUG: Exception creating .htaccess: " . htmlspecialchars($e->getMessage()) . " -->\n";
    }

    // Create SQL tables
    echo "<!-- DEBUG: Creating SQL tables -->\n";
    try {
        if ( !function_exists('yourls_create_sql_tables') ) {
            echo "<!-- DEBUG: ERROR - yourls_create_sql_tables function not found -->\n";
            $error[] = 'Function yourls_create_sql_tables() not found';
        } else {
            $install = yourls_create_sql_tables();
            echo "<!-- DEBUG: yourls_create_sql_tables() returned -->\n";
            echo "<!-- DEBUG: " . print_r($install, true) . " -->\n";
            if ( isset( $install['error'] ) )
                $error = array_merge( $error, $install['error'] );
            if ( isset( $install['success'] ) )
                $success = array_merge( $success, $install['success'] );
        }
    } catch (Exception $e) {
        $error[] = 'Error creating SQL tables: ' . $e->getMessage();
        echo "<!-- DEBUG: Exception creating SQL tables: " . htmlspecialchars($e->getMessage()) . " -->\n";
        echo "<!-- DEBUG: Stack trace: " . htmlspecialchars($e->getTraceAsString()) . " -->\n";
    }
} else {
    echo "<!-- DEBUG: Not starting installation (install not requested or errors present) -->\n";
}

// Start output
echo "<!-- DEBUG: Starting HTML output -->\n";
try {
    if ( !function_exists('yourls_html_head') ) {
        die("ERROR: Function yourls_html_head() not found. YOURLS functions not loaded properly.");
    }
    yourls_html_head( 'install', yourls__( 'Install YOURLS' ) );
    echo "<!-- DEBUG: HTML head output completed -->\n";
} catch (Exception $e) {
    die("<h1>Error Outputting HTML Head</h1><pre>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>");
}
?>
<div id="login">
    <form method="post" action="?"><?php // reset any QUERY parameters ?>
        <p>
            <img src="<?php yourls_site_url(); ?>/images/yourls-logo.svg" id="yourls-logo" alt="YOURLS" title="YOURLS" />
        </p>
        <?php
            // Print errors, warnings and success messages
            foreach ( array ('error', 'warning', 'success') as $info ) {
                if ( count( $$info ) > 0 ) {
                    echo "<ul class='$info'>";
                    foreach( $$info as $msg ) {
                        echo '<li>'.$msg."</li>\n";
                    }
                    echo '</ul>';
                }
            }

            // Display install button or link to admin area if applicable
            if( !yourls_is_installed() && !isset($_REQUEST['install']) ) {
                echo '<p style="text-align: center;"><input type="submit" name="install" value="' . yourls__( 'Install YOURLS') .'" class="button" /></p>';
            } else {
                if( count($error) == 0 )
                    echo '<p style="text-align: center;">&raquo; <a href="'.yourls_admin_url().'" title="' . yourls__( 'YOURLS Administration Page') . '">' . yourls__( 'YOURLS Administration Page') . '</a></p>';
            }
        ?>
    </form>
</div>
<?php yourls_html_footer(); ?>
