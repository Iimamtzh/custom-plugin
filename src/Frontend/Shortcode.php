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
        add_shortcode('show-dark-mode-toggle', array($this, 'show_dark_mode_toggle'));
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

    public function show_dark_mode_toggle()
    {
        ob_start();
    ?>
        <button id="dark-mode-toggle" class="btn btn-outline-secondary rounded-circle"
            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
            <svg id="dark-mode-icon-sun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-sun-fill" viewBox="0 0 16 16" style="display: none;">
                <path
                    d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
            </svg>
            <svg id="dark-mode-icon-moon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-moon-stars-fill" viewBox="0 0 16 16">
                <path
                    d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z" />
                <path
                    d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732l.258-.774z" />
            </svg>
        </button>
        <script>
            (function() {
                const toggle = document.getElementById('dark-mode-toggle');
                const sunIcon = document.getElementById('dark-mode-icon-sun');
                const moonIcon = document.getElementById('dark-mode-icon-moon');
                const html = document.documentElement;

                function updateIcons() {
                    if (html.classList.contains('dark-mode')) {
                        sunIcon.style.display = 'block';
                        moonIcon.style.display = 'none';
                    } else {
                        sunIcon.style.display = 'none';
                        moonIcon.style.display = 'block';
                    }
                }

                function setDarkMode(isDark) {
                    if (isDark) {
                        html.classList.add('dark-mode');
                        const now = new Date();
                        const expiry = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000); // 1 bulan
                        localStorage.setItem('custom-plugin-dark-mode', 'true');
                        localStorage.setItem('custom-plugin-dark-mode-expiry', expiry.toISOString());
                    } else {
                        html.classList.remove('dark-mode');
                        localStorage.removeItem('custom-plugin-dark-mode');
                        localStorage.removeItem('custom-plugin-dark-mode-expiry');
                    }
                    updateIcons();
                }

                // Cek expiry pada saat load
                (function checkExpiry() {
                    const expiry = localStorage.getItem('custom-plugin-dark-mode-expiry');
                    if (expiry) {
                        const expiryDate = new Date(expiry);
                        const now = new Date();
                        if (now > expiryDate) {
                            localStorage.removeItem('custom-plugin-dark-mode');
                            localStorage.removeItem('custom-plugin-dark-mode-expiry');
                        }
                    }
                })();

                updateIcons();

                toggle.addEventListener('click', function() {
                    const isDark = !html.classList.contains('dark-mode');
                    setDarkMode(isDark);
                });
            })();
        </script>
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
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                <?php foreach ($menu_items as $item) { ?>
                    <a href="<?php echo esc_url($item->url); ?>" style="text-decoration: none; color: inherit;"><?php echo esc_html($item->title); ?></a>
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
                // Inisialisasi Bootstrap Popover
                const popoverTrigger = document.getElementById('primary-menu-toggle');
                if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
                    new bootstrap.Popover(popoverTrigger);
                } else {
                    // Fallback jika Bootstrap JS tidak terload
                    popoverTrigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        alert('Bootstrap JS tidak terdeteksi! Silakan load Bootstrap JS untuk menggunakan popover.');
                    });
                }
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
