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
        // Example: To activate, uncomment the line below.
        // add_shortcode('custom_hello', array($this, 'hello_shortcode'));
        add_shortcode('show-view', array($this, 'show_view'));
    }

    public function show_view()
    {
        ob_start();
        global $post;
        if (empty($post)) {
            return '';
        }
        $post_id = $post->ID;
        $count = get_post_meta($post_id, 'view_count', true);
        $count = $count ? $count : 0;
        echo '<span class="view-count">' . $count . ' views</span>';
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
