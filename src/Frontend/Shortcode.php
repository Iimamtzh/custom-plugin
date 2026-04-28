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
    private static $mortgage_assets_printed = false;
    private static $property_specification_assets_printed = false;

    public function __construct()
    {
        add_shortcode('compare', array($this, 'compare_shortcode'));
        add_shortcode('property_search', array($this, 'property_search_shortcode'));
        add_shortcode('search', array($this, 'search_shortcode'));
        add_shortcode('property_price', array($this, 'shortcode_property_price'));
        add_shortcode('property_specifications', array($this, 'property_specifications_shortcode'));
        add_shortcode('property_location_map', array($this, 'property_location_map_shortcode'));
        add_shortcode('property_gallery', array($this, 'property_gallery_shortcode'));
        add_shortcode('simulasi_kpr', array($this, 'mortgage_simulation_shortcode'));
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
        global $post;

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

        $current_property_id = 0;

        if ($post instanceof \WP_Post && get_post_type($post) === 'property' && get_post_status($post) === 'publish') {
            $current_property_id = (int) $post->ID;
        }

        $compare_inputs = $this->get_compare_property_inputs($current_property_id);
        $selected_ids = $this->get_compare_property_ids($compare_inputs);
        $compare_rows = array();

        if (count($selected_ids) >= 2) {
            $compare_rows = $this->get_compare_rows($selected_ids);
        }

        return Template::get('frontend/property-compare', array(
            'properties'           => $properties,
            'selected_ids'         => $selected_ids,
            'compare_inputs'       => $compare_inputs,
            'current_property_id'  => $current_property_id,
            'compare_rows'         => $compare_rows,
        ));
    }

    private function is_valid_property($post_id)
    {
        if (!$post_id) {
            return false;
        }

        return get_post_type($post_id) === 'property' && get_post_status($post_id) === 'publish';
    }

    private function get_compare_property_inputs($current_property_id = 0)
    {
        $inputs = array(
            1 => 0,
            2 => 0,
            3 => 0,
        );

        if ($current_property_id && $this->is_valid_property($current_property_id)) {
            $inputs[1] = $current_property_id;
        }

        for ($i = 1; $i <= 3; $i++) {
            if ($current_property_id && 1 === $i) {
                continue;
            }

            $key = 'compare_property_' . $i;
            $property_id = isset($_GET[$key]) ? absint(wp_unslash($_GET[$key])) : 0;

            if (!$property_id || !$this->is_valid_property($property_id)) {
                continue;
            }

            $inputs[$i] = $property_id;
        }

        return $inputs;
    }

    private function get_compare_property_ids($inputs)
    {
        if (!is_array($inputs)) {
            return array();
        }

        return array_values(array_unique(array_filter(array_map('absint', $inputs))));
    }

    private function get_compare_rows($selected_ids)
    {
        $rows = array();

        if (empty($selected_ids)) {
            return $rows;
        }

        $definitions = array(
            'Gambar' => function ($post_id) {
                return $this->get_property_thumbnail($post_id);
            },
            'Tipe Properti' => function ($post_id) {
                return $this->get_terms_text($post_id, 'property_type');
            },
            'Lokasi' => function ($post_id) {
                return $this->get_terms_text($post_id, 'location');
            },
            'Proyek' => function ($post_id) {
                return $this->get_terms_text($post_id, 'property_project');
            },
            'Harga' => function ($post_id) {
                return $this->format_price($this->get_meta($post_id, 'property_price'));
            },
            'Catatan Harga' => function ($post_id) {
                return $this->get_meta_text($post_id, 'property_price_note');
            },
            'Tipe Transaksi' => function ($post_id) {
                return $this->get_mapped_meta($post_id, 'property_transaction_type', $this->transaction_type_options());
            },
            'Status Listing' => function ($post_id) {
                return $this->get_mapped_meta($post_id, 'property_listing_status', $this->listing_status_options());
            },
            'Kamar Tidur' => function ($post_id) {
                return $this->get_meta_text($post_id, 'property_bedrooms');
            },
            'Kamar Mandi' => function ($post_id) {
                return $this->get_meta_text($post_id, 'property_bathrooms');
            },
            'Garasi / Carport' => function ($post_id) {
                return $this->get_meta_text($post_id, 'property_garage');
            },
            'Luas Bangunan' => function ($post_id) {
                return $this->format_area($this->get_meta($post_id, 'property_building_area'));
            },
            'Luas Tanah' => function ($post_id) {
                return $this->format_area($this->get_meta($post_id, 'property_land_area'));
            },
            'Jumlah Lantai' => function ($post_id) {
                return $this->get_meta_text($post_id, 'property_floors');
            },
            'Sertifikat' => function ($post_id) {
                return $this->get_mapped_meta($post_id, 'property_certificate', $this->certificate_options());
            },
            'Kondisi Furnitur' => function ($post_id) {
                return $this->get_mapped_meta($post_id, 'property_furnishing', $this->furnishing_options());
            },
            'Alamat' => function ($post_id) {
                return $this->get_meta_text($post_id, 'property_address');
            },
            'Kota' => function ($post_id) {
                return $this->get_meta_text($post_id, 'property_city');
            },
            'Provinsi' => function ($post_id) {
                return $this->get_meta_text($post_id, 'property_province');
            },
            'Galeri Foto' => function ($post_id) {
                return $this->format_gallery_count($post_id);
            },
        );

        foreach ($definitions as $label => $callback) {
            $values = array();

            foreach ($selected_ids as $post_id) {
                $values[] = call_user_func($callback, $post_id);
            }

            $rows[] = array(
                'label'  => $label,
                'values' => $values,
            );
        }

        return apply_filters('custom_plugin_property_compare_rows', $rows, $selected_ids);
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

    public function property_specifications_shortcode($atts)
    {
        global $post;

        $atts = shortcode_atts(
            array(
                'id'    => 0,
                'title' => 'Spesifikasi Properti',
            ),
            $atts,
            'property_specifications'
        );

        $post_id = absint($atts['id']);

        if (!$post_id && $post instanceof \WP_Post) {
            $post_id = (int) $post->ID;
        }

        if (!$post_id || get_post_type($post_id) !== 'property') {
            return '';
        }

        $items = $this->get_property_specification_items($post_id);

        if (empty($items)) {
            return '';
        }

        $output = '';

        if (!self::$property_specification_assets_printed) {
            $output .= $this->get_property_specifications_assets();
            self::$property_specification_assets_printed = true;
        }

        $output .= '<section class="custom-property-specifications" aria-label="' . esc_attr($atts['title']) . '">';
        $output .= '<h3 class="custom-property-specifications__title">' . esc_html($atts['title']) . '</h3>';
        $output .= '<div class="custom-property-specifications__grid">';

        foreach ($items as $item) {
            $output .= '<div class="custom-property-specifications__item">';
            $output .= '<span class="custom-property-specifications__icon" aria-hidden="true">' . $item['icon'] . '</span>';
            $output .= '<div class="custom-property-specifications__content">';
            $output .= '<span class="custom-property-specifications__label">' . esc_html($item['label']) . '</span>';
            $output .= '<strong class="custom-property-specifications__value">' . esc_html($item['value']) . '</strong>';
            $output .= '</div>';
            $output .= '</div>';
        }

        $output .= '</div>';
        $output .= '</section>';

        return $output;
    }

    public function property_location_map_shortcode($atts)
    {
        global $post;

        $atts = shortcode_atts(
            array(
                'id'     => 0,
                'height' => '420',
                'zoom'   => '15',
            ),
            $atts,
            'property_location_map'
        );

        $post_id = absint($atts['id']);

        if (!$post_id && $post instanceof \WP_Post) {
            $post_id = (int) $post->ID;
        }

        if (!$post_id || get_post_type($post_id) !== 'property') {
            return '';
        }

        $latitude = get_post_meta($post_id, 'property_latitude', true);
        $longitude = get_post_meta($post_id, 'property_longitude', true);

        if ($latitude === '' || $longitude === '' || !is_numeric($latitude) || !is_numeric($longitude)) {
            return '';
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;
        $height = max(240, absint($atts['height']));
        $zoom = max(1, min(20, absint($atts['zoom'])));
        $title = get_the_title($post_id);
        $iframe_src = add_query_arg(
            array(
                'q'      => $latitude . ',' . $longitude,
                'z'      => $zoom,
                'hl'     => 'id',
                'output' => 'embed',
            ),
            'https://maps.google.com/maps'
        );

        return sprintf(
            '<div class="custom-property-location-map"><iframe src="%1$s" width="100%%" height="%2$d" style="border:0;" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" title="%3$s"></iframe></div>',
            esc_url($iframe_src),
            $height,
            esc_attr(sprintf('Peta lokasi %s', $title))
        );
    }

    public function mortgage_simulation_shortcode($atts)
    {
        global $post;

        $post_id = 0;

        if ($post instanceof \WP_Post) {
            $post_id = (int) $post->ID;
        }

        $default_price = $post_id ? (float) get_post_meta($post_id, 'property_price', true) : 0;

        $atts = shortcode_atts(
            array(
                'price'             => $default_price,
                'down_payment_pct'  => 20,
                'fixed_rate'        => 7,
                'floating_rate'     => 12,
                'fixed_years'       => 3,
                'loan_years'        => 10,
                'other_costs_pct'   => 6,
                'wa_link'           => '',
                'subtitle'          => 'Estimate your home loan financing with this mortgage calculator',
                'button_label'      => 'Simulasikan',
                'contact_label'     => 'Tanya KPR',
            ),
            $atts,
            'simulasi_kpr'
        );

        $price = max(0, (float) $atts['price']);
        $down_payment_pct = min(90, max(10, (float) $atts['down_payment_pct']));
        $fixed_rate = min(30, max(1, (float) $atts['fixed_rate']));
        $floating_rate = min(35, max($fixed_rate, (float) $atts['floating_rate']));
        $fixed_years = min(25, max(1, (int) $atts['fixed_years']));
        $loan_years = min(25, max($fixed_years, (int) $atts['loan_years']));
        $other_costs_pct = min(15, max(0, (float) $atts['other_costs_pct']));

        if (!$price) {
            return '';
        }

        $down_payment_amount = $price * ($down_payment_pct / 100);
        $simulation = $this->calculate_mortgage_simulation(
            $price,
            $down_payment_amount,
            $fixed_rate,
            $floating_rate,
            $fixed_years,
            $loan_years,
            $other_costs_pct
        );

        $simulator_id = wp_unique_id('custom-kpr-simulator-');
        $output = '';

        if (!self::$mortgage_assets_printed) {
            $output .= $this->get_mortgage_simulation_assets();
            self::$mortgage_assets_printed = true;
        }

        $output .= Template::get('frontend/mortgage-simulation', array(
            'simulator_id'         => $simulator_id,
            'post_id'              => $post_id,
            'post_title'           => $post_id ? get_the_title($post_id) : '',
            'subtitle'             => sanitize_text_field($atts['subtitle']),
            'button_label'         => sanitize_text_field($atts['button_label']),
            'contact_label'        => sanitize_text_field($atts['contact_label']),
            'wa_link'              => esc_url_raw($atts['wa_link']),
            'initial_values'       => array(
                'price'             => $price,
                'down_payment_pct'  => $down_payment_pct,
                'down_payment_amt'  => $down_payment_amount,
                'fixed_rate'        => $fixed_rate,
                'floating_rate'     => $floating_rate,
                'fixed_years'       => $fixed_years,
                'loan_years'        => $loan_years,
                'other_costs_pct'   => $other_costs_pct,
            ),
            'initial_result'       => $simulation,
        ));

        return $output;
    }

    private function calculate_mortgage_simulation($price, $down_payment_amount, $fixed_rate, $floating_rate, $fixed_years, $loan_years, $other_costs_pct)
    {
        $price = max(0, (float) $price);
        $down_payment_amount = max(0, min($price, (float) $down_payment_amount));
        $fixed_rate = max(0.01, (float) $fixed_rate);
        $floating_rate = max($fixed_rate, (float) $floating_rate);
        $fixed_years = max(1, (int) $fixed_years);
        $loan_years = max($fixed_years, (int) $loan_years);
        $other_costs_pct = max(0, (float) $other_costs_pct);

        $principal = max(0, $price - $down_payment_amount);
        $total_months = $loan_years * 12;
        $fixed_months = min($total_months, $fixed_years * 12);
        $floating_months = max(0, $total_months - $fixed_months);

        $fixed_payment = $this->calculate_annuity_payment($principal, $fixed_rate, $total_months);
        $remaining_balance = $this->calculate_remaining_balance($principal, $fixed_rate, $total_months, $fixed_months, $fixed_payment);
        $floating_payment = $floating_months > 0 ? $this->calculate_annuity_payment($remaining_balance, $floating_rate, $floating_months) : 0;

        $total_paid_fixed = $fixed_payment * $fixed_months;
        $total_paid_floating = $floating_payment * $floating_months;
        $total_installment_paid = $total_paid_fixed + $total_paid_floating;
        $total_interest = max(0, $total_installment_paid - $principal);
        $other_costs = $price * ($other_costs_pct / 100);
        $first_payment_total = $down_payment_amount + $fixed_payment + $other_costs;

        return array(
            'price'                   => $price,
            'down_payment_amount'     => $down_payment_amount,
            'down_payment_pct'        => $price > 0 ? ($down_payment_amount / $price) * 100 : 0,
            'principal'               => $principal,
            'fixed_rate'              => $fixed_rate,
            'floating_rate'           => $floating_rate,
            'fixed_years'             => $fixed_years,
            'loan_years'              => $loan_years,
            'fixed_monthly_payment'   => $fixed_payment,
            'floating_monthly_payment'=> $floating_payment,
            'remaining_balance'       => $remaining_balance,
            'other_costs'             => $other_costs,
            'other_costs_pct'         => $other_costs_pct,
            'first_payment_total'     => $first_payment_total,
            'first_installment'       => $fixed_payment,
            'total_interest'          => $total_interest,
            'total_loan_cost'         => $principal + $total_interest,
            'fixed_period_label'      => sprintf('Fix %d tahun, Floating %d tahun', $fixed_years, max(0, $loan_years - $fixed_years)),
        );
    }

    private function calculate_annuity_payment($principal, $annual_rate, $months)
    {
        $principal = (float) $principal;
        $annual_rate = (float) $annual_rate;
        $months = (int) $months;

        if ($principal <= 0 || $months <= 0) {
            return 0;
        }

        $monthly_rate = ($annual_rate / 100) / 12;

        if ($monthly_rate <= 0) {
            return $principal / $months;
        }

        return $principal * ($monthly_rate / (1 - pow(1 + $monthly_rate, -$months)));
    }

    private function calculate_remaining_balance($principal, $annual_rate, $months, $paid_months, $payment)
    {
        $principal = (float) $principal;
        $months = (int) $months;
        $paid_months = (int) $paid_months;
        $payment = (float) $payment;
        $monthly_rate = ((float) $annual_rate / 100) / 12;

        if ($principal <= 0 || $months <= 0 || $paid_months <= 0) {
            return max(0, $principal);
        }

        if ($monthly_rate <= 0) {
            return max(0, $principal - ($payment * $paid_months));
        }

        $growth = pow(1 + $monthly_rate, $paid_months);
        $remaining = ($principal * $growth) - ($payment * (($growth - 1) / $monthly_rate));

        return max(0, $remaining);
    }

    private function get_mortgage_simulation_assets()
    {
        ob_start();
        ?>
        <style>
            .custom-kpr-simulator {
                display: grid;
                grid-template-columns: minmax(0, 1.05fr) minmax(320px, 1fr);
                gap: 22px;
                margin: 28px 0;
            }

            .custom-kpr-simulator__panel,
            .custom-kpr-simulator__summary {
                border-radius: 18px;
                background: #fff;
                box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
            }

            .custom-kpr-simulator__panel {
                position: relative;
                padding: 28px 26px;
                border-top: 6px solid #0c4474;
            }

            .custom-kpr-simulator__summary {
                padding: 18px;
                background: #f6f7fb;
            }

            .custom-kpr-simulator__title {
                margin: 0;
                font-size: 2rem;
                line-height: 1.1;
                color: #0f172a;
            }

            .custom-kpr-simulator__subtitle {
                margin: 10px 0 26px;
                max-width: 520px;
                color: #64748b;
                font-size: 1.05rem;
                line-height: 1.6;
            }

            .custom-kpr-simulator__form {
                display: grid;
                gap: 28px;
            }

            .custom-kpr-simulator__group {
                display: grid;
                gap: 14px;
            }

            .custom-kpr-simulator__group--compact {
                gap: 10px;
            }

            .custom-kpr-simulator__label-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            .custom-kpr-simulator__label {
                font-weight: 600;
                color: #0f172a;
            }

            .custom-kpr-simulator__value-row {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(184px, 1fr);
                gap: 12px;
                align-items: center;
            }

            .custom-kpr-simulator__field,
            .custom-kpr-simulator__field-inline {
                display: flex;
                align-items: stretch;
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                background: #fff;
                overflow: hidden;
            }

            .custom-kpr-simulator__prefix,
            .custom-kpr-simulator__suffix {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 54px;
                padding: 0 14px;
                background: #f8fafc;
                color: #0f172a;
                font-weight: 500;
                border-right: 1px solid #cbd5e1;
            }

            .custom-kpr-simulator__suffix {
                border-right: 0;
                border-left: 1px solid #cbd5e1;
            }

            .custom-kpr-simulator__input {
                width: 100%;
                min-height: 44px;
                border: 0;
                padding: 0 14px;
                font-size: 1rem;
                color: #0f172a;
                background: transparent;
            }

            .custom-kpr-simulator__slider {
                width: 100%;
                accent-color: #0c4474;
            }

            .custom-kpr-simulator__scale {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                color: #94a3b8;
                font-size: 0.9rem;
            }

            .custom-kpr-simulator__button {
                min-height: 50px;
                border: 0;
                border-radius: 8px;
                background: #0c4474;
                color: #fff;
                font-size: 1rem;
                font-weight: 700;
                cursor: pointer;
            }

            .custom-kpr-simulator__summary-badge {
                display: inline-flex;
                margin: -18px 0 0 -18px;
                padding: 10px 14px;
                border-radius: 8px 8px 0 0;
                background: #0c4474;
                color: #fff;
                font-size: 0.85rem;
                font-weight: 700;
            }

            .custom-kpr-simulator__summary-card,
            .custom-kpr-simulator__detail-card {
                margin-top: 18px;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #fff;
            }

            .custom-kpr-simulator__summary-meta {
                display: grid;
                grid-template-columns: 1.7fr repeat(3, minmax(0, 1fr));
                gap: 10px;
                padding: 16px 18px;
                color: #94a3b8;
                font-size: 0.86rem;
            }

            .custom-kpr-simulator__summary-meta strong {
                display: block;
                margin-top: 6px;
                color: #0f172a;
                font-size: 0.95rem;
            }

            .custom-kpr-simulator__hero {
                margin-top: 22px;
                padding: 18px;
                border-radius: 12px;
                background: #eaf2f9;
            }

            .custom-kpr-simulator__hero-title {
                margin: 0 0 14px;
                color: #0f172a;
                font-size: 1.2rem;
            }

            .custom-kpr-simulator__hero-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                flex-wrap: wrap;
            }

            .custom-kpr-simulator__hero-copy {
                color: #334155;
                line-height: 1.7;
            }

            .custom-kpr-simulator__hero-copy strong {
                display: block;
                margin-top: 4px;
                color: #0f172a;
                font-size: 1.2rem;
            }

            .custom-kpr-simulator__contact {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 12px 18px;
                border-radius: 8px;
                background: #0c4474;
                color: #fff;
                text-decoration: none;
                font-weight: 700;
            }

            .custom-kpr-simulator__details-title {
                margin: 22px 2px 12px;
                color: #0f172a;
                font-size: 1.2rem;
            }

            .custom-kpr-simulator__detail-card {
                padding: 14px 18px;
            }

            .custom-kpr-simulator__detail-head,
            .custom-kpr-simulator__detail-item {
                display: flex;
                justify-content: space-between;
                gap: 18px;
            }

            .custom-kpr-simulator__detail-head {
                margin-bottom: 10px;
                color: #0f172a;
                font-weight: 700;
            }

            .custom-kpr-simulator__detail-item {
                padding: 4px 0;
                color: #64748b;
            }

            .custom-kpr-simulator__detail-item span:last-child,
            .custom-kpr-simulator__detail-head span:last-child {
                color: #0f172a;
                font-weight: 600;
                text-align: right;
            }

            .custom-kpr-simulator__disclaimer {
                margin: 18px 4px 0;
                color: #64748b;
                font-size: 0.84rem;
                line-height: 1.6;
            }

            @media (max-width: 991px) {
                .custom-kpr-simulator {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 767px) {
                .custom-kpr-simulator__panel,
                .custom-kpr-simulator__summary {
                    padding: 18px 16px;
                }

                .custom-kpr-simulator__title {
                    font-size: 1.65rem;
                }

                .custom-kpr-simulator__value-row,
                .custom-kpr-simulator__summary-meta {
                    grid-template-columns: 1fr;
                }

                .custom-kpr-simulator__summary-badge {
                    margin: -18px 0 0 -16px;
                }

                .custom-kpr-simulator__hero-row,
                .custom-kpr-simulator__detail-head,
                .custom-kpr-simulator__detail-item {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .custom-kpr-simulator__detail-item span:last-child,
                .custom-kpr-simulator__detail-head span:last-child {
                    text-align: left;
                }
            }
        </style>
        <script>
            (function () {
                if (window.customKprSimulatorInit) {
                    return;
                }

                window.customKprSimulatorInit = true;

                document.addEventListener('input', function (event) {
                    var root = event.target.closest('[data-kpr-simulator]');

                    if (!root) {
                        return;
                    }

                    syncSimulatorInputs(root, event.target);
                });

                document.addEventListener('submit', function (event) {
                    var form = event.target.closest('[data-kpr-form]');

                    if (!form) {
                        return;
                    }

                    event.preventDefault();
                    renderSimulator(form.closest('[data-kpr-simulator]'));
                });

                function syncSimulatorInputs(root, source) {
                    var priceInput = root.querySelector('[data-kpr-price]');
                    var dpPctInput = root.querySelector('[data-kpr-dp-pct]');
                    var dpAmtInput = root.querySelector('[data-kpr-dp-amt]');
                    var fixedRateInput = root.querySelector('[data-kpr-fixed-rate]');
                    var floatingRateInput = root.querySelector('[data-kpr-floating-rate]');
                    var fixedYearsInput = root.querySelector('[data-kpr-fixed-years]');
                    var loanYearsInput = root.querySelector('[data-kpr-loan-years]');
                    var dpSlider = root.querySelector('[data-kpr-dp-slider]');
                    var fixedYearsSlider = root.querySelector('[data-kpr-fixed-years-slider]');
                    var loanYearsSlider = root.querySelector('[data-kpr-loan-years-slider]');

                    var price = parseNumber(priceInput.value);
                    var dpPct = clamp(parseNumber(dpPctInput.value), 10, 90);
                    var dpAmt = clamp(parseNumber(dpAmtInput.value), 0, price);
                    var fixedRate = clamp(parseNumber(fixedRateInput.value), 1, 30);
                    var floatingRate = clamp(parseNumber(floatingRateInput.value), fixedRate, 35);
                    var fixedYears = clamp(parseInt(fixedYearsInput.value || 0, 10), 1, 25);
                    var loanYears = clamp(parseInt(loanYearsInput.value || 0, 10), fixedYears, 25);

                    if (source === dpAmtInput) {
                        dpPct = price > 0 ? (dpAmt / price) * 100 : 0;
                    } else {
                        dpAmt = price * (dpPct / 100);
                    }

                    if (source === fixedYearsInput || source === fixedYearsSlider) {
                        loanYears = Math.max(loanYears, fixedYears);
                    }

                    dpPct = clamp(dpPct, 10, 90);
                    dpAmt = clamp(dpAmt, 0, price);
                    floatingRate = clamp(floatingRate, fixedRate, 35);
                    loanYears = clamp(loanYears, fixedYears, 25);

                    priceInput.value = formatInteger(price);
                    dpPctInput.value = formatDecimal(dpPct);
                    dpAmtInput.value = formatInteger(dpAmt);
                    fixedRateInput.value = formatDecimal(fixedRate);
                    floatingRateInput.value = formatDecimal(floatingRate);
                    fixedYearsInput.value = String(fixedYears);
                    loanYearsInput.value = String(loanYears);
                    dpSlider.value = String(Math.round(dpPct));
                    fixedYearsSlider.value = String(fixedYears);
                    loanYearsSlider.value = String(loanYears);
                }

                function renderSimulator(root) {
                    if (!root) {
                        return;
                    }

                    var price = parseNumber(root.querySelector('[data-kpr-price]').value);
                    var dpPct = clamp(parseNumber(root.querySelector('[data-kpr-dp-pct]').value), 10, 90);
                    var dpAmt = clamp(parseNumber(root.querySelector('[data-kpr-dp-amt]').value), 0, price);
                    var fixedRate = clamp(parseNumber(root.querySelector('[data-kpr-fixed-rate]').value), 1, 30);
                    var floatingRate = clamp(parseNumber(root.querySelector('[data-kpr-floating-rate]').value), fixedRate, 35);
                    var fixedYears = clamp(parseInt(root.querySelector('[data-kpr-fixed-years]').value || 0, 10), 1, 25);
                    var loanYears = clamp(parseInt(root.querySelector('[data-kpr-loan-years]').value || 0, 10), fixedYears, 25);
                    var otherCostsPct = parseNumber(root.getAttribute('data-other-costs-pct'));

                    var result = calculateSimulation(price, dpAmt, fixedRate, floatingRate, fixedYears, loanYears, otherCostsPct);

                    setText(root, '[data-kpr-period]', 'Fix ' + fixedYears + ' tahun, Floating ' + Math.max(0, loanYears - fixedYears) + ' tahun');
                    setTextAll(root, '[data-kpr-fixed-rate-output]', formatPercent(fixedRate));
                    setText(root, '[data-kpr-fixed-years-output]', fixedYears + ' Tahun');
                    setText(root, '[data-kpr-loan-years-output]', loanYears + ' Tahun');
                    setText(root, '[data-kpr-fixed-payment]', formatCurrency(result.fixedMonthlyPayment));
                    setText(root, '[data-kpr-floating-payment]', formatCurrency(result.floatingMonthlyPayment));
                    setText(root, '[data-kpr-first-total]', formatCurrency(result.firstPaymentTotal));
                    setText(root, '[data-kpr-down-payment-output]', formatCurrency(result.downPaymentAmount));
                    setText(root, '[data-kpr-first-installment-output]', formatCurrency(result.fixedMonthlyPayment));
                    setText(root, '[data-kpr-other-costs-output]', formatCurrency(result.otherCosts));
                    setText(root, '[data-kpr-total-loan-cost]', formatCurrency(result.totalLoanCost));
                    setText(root, '[data-kpr-principal-output]', formatCurrency(result.principal));
                    setText(root, '[data-kpr-interest-output]', formatCurrency(result.totalInterest));
                }

                function calculateSimulation(price, downPaymentAmount, fixedRate, floatingRate, fixedYears, loanYears, otherCostsPct) {
                    var principal = Math.max(0, price - downPaymentAmount);
                    var totalMonths = loanYears * 12;
                    var fixedMonths = Math.min(totalMonths, fixedYears * 12);
                    var floatingMonths = Math.max(0, totalMonths - fixedMonths);
                    var fixedMonthlyPayment = annuity(principal, fixedRate, totalMonths);
                    var remainingBalance = balanceAfter(principal, fixedRate, totalMonths, fixedMonths, fixedMonthlyPayment);
                    var floatingMonthlyPayment = floatingMonths > 0 ? annuity(remainingBalance, floatingRate, floatingMonths) : 0;
                    var totalInstallmentPaid = (fixedMonthlyPayment * fixedMonths) + (floatingMonthlyPayment * floatingMonths);
                    var totalInterest = Math.max(0, totalInstallmentPaid - principal);
                    var otherCosts = price * (otherCostsPct / 100);

                    return {
                        principal: principal,
                        fixedMonthlyPayment: fixedMonthlyPayment,
                        floatingMonthlyPayment: floatingMonthlyPayment,
                        totalInterest: totalInterest,
                        totalLoanCost: principal + totalInterest,
                        otherCosts: otherCosts,
                        downPaymentAmount: downPaymentAmount,
                        firstPaymentTotal: downPaymentAmount + fixedMonthlyPayment + otherCosts
                    };
                }

                function annuity(principal, annualRate, months) {
                    if (principal <= 0 || months <= 0) {
                        return 0;
                    }

                    var monthlyRate = (annualRate / 100) / 12;

                    if (monthlyRate <= 0) {
                        return principal / months;
                    }

                    return principal * (monthlyRate / (1 - Math.pow(1 + monthlyRate, -months)));
                }

                function balanceAfter(principal, annualRate, months, paidMonths, payment) {
                    if (principal <= 0 || months <= 0 || paidMonths <= 0) {
                        return Math.max(0, principal);
                    }

                    var monthlyRate = (annualRate / 100) / 12;

                    if (monthlyRate <= 0) {
                        return Math.max(0, principal - (payment * paidMonths));
                    }

                    var growth = Math.pow(1 + monthlyRate, paidMonths);
                    return Math.max(0, (principal * growth) - (payment * ((growth - 1) / monthlyRate)));
                }

                function parseNumber(value) {
                    return Number(String(value || '').replace(/[^\d.]/g, '')) || 0;
                }

                function formatInteger(value) {
                    return Math.round(value).toString();
                }

                function formatDecimal(value) {
                    return (Math.round(value * 100) / 100).toString().replace(/\.00$/, '').replace(/(\.\d)0$/, '$1');
                }

                function formatCurrency(value) {
                    return 'Rp' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(value));
                }

                function formatPercent(value) {
                    return formatDecimal(value) + '%';
                }

                function clamp(value, min, max) {
                    return Math.min(Math.max(value, min), max);
                }

                function setText(root, selector, value) {
                    var element = root.querySelector(selector);

                    if (element) {
                        element.textContent = value;
                    }
                }

                function setTextAll(root, selector, value) {
                    root.querySelectorAll(selector).forEach(function (element) {
                        element.textContent = value;
                    });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('[data-kpr-simulator]').forEach(function (root) {
                        syncSimulatorInputs(root, root.querySelector('[data-kpr-dp-pct]'));
                        renderSimulator(root);
                    });
                });
            }());
        </script>
        <?php

        return ob_get_clean();
    }

    private function get_property_specification_items($post_id)
    {
        $certificate = $this->get_mapped_meta($post_id, 'property_certificate', $this->certificate_options());
        $furnishing = $this->get_mapped_meta($post_id, 'property_furnishing', $this->furnishing_options());

        $definitions = array(
            array(
                'label' => 'Kamar Tidur',
                'value' => $this->format_spec_number($this->get_meta($post_id, 'property_bedrooms'), 'KT'),
                'icon'  => $this->get_specification_icon('bedroom'),
            ),
            array(
                'label' => 'Kamar Mandi',
                'value' => $this->format_spec_number($this->get_meta($post_id, 'property_bathrooms'), 'KM'),
                'icon'  => $this->get_specification_icon('bathroom'),
            ),
            array(
                'label' => 'Garasi / Carport',
                'value' => $this->format_spec_number($this->get_meta($post_id, 'property_garage'), 'Mobil'),
                'icon'  => $this->get_specification_icon('garage'),
            ),
            array(
                'label' => 'Luas Bangunan',
                'value' => $this->format_area($this->get_meta($post_id, 'property_building_area')),
                'icon'  => $this->get_specification_icon('building'),
            ),
            array(
                'label' => 'Luas Tanah',
                'value' => $this->format_area($this->get_meta($post_id, 'property_land_area')),
                'icon'  => $this->get_specification_icon('land'),
            ),
            array(
                'label' => 'Jumlah Lantai',
                'value' => $this->format_spec_number($this->get_meta($post_id, 'property_floors'), 'Lantai'),
                'icon'  => $this->get_specification_icon('floors'),
            ),
            array(
                'label' => 'Sertifikat',
                'value' => $certificate,
                'icon'  => $this->get_specification_icon('certificate'),
            ),
            array(
                'label' => 'Kondisi Furnitur',
                'value' => $furnishing,
                'icon'  => $this->get_specification_icon('furnishing'),
            ),
        );

        return array_values(array_filter($definitions, function ($item) {
            return isset($item['value']) && $item['value'] !== '-';
        }));
    }

    private function format_spec_number($value, $suffix = '')
    {
        if ($value === '' || !is_numeric($value)) {
            return '-';
        }

        $formatted = number_format((float) $value, 0, ',', '.');

        if ($suffix === '') {
            return $formatted;
        }

        return $formatted . ' ' . $suffix;
    }

    private function get_specification_icon($type)
    {
        $icons = array(
            'bedroom' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11V7a2 2 0 0 1 2-2h4a3 3 0 0 1 3 3v3"/><path d="M14 11V9a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v2"/><path d="M2 11h20v5H2z"/><path d="M4 16v3"/><path d="M20 16v3"/></svg>',
            'bathroom' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4v7"/><path d="M7 7h4a2 2 0 1 0 0-4"/><path d="M4 13h16"/><path d="M6 13v2a6 6 0 0 0 12 0v-2"/><path d="M9 19v1"/><path d="M15 19v1"/></svg>',
            'garage' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l2-5h14l2 5"/><path d="M5 11h14v6a1 1 0 0 1-1 1h-1"/><path d="M7 18H6a1 1 0 0 1-1-1v-6"/><path d="M7 18h10"/><path d="M8 14h.01"/><path d="M16 14h.01"/><circle cx="7.5" cy="18" r="1.5"/><circle cx="16.5" cy="18" r="1.5"/></svg>',
            'building' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v14"/><path d="M16 10h2a2 2 0 0 1 2 2v8"/><path d="M8 8h4"/><path d="M8 12h4"/><path d="M8 16h4"/><path d="M10 20v-2"/></svg>',
            'land' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="M4 10h16"/><path d="M8 6v12"/><path d="M14 6v12"/><path d="M4 18l6-6 4 4 6-6"/></svg>',
            'floors' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19h14"/><path d="M7 19V8l5-3 5 3v11"/><path d="M9 11h.01"/><path d="M12 11h.01"/><path d="M15 11h.01"/><path d="M9 14h.01"/><path d="M12 14h.01"/><path d="M15 14h.01"/></svg>',
            'certificate' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h8l4 4v14H7z"/><path d="M15 3v5h5"/><path d="M10 13h6"/><path d="M10 17h4"/></svg>',
            'furnishing' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v4H4z"/><path d="M7 9V7a2 2 0 0 1 2-2h1"/><path d="M14 9V7a2 2 0 0 0-2-2h-1"/><path d="M6 16v3"/><path d="M18 16v3"/></svg>',
        );

        return isset($icons[$type]) ? $icons[$type] : $icons['building'];
    }

    private function get_property_specifications_assets()
    {
        ob_start();
?>
        <style>
            .custom-property-specifications {
                margin: 28px 0;
                padding: 28px;
                border-radius: 24px;
                background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
                box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            }

            .custom-property-specifications__title {
                margin: 0 0 22px;
                color: #0f172a;
                font-size: 32px;
                font-weight: 700;
                line-height: 1.1;
            }

            .custom-property-specifications__grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 20px 24px;
            }

            .custom-property-specifications__item {
                display: flex;
                align-items: flex-start;
                gap: 14px;
                min-width: 0;
            }

            .custom-property-specifications__icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                flex: 0 0 44px;
                border-radius: 14px;
                background: #fff;
                color: #334155;
                box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.26);
            }

            .custom-property-specifications__icon svg {
                width: 24px;
                height: 24px;
            }

            .custom-property-specifications__content {
                display: flex;
                flex-direction: column;
                gap: 4px;
                min-width: 0;
            }

            .custom-property-specifications__label {
                color: #475569;
                font-size: 14px;
                line-height: 1.45;
            }

            .custom-property-specifications__value {
                color: #0f172a;
                font-size: 17px;
                font-weight: 700;
                line-height: 1.35;
            }

            @media (max-width: 1024px) {
                .custom-property-specifications__grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .custom-property-specifications {
                    padding: 22px 18px;
                    border-radius: 20px;
                }

                .custom-property-specifications__title {
                    margin-bottom: 18px;
                    font-size: 26px;
                }

                .custom-property-specifications__grid {
                    grid-template-columns: 1fr;
                    gap: 16px;
                }
            }
        </style>
<?php

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
