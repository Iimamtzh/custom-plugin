<?php

namespace CustomPlugin\Frontend;

use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Shortcode
 * 
 * Best practice for Shortcode implementation.
 */
class Shortcode
{

    public function __construct()
    {
        add_shortcode('kbli_list', array($this, 'kbli_list_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_kbli_assets'));
    }

    public function enqueue_kbli_assets()
    {
        wp_register_style('datatables', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array(), '1.13.6');
        wp_register_script('datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), '1.13.6', true);
    }

    public function kbli_list_shortcode($atts)
    {
        wp_enqueue_style('datatables');
        wp_enqueue_script('datatables');

        $paged   = get_query_var('paged') ? get_query_var('paged') : 1;
        $per_page = 25;

        $query = new \WP_Query(array(
            'post_type'      => 'kbli',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));

        $rows = '';
        $no   = ($paged - 1) * $per_page + 1;

        while ($query->have_posts()) {
            $query->the_post();
            $kode       = get_post_meta(get_the_ID(), '_kbli_kode', true);
            $keterangan = get_post_meta(get_the_ID(), '_kbli_keterangan', true);
            $rows .= '<tr>';
            $rows .= '<td>' . $no++ . '</td>';
            $rows .= '<td>' . esc_html($kode) . '</td>';
            $rows .= '<td>' . esc_html(get_the_title()) . '</td>';
            $rows .= '<td>' . esc_html($keterangan) . '</td>';
            $rows .= '</tr>';
        }

        $pagination = paginate_links(array(
            'total'   => $query->max_num_pages,
            'current' => $paged,
        ));

        wp_reset_postdata();

        ob_start();
?>
<div class="kbli-table-wrapper">
    <table id="kbli-table" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Judul</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php echo $rows; ?>
        </tbody>
    </table>
    <?php if ($pagination): ?>
    <div class="kbli-pagination">
        <?php echo $pagination; ?>
    </div>
    <?php endif; ?>
</div>
<script>
jQuery(document).ready(function($) {
    $('#kbli-table').DataTable({
        paging: false,
        searching: true,
        ordering: true,
        info: false,
        language: {
            search: 'Cari:',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Tidak ada data',
        }
    });
});
</script>
<?php
        return ob_get_clean();
    }
}