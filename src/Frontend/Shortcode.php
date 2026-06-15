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
        add_shortcode('show-tags', array($this, 'show_tags'));
        add_shortcode('show-date', array($this, 'show_date'));
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

    public function show_tags()
    {
        ob_start();
        global $post;
        if (empty($post)) {
            return '';
        }
        $tags = get_the_tags($post->ID);
        if ($tags) {
            echo '<div class="d-flex flex-wrap gap-2">';
            foreach ($tags as $tag) {
                $tag_link = get_tag_link($tag->term_id);
                echo '<a href="' . esc_url($tag_link) . '" class="btn btn-outline-secondary text-decoration-none rounded-pill px-3 py-1">' . esc_html('# ' . $tag->name) . '</a>';
            }
            echo '</div>';
        }
        return ob_get_clean();
    }

    public function show_date()
    {
        $hari = array(
            'Minggu',
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu'
        );
        $bulan = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        );
        $tahun = date('Y');
        $bulan_num = date('n');
        $tanggal = date('j');
        $hari_num = date('w');
        return $hari[$hari_num] . ', ' . $tanggal . ' ' . $bulan[$bulan_num] . ' ' . $tahun;
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
