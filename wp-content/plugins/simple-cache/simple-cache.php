<?php

/*
Plugin Name: Simple Cache
Plugin URI: https://example.com/simple-cache
Description: A lightweight WordPress plugin for caching pages with custom exceptions and durations.
Version: 1.0.0
Author: Alex Galben
Author URI: https://example.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: simple-cache
Domain Path: /languages
*/

// Adaugă meniul de administrare
add_action('admin_menu', 'sc_add_admin_menu');
function sc_add_admin_menu() {
    add_menu_page(
        'Simple Cache Settings',
        'Cache Settings',
        'manage_options',
        'simple-cache',
        'sc_admin_page',
        'dashicons-performance',
        80
    );
}

// Pagina de administrare
function sc_admin_page() {
    // Salvează setările dacă formularul a fost trimis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        update_option('sc_cache_enabled', isset($_POST['cache_enabled']) ? 1 : 0);

        // Salvare excepții
        if (isset($_POST['exceptions'])) {
            $exceptions = array_filter(array_map('sanitize_text_field', explode("\n", trim($_POST['exceptions']))));
            update_option('sc_cache_exceptions', $exceptions);
        }

        // Salvare durate
        if (isset($_POST['durations'])) {
            update_option('sc_cache_durations', sc_parse_durations($_POST['durations']));
        }
    }

    // Obține setările existente
    $enabled = get_option('sc_cache_enabled', 0);
    $exceptions = implode("\n", get_option('sc_cache_exceptions', []));
    $durations = sc_format_durations(get_option('sc_cache_durations', []));

    ?>
    <div class="wrap">
        <h1>Simple Cache Settings</h1>
        <form method="POST">
            <!-- Activare/Dezactivare Cache -->
            <h2>Global Settings</h2>
            <label>
                <input type="checkbox" name="cache_enabled" <?php checked($enabled, 1); ?>>
                Enable Cache
            </label>
            <p>Toggle the cache functionality globally.</p>

            <!-- Excepții -->
            <h2>Cache Exceptions</h2>
            <p>Exclude specific pages or URLs from caching. Add one exception per line.</p>
            <textarea name="exceptions" rows="5" style="width: 100%;"><?php echo esc_textarea($exceptions); ?></textarea>

            <!-- Durate personalizate -->
            <h2>Cache Durations</h2>
            <p>Set custom cache durations for specific URL patterns (e.g., `blog:600` for 10 minutes). One rule per line.</p>
            <textarea name="durations" rows="5" style="width: 100%;"><?php echo esc_textarea($durations); ?></textarea>

            <!-- Buton Salvare -->
            <p>
                <button type="submit" class="button button-primary">Save Settings</button>
            </p>
        </form>
    </div>
    <?php
}

// Funcție pentru a parsa durata
function sc_parse_durations($input) {
    $durations = [];
    $lines = array_filter(array_map('trim', explode("\n", $input)));
    foreach ($lines as $line) {
        $parts = explode(':', $line);
        if (count($parts) === 2) {
            $durations[trim($parts[0])] = (int) trim($parts[1]);
        }
    }
    return $durations;
}

// Funcție pentru a formata durata pentru afișare
function sc_format_durations($durations) {
    $formatted = [];
    foreach ($durations as $pattern => $seconds) {
        $formatted[] = "$pattern:$seconds";
    }
    return implode("\n", $formatted);
}
?>