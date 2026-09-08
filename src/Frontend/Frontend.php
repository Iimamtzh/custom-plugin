<?php

namespace CustomPlugin\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Frontend
 * 
 * Best practice for WordPress Action & Filter hooks.
 */
class Frontend
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('language_attributes', array($this, 'velocitytheme_color_scheme'));

        // Filter with priority and number of arguments
        // add_filter('excerpt_length', array($this, 'custom_excerpt_length'), 999, 1);

        // Trigger a custom action (so other devs can hook into your plugin)
        // do_action('custom_plugin_after_frontend_init', $this);
    }

    /**
     * Adds a data-bs-theme attribute to the <html> tag based on a cookie.
     */
    public function velocitytheme_color_scheme($output)
    {
        $color_scheme = isset($_COOKIE["color_scheme"]) ? sanitize_text_field($_COOKIE["color_scheme"]) : 'light';
        return $output . ' data-bs-theme="' . esc_attr($color_scheme) . '"';
    }

    /**
     * Proper way to filter data with arguments.
     * 
     * @param int $length
     * @return int
     */
    public function custom_excerpt_length($length)
    {
        // Apply logic only on specific pages
        if (is_front_page()) {
            return 20;
        }
        return $length;
    }

    /**
     * Best Practice: Wrapping output in a filter.
     * 
     * @return string
     */
    public static function get_formatted_price($price)
    {
        $formatted = 'Rp ' . number_format($price, 0, ',', '.');

        // Always provide a filter so others can modify your output
        return apply_filters('custom_plugin_format_price', $formatted, $price);
    }

    /**
     * Proper way to enqueue styles and scripts.
     */
    public function enqueue_scripts()
    {
        $wa = get_option('custom_plugin_cf7_whatsapp_number', '6285806522700');
        $wa = preg_replace('/\D/', '', (string) $wa);
        if (empty($wa)) {
            $wa = '6285806522700';
        }
        wp_enqueue_script('custom-plugin-cf7-whatsapp', CUSTOM_PLUGIN_URL . 'assets/frontend/js/cf7-whatsapp.js', array('jquery'), CUSTOM_PLUGIN_VERSION, true);
        wp_localize_script('custom-plugin-cf7-whatsapp', 'CustomPluginCf7Wa', array(
            'number' => $wa,
        ));

        // Dark mode toggle (ikon-only)
        wp_enqueue_style('custom-plugin-dark-mode', CUSTOM_PLUGIN_URL . 'assets/frontend/css/dark-mode.css', array(), CUSTOM_PLUGIN_VERSION);
        wp_enqueue_script('custom-plugin-dark-mode', CUSTOM_PLUGIN_URL . 'assets/frontend/js/dark-mode-toggle.js', array(), CUSTOM_PLUGIN_VERSION, true);

        // Risk disclosure (validasi checkbox + frasa, file eksternal agar tidak di-strip builder)
        // Versi pakai filemtime agar tidak ke-cache browser setelah update file JS.
        $risk_js_path = CUSTOM_PLUGIN_DIR . 'assets/frontend/js/risk-disclosure.js';
        $risk_js_ver  = file_exists($risk_js_path) ? (string) filemtime($risk_js_path) : CUSTOM_PLUGIN_VERSION;
        wp_enqueue_script('custom-plugin-risk-disclosure', CUSTOM_PLUGIN_URL . 'assets/frontend/js/risk-disclosure.js', array(), $risk_js_ver, true);
    }

    /**
     * Example: add_action('wp_head') - adding tags to <head>.
     */
    public function add_meta_tags()
    {
        // echo '<meta name="custom-plugin" content="enabled" />' . "\n";
    }

    /**
     * Example: add_filter('the_content') - modifying content before output.
     */
    public function modify_content($content)
    {
        // Always check context if modifying core templates.
        // if (is_single()) {
        //     $content .= '<div class="custom-plugin-notice">' . __('Modified by Custom Plugin', 'custom-plugin') . '</div>';
        // }
        return $content;
    }
}
