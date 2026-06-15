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
        // Example: To activate, uncomment the line below.
        // add_shortcode('custom_hello', array($this, 'hello_shortcode'));
        add_shortcode('show-view', array($this, 'show_view'));
        add_shortcode('show-tags', array($this, 'show_tags'));
        add_shortcode('show-date', array($this, 'show_date'));
        add_shortcode('show-search', array($this, 'show_search'));
        add_shortcode('show-primary-menu', array($this, 'show_primary_menu'));
    }

    public function show_view()
    {
        ob_start();
        global $post;
        if (empty($post)) {
            return '';
        }
        $post_id = $post->ID;
        $count = get_post_meta($post_id, 'view_count', true);
        $count = $count ? $count : 0;
        echo '<span class="view-count">' . $count . ' views</span>';
        return ob_get_clean();
    }

    public function show_tags()
    {
        ob_start();
        global $post;
        if (empty($post)) {
            return '';
        }
        $tags = get_the_tags($post->ID);
        if ($tags) {
            echo '<div class="d-flex flex-wrap gap-2">';
            foreach ($tags as $tag) {
                $tag_link = get_tag_link($tag->term_id);
                echo '<a href="' . esc_url($tag_link) . '" class="btn btn-outline-secondary text-decoration-none rounded-pill px-3 py-1">' . esc_html('# ' . $tag->name) . '</a>';
            }
            echo '</div>';
        }
        return ob_get_clean();
    }

    public function show_date()
    {
        $hari = array(
            'Minggu',
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu'
        );
        $bulan = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        );
        $tahun = date('Y');
        $bulan_num = date('n');
        $tanggal = date('j');
        $hari_num = date('w');
        return $hari[$hari_num] . ', ' . $tanggal . ' ' . $bulan[$bulan_num] . ' ' . $tahun;
    }

    public function show_search()
    {
        ob_start();
?>
        <form role="search" method="get" class="d-flex" action="<?php echo esc_url(home_url('/')); ?>">
            <div class="input-group">
                <input type="search" class="form-control rounded-pill rounded-end-0" placeholder="Pencarian"
                    value="<?php echo get_search_query(); ?>" name="s">
                <button class="btn btn-outline-secondary rounded-pill rounded-start-0" type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search"
                        viewBox="0 0 16 16">
                        <path
                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                    </svg>
                </button>
            </div>
        </form>
    <?php
        return ob_get_clean();
    }



    public function show_primary_menu()
    {
        ob_start();
        $menu_name = 'Primary Menu';
        $menu = wp_get_nav_menu_object($menu_name);
        if (!$menu) {
            return '';
        }
        $menu_items = wp_get_nav_menu_items($menu->term_id, array('orderby' => 'menu_order'));
        if (!$menu_items) {
            return '';
        }
    ?>
        <button id="primary-menu-toggle" class="btn btn-outline-secondary rounded-circle"
            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"
            data-bs-toggle="popover" data-bs-html="true" data-bs-placement="bottom" data-bs-content='
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; min-width: 220px;">
                <?php foreach ($menu_items as $item) { ?>
                    <a href="<?php echo esc_url($item->url); ?>" style="text-decoration: none; color: #333; padding: 6px 10px; border-radius: 6px; display: block; line-height: 1.4;"><?php echo esc_html($item->title); ?></a>
                <?php } ?>
            </div>
        '>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-grid-3x3-gap-fill"
                viewBox="0 0 16 16">
                <path
                    d="M1 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V2zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V2zM1 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V7zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V7zM1 12a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-2zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-2zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-2z" />
            </svg>
        </button>
        <script>
            (function() {
                // Inisialisasi Bootstrap Popover setelah Bootstrap siap
                const popoverTrigger = document.getElementById('primary-menu-toggle');

                function initPopover() {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
                        new bootstrap.Popover(popoverTrigger);
                        return true;
                    }
                    return false;
                }

                // Polling setiap 100ms sampai Bootstrap siap
                let pollCount = 0;
                const pollInterval = setInterval(function() {
                    pollCount++;
                    if (initPopover()) {
                        clearInterval(pollInterval);
                    } else if (pollCount > 100) { // Timeout setelah 10 detik
                        clearInterval(pollInterval);
                        console.error('Bootstrap JS tidak terdeteksi!');
                    }
                }, 100);

                // Atau tunggu window load
                window.addEventListener('load', function() {
                    initPopover();
                });
            })();
        </script>
<?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [custom_hello name="User"]
     * 
     * @param array $atts
     * @return string
     */
    public function hello_shortcode($atts)
    {
        // 1. Define default attributes and merge with user inputs
        $atts = shortcode_atts(
            array(
                'name' => 'User',
                'color' => 'blue'
            ),
            $atts,
            'custom_hello'
        );

        // 2. Data to pass to template (Logic)
        $data = array(
            'name'  => sanitize_text_field($atts['name']),
            'color' => sanitize_hex_color($atts['color']) ?: 'blue'
        );

        // 3. Render using Template Engine (Separation of Concerns)
        return Template::get('frontend/hello-message', $data);
    }
}
