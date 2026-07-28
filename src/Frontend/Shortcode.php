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
        add_shortcode('daftar_dokumen', array($this, 'daftar_dokumen_shortcode'));
        add_shortcode('dokumen_viewer', array($this, 'dokumen_viewer_shortcode'));
    }

    /**
     * Shortcode: [daftar_dokumen]
     * Menampilkan grid daftar dokumen dengan filter kategori.
     *
     * @param array $atts
     * @return string
     */
    public function daftar_dokumen_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'kategori'     => '',
                'per_page'     => 12,
                'show_filter'  => 'yes',
                'show_search'  => 'yes',
            ),
            $atts,
            'daftar_dokumen'
        );

        // Build WP_Query args
        $args = array(
            'post_type'      => 'dokumen',
            'posts_per_page' => absint($atts['per_page']),
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        // Filter by category slug if provided
        if (!empty($atts['kategori'])) {
            $category_slugs = array_map('trim', explode(',', $atts['kategori']));
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'kategori_dokumen',
                    'field'    => 'slug',
                    'terms'    => $category_slugs,
                ),
            );
        }

        // Handle search
        if ($atts['show_search'] === 'yes' && isset($_GET['cari_dokumen']) && !empty($_GET['cari_dokumen'])) {
            $args['s'] = sanitize_text_field($_GET['cari_dokumen']);
        }

        // Handle category filter from URL
        if ($atts['show_filter'] === 'yes' && isset($_GET['kat_dokumen']) && !empty($_GET['kat_dokumen'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'kategori_dokumen',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['kat_dokumen']),
                ),
            );
        }

        // Pagination
        $paged = get_query_var('paged') ? get_query_var('paged') : (isset($_GET['halaman']) ? absint($_GET['halaman']) : 1);
        $args['paged'] = $paged;

        $query = new \WP_Query($args);

        // Get all categories for filter
        $categories = get_terms(array(
            'taxonomy'   => 'kategori_dokumen',
            'hide_empty' => true,
        ));

        $current_kat = isset($_GET['kat_dokumen']) ? sanitize_text_field($_GET['kat_dokumen']) : '';
        $current_search = isset($_GET['cari_dokumen']) ? sanitize_text_field($_GET['cari_dokumen']) : '';

        $data = array(
            'query'           => $query,
            'categories'      => $categories,
            'current_kat'     => $current_kat,
            'current_search'  => $current_search,
            'show_filter'     => $atts['show_filter'],
            'show_search'     => $atts['show_search'],
            'paged'           => $paged,
        );

        return Template::get('frontend/daftar-dokumen', $data);
    }

    /**
     * Shortcode: [dokumen_viewer id="123"]
     * Menampilkan PDF viewer untuk dokumen tertentu.
     *
     * @param array $atts
     * @return string
     */
    public function dokumen_viewer_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'dokumen_viewer'
        );

        $post_id = absint($atts['id']);

        if (!$post_id || get_post_type($post_id) !== 'dokumen') {
            return '<p>Dokumen tidak ditemukan.</p>';
        }

        $pdf_id  = get_post_meta($post_id, '_dokumen_pdf_id', true);
        $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';

        $data = array(
            'post'    => get_post($post_id),
            'pdf_url' => $pdf_url,
        );

        return Template::get('frontend/single-dokumen', $data);
    }
}
