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
        add_shortcode('page_nav', array($this, 'page_nav_shortcode'));
    }

    /**
     * Shortcode: [custom_hello name="User"]
     */
    public function hello_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'name' => 'User',
                'color' => 'blue'
            ),
            $atts,
            'custom_hello'
        );

        $data = array(
            'name'  => sanitize_text_field($atts['name']),
            'color' => sanitize_hex_color($atts['color']) ?: 'blue'
        );

        return Template::get('frontend/hello-message', $data);
    }

    /**
     * Shortcode: [page_nav depth="2"]
     * 
     * Otomatis mendeteksi section root dari halaman saat ini,
     * lalu menampilkan parent + semua sub-page terkait.
     * 
     * @param array $atts
     * @return string
     */
    public function page_nav_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'depth'       => 2,
                'sort_column' => 'menu_order,post_title',
            ),
            $atts,
            'page_nav'
        );

        $depth       = absint($atts['depth']);
        $sort_column = sanitize_text_field($atts['sort_column']);

        $current_id  = get_the_ID();
        if (!$current_id) {
            return '';
        }

        // Cari section root: halaman saat ini atau ancestor tertingginya
        $section_root_id = $this->get_section_root_id($current_id);
        $section_root    = get_post($section_root_id);
        if (!$section_root) {
            return '';
        }

        // Ambil anak-anak langsung dari section root
        $child_pages = get_pages(array(
            'child_of'    => $section_root_id,
            'parent'      => $section_root_id,
            'sort_column' => $sort_column,
            'sort_order'  => 'ASC',
        ));

        // Jika section root tidak punya anak, tidak ada yang ditampilkan
        if (empty($child_pages)) {
            return '';
        }

        // Build tree: section root sebagai parent, anak-anak sebagai sub
        $menu_items = $this->build_page_tree($child_pages, $current_id, $depth, 1);

        $data = array(
            'title'      => get_the_title($section_root),
            'menu_items' => $menu_items,
        );

        return Template::get('frontend/page-nav', $data);
    }

    /**
     * Cari ancestor tertinggi (section root) dari halaman saat ini.
     */
    private function get_section_root_id($page_id)
    {
        $ancestors = get_post_ancestors($page_id);
        if (empty($ancestors)) {
            return $page_id; // Halaman top-level, jadi dia sendiri root
        }
        return end($ancestors); // Ancestor paling atas
    }

    /**
     * Build recursive page tree for navigation.
     */
    private function build_page_tree($pages, $current_id, $max_depth, $current_depth)
    {
        $items = array();

        foreach ($pages as $page) {
            $is_active = ($page->ID == $current_id);
            $children  = array();

            if ($current_depth < $max_depth) {
                $child_pages = get_pages(array(
                    'child_of'    => $page->ID,
                    'parent'      => $page->ID,
                    'sort_column' => 'menu_order,post_title',
                    'sort_order'  => 'ASC',
                ));

                if (!empty($child_pages)) {
                    $is_active = $is_active || $this->is_child_active($child_pages, $current_id, $max_depth, $current_depth + 1);
                    $children = $this->build_page_tree($child_pages, $current_id, $max_depth, $current_depth + 1);
                }
            }

            $items[] = array(
                'id'        => $page->ID,
                'title'     => get_the_title($page->ID),
                'permalink' => get_permalink($page->ID),
                'active'    => $is_active,
                'children'  => $children,
            );
        }

        return $items;
    }

    /**
     * Check if any child page (or descendant) is the current page.
     */
    private function is_child_active($children, $current_id, $max_depth, $current_depth)
    {
        foreach ($children as $child) {
            if ($child->ID == $current_id) {
                return true;
            }
            if ($current_depth < $max_depth) {
                $grandchildren = get_pages(array(
                    'child_of'    => $child->ID,
                    'parent'      => $child->ID,
                    'sort_column' => 'menu_order,post_title',
                    'sort_order'  => 'ASC',
                ));
                if (!empty($grandchildren) && $this->is_child_active($grandchildren, $current_id, $max_depth, $current_depth + 1)) {
                    return true;
                }
            }
        }
        return false;
    }
}
