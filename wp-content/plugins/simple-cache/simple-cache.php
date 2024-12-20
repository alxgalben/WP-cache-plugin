<?php
/*
Plugin Name: Plugin de Cache pentru WordPress
Description: Plugin care salveaza paginile în format HTML in folderul mycache.
Version: 1.0
Author: [Alex Galben]
*/

define('SC_CACHE_DIR', WP_CONTENT_DIR . '/mycache/');
define('SC_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once SC_PLUGIN_DIR . 'admin.php';
require_once SC_PLUGIN_DIR . 'functions.php';

// activare
register_activation_hook(__FILE__, 'sc_activate_plugin');
function sc_activate_plugin() {
    if (!file_exists(SC_CACHE_DIR)) {
        mkdir(SC_CACHE_DIR, 0755, true);
    }
    add_option('sc_cache_enabled', 1);
    add_option('sc_cache_exceptions', []);
    add_option('sc_cache_durations', []);
}

// dezactivare
register_deactivation_hook(__FILE__, 'sc_deactivate_plugin');
function sc_deactivate_plugin() {
    delete_option('sc_cache_enabled');
    delete_option('sc_cache_exceptions');
    delete_option('sc_cache_durations');
}

// procesare cache
add_action('template_redirect', 'sc_handle_cache');
function sc_handle_cache() {
    if (!get_option('sc_cache_enabled')) return;

    $url = $_SERVER['REQUEST_URI'];
    if (sc_is_url_excluded($url)) return;

    $cache_file = SC_CACHE_DIR . md5($url) . '.html';
    $cache_durations = get_option('sc_cache_durations', []);
    $duration = sc_get_cache_duration($url, $cache_durations);

    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $duration) {
        echo file_get_contents($cache_file);
        exit;
    }

    ob_start();
    add_action('shutdown', function() use ($cache_file) {
        $content = ob_get_clean();
        file_put_contents($cache_file, $content);
        echo $content;
    });
}