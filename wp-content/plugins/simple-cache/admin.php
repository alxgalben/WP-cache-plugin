<?php

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
        update_option('sc_cache_enabled', isset($_POST['cache_enabled']) ? 1 : 0);
        update_option('sc_cache_exceptions', array_filter(explode(',', sanitize_text_field($_POST['exceptions']))));
        update_option('sc_cache_durations', sc_parse_durations($_POST['durations']));
    }

    $enabled = get_option('sc_cache_enabled');
    $exceptions = implode(',', get_option('sc_cache_exceptions', []));
    $durations = sc_format_durations(get_option('sc_cache_durations', []));
    ?>
    <div class="wrap">
        <h1>Cache Settings</h1>
        <form method="POST">
            <label>
                <input type="checkbox" name="cache_enabled" <?php checked($enabled, 1); ?>>
                Enable Cache
            </label>
            <br><br>
            <label>
                Exceptions:
                <input type="text" name="exceptions" value="<?php echo esc_attr($exceptions); ?>">
            </label>
            <br><br>
            <label>
                Cache Durations:
                <input type="text" name="durations" value="<?php echo esc_attr($durations); ?>">
            </label>
            <br><br>
            <button type="submit" class="button button-primary">Save Settings</button>
        </form>
    </div>
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