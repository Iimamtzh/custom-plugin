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
    // Example: Register 'Project' Custom Post Type
    // Uncomment the lines below to enable
    /*
        $labels = array(
            'name'                  => 'Proyek',
            'singular_name'         => 'Proyek',
            'menu_name'             => 'Proyek',
            'name_admin_bar'        => 'Proyek',
            'archives'              => 'Arsip Proyek',
            'attributes'            => 'Atribut Proyek',
            'parent_item_colon'     => 'Induk Proyek:',
            'all_items'             => 'Semua Proyek',
            'add_new_item'          => 'Tambah Proyek Baru',
            'add_new'               => 'Tambah Baru',
            'new_item'              => 'Proyek Baru',
            'edit_item'             => 'Edit Proyek',
            'update_item'           => 'Perbarui Proyek',
            'view_item'             => 'Lihat Proyek',
            'view_items'            => 'Lihat Proyek',
            'search_items'          => 'Cari Proyek',
            'not_found'             => 'Tidak ditemukan',
            'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
            'featured_image'        => 'Gambar Utama',
            'set_featured_image'    => 'Atur gambar utama',
            'remove_featured_image' => 'Hapus gambar utama',
            'use_featured_image'    => 'Gunakan sebagai gambar utama',
            'insert_into_item'      => 'Masukkan ke dalam proyek',
            'uploaded_to_this_item' => 'Diunggah ke proyek ini',
            'items_list'            => 'Daftar proyek',
            'items_list_navigation' => 'Navigasi daftar proyek',
            'filter_items_list'     => 'Filter daftar proyek',
        );
        $args = array(
            'label'                 => 'Proyek',
            'description'           => 'Deskripsi Tipe Postingan',
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
            'taxonomies'            => array('project_category'), // Make sure this taxonomy is registered
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-portfolio', // https://developer.wordpress.org/resource/dashicons/
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true, // Enable Gutenberg
        );
        register_post_type('project', $args);
        */

    $this->register_catatan_malvocs();
    $this->register_dokumen();

    // You can add more Custom Post Types here
  }

  /**
   * Register Catatan Malvocs Custom Post Type
   */
  private function register_catatan_malvocs()
  {
    $labels = array(
      'name'                  => 'Catatan Malvocs',
      'singular_name'         => 'Catatan Malvocs',
      'menu_name'             => 'Catatan Malvocs',
      'name_admin_bar'        => 'Catatan Malvocs',
      'archives'              => 'Arsip Catatan',
      'attributes'            => 'Atribut Catatan',
      'parent_item_colon'     => 'Induk Catatan:',
      'all_items'             => 'Semua Catatan',
      'add_new_item'          => 'Tambah Catatan Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'Catatan Baru',
      'edit_item'             => 'Edit Catatan',
      'update_item'           => 'Perbarui Catatan',
      'view_item'             => 'Lihat Catatan',
      'view_items'            => 'Lihat Catatan',
      'search_items'          => 'Cari Catatan',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
      'featured_image'        => 'Gambar Utama',
      'set_featured_image'    => 'Atur gambar utama',
      'remove_featured_image' => 'Hapus gambar utama',
      'use_featured_image'    => 'Gunakan sebagai gambar utama',
      'insert_into_item'      => 'Masukkan ke dalam catatan',
      'uploaded_to_this_item' => 'Diunggah ke catatan ini',
      'items_list'            => 'Daftar catatan',
      'items_list_navigation' => 'Navigasi daftar catatan',
      'filter_items_list'     => 'Filter daftar catatan',
    );

    $args = array(
      'label'               => 'Catatan Malvocs',
      'description'         => 'Post type untuk catatan guru Malvocs',
      'labels'              => $labels,
      'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments'),
      'taxonomies'          => array('kategori_catatan_malvocs'),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 5,
      'menu_icon'           => 'dashicons-welcome-write-blog',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'post',
      'show_in_rest'        => true,
    );

    register_post_type('catatan_malvocs', $args);
  }

  /**
   * Register Dokumen Custom Post Type
   * Untuk lampiran tata tertib, surat edaran, dan dokumen sekolah lainnya.
   */
  private function register_dokumen()
  {
    $labels = array(
      'name'                  => 'Dokumen',
      'singular_name'         => 'Dokumen',
      'menu_name'             => 'Dokumen',
      'name_admin_bar'        => 'Dokumen',
      'archives'              => 'Arsip Dokumen',
      'attributes'            => 'Atribut Dokumen',
      'parent_item_colon'     => 'Induk Dokumen:',
      'all_items'             => 'Semua Dokumen',
      'add_new_item'          => 'Tambah Dokumen Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'Dokumen Baru',
      'edit_item'             => 'Edit Dokumen',
      'update_item'           => 'Perbarui Dokumen',
      'view_item'             => 'Lihat Dokumen',
      'view_items'            => 'Lihat Dokumen',
      'search_items'          => 'Cari Dokumen',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
      'featured_image'        => 'Sampul Dokumen',
      'set_featured_image'    => 'Atur sampul',
      'remove_featured_image' => 'Hapus sampul',
      'use_featured_image'    => 'Gunakan sebagai sampul',
      'insert_into_item'      => 'Masukkan ke dalam dokumen',
      'uploaded_to_this_item' => 'Diunggah ke dokumen ini',
      'items_list'            => 'Daftar dokumen',
      'items_list_navigation' => 'Navigasi daftar dokumen',
      'filter_items_list'     => 'Filter daftar dokumen',
    );

    $args = array(
      'label'               => 'Dokumen',
      'description'         => 'Post type untuk dokumen dan lampiran sekolah (tata tertib, surat edaran, dll)',
      'labels'              => $labels,
      'supports'            => array('title', 'editor', 'thumbnail'),
      'taxonomies'          => array('kategori_dokumen'),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 6,
      'menu_icon'           => 'dashicons-media-document',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'post',
      'show_in_rest'        => true,
    );

    register_post_type('dokumen', $args);
  }
}
