<?php

function sc_is_url_excluded($url) {
    $exceptions = get_option('sc_cache_exceptions', []);
    foreach ($exceptions as $exception) {
        if (strpos($url, $exception) !== false) {
            return true;
        }
    }
    return false;
}

function sc_get_cache_duration($url, $durations) {
    foreach ($durations as $pattern => $duration) {
        if (strpos($url, $pattern) !== false) {
            return $duration;
        }
    }
    return 3600; // 1 ora
}