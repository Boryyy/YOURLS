<?php
/*
Plugin Name: Bulk URL Shortener
Plugin URI: https://yourls.org/
Description: Allows bulk URL shortening with pattern support. Switch to advanced mode to create multiple URLs with a pattern like example.com/{value} where {value} can be a range (e.g., 1-50).
Version: 1.0
Author: boryyy
Author URI: https://yourls.org/
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Hook into the html_addnew action to add our advanced form
yourls_add_action( 'html_addnew', 'bulk_url_shortener_add_advanced_form' );

// Hook into AJAX to handle bulk creation
yourls_add_action( 'yourls_ajax_bulk_add', 'bulk_url_shortener_ajax_handler' );

// Enqueue JavaScript and CSS
yourls_add_action( 'html_head', 'bulk_url_shortener_add_scripts' );

/**
 * Add the advanced form HTML after the regular form
 */
function bulk_url_shortener_add_advanced_form() {
    ?>
    <div id="bulk-url-shortener-advanced" style="display:none; margin-top:20px; padding:15px; background:#f5f5f5; border:1px solid #ddd; border-radius:5px;">
        <h3 style="margin-top:0;">Bulk URL Shortener - Advanced Mode</h3>
        <p>Create multiple short URLs with a pattern. Use <code>{value}</code> as a placeholder that will be replaced with numbers from your range.</p>
        
        <form id="bulk-url-form">
            <div style="margin-bottom:15px;">
                <label for="bulk-url-pattern"><strong>URL Pattern:</strong></label><br>
                <input type="text" id="bulk-url-pattern" name="pattern" value="" class="text" size="80" placeholder="https://example.com/sunbed/{value}" style="width:100%; max-width:600px;">
                <br><small>Use {value} as placeholder for the number</small>
            </div>
            
            <div style="margin-bottom:15px;">
                <label for="bulk-url-start"><strong>Start Number:</strong></label>
                <input type="number" id="bulk-url-start" name="start" value="1" min="0" step="1" class="text" size="5">
                
                <label for="bulk-url-end" style="margin-left:20px;"><strong>End Number:</strong></label>
                <input type="number" id="bulk-url-end" name="end" value="50" min="0" step="1" class="text" size="5">
                
                <br><small>Range of numbers to replace {value} (e.g., 1 to 50)</small>
            </div>
            
            <div style="margin-bottom:15px;">
                <label for="bulk-keyword-pattern"><strong>Custom Short URL Pattern (Optional):</strong></label><br>
                <input type="text" id="bulk-keyword-pattern" name="keyword_pattern" value="" class="text" size="30" placeholder="sunbed{value}" style="max-width:300px;">
                <br><small>Use {value} as placeholder. Leave empty for auto-generated keywords.</small>
            </div>
            
            <div>
                <input type="button" id="bulk-add-button" value="Create Bulk URLs" class="button primary" onclick="bulk_add_urls();">
                <input type="button" id="bulk-cancel-button" value="Switch to Simple Mode" class="button" onclick="toggle_bulk_mode();" style="margin-left:10px;">
            </div>
            
            <div id="bulk-progress" style="display:none; margin-top:15px;">
                <div style="background:#fff; border:1px solid #ccc; padding:10px; border-radius:3px;">
                    <div id="bulk-progress-text">Processing...</div>
                    <div id="bulk-progress-bar" style="background:#ddd; height:20px; border-radius:3px; margin-top:10px; overflow:hidden;">
                        <div id="bulk-progress-fill" style="background:#0073aa; height:100%; width:0%; transition:width 0.3s;"></div>
                    </div>
                </div>
            </div>
            
            <div id="bulk-results" style="margin-top:15px;"></div>
        </form>
    </div>
    
    <div style="margin-top:10px;">
        <a href="#" id="toggle-bulk-mode" onclick="toggle_bulk_mode(); return false;" style="text-decoration:none; color:#0073aa;">Switch to Advanced Mode</a>
    </div>
    <?php
}

/**
 * Add JavaScript and CSS
 */
function bulk_url_shortener_add_scripts() {
    // Only add on admin pages
    if( !yourls_is_admin() ) {
        return;
    }
    
    $plugin_url = YOURLS_PLUGINURL . '/bulk-url-shortener';
    ?>
    <style>
        #bulk-url-shortener-advanced {
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        #bulk-results .success-item {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 8px;
            margin: 5px 0;
            border-radius: 3px;
        }
        #bulk-results .error-item {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 8px;
            margin: 5px 0;
            border-radius: 3px;
        }
    </style>
    <script>
    var bulkModeActive = false;
    
    function toggle_bulk_mode() {
        bulkModeActive = !bulkModeActive;
        
        if (bulkModeActive) {
            $('#new_url_form').slideUp(300);
            $('#bulk-url-shortener-advanced').slideDown(300);
            $('#toggle-bulk-mode').text('Switch to Simple Mode');
        } else {
            $('#bulk-url-shortener-advanced').slideUp(300);
            $('#new_url_form').slideDown(300);
            $('#toggle-bulk-mode').text('Switch to Advanced Mode');
            $('#bulk-results').html('');
            $('#bulk-progress').hide();
        }
    }
    
    function bulk_add_urls() {
        var pattern = $('#bulk-url-pattern').val();
        var start = parseInt($('#bulk-url-start').val());
        var end = parseInt($('#bulk-url-end').val());
        var keywordPattern = $('#bulk-keyword-pattern').val();
        
        // Validation
        if (!pattern || pattern.indexOf('{value}') === -1) {
            feedback('Please enter a URL pattern with {value} placeholder', 'fail');
            return;
        }
        
        if (isNaN(start) || isNaN(end) || start > end) {
            feedback('Please enter valid start and end numbers', 'fail');
            return;
        }
        
        if (end - start > 1000) {
            if (!confirm('You are about to create ' + (end - start + 1) + ' URLs. This may take a while. Continue?')) {
                return;
            }
        }
        
        // Disable button
        $('#bulk-add-button').addClass('disabled').prop('disabled', true);
        
        // Show progress
        $('#bulk-progress').show();
        $('#bulk-results').html('');
        
        var total = end - start + 1;
        var processed = 0;
        var successCount = 0;
        var errorCount = 0;
        var results = [];
        
        // Process URLs one by one
        function processNext(current) {
            if (current > end) {
                // All done
                $('#bulk-progress-fill').css('width', '100%');
                $('#bulk-progress-text').html('Completed! Created ' + successCount + ' URLs, ' + errorCount + ' errors.');
                $('#bulk-add-button').removeClass('disabled').prop('disabled', false);
                
                // Refresh the table if we have successes
                if (successCount > 0) {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
                return;
            }
            
            // Replace {value} in pattern
            var url = pattern.replace(/{value}/g, current);
            var keyword = keywordPattern ? keywordPattern.replace(/{value}/g, current) : '';
            
            // Get nonce
            var nonce = $('#nonce-add').val();
            
            // Make AJAX call
            $.getJSON(
                ajaxurl,
                {
                    action: 'bulk_add',
                    url: url,
                    keyword: keyword,
                    nonce: nonce,
                    value: current
                },
                function(data) {
                    processed++;
                    var progress = Math.round((processed / total) * 100);
                    $('#bulk-progress-fill').css('width', progress + '%');
                    $('#bulk-progress-text').html('Processing... ' + processed + ' / ' + total + ' (' + progress + '%)');
                    
                    if (data.status == 'success') {
                        successCount++;
                        results.push('<div class="success-item">✓ ' + current + ': ' + data.shorturl + '</div>');
                    } else {
                        errorCount++;
                        results.push('<div class="error-item">✗ ' + current + ': ' + (data.message || 'Error') + '</div>');
                    }
                    
                    // Update results (show last 20)
                    var displayResults = results.slice(-20);
                    $('#bulk-results').html(displayResults.join(''));
                    
                    // Process next
                    processNext(current + 1);
                }
            ).fail(function() {
                processed++;
                errorCount++;
                results.push('<div class="error-item">✗ ' + current + ': Network error</div>');
                
                var displayResults = results.slice(-20);
                $('#bulk-results').html(displayResults.join(''));
                
                processNext(current + 1);
            });
        }
        
        // Start processing
        processNext(start);
    }
    </script>
    <?php
}

/**
 * Handle AJAX request for bulk URL creation
 */
function bulk_url_shortener_ajax_handler() {
    // Verify nonce
    yourls_verify_nonce( 'add_url', $_REQUEST['nonce'], false, 'Invalid nonce' );
    
    $url = $_REQUEST['url'];
    $keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
    $value = isset($_REQUEST['value']) ? $_REQUEST['value'] : '';
    
    // Add the URL
    $return = yourls_add_new_link( $url, $keyword );
    
    // Return JSON (headers already set by admin-ajax.php)
    echo json_encode($return);
    die();
}

