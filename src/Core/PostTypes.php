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
    add_action('add_meta_boxes', array($this, 'add_kbli_meta_box'));
    add_action('add_meta_boxes', array($this, 'add_layanan_meta_box'));
    add_action('save_post', array($this, 'save_kbli_meta'));
    add_action('save_post', array($this, 'save_layanan_meta'));
  }

  public function register_post_types()
  {
    $this->register_kbli();
    $this->register_layanan();
  }

  private function register_kbli()
  {
    $labels = array(
      'name'                  => 'KBLI',
      'singular_name'         => 'KBLI',
      'menu_name'             => 'KBLI',
      'name_admin_bar'        => 'KBLI',
      'add_new'               => 'Tambah Baru',
      'add_new_item'          => 'Tambah KBLI Baru',
      'new_item'              => 'KBLI Baru',
      'edit_item'             => 'Edit KBLI',
      'view_item'             => 'Lihat KBLI',
      'all_items'             => 'Semua KBLI',
      'search_items'          => 'Cari KBLI',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
    );

    $args = array(
      'label'               => 'KBLI',
      'labels'              => $labels,
      'supports'            => array('title'),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 6,
      'menu_icon'           => 'dashicons-list-view',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => false,
      'can_export'          => true,
      'has_archive'         => false,
      'exclude_from_search' => true,
      'publicly_queryable'  => true,
      'capability_type'     => 'post',
      'show_in_rest'        => true,
    );

    register_post_type('kbli', $args);
  }

  private function register_layanan()
  {
    $labels = array(
      'name'               => 'Layanan',
      'singular_name'      => 'Layanan',
      'menu_name'          => 'Layanan',
      'name_admin_bar'     => 'Layanan',
      'add_new'            => 'Tambah Baru',
      'add_new_item'       => 'Tambah Layanan Baru',
      'new_item'           => 'Layanan Baru',
      'edit_item'          => 'Edit Layanan',
      'view_item'          => 'Lihat Layanan',
      'all_items'          => 'Semua Layanan',
      'search_items'       => 'Cari Layanan',
      'not_found'          => 'Tidak ditemukan',
      'not_found_in_trash' => 'Tidak ditemukan di Tong Sampah',
    );

    $args = array(
      'label'               => 'Layanan',
      'labels'              => $labels,
      'supports'            => array('title', 'excerpt', 'thumbnail'),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 7,
      'menu_icon'           => 'dashicons-admin-tools',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'post',
      'show_in_rest'        => true,
    );

    register_post_type('layanan', $args);
  }

  public function add_kbli_meta_box()
  {
    add_meta_box(
      'kbli_meta',
      'Data KBLI',
      array($this, 'render_kbli_meta_box'),
      'kbli',
      'normal',
      'high'
    );
  }

  public function add_layanan_meta_box()
  {
    add_meta_box(
      'layanan_meta',
      'Data Layanan',
      array($this, 'render_layanan_meta_box'),
      'layanan',
      'normal',
      'high'
    );
  }

  public function render_kbli_meta_box($post)
  {
    wp_nonce_field('kbli_meta_nonce', 'kbli_meta_nonce_field');

    $kode       = get_post_meta($post->ID, '_kbli_kode', true);
    $keterangan = get_post_meta($post->ID, '_kbli_keterangan', true);
?>
<table class="form-table">
    <tr>
        <th><label for="kbli_kode">Kode</label></th>
        <td>
            <input type="text" id="kbli_kode" name="kbli_kode" value="<?php echo esc_attr($kode); ?>"
                class="regular-text" />
        </td>
    </tr>
    <tr>
        <th><label for="kbli_keterangan">Keterangan</label></th>
        <td>
            <textarea id="kbli_keterangan" name="kbli_keterangan" class="large-text"
                rows="3"><?php echo esc_textarea($keterangan); ?></textarea>
        </td>
    </tr>
</table>
<?php
  }

  public function render_layanan_meta_box($post)
  {
    wp_nonce_field('layanan_meta_nonce', 'layanan_meta_nonce_field');

    $harga = get_post_meta($post->ID, '_layanan_harga', true);
  ?>
<table class="form-table">
    <tr>
        <th><label for="layanan_harga">Harga</label></th>
        <td>
            <input type="text" id="layanan_harga" name="layanan_harga" value="<?php echo esc_attr($harga); ?>"
                class="regular-text" />
        </td>
    </tr>
    <tr>
        <th>Deskripsi Singkat</th>
        <td>
            <p>Gunakan field excerpt bawaan WordPress untuk deskripsi singkat.</p>
        </td>
    </tr>
</table>
<?php
  }

  public function save_kbli_meta($post_id)
  {
    if (!isset($_POST['kbli_meta_nonce_field']) || !wp_verify_nonce($_POST['kbli_meta_nonce_field'], 'kbli_meta_nonce')) {
      return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    if (!current_user_can('edit_post', $post_id)) {
      return;
    }

    if (isset($_POST['kbli_kode'])) {
      update_post_meta($post_id, '_kbli_kode', sanitize_text_field($_POST['kbli_kode']));
    }

    if (isset($_POST['kbli_keterangan'])) {
      update_post_meta($post_id, '_kbli_keterangan', sanitize_textarea_field($_POST['kbli_keterangan']));
    }
  }

  public function save_layanan_meta($post_id)
  {
    if (!isset($_POST['layanan_meta_nonce_field']) || !wp_verify_nonce($_POST['layanan_meta_nonce_field'], 'layanan_meta_nonce')) {
      return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    if (!current_user_can('edit_post', $post_id)) {
      return;
    }

    if (isset($_POST['layanan_harga'])) {
      update_post_meta($post_id, '_layanan_harga', sanitize_text_field($_POST['layanan_harga']));
    }
  }
}