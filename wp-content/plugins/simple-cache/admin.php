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
        // Salvarea setărilor
        update_option('sc_cache_enabled', isset($_POST['cache_enabled']) ? 1 : 0);

// Gestionarea excepțiilor
if (isset($_POST['exceptions'])) {
    $exceptions = array_map('sanitize_text_field', explode("\n", trim($_POST['exceptions'])));
    update_option('sc_cache_exceptions', $exceptions);
}

if (isset($_POST['durations'])) {
    update_option('sc_cache_durations', sc_parse_durations($_POST['durations']));
}

    $enabled = get_option('sc_cache_enabled');
    $exceptions = implode("\n", get_option('sc_cache_exceptions', []));
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
            <label>Exceptions:</label>
            <div id="exception-list">
                <ul>
                    <?php
                    $saved_exceptions = get_option('sc_cache_exceptions', []);
                    foreach ($saved_exceptions as $exception) {
                        echo "<li>$exception <button type='button' class='remove-exception'>Remove</button></li>";
                    }
                    ?>
                </ul>
            </div>
            <input type="text" id="new-exception" placeholder="Add new exception">
            <button type="button" id="add-exception" class="button">Add Exception</button>
            <br><br>
            <label>Cache Durations:</label>
            <input type="text" name="durations" value="<?php echo esc_attr($durations); ?>">
            <br><br>
            <button type="submit" class="button button-primary">Save Settings</button>
        </form>
    </div>

    <script>
        document.getElementById('add-exception').addEventListener('click', function() {
            var newException = document.getElementById('new-exception').value.trim();
            if (newException) {
                var ul = document.querySelector('#exception-list ul');
                var li = document.createElement('li');
                li.innerHTML = newException + ' <button type="button" class="remove-exception">Remove</button>';
                ul.appendChild(li);
                document.getElementById('new-exception').value = ''; // Reset the input
            }
        });

        // Remove exception when clicking 'Remove'
        document.querySelector('#exception-list').addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-exception')) {
                var li = event.target.closest('li');
                li.parentNode.removeChild(li);
            }
        });

        // Before submitting form, collect exceptions
        document.querySelector('form').addEventListener('submit', function() {
            var exceptions = [];
            var exceptionItems = document.querySelectorAll('#exception-list ul li');
            exceptionItems.forEach(function(item) {
                exceptions.push(item.firstChild.textContent.trim());
            });
            document.querySelector('textarea[name="exceptions"]').value = exceptions.join("\n");
        });
    </script>
<?php }

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