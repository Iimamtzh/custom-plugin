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
    private static $property_gallery_assets_printed = false;

    public function __construct()
    {
        add_shortcode('compare', array($this, 'compare_shortcode'));
        add_shortcode('property_search', array($this, 'property_search_shortcode'));
        add_shortcode('search', array($this, 'search_shortcode'));
        add_shortcode('property_price', array($this, 'shortcode_property_price'));
        add_shortcode('property_gallery', array($this, 'property_gallery_shortcode'));
        add_action('pre_get_posts', array($this, 'filter_property_search_query'));

        // add_shortcode('custom_hello', array($this, 'hello_shortcode'));
    }

    public function search_shortcode($atts)
    {
        return $this->property_search_shortcode($atts);
    }

    public function property_search_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'result_url' => '',
            ),
            $atts,
            'property_search'
        );

        $result_url = $atts['result_url'] ? esc_url_raw($atts['result_url']) : get_post_type_archive_link('property');

        if (!$result_url) {
            $result_url = get_permalink();
        }

        return Template::get('frontend/property-search', array(
            'result_url'         => $result_url,
            'locations'          => $this->get_taxonomy_options('location'),
            'property_types'     => $this->get_taxonomy_options('property_type'),
            'property_projects'  => $this->get_taxonomy_options('property_project'),
            'price_ranges'       => $this->price_range_options(),
            'selected_location'  => $this->get_query_value('property_location'),
            'selected_type'      => $this->get_query_value('property_type'),
            'selected_project'   => $this->get_query_value('property_project'),
            'selected_price'     => $this->get_query_value('property_price'),
        ));
    }

    public function filter_property_search_query($query)
    {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        $post_type = $query->get('post_type');
        $is_property_query = $query->is_post_type_archive('property') || $post_type === 'property';

        if (is_array($post_type)) {
            $is_property_query = in_array('property', $post_type, true);
        }

        if (!$is_property_query) {
            return;
        }

        $tax_query = array();
        $taxonomies = array(
            'property_location' => 'location',
            'property_type'     => 'property_type',
            'property_project'  => 'property_project',
        );

        foreach ($taxonomies as $query_key => $taxonomy) {
            $value = $this->get_query_value($query_key);

            if ($value) {
                $tax_query[] = array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $value,
                );
            }
        }

        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }

        if (!empty($tax_query)) {
            $query->set('tax_query', $tax_query);
        }

        $price_range = $this->get_price_range($this->get_query_value('property_price'));

        if ($price_range) {
            $meta_query = (array) $query->get('meta_query');

            $price_query = array(
                'key'     => 'property_price',
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
                'value'   => array($price_range['min'], $price_range['max']),
            );

            if ($price_range['min'] && !$price_range['max']) {
                $price_query['compare'] = '>=';
                $price_query['value'] = $price_range['min'];
            } elseif (!$price_range['min'] && $price_range['max']) {
                $price_query['compare'] = '<=';
                $price_query['value'] = $price_range['max'];
            }

            $meta_query[] = $price_query;
            $query->set('meta_query', $meta_query);
        }
    }

    private function get_taxonomy_options($taxonomy)
    {
        $terms = get_terms(array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));

        if (is_wp_error($terms)) {
            return array();
        }

        return $terms;
    }

    private function get_query_value($key)
    {
        return isset($_GET[$key]) ? sanitize_key(wp_unslash($_GET[$key])) : '';
    }

    private function price_range_options()
    {
        return array(
            'under_500m' => 'Di bawah 500 Juta',
            '500m_1b'    => '500 Juta - 1 Miliar',
            '1b_2b'      => '1 - 2 Miliar',
            '2b_5b'      => '2 - 5 Miliar',
            'above_5b'   => 'Di atas 5 Miliar',
        );
    }

    private function get_price_range($key)
    {
        $ranges = array(
            'under_500m' => array(
                'min' => 0,
                'max' => 500000000,
            ),
            '500m_1b' => array(
                'min' => 500000000,
                'max' => 1000000000,
            ),
            '1b_2b' => array(
                'min' => 1000000000,
                'max' => 2000000000,
            ),
            '2b_5b' => array(
                'min' => 2000000000,
                'max' => 5000000000,
            ),
            'above_5b' => array(
                'min' => 5000000000,
                'max' => 0,
            ),
        );

        return isset($ranges[$key]) ? $ranges[$key] : array();
    }

    public function compare_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(),
            $atts,
            'compare'
        );

        $properties = get_posts(array(
            'post_type'      => 'property',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));

        $first_id = isset($_GET['compare_property_1']) ? absint(wp_unslash($_GET['compare_property_1'])) : 0;
        $second_id = isset($_GET['compare_property_2']) ? absint(wp_unslash($_GET['compare_property_2'])) : 0;

        if (!$this->is_valid_property($first_id)) {
            $first_id = 0;
        }

        if (!$this->is_valid_property($second_id)) {
            $second_id = 0;
        }

        $compare_rows = array();

        if ($first_id && $second_id) {
            $compare_rows = $this->get_compare_rows($first_id, $second_id);
        }

        return Template::get('frontend/property-compare', array(
            'properties'   => $properties,
            'first_id'     => $first_id,
            'second_id'    => $second_id,
            'compare_rows' => $compare_rows,
        ));
    }

    private function is_valid_property($post_id)
    {
        if (!$post_id) {
            return false;
        }

        return get_post_type($post_id) === 'property' && get_post_status($post_id) === 'publish';
    }

    private function get_compare_rows($first_id, $second_id)
    {
        $rows = array(
            array(
                'label'  => 'Gambar',
                'first'  => $this->get_property_thumbnail($first_id),
                'second' => $this->get_property_thumbnail($second_id),
            ),
            array(
                'label'  => 'Tipe Properti',
                'first'  => $this->get_terms_text($first_id, 'property_type'),
                'second' => $this->get_terms_text($second_id, 'property_type'),
            ),
            array(
                'label'  => 'Lokasi',
                'first'  => $this->get_terms_text($first_id, 'location'),
                'second' => $this->get_terms_text($second_id, 'location'),
            ),
            array(
                'label'  => 'Proyek',
                'first'  => $this->get_terms_text($first_id, 'property_project'),
                'second' => $this->get_terms_text($second_id, 'property_project'),
            ),
            array(
                'label'  => 'Harga',
                'first'  => $this->format_price($this->get_meta($first_id, 'property_price')),
                'second' => $this->format_price($this->get_meta($second_id, 'property_price')),
            ),
            array(
                'label'  => 'Catatan Harga',
                'first'  => $this->get_meta_text($first_id, 'property_price_note'),
                'second' => $this->get_meta_text($second_id, 'property_price_note'),
            ),
            array(
                'label'  => 'Tipe Transaksi',
                'first'  => $this->get_mapped_meta($first_id, 'property_transaction_type', $this->transaction_type_options()),
                'second' => $this->get_mapped_meta($second_id, 'property_transaction_type', $this->transaction_type_options()),
            ),
            array(
                'label'  => 'Status Listing',
                'first'  => $this->get_mapped_meta($first_id, 'property_listing_status', $this->listing_status_options()),
                'second' => $this->get_mapped_meta($second_id, 'property_listing_status', $this->listing_status_options()),
            ),
            array(
                'label'  => 'Kamar Tidur',
                'first'  => $this->get_meta_text($first_id, 'property_bedrooms'),
                'second' => $this->get_meta_text($second_id, 'property_bedrooms'),
            ),
            array(
                'label'  => 'Kamar Mandi',
                'first'  => $this->get_meta_text($first_id, 'property_bathrooms'),
                'second' => $this->get_meta_text($second_id, 'property_bathrooms'),
            ),
            array(
                'label'  => 'Garasi / Carport',
                'first'  => $this->get_meta_text($first_id, 'property_garage'),
                'second' => $this->get_meta_text($second_id, 'property_garage'),
            ),
            array(
                'label'  => 'Luas Bangunan',
                'first'  => $this->format_area($this->get_meta($first_id, 'property_building_area')),
                'second' => $this->format_area($this->get_meta($second_id, 'property_building_area')),
            ),
            array(
                'label'  => 'Luas Tanah',
                'first'  => $this->format_area($this->get_meta($first_id, 'property_land_area')),
                'second' => $this->format_area($this->get_meta($second_id, 'property_land_area')),
            ),
            array(
                'label'  => 'Jumlah Lantai',
                'first'  => $this->get_meta_text($first_id, 'property_floors'),
                'second' => $this->get_meta_text($second_id, 'property_floors'),
            ),
            array(
                'label'  => 'Sertifikat',
                'first'  => $this->get_mapped_meta($first_id, 'property_certificate', $this->certificate_options()),
                'second' => $this->get_mapped_meta($second_id, 'property_certificate', $this->certificate_options()),
            ),
            array(
                'label'  => 'Kondisi Furnitur',
                'first'  => $this->get_mapped_meta($first_id, 'property_furnishing', $this->furnishing_options()),
                'second' => $this->get_mapped_meta($second_id, 'property_furnishing', $this->furnishing_options()),
            ),
            array(
                'label'  => 'Alamat',
                'first'  => $this->get_meta_text($first_id, 'property_address'),
                'second' => $this->get_meta_text($second_id, 'property_address'),
            ),
            array(
                'label'  => 'Kota',
                'first'  => $this->get_meta_text($first_id, 'property_city'),
                'second' => $this->get_meta_text($second_id, 'property_city'),
            ),
            array(
                'label'  => 'Provinsi',
                'first'  => $this->get_meta_text($first_id, 'property_province'),
                'second' => $this->get_meta_text($second_id, 'property_province'),
            ),
            array(
                'label'  => 'Galeri Foto',
                'first'  => $this->format_gallery_count($first_id),
                'second' => $this->format_gallery_count($second_id),
            ),
        );

        return apply_filters('custom_plugin_property_compare_rows', $rows, $first_id, $second_id);
    }

    private function get_property_thumbnail($post_id)
    {
        if (!has_post_thumbnail($post_id)) {
            return '-';
        }

        return get_the_post_thumbnail($post_id, 'medium', array(
            'class' => 'custom-property-compare__image',
            'alt'   => get_the_title($post_id),
        ));
    }

    private function get_terms_text($post_id, $taxonomy)
    {
        $terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'names'));

        if (is_wp_error($terms) || empty($terms)) {
            return '-';
        }

        return implode(', ', $terms);
    }

    private function get_meta($post_id, $key)
    {
        return get_post_meta($post_id, $key, true);
    }

    private function get_meta_text($post_id, $key)
    {
        $value = $this->get_meta($post_id, $key);

        if ($value === '' || $value === array()) {
            return '-';
        }

        return is_array($value) ? implode(', ', array_filter($value)) : (string) $value;
    }

    private function get_mapped_meta($post_id, $key, $options)
    {
        $value = $this->get_meta($post_id, $key);

        if ($value === '') {
            return '-';
        }

        return isset($options[$value]) ? $options[$value] : $value;
    }

    private function format_price($price)
    {
        if ($price === '' || !is_numeric($price)) {
            return '-';
        }

        return Frontend::get_formatted_price((float) $price);
    }

    private function format_area($area)
    {
        if ($area === '' || !is_numeric($area)) {
            return '-';
        }

        return number_format((float) $area, 0, ',', '.') . ' m2';
    }

    private function format_gallery_count($post_id)
    {
        $gallery = get_post_meta($post_id, 'property_gallery', false);
        $gallery = array_filter($gallery);

        if (empty($gallery)) {
            return '-';
        }

        return count($gallery) . ' foto';
    }

    private function transaction_type_options()
    {
        return array(
            'sale' => 'Dijual',
            'rent' => 'Disewa',
        );
    }

    private function listing_status_options()
    {
        return array(
            'available' => 'Tersedia',
            'booked'    => 'Booked',
            'sold'      => 'Terjual',
            'rented'    => 'Tersewa',
        );
    }

    private function certificate_options()
    {
        return array(
            'shm'          => 'SHM',
            'hgb'          => 'HGB',
            'girik'        => 'Girik',
            'strata_title' => 'Strata Title',
            'other'        => 'Lainnya',
        );
    }

    private function furnishing_options()
    {
        return array(
            'unfurnished'    => 'Unfurnished',
            'semi_furnished' => 'Semi Furnished',
            'furnished'      => 'Furnished',
        );
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

    public function shortcode_property_price()
    {
        global $post;

        ob_start();

        if (!$post) {
            return '';
        }

        $price = get_post_meta($post->ID, 'property_price', true);

        if (!empty($price)) {
            echo 'Rp ' . number_format((float) $price, 0, ',', '.');
        } else {
            echo 'Hubungi Kami';
        }

        return ob_get_clean();
    }

    public function property_gallery_shortcode($atts)
    {
        global $post;

        $atts = shortcode_atts(
            array(
                'id'    => 0,
                'limit' => 3,
            ),
            $atts,
            'property_gallery'
        );

        $post_id = absint($atts['id']);

        if (!$post_id && $post instanceof \WP_Post) {
            $post_id = (int) $post->ID;
        }

        if (!$post_id) {
            return '';
        }

        $gallery_ids = $this->get_property_gallery_ids($post_id);

        if (empty($gallery_ids)) {
            return '';
        }

        $limit = max(1, min(3, absint($atts['limit']) ?: 3));
        $items = array();

        foreach ($gallery_ids as $attachment_id) {
            $full = wp_get_attachment_image_url($attachment_id, 'full');
            $large = wp_get_attachment_image_url($attachment_id, 'large');

            if (!$full || !$large) {
                continue;
            }

            $items[] = array(
                'attachment_id' => $attachment_id,
                'full_url'      => $full,
                'preview_url'   => $large,
                'thumbnail_url' => wp_get_attachment_image_url($attachment_id, 'thumbnail') ?: $large,
                'alt'           => get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: get_the_title($post_id),
                'caption'       => wp_get_attachment_caption($attachment_id),
            );
        }

        if (empty($items)) {
            return '';
        }

        $preview_items = array_slice($items, 0, $limit);
        $gallery_id = wp_unique_id('custom-property-gallery-');
        $output = '';

        if (!self::$property_gallery_assets_printed) {
            $output .= $this->get_property_gallery_assets();
            self::$property_gallery_assets_printed = true;
        }

        $output .= Template::get('frontend/property-gallery', array(
            'gallery_id'     => $gallery_id,
            'post_id'        => $post_id,
            'post_title'     => get_the_title($post_id),
            'items'          => $items,
            'preview_items'  => $preview_items,
            'total_images'   => count($items),
        ));

        return $output;
    }

    private function get_property_gallery_ids($post_id)
    {
        $gallery = get_post_meta($post_id, 'property_gallery', false);

        if (!is_array($gallery)) {
            $gallery = array();
        }

        $gallery = array_values(array_filter(array_map('absint', $gallery)));

        if (empty($gallery) && has_post_thumbnail($post_id)) {
            $gallery[] = get_post_thumbnail_id($post_id);
        }

        return array_values(array_unique($gallery));
    }

    private function get_property_gallery_assets()
    {
        ob_start();
?>
        <style>
            .custom-property-gallery {
                display: grid;
                grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
                gap: 16px;
                margin: 24px 0;
            }

            .custom-property-gallery__primary,
            .custom-property-gallery__secondary {
                min-width: 0;
            }

            .custom-property-gallery__secondary {
                display: grid;
                gap: 16px;
            }

            .custom-property-gallery__card {
                position: relative;
                display: block;
                width: 100%;
                height: 100%;
                overflow: hidden;
                border: 0;
                border-radius: 18px;
                padding: 0;
                cursor: pointer;
                background: #e9e1d4;
                box-shadow: 0 14px 40px rgba(17, 24, 39, 0.14);
            }

            .custom-property-gallery__card img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.35s ease;
            }

            .custom-property-gallery__card:hover img,
            .custom-property-gallery__card:focus-visible img {
                transform: scale(1.04);
            }

            .custom-property-gallery__primary .custom-property-gallery__card {
                aspect-ratio: 16 / 10;
            }

            .custom-property-gallery__secondary .custom-property-gallery__card {
                aspect-ratio: 16 / 7.65;
            }

            .custom-property-gallery__overlay {
                position: absolute;
                inset: auto 16px 16px auto;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                border-radius: 999px;
                padding: 12px 16px;
                background: rgba(15, 23, 42, 0.72);
                color: #fff;
                font-size: 15px;
                font-weight: 600;
                backdrop-filter: blur(8px);
            }

            .custom-property-gallery__count {
                position: absolute;
                top: 16px;
                right: 16px;
                border-radius: 999px;
                padding: 8px 12px;
                background: rgba(255, 255, 255, 0.92);
                color: #111827;
                font-size: 13px;
                font-weight: 700;
            }

            .custom-property-gallery__modal[hidden] {
                display: none !important;
            }

            .custom-property-gallery__modal {
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background: rgba(2, 6, 23, 0.86);
            }

            .custom-property-gallery__dialog {
                position: relative;
                width: min(1100px, 100%);
                max-height: min(92vh, 900px);
                overflow: hidden;
                border-radius: 24px;
                background: #0f172a;
                color: #fff;
                box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
            }

            .custom-property-gallery__viewport {
                position: relative;
                background: #020617;
            }

            .custom-property-gallery__viewport img {
                display: block;
                width: 100%;
                max-height: 72vh;
                object-fit: contain;
                background: #020617;
            }

            .custom-property-gallery__toolbar {
                position: absolute;
                top: 16px;
                right: 16px;
                left: 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                pointer-events: none;
            }

            .custom-property-gallery__toolbar>* {
                pointer-events: auto;
            }

            .custom-property-gallery__index,
            .custom-property-gallery__close,
            .custom-property-gallery__nav {
                border: 0;
                border-radius: 999px;
                background: rgba(15, 23, 42, 0.82);
                color: #fff;
            }

            .custom-property-gallery__index {
                padding: 10px 14px;
                font-size: 14px;
                font-weight: 600;
            }

            .custom-property-gallery__close,
            .custom-property-gallery__nav {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                cursor: pointer;
                font-size: 22px;
                line-height: 1;
            }

            .custom-property-gallery__nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
            }

            .custom-property-gallery__nav--prev {
                left: 16px;
            }

            .custom-property-gallery__nav--next {
                right: 16px;
            }

            .custom-property-gallery__meta {
                padding: 18px 20px 10px;
            }

            .custom-property-gallery__title {
                margin: 0;
                font-size: 20px;
                font-weight: 700;
                color: #fff;
            }

            .custom-property-gallery__caption {
                margin: 8px 0 0;
                font-size: 14px;
                color: rgba(255, 255, 255, 0.78);
            }

            .custom-property-gallery__thumbs {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(82px, 1fr));
                gap: 10px;
                padding: 0 20px 20px;
            }

            .custom-property-gallery__thumb {
                border: 2px solid transparent;
                border-radius: 14px;
                padding: 0;
                overflow: hidden;
                cursor: pointer;
                background: transparent;
                opacity: 0.72;
            }

            .custom-property-gallery__thumb.is-active {
                border-color: #f59e0b;
                opacity: 1;
            }

            .custom-property-gallery__thumb img {
                display: block;
                width: 100%;
                aspect-ratio: 1 / 1;
                object-fit: cover;
            }

            @media (max-width: 767px) {
                .custom-property-gallery {
                    grid-template-columns: 1fr;
                }

                .custom-property-gallery__secondary {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .custom-property-gallery__overlay {
                    inset: auto 12px 12px auto;
                    padding: 10px 14px;
                    font-size: 14px;
                }

                .custom-property-gallery__dialog {
                    border-radius: 18px;
                }

                .custom-property-gallery__viewport img {
                    max-height: 54vh;
                }

                .custom-property-gallery__nav {
                    width: 40px;
                    height: 40px;
                }
            }
        </style>
        <script>
            (function() {
                if (window.customPropertyGalleryInit) {
                    return;
                }

                window.customPropertyGalleryInit = true;

                document.addEventListener('click', function(event) {
                    var trigger = event.target.closest('[data-property-gallery-trigger]');
                    var modal = event.target.closest('[data-property-gallery-modal]');
                    var closeButton = event.target.closest('[data-property-gallery-close]');
                    var navButton = event.target.closest('[data-property-gallery-nav]');
                    var thumbButton = event.target.closest('[data-property-gallery-thumb]');

                    if (trigger) {
                        event.preventDefault();
                        openGallery(trigger.getAttribute('data-property-gallery-target'), parseInt(trigger.getAttribute('data-index'), 10) || 0);
                        return;
                    }

                    if (thumbButton) {
                        event.preventDefault();
                        updateGallery(modal, parseInt(thumbButton.getAttribute('data-index'), 10) || 0);
                        return;
                    }

                    if (navButton && modal) {
                        event.preventDefault();
                        navigateGallery(modal, navButton.getAttribute('data-property-gallery-nav') === 'next' ? 1 : -1);
                        return;
                    }

                    if (closeButton && modal) {
                        event.preventDefault();
                        closeGallery(modal);
                        return;
                    }

                    if (modal && event.target === modal) {
                        closeGallery(modal);
                    }
                });

                document.addEventListener('keydown', function(event) {
                    var modal = document.querySelector('.custom-property-gallery__modal:not([hidden])');

                    if (!modal) {
                        return;
                    }

                    if (event.key === 'Escape') {
                        closeGallery(modal);
                    } else if (event.key === 'ArrowRight') {
                        navigateGallery(modal, 1);
                    } else if (event.key === 'ArrowLeft') {
                        navigateGallery(modal, -1);
                    }
                });

                function openGallery(targetId, index) {
                    var modal = document.getElementById(targetId);

                    if (!modal) {
                        return;
                    }

                    modal.hidden = false;
                    document.body.style.overflow = 'hidden';
                    updateGallery(modal, index);
                }

                function closeGallery(modal) {
                    modal.hidden = true;
                    document.body.style.overflow = '';
                }

                function navigateGallery(modal, direction) {
                    var items = getItems(modal);
                    var current = parseInt(modal.getAttribute('data-current-index'), 10) || 0;
                    var next = (current + direction + items.length) % items.length;
                    updateGallery(modal, next);
                }

                function updateGallery(modal, index) {
                    var items = getItems(modal);
                    var item = items[index];

                    if (!item) {
                        return;
                    }

                    modal.setAttribute('data-current-index', index);

                    var image = modal.querySelector('[data-property-gallery-image]');
                    var caption = modal.querySelector('[data-property-gallery-caption]');
                    var counter = modal.querySelector('[data-property-gallery-counter]');
                    var thumbs = modal.querySelectorAll('[data-property-gallery-thumb]');

                    if (image) {
                        image.src = item.fullUrl;
                        image.alt = item.alt || '';
                    }

                    if (caption) {
                        caption.textContent = item.caption || '';
                        caption.hidden = !item.caption;
                    }

                    if (counter) {
                        counter.textContent = (index + 1) + ' / ' + items.length;
                    }

                    thumbs.forEach(function(thumb, thumbIndex) {
                        thumb.classList.toggle('is-active', thumbIndex === index);
                    });
                }

                function getItems(modal) {
                    try {
                        return JSON.parse(modal.getAttribute('data-items') || '[]');
                    } catch (error) {
                        return [];
                    }
                }
            }());
        </script>
<?php

        return ob_get_clean();
    }
}
