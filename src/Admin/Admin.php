<?php

namespace CustomPlugin\Admin;

use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Admin
 *
 * Best practice for WordPress Admin Menu and Hooks.
 */
class Admin
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('add_meta_boxes', array($this, 'add_dokumen_meta_box'));
        add_action('save_post', array($this, 'save_dokumen_pdf'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Proper way to add an Admin Menu Page.
     */
    public function add_admin_menu()
    {
        add_menu_page(
            __('Custom Plugin', 'custom-plugin'),
            __('Plugin Kustom', 'custom-plugin'),
            'manage_options',
            'custom-plugin',
            array($this, 'admin_page'),
            'dashicons-admin-plugins',
            30
        );
    }

    /**
     * Add meta box for PDF upload on dokumen post type.
     */
    public function add_dokumen_meta_box()
    {
        add_meta_box(
            'dokumen_pdf_upload',
            'File PDF Dokumen',
            array($this, 'render_dokumen_pdf_meta_box'),
            'dokumen',
            'normal',
            'high'
        );
    }

    /**
     * Render PDF upload meta box.
     */
    public function render_dokumen_pdf_meta_box($post)
    {
        wp_nonce_field('dokumen_pdf_upload', 'dokumen_pdf_nonce');

        $pdf_id = get_post_meta($post->ID, '_dokumen_pdf_id', true);
        $pdf_url = '';
        $pdf_name = '';

        if ($pdf_id) {
            $pdf_url = wp_get_attachment_url($pdf_id);
            $pdf_name = get_the_title($pdf_id);
        }
        ?>
        <div class="dokumen-pdf-upload-wrapper">
            <div class="dokumen-pdf-preview" style="margin-bottom: 10px;">
                <?php if ($pdf_url) : ?>
                    <p>
                        <strong>File saat ini:</strong>
                        <a href="<?php echo esc_url($pdf_url); ?>" target="_blank">
                            <?php echo esc_html($pdf_name); ?>
                        </a>
                        <span class="dashicons dashicons-pdf" style="color:#d63638;vertical-align:middle;"></span>
                    </p>
                <?php else : ?>
                    <p style="color: #888;">Belum ada file PDF yang diunggah.</p>
                <?php endif; ?>
            </div>

            <input type="hidden" name="dokumen_pdf_id" id="dokumen_pdf_id" value="<?php echo esc_attr($pdf_id); ?>" />

            <button type="button" class="button" id="upload_pdf_btn">
                <span class="dashicons dashicons-upload" style="vertical-align:middle;"></span>
                <?php echo $pdf_id ? 'Ganti PDF' : 'Unggah PDF'; ?>
            </button>

            <?php if ($pdf_id) : ?>
                <button type="button" class="button" id="remove_pdf_btn" style="color:#d63638;">
                    <span class="dashicons dashicons-trash" style="vertical-align:middle;"></span>
                    Hapus PDF
                </button>
            <?php endif; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var frame;

            $('#upload_pdf_btn').on('click', function(e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: 'Pilih atau Unggah File PDF',
                    button: { text: 'Gunakan PDF ini' },
                    library: { type: 'application/pdf' },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#dokumen_pdf_id').val(attachment.id);
                    $('.dokumen-pdf-preview').html(
                        '<p><strong>File saat ini:</strong> ' +
                        '<a href="' + attachment.url + '" target="_blank">' + attachment.title + '</a> ' +
                        '<span class="dashicons dashicons-pdf" style="color:#d63638;vertical-align:middle;"></span></p>'
                    );
                    $('#upload_pdf_btn').text('Ganti PDF');
                    if ($('#remove_pdf_btn').length === 0) {
                        $('#upload_pdf_btn').after(
                            '<button type="button" class="button" id="remove_pdf_btn" style="color:#d63638;">' +
                            '<span class="dashicons dashicons-trash" style="vertical-align:middle;"></span> Hapus PDF</button>'
                        );
                    }
                });

                frame.open();
            });

            $(document).on('click', '#remove_pdf_btn', function(e) {
                e.preventDefault();
                $('#dokumen_pdf_id').val('');
                $('.dokumen-pdf-preview').html('<p style="color:#888;">Belum ada file PDF yang diunggah.</p>');
                $('#upload_pdf_btn').text('Unggah PDF');
                $(this).remove();
            });
        });
        </script>
        <?php
    }

    /**
     * Save PDF attachment ID when dokumen post is saved.
     */
    public function save_dokumen_pdf($post_id, $post)
    {
        if ($post->post_type !== 'dokumen') {
            return;
        }

        if (!isset($_POST['dokumen_pdf_nonce']) ||
            !wp_verify_nonce($_POST['dokumen_pdf_nonce'], 'dokumen_pdf_upload')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['dokumen_pdf_id'])) {
            $pdf_id = absint($_POST['dokumen_pdf_id']);
            if ($pdf_id) {
                update_post_meta($post_id, '_dokumen_pdf_id', $pdf_id);
            } else {
                delete_post_meta($post_id, '_dokumen_pdf_id');
            }
        }
    }

    /**
     * Enqueue admin scripts only on dokumen edit page.
     */
    public function enqueue_admin_scripts($hook)
    {
        // Only load on dokumen post type screens
        global $post_type;
        if (($hook === 'post-new.php' || $hook === 'post.php') && $post_type === 'dokumen') {
            wp_enqueue_media();
        }
    }

    /**
     * Admin Dashboard Callback.
     */
    public function admin_page()
    {
        Template::render('admin/dashboard');
    }
}
