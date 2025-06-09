<?php
function crf_track_user_login($user_login, $user) {
    if ($user && is_a($user, 'WP_User')) {
        update_user_meta($user->ID, 'last_login', current_time('mysql'));
        error_log("Last login updated for user ID: {$user->ID}"); // Логирование
    }
}
add_action('wp_login', 'crf_track_user_login', 10, 2);
//функцию отслеживания входа
