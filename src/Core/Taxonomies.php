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
        $this->register_kategori_paket();
    }

    /**
     * Register Kategori Paket Taxonomy
     */
    private function register_kategori_paket()
    {
        $labels = array(
            'name'              => 'Kategori Paket',
            'singular_name'     => 'Kategori Paket',
            'search_items'      => 'Cari Kategori Paket',
            'all_items'         => 'Semua Kategori Paket',
            'parent_item'       => 'Induk Kategori Paket',
            'parent_item_colon' => 'Induk Kategori Paket:',
            'edit_item'         => 'Edit Kategori Paket',
            'update_item'       => 'Perbarui Kategori Paket',
            'add_new_item'      => 'Tambah Kategori Paket Baru',
            'new_item_name'     => 'Nama Kategori Paket Baru',
            'menu_name'         => 'Kategori Paket',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'kategori-paket'),
            'show_in_rest'      => true,
        );

        register_taxonomy('kategori-paket', array('paket-umrah'), $args);
    }
}
