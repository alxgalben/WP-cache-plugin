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
        'Cache Settings',
        'Cache',
        'manage_options',
        'simple-cache',
        'sc_admin_page'
    );
}

function sc_admin_page() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Salvarea setărilor
        update_option('sc_cache_enabled', isset($_POST['cache_enabled']) ? 1 : 0);

        // Gestionarea excepțiilor
        if (isset($_POST['exceptions']) && is_array($_POST['exceptions'])) {
            $exceptions = array_map('sanitize_text_field', $_POST['exceptions']);
            update_option('sc_cache_exceptions', $exceptions);
        }

        // Gestionarea duratelor
        if (isset($_POST['durations'])) {
            update_option('sc_cache_durations', sc_parse_durations($_POST['durations']));
        }
    }

    // Obține setările salvate
    $enabled = get_option('sc_cache_enabled', 1);
    $exceptions = get_option('sc_cache_exceptions', []);
    $durations = sc_format_durations(get_option('sc_cache_durations', []));

    ?>
    <div class="wrap">
        <h1>Cache Settings</h1>
        <form method="POST">
            <!-- Activare cache -->
            <label>
                <input type="checkbox" name="cache_enabled" <?php checked($enabled, 1); ?>>
                Enable Cache
            </label>
            <br><br>

            <!-- Excepții -->
            <h2>Exceptions</h2>
            <div id="exception-list">
                <ul>
                    <?php foreach ($exceptions as $exception): ?>
                        <li>
                            <input type="text" name="exceptions[]" value="<?php echo esc_attr($exception); ?>" />
                            <button type="button" class="remove-exception button">Remove</button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button type="button" id="add-exception" class="button">Add New Exception</button>
            <br><br>

            <!-- Durate personalizate -->
            <h2>Cache Durations</h2>
            <label>Set custom durations (e.g., `blog:600,checkout:0`):</label>
            <input type="text" name="durations" value="<?php echo esc_attr($durations); ?>">
            <br><br>

            <!-- Salvare -->
            <button type="submit" class="button button-primary">Save Settings</button>
        </form>
    </div>

    <!-- JavaScript pentru interactivitate -->
    <script>
        document.getElementById('add-exception').addEventListener('click', function() {
            const ul = document.querySelector('#exception-list ul');
            const li = document.createElement('li');
            li.innerHTML = `
                <input type="text" name="exceptions[]" value="" />
                <button type="button" class="remove-exception button">Remove</button>
            `;
            ul.appendChild(li);
        });

        document.getElementById('exception-list').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-exception')) {
                e.target.parentElement.remove();
            }
        });
    </script>
    <?php
}

function sc_parse_durations($input) {
    $durations = [];
    $pairs = explode(',', $input);
    foreach ($pairs as $pair) {
        list($keyword, $seconds) = explode(':', $pair);
        $durations[trim($keyword)] = (int) trim($seconds);
    }
    return $durations;
}

function sc_format_durations($durations) {
    $formatted = [];
    foreach ($durations as $keyword => $seconds) {
        $formatted[] = "$keyword:$seconds";
    }
    return implode(',', $formatted);
}
?>