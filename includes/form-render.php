<?php
/*function crf_render_registration_form() {
    $options = get_option('custom_registration_form_settings');
    ob_start();

    // Получаем настройки изображения
    $image_url = esc_url($options['form_image_url'] ?? '');
    $position = esc_attr($options['form_image_position'] ?? 'right');
    $width = esc_attr($options['form_image_width'] ?? '300px');

    if ($image_url) {
        echo '<div class="crf-form-container crf-image-' . $position . '">';

        if (in_array($position, ['left', 'top'])) {
            echo '<div class="crf-form-image"><img src="' . $image_url . '" style="width:' . $width . '"></div>';
        }
    }
    ?>*/
function crf_render_registration_form() {
    $options = get_option('custom_registration_form_settings');
    ob_start();

    // 1. Настройки фонового изображения
    $bg_image = esc_url($options['form_bg_image'] ?? ''); // URL картинки
    $bg_opacity = esc_attr($options['form_bg_opacity'] ?? 0.8); // Прозрачность (0.1-1)
    $bg_position = esc_attr($options['form_bg_position'] ?? 'center center'); // Позиция
    $bg_size = esc_attr($options['form_bg_size'] ?? 'cover'); // Размер

    // 2. Создаём обёртку для формы с фоном
    echo '<div class="crf-form-wrapper">';

    // 3. Если есть фоновое изображение - выводим его
    if ($bg_image) {
        echo '<div class="crf-form-bg" style="'
            . 'background-image: url(' . esc_url($bg_image) . '); '
            . 'background-position: ' . esc_attr($bg_position) . '; '
            . 'background-size: ' . esc_attr($bg_size) . '; '
            . 'opacity: ' . esc_attr($bg_opacity) . ';'
            . '"></div>';
    }


    // 4. Основная форма (как у вас было)
    ?>

    <form id="registration-form" class="<?php echo $bg_image ? 'has-bg' : ''; ?>">
        <input type="hidden" name="action" value="handle_registration">
        <?php wp_nonce_field('registration_nonce', 'registration_nonce_field'); ?>

        <?php if ($options['show_username_field'] ?? true) : ?>
        <input type="text" name="username" placeholder="<?php echo esc_attr($options['username_placeholder'] ?? 'Имя пользователя'); ?>" <?php echo ($options['username_required'] ?? true) ? 'required' : ''; ?>>
        <?php endif; ?>

        <input type="email" name="email" placeholder="<?php echo esc_attr($options['email_placeholder'] ?? 'Email'); ?>" required>

        <input type="password" name="password" placeholder="<?php echo esc_attr($options['password_placeholder'] ?? 'Пароль'); ?>" required>

        <?php if ($options['show_phone_field'] ?? false) : ?>
        <input type="tel" name="phone" placeholder="<?php echo esc_attr($options['phone_placeholder'] ?? 'Телефон'); ?>" <?php echo ($options['phone_required'] ?? false) ? 'required' : ''; ?>>
        <?php endif; ?>

        <button type="submit"><?php echo esc_html($options['submit_text'] ?? 'Зарегистрироваться'); ?></button>
    </form>
    <?php
        // Закрываем обёртку
    echo '</div>';

 /*   if ($image_url) {
        if (in_array($position, ['right', 'bottom'])) {
            echo '<div class="crf-form-image"><img src="' . $image_url . '" style="width:' . $width . '"></div>';
        }
        echo '</div>'; // закрываем .crf-form-container
    }*/

    // Добавляем CSS
    ?>
    <style>
        /* Обёртка формы */
    .crf-form-wrapper {
        position: relative;
        padding: 30px;
        border-radius: 10px;
        overflow: hidden;
    }

    /* Фоновое изображение */
    .crf-form-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    /* Сама форма */
    #registration-form {
        position: relative;
        z-index: 2;
        max-width: <?php echo esc_attr($options['form_width'] ?? '300px'); ?>;
        margin: 0 auto;
        padding: 20px;
        background: rgba(255,255,255,0.85);
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    }
    .crf-form-container {
        display: flex;
        gap: 30px;
        align-items: center;
        max-width: 800px;
        margin: 0 auto;
    }
    .crf-form-image img {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .crf-image-left, .crf-image-right {
        flex-direction: row;
    }
    .crf-image-top, .crf-image-bottom {
        flex-direction: column;
    }
    #registration-form {
        flex: 1;
    }
            #registration-form {
            max-width: <?php echo esc_attr($options['form_width'] ?? '300px'); ?>;
            margin: auto;
            padding: 20px;
            background: <?php echo esc_attr($options['form_bg_color'] ?? '#f9f9f9'); ?>;
            border-radius: 5px;
        }
        #registration-form input {
            display: block;
            margin-bottom: 10px;
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid <?php echo esc_attr($options['input_border_color'] ?? '#ddd'); ?>;
        }
        #registration-form button {
            padding: 10px;
            background: <?php echo esc_attr($options['button_bg_color'] ?? '#0073aa'); ?>;
            color: <?php echo esc_attr($options['button_text_color'] ?? 'white'); ?>;
            border: none;
            cursor: pointer;
            width: 100%;
            border-radius: 3px;
        }
        #registration-form button:hover {
            background: <?php echo esc_attr($options['button_hover_color'] ?? '#005177'); ?>;
        }
    </style>
    <script>
    document.getElementById("registration-form").addEventListener("submit", function(event) {
        event.preventDefault();
        let formData = new FormData(this);

        fetch('<?php echo esc_url("admin-ajax.php"); ?>', {
            method: "POST",
            body: formData
        }).then(response => response.json())
          .then(data => {
              alert(data.message);
              if (data.success) {
                  window.location.href = data.redirect || '<?php echo esc_url($options['redirect_url'] ?? home_url()); ?>';
              }
          })
          .catch(error => console.error("Ошибка:", error));
    });
    </script>


    <?php
    return ob_get_clean();
}
