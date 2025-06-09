<?php
function crf_add_users_list_page() {
    add_menu_page(
        'Пользователи формы регистрации',
        'Форма регистрации',
        'manage_options',
        'custom-registration-users',
        'crf_render_users_list_page',
        'dashicons-id-alt',
        30
    );
}
add_action('admin_menu', 'crf_add_users_list_page', 20);

function crf_render_users_list_page() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('У вас недостаточно прав для доступа к этой странице.', 'crf'));
    }

    // Проверяем существование таблицы
    $table_name = $wpdb->prefix . 'crf_registrations';

    // Получаем данные из кастомной таблицы или fallback к usermeta
    if (crf_table_exists()) {
        $users = $wpdb->get_results("
            SELECT u.*, r.registration_date, r.phone
            FROM {$wpdb->users} u
            JOIN $table_name r ON u.ID = r.user_id
            ORDER BY r.registration_date DESC
            LIMIT 100
        ");
    } else {
        // Fallback: получаем через usermeta
        $users = get_users([
            'meta_key' => 'registered_via_custom_form',
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'number' => 100,
            'fields' => 'all_with_meta'
        ]);
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Пользователи, зарегистрированные через форму', 'crf'); ?></h1>

        <a href="<?php echo esc_url(admin_url('options-general.php?page=custom-registration-form')); ?>"
           class="page-title-action">
           → <?php esc_html_e('Настройки формы', 'crf'); ?>
        </a>

        <a href="<?php echo esc_url(admin_url('admin.php?page=custom-registration-users&export=csv')); ?>"
           class="page-title-action">
           <?php esc_html_e('Экспорт в CSV', 'crf'); ?>
        </a>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Логин</th>
                    <th>Email</th>
                    <th>Имя</th>
                    <th>Фамилия</th>
                    <th>Телефон</th>
                    <th>Роль</th>
                    <th>Дата регистрации</th>
                    <th>Последний вход</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center;"><?php esc_html_e('Нет зарегистрированных пользователей', 'crf'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user):
                        $user_data = get_userdata($user->ID); // Получаем полные данные пользователя
                    ?>
                        <tr>
                            <td><?php echo esc_html($user->ID); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>">
                                    <?php echo esc_html($user->user_login); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html($user->first_name); ?></td>
                            <td><?php echo esc_html($user->last_name); ?></td>
                            <td>
                                <?php
                                $phone = crf_table_exists() ? $user->phone : get_user_meta($user->ID, 'phone', true);
                                echo esc_html($phone ?: '—');
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($user_data && !empty($user_data->roles)) {
                                    $roles = array_map('translate_user_role', $user_data->roles);
                                    echo esc_html(implode(', ', $roles));
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $reg_date = crf_table_exists()
                                    ? $user->registration_date
                                    : get_user_meta($user->ID, 'registered_via_custom_form', true);
                                echo esc_html($reg_date ? date('d.m.Y H:i', strtotime($reg_date)) : '—');
                                ?>
                            </td>
                            <td>
                                <?php
                                $last_login = get_user_meta($user->ID, 'last_login', true);
                                echo esc_html($last_login ? date('d.m.Y H:i', strtotime($last_login)) : '—');
                                if ($last_login) {
                                    $diff = human_time_diff(strtotime($last_login), current_time('timestamp'));
                                    echo '<br><small>(' . esc_html($diff) . ' назад)</small>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
/*function crf_add_users_list_page() {
    add_menu_page(
        'Пользователи формы регистрации',
        'Форма регистрации',
        'manage_options',
        'custom-registration-users',
        'crf_render_users_list_page',
        'dashicons-id-alt',
        30
    );
}
add_action('admin_menu', 'crf_add_users_list_page', 20);

function crf_render_users_list_page() {
    global $wpdb;
    if (!current_user_can('manage_options')) {
        wp_die('У вас недостаточно прав для доступа к этой странице.');
    }
        $table_name = $wpdb->prefix . 'crf_registrations';
        $users = $wpdb->get_results("
        SELECT u.*, r.registration_date, r.phone
        FROM {$wpdb->users} u
        JOIN $table_name r ON u.ID = r.user_id
        ORDER BY r.registration_date DESC
        LIMIT 100
    ");
       // Получаем пользователей с метаданными
        $users = get_users([
            'meta_key' => 'registered_via_custom_form',
            'meta_compare' => 'EXISTS',
            'orderby' => 'registered_via_custom_form',
            'order' => 'DESC',
            'number' => -1,
            'fields' => 'all_with_meta' // Важно для загрузки метаданных
        ]);

    ?>
    <div class="wrap">
        <h1>Пользователи, зарегистрированные через форму</h1>

        <a href="<?php echo esc_url(admin_url('options-general.php?page=custom-registration-form')); ?>"
           class="page-title-action">
           → Настройки формы
        </a>

        <a href="<?php echo esc_url(admin_url('admin.php?page=custom-registration-users&export=csv')); ?>"
           class="page-title-action">
           Экспорт в CSV
        </a>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Логин</th>
            <th>Email</th>
            <th>Имя</th>
            <th>Фамилия</th>
            <th>Телефон</th>
            <th>Роль</th>
            <th>Дата регистрации</th>
            <th>Последний вход</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($users)): ?>
            <tr>
                <td colspan="9" style="text-align: center;">Нет зарегистрированных пользователей</td>
            </tr>
        <?php else: ?>
            <?php foreach ($users as $user):
                $user_data = get_userdata($user->ID); // Получаем полные данные пользователя
            ?>
                <tr>
                    <td><?php echo esc_url($user->ID); ?></td>
                    <td>
                        <a href="<?php echo esc_url($user->ID); ?>">
                            <?php echo esc_html($user->user_login); ?>
                        </a>
                    </td>



                    <td><?php echo esc_html($user->user_email); ?></td>
                    <td><?php echo esc_html($user->first_name); ?></td>
                    <td><?php echo esc_html($user->last_name); ?></td>
                    <td><?php echo esc_html(get_user_meta($user->ID, 'phone', true) ?: '—'); ?></td>
                    <td>
                        <?php
                        if ($user_data && !empty($user_data->roles)) {
                            $roles = array_map('translate_user_role', $user_data->roles);
                            echo esc_html(implode(', ', $roles));
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>




                    <td>
                        <?php
                        $reg_date = get_user_meta($user->ID, 'registered_via_custom_form', true);
                        echo esc_html($reg_date ? date('d.m.Y H:i', strtotime($reg_date)) : '—');
                        ?>
                    </td>
                    <td>
                        <?php
                        $last_login = get_user_meta($user->ID, 'last_login', true);
                        echo esc_html($last_login ? date('d.m.Y H:i', strtotime($last_login)) : '—');
                        if ($last_login) {
                            $date = date_create($last_login);
                            echo esc_html($date, 'd.m.Y H:i');

                            // Добавляем человеко-читаемый интервал
                            $diff = human_time_diff(strtotime($last_login), current_time('timestamp'));
                            echo esc_attr('<br><small>(' . $diff . ' назад)</small>');
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
    </div>
    <?php
}
*/
