<?php

namespace CustomPlugin\Frontend;

use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Shortcode
 * 
 * Best practice for Shortcode implementation.
 */
class Shortcode
{

    public function __construct()
    {
        add_shortcode('custom_hello', array($this, 'hello_shortcode'));
        add_shortcode('custom_dark_mode', array($this, 'dark_mode_shortcode'));
        // Alias biar fleksibel
        add_shortcode('dark_mode_toggle', array($this, 'dark_mode_shortcode'));
    }

    /**
     * Shortcode: [custom_dark_mode size="md" label="Toggle dark mode" class=""]
     * Tampilan hanya ikon (sun/moon) — klik untuk toggle.
     *
     * @param array $atts
     * @return string
     */
    public function dark_mode_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'size'  => 'md', // sm|md|lg
                'label' => __('Toggle dark mode', 'custom-plugin'),
                'class' => '',
            ),
            $atts,
            'custom_dark_mode'
        );

        $size  = in_array($atts['size'], array('sm', 'md', 'lg'), true) ? $atts['size'] : 'md';
        $label = sanitize_text_field($atts['label']);
        $extra = sanitize_text_field($atts['class']);

        // Ikon SVG inline (tanpa dependensi font/icon)
        $icon_sun = '<svg class="cp-darkmode-svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.42 1.41M18.36 5.64l1.42-1.42"/></svg>';
        $icon_moon = '<svg class="cp-darkmode-svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';

        ob_start();
        ?>
        <button
            type="button"
            class="cp-darkmode-toggle cp-darkmode-toggle--<?php echo esc_attr($size); ?> <?php echo esc_attr($extra); ?>"
            aria-label="<?php echo esc_attr($label); ?>"
            aria-pressed="false"
            data-cp-darkmode-toggle
        >
            <span class="cp-darkmode-icon cp-darkmode-icon--light" aria-hidden="true"><?php echo $icon_sun; // phpcs:ignore ?></span>
            <span class="cp-darkmode-icon cp-darkmode-icon--dark" aria-hidden="true"><?php echo $icon_moon; // phpcs:ignore ?></span>
        </button>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [custom_hello name="User"]
     * 
     * @param array $atts
     * @return string
     */
    public function hello_shortcode($atts)
    {
        // 1. Define default attributes and merge with user inputs
        $atts = shortcode_atts(
            array(
                'name' => 'User',
                'color' => 'blue'
            ),
            $atts,
            'custom_hello'
        );

        // 2. Data to pass to template (Logic)
        $data = array(
            'name'  => sanitize_text_field($atts['name']),
            'color' => sanitize_hex_color($atts['color']) ?: 'blue'
        );

        // 3. Render using Template Engine (Separation of Concerns)
        return Template::get('frontend/hello-message', $data);
    }
}
