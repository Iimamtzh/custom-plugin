<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Taxonomies
{

    public function __construct()
    {
        add_action('init', array($this, 'register_taxonomies'));
    }

    public function register_taxonomies()
    {
        $this->register_brand();
    }

    /**
     * Register Brand Taxonomy
     */
    private function register_brand()
    {
        $labels = array(
            'name'              => 'Brand',
            'singular_name'     => 'Brand',
            'search_items'      => 'Search Brand',
            'all_items'         => 'All Brands',
            'parent_item'       => 'Parent Brand',
            'parent_item_colon' => 'Parent Brand:',
            'edit_item'         => 'Edit Brand',
            'update_item'       => 'Update Brand',
            'add_new_item'      => 'Add New Brand',
            'new_item_name'     => 'New Brand Name',
            'menu_name'         => 'Brand',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'brand'),
            'show_in_rest'      => true,
        );

        register_taxonomy('brand', array('store_product'), $args);
    }
}
