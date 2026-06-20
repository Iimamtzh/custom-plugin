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

        register_taxonomy('brand', array('post', 'product'), $args);
    }

    /**
     * Register Project Category Taxonomy
     */
    private function register_project_category()
    {
        $labels = array(
            'name'              => 'Kategori Proyek',
            'singular_name'     => 'Kategori Proyek',
            'search_items'      => 'Cari Kategori Proyek',
            'all_items'         => 'Semua Kategori Proyek',
            'parent_item'       => 'Induk Kategori Proyek',
            'parent_item_colon' => 'Induk Kategori Proyek:',
            'edit_item'         => 'Edit Kategori Proyek',
            'update_item'       => 'Perbarui Kategori Proyek',
            'add_new_item'      => 'Tambah Kategori Proyek Baru',
            'new_item_name'     => 'Nama Kategori Proyek Baru',
            'menu_name'         => 'Kategori Proyek',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'project-category'),
            'show_in_rest'      => true, // Enable Gutenberg editor support
        );

        register_taxonomy('project_category', array('project'), $args);
    }
}