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
    add_action('add_meta_boxes', array($this, 'add_metabox'));
    add_action('save_post', array($this, 'save_metabox'));
  }

  public function register_post_types()
  {
    $this->register_paket_umrah();
    $this->register_paket_umrah_meta();
  }

  private function register_paket_umrah()
  {
    $labels = array(
      'name'                  => 'Paket Umrah',
      'singular_name'         => 'Paket Umrah',
      'menu_name'             => 'Paket Umrah',
      'name_admin_bar'        => 'Paket Umrah',
      'archives'              => 'Arsip Paket Umrah',
      'attributes'            => 'Atribut Paket Umrah',
      'parent_item_colon'     => 'Induk Paket Umrah:',
      'all_items'             => 'Semua Paket Umrah',
      'add_new_item'          => 'Tambah Paket Umrah Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'Paket Umrah Baru',
      'edit_item'             => 'Edit Paket Umrah',
      'update_item'           => 'Perbarui Paket Umrah',
      'view_item'             => 'Lihat Paket Umrah',
      'view_items'            => 'Lihat Paket Umrah',
      'search_items'          => 'Cari Paket Umrah',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
      'featured_image'        => 'Gambar Banner',
      'set_featured_image'    => 'Atur banner',
      'remove_featured_image' => 'Hapus banner',
      'use_featured_image'    => 'Gunakan sebagai banner',
      'insert_into_item'      => 'Masukkan ke dalam paket umrah',
      'uploaded_to_this_item' => 'Diunggah ke paket umrah ini',
      'items_list'            => 'Daftar paket umrah',
      'items_list_navigation' => 'Navigasi daftar paket umrah',
      'filter_items_list'     => 'Filter daftar paket umrah',
    );

    $args = array(
      'label'               => 'Paket Umrah',
      'description'         => 'Paket perjalanan umrah',
      'labels'              => $labels,
      'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
      'taxonomies'          => array('kategori-paket'),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 5,
      'menu_icon'           => 'dashicons-airplane',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'page',
      'show_in_rest'        => true,
    );

    register_post_type('paket-umrah', $args);
  }

  private function register_paket_umrah_meta()
  {
    $fields = array(
      '_harga'            => array('type' => 'number', 'label' => 'Harga'),
      '_harga_coret'      => array('type' => 'number', 'label' => 'Harga Coret'),
      '_durasi'           => array('type' => 'string', 'label' => 'Durasi'),
      '_maskapai'         => array('type' => 'string', 'label' => 'Maskapai'),
      '_hotel_mekkah'     => array('type' => 'string', 'label' => 'Hotel Mekkah'),
      '_hotel_madinah'    => array('type' => 'string', 'label' => 'Hotel Madinah'),
      '_kuota'            => array('type' => 'number', 'label' => 'Kuota'),
      '_tanggal_berangkat' => array('type' => 'string', 'label' => 'Tanggal Berangkat'),
      '_tanggal_pulang'   => array('type' => 'string', 'label' => 'Tanggal Pulang'),
      '_fasilitas'        => array('type' => 'string', 'label' => 'Benefit'),
      '_kenapa'           => array('type' => 'string', 'label' => 'Kenapa'),
      '_syarat_ketentuan' => array('type' => 'string', 'label' => 'Syarat & Ketentuan'),
      '_itinerary'        => array('type' => 'string', 'label' => 'Itinerary'),
    );

    foreach ($fields as $key => $field) {
      register_post_meta('paket-umrah', $key, array(
        'type'         => $field['type'],
        'description'  => $field['label'],
        'single'       => true,
        'show_in_rest' => true,
        'auth_callback' => function () {
          return current_user_can('edit_posts');
        },
      ));
    }
  }

  public function add_metabox()
  {
    add_meta_box(
      'paket_umrah_fields',
      'Detail Paket Umrah',
      array($this, 'render_metabox'),
      'paket-umrah',
      'normal',
      'high'
    );
  }

  public function render_metabox($post)
  {
    wp_nonce_field('paket_umrah_metabox', 'paket_umrah_nonce');

    $fields = array(
      '_harga'             => array('label' => 'Harga',              'type' => 'number', 'placeholder' => 'Contoh: 25000000'),
      '_harga_coret'       => array('label' => 'Harga Coret',        'type' => 'number', 'placeholder' => 'Contoh: 28000000'),
      '_durasi'            => array('label' => 'Durasi',             'type' => 'text',   'placeholder' => 'Contoh: 9 Hari'),
      '_maskapai'          => array('label' => 'Maskapai',           'type' => 'text',   'placeholder' => 'Contoh: Saudi Airlines'),
      '_hotel_mekkah'      => array('label' => 'Hotel Mekkah',       'type' => 'text',   'placeholder' => 'Contoh: Pullman Zamzam'),
      '_hotel_madinah'     => array('label' => 'Hotel Madinah',      'type' => 'text',   'placeholder' => 'Contoh: Al Haram Hotel'),
      '_kuota'             => array('label' => 'Kuota',              'type' => 'number', 'placeholder' => 'Contoh: 45'),
      '_tanggal_berangkat' => array('label' => 'Tanggal Berangkat',  'type' => 'date'),
      '_tanggal_pulang'    => array('label' => 'Tanggal Pulang',     'type' => 'date'),
      '_fasilitas'         => array('label' => 'Benefit',             'type' => 'wysiwyg', 'settings' => array('textarea_rows' => 5)),
      '_kenapa'            => array('label' => 'Kenapa',              'type' => 'wysiwyg', 'settings' => array('textarea_rows' => 5)),
      '_syarat_ketentuan'  => array('label' => 'Syarat & Ketentuan',  'type' => 'wysiwyg', 'settings' => array('textarea_rows' => 5)),
      '_itinerary'         => array('label' => 'Itinerary',           'type' => 'wysiwyg', 'settings' => array('textarea_rows' => 8)),
    );

    echo '<table class="form-table"><tbody>';

    foreach ($fields as $key => $field) {
      $value = get_post_meta($post->ID, $key, true);
      $esc_value = esc_attr($value);
      echo '<tr>';
      echo '<th><label for="' . esc_attr($key) . '">' . esc_html($field['label']) . '</label></th>';
      echo '<td>';

      switch ($field['type']) {
        case 'wysiwyg':
          $editor_id = str_replace('_', '-', $key);
          wp_editor($value, $editor_id, array(
            'textarea_name' => $key,
            'textarea_rows' => $field['settings']['textarea_rows'],
            'media_buttons' => true,
          ));
          break;
        default:
          echo '<input type="' . esc_attr($field['type']) . '" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . $esc_value . '" class="large-text" placeholder="' . esc_attr($field['placeholder'] ?? '') . '" />';
          break;
      }

      echo '</td></tr>';
    }

    echo '</tbody></table>';
  }

  public function save_metabox($post_id)
  {
    if (!isset($_POST['paket_umrah_nonce'])) return;
    if (!wp_verify_nonce($_POST['paket_umrah_nonce'], 'paket_umrah_metabox')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_page', $post_id)) return;

    $fields = array(
      '_harga',
      '_harga_coret',
      '_durasi',
      '_maskapai',
      '_hotel_mekkah',
      '_hotel_madinah',
      '_kuota',
      '_tanggal_berangkat',
      '_tanggal_pulang',
      '_fasilitas',
      '_kenapa',
      '_syarat_ketentuan',
      '_itinerary',
    );

    foreach ($fields as $field) {
      if (isset($_POST[$field])) {
        $wysiwyg_fields = array('_fasilitas', '_kenapa', '_syarat_ketentuan', '_itinerary');
        if (in_array($field, $wysiwyg_fields)) {
          update_post_meta($post_id, $field, wp_kses_post($_POST[$field]));
        } elseif (is_array($_POST[$field])) {
          update_post_meta($post_id, $field, sanitize_text_field(implode(',', $_POST[$field])));
        } else {
          update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
      }
    }
  }
}
