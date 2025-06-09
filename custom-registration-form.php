<?php
/*
Plugin Name: Custom Registration Form
Description: Плагин для кастомной формы регистрации пользователей с настройками через админ-панель
Version: 1.2
Author: Your Name
*/

// Основные константы плагина
define('CRF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CRF_PLUGIN_URL', plugin_dir_url(__FILE__));
// В начало файла после констант добавляем:
register_activation_hook(__FILE__, 'crf_create_custom_table');



function crf_create_custom_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'crf_registrations';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        user_id bigint(20) NOT NULL,
        registration_date datetime NOT NULL,
        phone varchar(20) DEFAULT NULL,
        PRIMARY KEY (user_id),
        KEY registration_date_idx (registration_date)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Переносим существующие данные
    crf_migrate_existing_data();
}

function crf_migrate_existing_data() {
    global $wpdb;

    $users = get_users([
        'meta_key' => 'registered_via_custom_form',
        'fields' => 'ID'
    ]);

    foreach ($users as $user_id) {
        $reg_date = get_user_meta($user_id, 'registered_via_custom_form', true);
        $phone = get_user_meta($user_id, 'phone', true);

        $wpdb->replace(
            $wpdb->prefix . 'crf_registrations',
            [
                'user_id' => $user_id,
                'registration_date' => $reg_date ?: current_time('mysql'),
                'phone' => $phone
            ],
            ['%d', '%s', '%s']
        );
    }
}

// Подключаем файлы с функционалом
require_once CRF_PLUGIN_DIR . 'includes/form-render.php';
require_once CRF_PLUGIN_DIR . 'includes/form-handler.php';
require_once CRF_PLUGIN_DIR . 'admin/settings-page.php';
require_once CRF_PLUGIN_DIR . 'admin/users-list.php';
require_once CRF_PLUGIN_DIR . 'includes/user-tracking.php';

// Инициализация плагина
add_action('plugins_loaded', 'crf_init_plugin');
//поддержкf медиабиблиотеки
add_action('admin_enqueue_scripts', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'custom-registration-form') {
        wp_enqueue_media();
    }
});

function crf_init_plugin() {
    // Регистрируем шорткод
    add_shortcode('custom_registration_form', 'crf_render_registration_form');

    // Подключаем обработчик AJAX
    add_action('wp_ajax_handle_registration', 'crf_handle_registration');
    add_action('wp_ajax_nopriv_handle_registration', 'crf_handle_registration');

    // Инициализация админ-панели
    if (is_admin()) {
        add_action('admin_menu', 'crf_admin_menu');
    }
}
function crf_mark_existing_users() {
    $all_users = get_users();

    foreach ($all_users as $user) {
        // Если мета-поле отсутствует, добавляем его
        if (!get_user_meta($user->ID, 'registered_via_custom_form', true)) {
            update_user_meta(
                $user->ID,
                'registered_via_custom_form',
                $user->user_registered // Используем дату регистрации из wp_users
            );
        }
    }
}

// Раскомментируйте следующую строку для однократного выполнения
// add_action('init', 'crf_mark_existing_users');
// Проверка существования таблицы
function crf_table_exists() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crf_registrations';
    return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
}

// Очистка при деактивации (опционально)
function crf_deactivate_plugin() {
    // Если хотите сохранить данные - оставьте пустым
    // Или раскомментируйте для удаления таблицы:
    /*
    global $wpdb;
    $table_name = $wpdb->prefix . 'crf_registrations';
    if (crf_table_exists()) {
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
    */
}
register_deactivation_hook(__FILE__, 'crf_deactivate_plugin');
