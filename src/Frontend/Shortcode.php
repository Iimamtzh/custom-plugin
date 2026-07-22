<?php

namespace CustomPlugin\Frontend;

use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Shortcode
 */
class Shortcode
{

    public function __construct()
    {
        add_shortcode('jadwal_keberangkatan', array($this, 'jadwal_keberangkatan'));
    }

    /**
     * Shortcode: [jadwal_keberangkatan]
     * 
     * Menampilkan tabel jadwal keberangkatan semua paket umrah.
     * 
     * @param array $atts
     * @return string
     */
    public function jadwal_keberangkatan($atts)
    {
        $atts = shortcode_atts(array(
            'kategori' => '',    // Slug kategori, kosong = semua
            'limit'    => -1,    // Jumlah data, -1 = semua
            'show'     => 'all', // all / tersedia / habis
        ), $atts, 'jadwal_keberangkatan');

        $args = array(
            'post_type'      => 'paket-umrah',
            'posts_per_page' => intval($atts['limit']),
            'orderby'        => 'meta_value',
            'meta_key'       => '_tanggal_berangkat',
            'order'          => 'ASC',
        );

        // Filter by kategori
        if (!empty($atts['kategori'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'kategori-paket',
                    'field'    => 'slug',
                    'terms'    => explode(',', $atts['kategori']),
                ),
            );
        }

        // Filter by availability
        if ($atts['show'] === 'tersedia') {
            $args['meta_query'] = array(
                array(
                    'key'     => '_kuota',
                    'value'   => '0',
                    'compare' => '>',
                    'type'    => 'NUMERIC',
                ),
            );
        } elseif ($atts['show'] === 'habis') {
            $args['meta_query'] = array(
                array(
                    'key'     => '_kuota',
                    'value'   => '0',
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ),
            );
        }

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            return '<p class="no-jadwal">' . esc_html__('Belum ada jadwal keberangkatan.', 'custom-plugin') . '</p>';
        }

        $rows = array();
        while ($query->have_posts()) {
            $query->the_post();
            $kuota       = intval(get_post_meta(get_the_ID(), '_kuota', true));
            $maskapai    = get_post_meta(get_the_ID(), '_maskapai', true);
            $tanggal     = get_post_meta(get_the_ID(), '_tanggal_berangkat', true);
            $pulang      = get_post_meta(get_the_ID(), '_tanggal_pulang', true);
            $harga       = get_post_meta(get_the_ID(), '_harga', true);
            $harga_coret = get_post_meta(get_the_ID(), '_harga_coret', true);
            $durasi      = get_post_meta(get_the_ID(), '_durasi', true);

            $rows[] = array(
                'paket'        => get_the_title(),
                'url'          => get_permalink(),
                'tanggal'      => $tanggal ? date_i18n('j F Y', strtotime($tanggal)) : '-',
                'pulang'       => $pulang ? date_i18n('j F Y', strtotime($pulang)) : '-',
                'durasi'       => $durasi ?: '-',
                'maskapai'     => $maskapai ?: '-',
                'harga'        => $harga ? 'Rp ' . number_format(intval($harga), 0, ',', '.') : '-',
                'harga_coret'  => $harga_coret ? 'Rp ' . number_format(intval($harga_coret), 0, ',', '.') : '',
                'kuota'        => $kuota,
                'tersedia'     => $kuota > 0 ? '<span class="badge badge-tersedia">' . esc_html__('Tersedia', 'custom-plugin') . '</span>' : '<span class="badge badge-habis">' . esc_html__('Habis', 'custom-plugin') . '</span>',
            );
        }
        wp_reset_postdata();

        $kategori_label = '';
        if (!empty($atts['kategori'])) {
            $term = get_term_by('slug', trim($atts['kategori']), 'kategori-paket');
            $kategori_label = $term ? $term->name : $atts['kategori'];
        }

        return Template::get('frontend/jadwal-keberangkatan', array(
            'rows'           => $rows,
            'kategori_label' => $kategori_label,
        ));
    }
}
