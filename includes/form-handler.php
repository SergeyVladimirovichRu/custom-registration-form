<?php
function crf_handle_registration() {
        global $wpdb;

    // Проверяем существование таблицы
    if (!crf_table_exists()) {
        // Можно создать таблицу на лету или использовать fallback
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        crf_create_custom_table(); // Функция из custom-registration-form.php
    }
       // Проверяем nonce для безопасности
    if (!isset($_POST['registration_nonce_field']) ||
        !wp_verify_nonce($_POST['registration_nonce_field'], 'registration_nonce')) {
        wp_send_json([
            'success' => false,
            'message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.'
        ]);
    }
    check_ajax_referer('registration_nonce', 'registration_nonce_field');

    $options = get_option('custom_registration_form_settings');
    $username = sanitize_text_field($_POST['username'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = sanitize_text_field($_POST['phone'] ?? '');

    // Валидация данных
    $errors = [];

    if (($options['show_username_field'] ?? true) && empty($username)) {
        $errors[] = 'Имя пользователя обязательно для заполнения';
    }

    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    }

    if (empty($password)) {
        $errors[] = 'Пароль обязателен для заполнения';
    }

    if (($options['show_phone_field'] ?? false) && ($options['phone_required'] ?? false) && empty($phone)) {
        $errors[] = 'Телефон обязателен для заполнения';
    }

    if (!empty($errors)) {
        wp_send_json([
            'success' => false,
            'message' => implode('<br>', $errors)
        ]);
    }

    if (($options['show_username_field'] ?? true) && username_exists($username)) {
        wp_send_json([
            'success' => false,
            'message' => 'Пользователь с таким именем уже существует'
        ]);
    }

    if (email_exists($email)) {
        wp_send_json([
            'success' => false,
            'message' => 'Пользователь с таким email уже существует'
        ]);
    }




// После успешной регистрации пользователя добавляем:

    $wpdb->replace(
        $wpdb->prefix . 'crf_registrations',
        [
            'user_id' => $user_id,
            'registration_date' => current_time('mysql'),
            'phone' => $phone ?? null
        ],
        ['%d', '%s', '%s']
    );



    // Создаём пользователя
    $user_id = wp_create_user(
        ($options['show_username_field'] ?? true) ? $username : $email,
        $password,
        $email
    );

    if (is_wp_error($user_id)) {
        wp_send_json([
            'success' => false,
            'message' => 'Ошибка при регистрации: ' . $user_id->get_error_message()
        ]);
    }

    // Сохраняем дополнительные поля
    if (($options['show_phone_field'] ?? false) && !empty($phone)) {
        update_user_meta($user_id, 'phone', $phone);
    }

    // Авторизация после регистрации
    if ($options['auto_login'] ?? false) {
        wp_set_auth_cookie($user_id);
    }
    // После успешной регистрации добавим мета-поле
if (!is_wp_error($user_id)) {
    // Устанавливаем роль пользователя
    $user = new WP_User($user_id);
    $user->set_role($options['default_role'] ?? 'subscriber');

    // Автоматическая авторизация
    if ($options['auto_login'] ?? false) {
        wp_clear_auth_cookie();
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        // Обновляем последний логин
        update_user_meta($user_id, 'last_login', current_time('mysql'));
    }
    $message = ($options['auto_login'] ?? false)
        ? $options['success_message'] ?? 'Регистрация успешна!'
        : $options['success_message'] ?? 'Регистрация успешна! Пожалуйста, авторизуйтесь.';
    wp_send_json([
        'success' => true,
        'message' => $options['success_message'] ?? 'Регистрация успешна!',
        'redirect' => ($options['auto_login'] ?? false)
            ? esc_url($options['redirect_url'] ?? home_url())
            : false
    ]);
        // Отправляем уведомления
    require_once CRF_PLUGIN_DIR . 'includes/notifications.php';
    crf_send_registration_notification($user_id);
}
}
