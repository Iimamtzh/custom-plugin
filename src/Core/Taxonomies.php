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
        // Register 'tim_kami_kategori' Taxonomy for Tim Kami post type
        $this->register_tim_kami_kategori();
    }

    /**
     * Register Tim Kami Kategori Taxonomy
     */
    private function register_tim_kami_kategori()
    {
        $labels = array(
            'name'              => 'Kategori Tim Kami',
            'singular_name'     => 'Kategori',
            'search_items'      => 'Cari Kategori',
            'all_items'         => 'Semua Kategori',
            'parent_item'       => 'Induk Kategori',
            'parent_item_colon' => 'Induk Kategori:',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Perbarui Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'tim-kami-kategori'),
            'show_in_rest'      => true, // Enable Gutenberg editor support
        );

        register_taxonomy('tim_kami_kategori', array('tim_kami'), $args);
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
