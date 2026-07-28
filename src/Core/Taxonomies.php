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
        // Example: Register 'project_category' Taxonomy
        // $this->register_project_category();
        $this->register_kategori_catatan_malvocs();
        $this->register_kategori_dokumen();
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

    /**
     * Register Kategori Catatan Malvocs Taxonomy
     */
    private function register_kategori_catatan_malvocs()
    {
        $labels = array(
            'name'              => 'Kategori Catatan',
            'singular_name'     => 'Kategori Catatan',
            'search_items'      => 'Cari Kategori Catatan',
            'all_items'         => 'Semua Kategori',
            'parent_item'       => 'Induk Kategori',
            'parent_item_colon' => 'Induk Kategori:',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Perbarui Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori Catatan',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'kategori-catatan-malvocs'),
            'show_in_rest'      => true,
        );

        register_taxonomy('kategori_catatan_malvocs', array('catatan_malvocs'), $args);
    }

    /**
     * Register Kategori Dokumen Taxonomy
     */
    private function register_kategori_dokumen()
    {
        $labels = array(
            'name'              => 'Kategori Dokumen',
            'singular_name'     => 'Kategori Dokumen',
            'search_items'      => 'Cari Kategori Dokumen',
            'all_items'         => 'Semua Kategori',
            'parent_item'       => 'Induk Kategori',
            'parent_item_colon' => 'Induk Kategori:',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Perbarui Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori Dokumen',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'kategori-dokumen'),
            'show_in_rest'      => true,
        );

        register_taxonomy('kategori_dokumen', array('dokumen'), $args);
    }
}