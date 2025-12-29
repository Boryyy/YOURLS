<?php
/*
 * Functions relative to debugging
 */

/**
 * Add a message to the debug log
 *
 * When in debug mode ( YOURLS_DEBUG == true ) the debug log is echoed in yourls_html_footer()
 * Log messages are appended to $ydb->debug_log array, which is instanciated within class Database\YDB
 *
 * @since 1.7
 * @param string $msg Message to add to the debug log
 * @return string The message itself
 */
function yourls_debug_log( $msg ) {
    yourls_do_action( 'debug_log', $msg );
    // Get the DB object ($ydb), get its profiler (\Aura\Sql\Profiler\Profiler), its logger (\Aura\Sql\Profiler\MemoryLogger) and
    // pass it a unused argument (loglevel) and the message
    // Check if function exists to allow usage of the function in very early stages
    if(function_exists('yourls_get_db')) {
        try {
            $ydb = yourls_get_db();
            if ($ydb && method_exists($ydb, 'getProfiler')) {
                $ydb->getProfiler()->getLogger()->log( 'debug', $msg);
            }
        } catch (Exception $e) {
            // Database not initialized yet, skip logging
        }
    }
    return $msg;
}

/**
 * Get the debug log
 *
 * @since  1.7.3
 * @return array
 */
function yourls_get_debug_log() {
    if (!function_exists('yourls_get_db')) {
        return [];
    }
    try {
        $ydb = yourls_get_db();
        if ($ydb && method_exists($ydb, 'getProfiler')) {
            return $ydb->getProfiler()->getLogger()->getMessages();
        }
    } catch (Exception $e) {
        // Database not initialized yet
    }
    return [];
}

/**
 * Get number of SQL queries performed
 *
 * @return int
 */
function yourls_get_num_queries() {
    if (!function_exists('yourls_get_db')) {
        return 0;
    }
    try {
        $ydb = yourls_get_db();
        if ($ydb && method_exists($ydb, 'get_num_queries')) {
            return yourls_apply_filter( 'get_num_queries', $ydb->get_num_queries() );
        }
    } catch (Exception $e) {
        // Database not initialized yet
    }
    return 0;
}

/**
 * Debug mode set
 *
 * @since 1.7.3
 * @param bool $bool Debug on or off
 * @return void
 */
function yourls_debug_mode( $bool ) {
    // log queries if true
    if (function_exists('yourls_get_db')) {
        try {
            $ydb = yourls_get_db();
            if ($ydb && method_exists($ydb, 'getProfiler')) {
                $ydb->getProfiler()->setActive( (bool)$bool );
            }
        } catch (Exception $e) {
            // Database not initialized yet, skip
        }
    }

    // report notices if true
    $level = $bool ? -1 : ( E_ERROR | E_PARSE );
    error_reporting( $level );
}

/**
 * Return YOURLS debug mode
 *
 * @since 1.7.7
 * @return bool
 */
function yourls_get_debug_mode() {
    return defined( 'YOURLS_DEBUG' ) && YOURLS_DEBUG;
}
