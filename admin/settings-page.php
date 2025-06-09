<?php
function crf_admin_menu() {
    add_options_page(
        'Настройки формы регистрации',
        'Custom Registration',
        'manage_options',
        'custom-registration-form',
        'crf_render_settings_page'
    );

    // Возвращаем slug страницы для возможного использования
    return 'custom-registration-form';
}
function crf_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('У вас недостаточно прав для доступа к этой странице.'));
    }

    // Получаем текущие настройки
    $options = get_option('custom_registration_form_settings');
    $allowed_roles = ['subscriber', 'contributor']; // Разрешенные роли

    // Обработка отправки формы
    if (isset($_POST['submit'])) {
        check_admin_referer('custom_registration_form_settings');

        // Валидация роли
        $submitted_role = sanitize_text_field($_POST['default_role'] ?? 'subscriber');
        $default_role = in_array($submitted_role, $allowed_roles)
            ? $submitted_role
            : 'subscriber';

        // Логирование попытки установки недопустимой роли
        if ($submitted_role !== $default_role) {
            error_log('Custom Registration Form: Попытка установки недопустимой роли - ' . $submitted_role);
        }

        // Подготовка настроек
        $settings = [
            'show_username_field' => isset($_POST['show_username_field']),
            'username_placeholder' => sanitize_text_field($_POST['username_placeholder'] ?? ''),
            'username_required' => isset($_POST['username_required']),
            'email_placeholder' => sanitize_text_field($_POST['email_placeholder'] ?? ''),
            'password_placeholder' => sanitize_text_field($_POST['password_placeholder'] ?? ''),
            'show_phone_field' => isset($_POST['show_phone_field']),
            'phone_placeholder' => sanitize_text_field($_POST['phone_placeholder'] ?? ''),
            'phone_required' => isset($_POST['phone_required']),
            'submit_text' => sanitize_text_field($_POST['submit_text'] ?? ''),
            'success_message' => sanitize_text_field($_POST['success_message'] ?? ''),
            'redirect_url' => esc_url_raw($_POST['redirect_url'] ?? ''),
            'auto_login' => isset($_POST['auto_login']),
            'default_role' => $default_role,
            'form_width' => sanitize_text_field($_POST['form_width'] ?? ''),
            'form_image_url' => esc_url_raw($_POST['form_image_url'] ?? ''),
            'form_image_position' => sanitize_text_field($_POST['form_image_position'] ?? 'right'),
            'form_image_width' => sanitize_text_field($_POST['form_image_width'] ?? '300px'),
            'enable_sms_notify' => isset($_POST['enable_sms_notify']),
            'admin_phone' => sanitize_text_field($_POST['admin_phone'] ?? ''),
            'hide_auth_notice' => isset($_POST['hide_auth_notice']),
            'form_bg_image' => esc_url_raw($_POST['form_bg_image'] ?? ''),
            'form_bg_opacity' => floatval($_POST['form_bg_opacity'] ?? 0.8),
            'form_bg_position' => sanitize_text_field($_POST['form_bg_position'] ?? 'center center'),
            'form_bg_size' => sanitize_text_field($_POST['form_bg_size'] ?? 'cover'),
            'enable_email_notify' => isset($_POST['enable_email_notify']),
            'admin_email' => sanitize_email($_POST['admin_email'] ?? get_option('admin_email')),
            'form_bg_color' => sanitize_hex_color($_POST['form_bg_color'] ?? '#f9f9f9'),
            'input_border_color' => sanitize_hex_color($_POST['input_border_color'] ?? '#ddd'),
            'button_bg_color' => sanitize_hex_color($_POST['button_bg_color'] ?? '#0073aa'),
            'button_text_color' => sanitize_hex_color($_POST['button_text_color'] ?? '#fff'),
            'button_hover_color' => sanitize_hex_color($_POST['button_hover_color'] ?? '#005177')
        ];

        update_option('custom_registration_form_settings', $settings);
        echo '<div class="notice notice-success"><p>Настройки сохранены!</p></div>';

        // Обновляем текущие настройки
        $options = $settings;
    }
    // HTML форма настроек
    ?>
    <div class="wrap">
            <h1>Настройки формы регистрации</h1>
            <form method="post">
                <?php wp_nonce_field('custom_registration_form_settings'); ?>

                <h2>Основные настройки</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Показывать поле имени пользователя</th>
                        <td>
                            <input type="checkbox" name="show_username_field" <?php checked($options['show_username_field'] ?? true, true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Placeholder для имени пользователя</th>
                        <td>
                            <input type="text" name="username_placeholder" value="<?php echo esc_attr($options['username_placeholder'] ?? 'Имя пользователя'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Обязательное поле имени</th>
                        <td>
                            <input type="checkbox" name="username_required" <?php checked($options['username_required'] ?? true, true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Placeholder для email</th>
                        <td>
                            <input type="text" name="email_placeholder" value="<?php echo esc_attr($options['email_placeholder'] ?? 'Email'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Placeholder для пароля</th>
                        <td>
                            <input type="text" name="password_placeholder" value="<?php echo esc_attr($options['password_placeholder'] ?? 'Пароль'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Показывать поле телефона</th>
                        <td>
                            <input type="checkbox" name="show_phone_field" <?php checked($options['show_phone_field'] ?? false, true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Placeholder для телефона</th>
                        <td>
                            <input type="text" name="phone_placeholder" value="<?php echo esc_attr($options['phone_placeholder'] ?? 'Телефон'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Обязательное поле телефона</th>
                        <td>
                            <input type="checkbox" name="phone_required" <?php checked($options['phone_required'] ?? false, true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Текст кнопки отправки</th>
                        <td>
                            <input type="text" name="submit_text" value="<?php echo esc_attr($options['submit_text'] ?? 'Зарегистрироваться'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Сообщение об успешной регистрации</th>
                        <td>
                            <input type="text" name="success_message" value="<?php echo esc_attr($options['success_message'] ?? 'Регистрация успешна!'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">URL для перенаправления после регистрации</th>
                        <td>
                            <input type="text" name="redirect_url" value="<?php echo esc_attr($options['redirect_url'] ?? home_url()); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Автоматически авторизовать пользователя</th>
                        <td>
                            <input type="checkbox" name="auto_login" <?php checked($options['auto_login'] ?? false, true); ?>>
                        </td>
                    </tr>
                </table>

                <h2>Настройки стилей</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Ширина формы</th>
                        <td>
                            <input type="text" name="form_width" value="<?php echo esc_attr($options['form_width'] ?? '300px'); ?>">
                            <p class="description">Например: 300px, 50%, 100%</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Цвет фона формы</th>
                        <td>
                            <input type="color" name="form_bg_color" value="<?php echo esc_attr($options['form_bg_color'] ?? '#f9f9f9'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Цвет рамки полей ввода</th>
                        <td>
                            <input type="color" name="input_border_color" value="<?php echo esc_attr($options['input_border_color'] ?? '#dddddd'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Цвет фона кнопки</th>
                        <td>
                            <input type="color" name="button_bg_color" value="<?php echo esc_attr($options['button_bg_color'] ?? '#0073aa'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Цвет текста кнопки</th>
                        <td>
                            <input type="color" name="button_text_color" value="<?php echo esc_attr($options['button_text_color'] ?? '#ffffff'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Цвет кнопки при наведении</th>
                        <td>
                            <input type="color" name="button_hover_color" value="<?php echo esc_attr($options['button_hover_color'] ?? '#005177'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Автоматическая авторизация после регистрации</th>
                        <td>
                            <input type="checkbox" name="auto_login" <?php checked($options['auto_login'] ?? false, true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Скрыть сообщение об авторизации</th>
                        <td>
                            <input type="checkbox" name="hide_auth_notice" <?php checked($options['hide_auth_notice'] ?? false, true); ?>>
                            <p class="description">Скрывает сообщение "Вы уже авторизованы" для администраторов</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Роль по умолчанию</th>
                        <td>
                            <select name="default_role">
                                <?php
                                $roles = get_editable_roles();
                                $current_role = $options['default_role'] ?? 'subscriber';

                                foreach ($roles as $role => $details) {
                                    if (in_array($role, $allowed_roles)) {
                                        echo '<option value="' . esc_attr($role) . '" ' . selected($current_role, $role, false) . '>';
                                        echo esc_html($details['name']);
                                        echo '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <p class="description">Выберите роль для новых пользователей</p>
                        </td>
                    </tr>
                    <tr>
                <th colspan="2"><h3>Уведомления о регистрации</h3></th>
                    </tr>
                    <tr>
                        <th scope="row">Включить SMS-уведомления</th>
                        <td>
                            <input type="checkbox" name="enable_sms_notify" <?php checked($options['enable_sms_notify'] ?? false, true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Номер для уведомлений</th>
                        <td>
                            <input type="tel" name="admin_phone" value="<?php echo esc_attr($options['admin_phone'] ?? ''); ?>" placeholder="+79991234567">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Включить email-уведомления</th>
                        <td>
                            <input type="checkbox" name="enable_email_notify" <?php checked($options['enable_email_notify'] ?? true, true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Email для уведомлений</th>
                        <td>
                            <input type="email" name="admin_email" value="<?php echo esc_attr($options['admin_email'] ?? get_option('admin_email')); ?>" placeholder="admin@example.com">
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h3>Изображение для формы</h3></th>
                    </tr>
                    <tr>
                        <th scope="row">URL изображения</th>
                        <td>
                            <input type="text" name="form_image_url" id="form_image_url"
                                value="<?php echo esc_attr($options['form_image_url'] ?? ''); ?>" class="regular-text">
                            <button type="button" class="button crf-upload-image">Выбрать изображение</button>
                            <p class="description">Оставьте пустым, чтобы отключить изображение</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Позиция изображения</th>
                        <td>
                            <select name="form_image_position">
                                <option value="left" <?php selected($options['form_image_position'] ?? 'right', 'left'); ?>>Слева от формы</option>
                                <option value="right" <?php selected($options['form_image_position'] ?? 'right', 'right'); ?>>Справа от формы</option>
                                <option value="top" <?php selected($options['form_image_position'] ?? 'right', 'top'); ?>>Над формой</option>
                                <option value="bottom" <?php selected($options['form_image_position'] ?? 'right', 'bottom'); ?>>Под формой</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Ширина изображения</th>
                        <td>
                            <input type="text" name="form_image_width"
                                value="<?php echo esc_attr($options['form_image_width'] ?? '300px'); ?>">
                            <p class="description">Например: 300px, 50%, auto</p>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h3>Фоновое изображение формы</h3></th>
                    </tr>
                    <tr>
                        <th scope="row">URL фонового изображения</th>
                        <td>
                            <input type="text" name="form_bg_image" id="form_bg_image"
                                value="<?php echo esc_attr($options['form_bg_image'] ?? ''); ?>" class="regular-text">
                            <button type="button" class="button crf-upload-bg-image">Выбрать изображение</button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Прозрачность формы</th>
                        <td>
                            <input type="range" name="form_bg_opacity" min="0.1" max="1" step="0.1"
                                value="<?php echo esc_attr($options['form_bg_opacity'] ?? 0.8); ?>">
                            <span class="crf-opacity-value"><?php echo esc_html($options['form_bg_opacity'] ?? 0.8); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Позиция фона</th>
                        <td>
                            <select name="form_bg_position">
                                <option value="center center" <?php selected($options['form_bg_position'] ?? 'center center', 'center center'); ?>>По центру</option>
                                <option value="left top" <?php selected($options['form_bg_position'] ?? 'center center', 'left top'); ?>>Слева сверху</option>
                                <option value="right bottom" <?php selected($options['form_bg_position'] ?? 'center center', 'right bottom'); ?>>Справа снизу</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Размер фона</th>
                        <td>
                            <select name="form_bg_size">
                                <option value="cover" <?php selected($options['form_bg_size'] ?? 'cover', 'cover'); ?>>Cover (заполнить)</option>
                                <option value="contain" <?php selected($options['form_bg_size'] ?? 'cover', 'contain'); ?>>Contain (вписать)</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('.crf-upload-image').click(function(e) {
                    e.preventDefault();
                    var image = wp.media({
                        title: 'Выберите изображение',
                        multiple: false
                    }).open()
                    .on('select', function() {
                        var uploaded_image = image.state().get('selection').first();
                        $('#form_image_url').val(uploaded_image.toJSON().url);
                    });
                });
            });
                // Для фонового изображения
            $('.crf-upload-bg-image').click(function(e) {
                e.preventDefault();
                var image = wp.media({
                    title: 'Выберите фоновое изображение',
                    multiple: false
                }).open()
                .on('select', function() {
                    var uploaded_image = image.state().get('selection').first();
                    $('#form_bg_image').val(uploaded_image.toJSON().url);
                });
            });

            // Для отображения значения прозрачности
            $('input[name="form_bg_opacity"]').on('input', function() {
                $(this).next('.crf-opacity-value').text($(this).val());
            });
        });
            </script>
    <?php
}
