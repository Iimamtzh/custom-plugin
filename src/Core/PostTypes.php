<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
  exit;
}

class PostTypes
{

  public function __construct()
  {
    add_action('init', array($this, 'register_post_types'));
  }

  public function register_post_types()
  {
    $labels = array(
      'name'                  => 'Bidang Praktik',
      'singular_name'         => 'Bidang Praktik',
      'menu_name'             => 'Bidang Praktik',
      'name_admin_bar'        => 'Bidang Praktik',
      'archives'              => 'Arsip Bidang Praktik',
      'attributes'            => 'Atribut Bidang Praktik',
      'parent_item_colon'     => 'Induk Bidang Praktik:',
      'all_items'             => 'Semua Bidang Praktik',
      'add_new_item'          => 'Tambah Bidang Praktik Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'Bidang Praktik Baru',
      'edit_item'             => 'Edit Bidang Praktik',
      'update_item'           => 'Perbarui Bidang Praktik',
      'view_item'             => 'Lihat Bidang Praktik',
      'view_items'            => 'Lihat Bidang Praktik',
      'search_items'          => 'Cari Bidang Praktik',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
      'featured_image'        => 'Gambar Utama',
      'set_featured_image'    => 'Atur gambar utama',
      'remove_featured_image' => 'Hapus gambar utama',
      'use_featured_image'    => 'Gunakan sebagai gambar utama',
      'insert_into_item'      => 'Masukkan ke dalam bidang praktik',
      'uploaded_to_this_item' => 'Diunggah ke bidang praktik ini',
      'items_list'            => 'Daftar bidang praktik',
      'items_list_navigation' => 'Navigasi daftar bidang praktik',
      'filter_items_list'     => 'Filter daftar bidang praktik',
    );
    $args = array(
      'label'               => 'Bidang Praktik',
      'description'         => 'Tipe Postingan Bidang Praktik',
      'labels'              => $labels,
      'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 5,
      'menu_icon'           => 'dashicons-welcome-learn-more', // https://developer.wordpress.org/resource/dashicons/
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'page',
      'show_in_rest'        => true, // Enable Gutenberg
    );
    register_post_type('bidang_praktik', $args);
  }
}
