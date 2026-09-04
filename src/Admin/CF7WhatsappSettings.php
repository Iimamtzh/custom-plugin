<?php

namespace CustomPlugin\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class CF7WhatsappSettings
{
    const OPTION_KEY = 'custom_plugin_cf7_whatsapp_number';
    const DEFAULT_NUMBER = '6285806522700';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_submenu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_submenu()
    {
        add_submenu_page(
            'wpcf7',
            __('WhatsApp CF7', 'custom-plugin'),
            __('WhatsApp', 'custom-plugin'),
            'manage_options',
            'custom-plugin-cf7-whatsapp',
            array($this, 'render_page')
        );
    }

    public function register_settings()
    {
        register_setting(
            'custom_plugin_cf7_whatsapp_group',
            self::OPTION_KEY,
            array(
                'type'              => 'string',
                'sanitize_callback' => array($this, 'sanitize_number'),
                'default'           => self::DEFAULT_NUMBER,
            )
        );

        add_settings_section(
            'custom_plugin_cf7_whatsapp_section',
            __('Pengaturan WhatsApp CF7', 'custom-plugin'),
            array($this, 'section_desc'),
            'custom-plugin-cf7-whatsapp'
        );

        add_settings_field(
            self::OPTION_KEY,
            __('Nomor WhatsApp Admin', 'custom-plugin'),
            array($this, 'field_number'),
            'custom-plugin-cf7-whatsapp',
            'custom_plugin_cf7_whatsapp_section'
        );
    }

    public function sanitize_number($value)
    {
        $value = preg_replace('/\D/', '', (string) $value);
        if (empty($value)) {
            add_settings_error(self::OPTION_KEY, 'empty', __('Nomor WhatsApp tidak boleh kosong.', 'custom-plugin'));
            return get_option(self::OPTION_KEY, self::DEFAULT_NUMBER);
        }
        // Optional: ensure starts with 62, strip leading 0
        if (substr($value, 0, 1) === '0') {
            $value = '62' . substr($value, 1);
        }
        return $value;
    }

    public function section_desc()
    {
        echo '<p>' . esc_html__('Atur nomor WhatsApp tujuan untuk redirect setelah submit CF7. Format: 628xxxxxxxxxx (tanpa + / spasi).', 'custom-plugin') . '</p>';
    }

    public function field_number()
    {
        $value = get_option(self::OPTION_KEY, self::DEFAULT_NUMBER);
        echo '<input type="text" name="' . esc_attr(self::OPTION_KEY) . '" value="' . esc_attr($value) . '" class="regular-text" placeholder="6285806522700" pattern="[0-9]*" inputmode="numeric" />';
        echo '<p class="description">' . esc_html__('Contoh: 6281234567890. Disimpan tanpa tanda +.', 'custom-plugin') . '</p>';
        echo '<p class="description">Preview link: <code>https://wa.me/' . esc_html($value) . '?text=...</code></p>';
    }

    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('WhatsApp CF7', 'custom-plugin'); ?></h1>
            <p><?php esc_html_e('Script akan redirect ke WhatsApp setelah form CF7 berhasil submit (status bukan validation_failed).', 'custom-plugin'); ?></p>
            <form method="post" action="options.php">
                <?php
                settings_fields('custom_plugin_cf7_whatsapp_group');
                do_settings_sections('custom-plugin-cf7-whatsapp');
                submit_button(__('Simpan Nomor', 'custom-plugin'));
                ?>
            </form>
            <hr>
            <h2><?php esc_html_e('Cara kerja', 'custom-plugin'); ?></h2>
            <p><?php esc_html_e('Field yang diambil otomatis: your-name, your-email, your-whatsapp, your-city, trading-experience, learning-readiness, trading-challenge[]. Pastikan name field di CF7 sesuai.', 'custom-plugin'); ?></p>
        </div>
        <?php
    }
}
