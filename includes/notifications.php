<?php
function crf_send_registration_notification($user_id) {
    $options = get_option('custom_registration_form_settings');
    $user = get_userdata($user_id);

    // SMS уведомление
    if ($options['enable_sms_notify'] ?? false && !empty($options['admin_phone'])) {
        $phone = preg_replace('/[^0-9+]/', '', $options['admin_phone']);
        $message = sprintf(
            'Новый пользователь: %s (%s) зарегистрирован %s',
            $user->user_login,
            $user->user_email,
            date('d.m.Y H:i')
        );

        // Здесь должна быть реализация отправки SMS через API
        crf_send_sms($phone, $message);
    }

    // Email уведомление
    if ($options['enable_email_notify'] ?? true && !empty($options['admin_email'])) {
        $to = $options['admin_email'];
        $subject = 'Новая регистрация на сайте ' . get_bloginfo('name');
        $message = sprintf(
            "Зарегистрирован новый пользователь:\n\nЛогин: %s\nEmail: %s\nДата: %s\n\nСтраница профиля: %s",
            $user->user_login,
            $user->user_email,
            date('d.m.Y H:i'),
            admin_url('user-edit.php?user_id=' . $user_id)
        );

        wp_mail($to, $subject, $message);
    }
}

// Заглушка для SMS-отправки (реализуйте под ваш SMS-шлюз)
function crf_send_sms($phone, $message) {
    // Пример для SMS.ru:
    $api_id = 'ваш_api_id'; // Получите в сервисе
    $url = "https://sms.ru/sms/send?api_id=$api_id&to=$phone&msg=" . urlencode($message);

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        error_log('SMS sending error: ' . $response->get_error_message());
    }

    // Логируем для отладки
    error_log("SMS sent to $phone: " . substr($message, 0, 50) . '...');
}
