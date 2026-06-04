<?php

namespace CustomPlugin\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Frontend
 * 
 * Best practice for WordPress Action & Filter hooks.
 */
class Frontend
{

    public function __construct()
    {
        // Example: To activate frontend hooks, uncomment below.
        // add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('language_attributes', array($this, 'velocitytheme_color_scheme'));
        add_action('template_redirect', array($this, 'require_login_for_public_site'));
        add_action('login_enqueue_scripts', array($this, 'customize_login_screen'));
        add_action('login_header', array($this, 'render_login_topbar'));
        add_filter('login_headerurl', array($this, 'get_login_header_url'));
        add_filter('login_headertext', array($this, 'get_login_header_text'));

        // Filter with priority and number of arguments
        // add_filter('excerpt_length', array($this, 'custom_excerpt_length'), 999, 1);

        // Trigger a custom action (so other devs can hook into your plugin)
        // do_action('custom_plugin_after_frontend_init', $this);
    }

    /**
     * Adds a data-bs-theme attribute to the <html> tag based on a cookie.
     */
    public function velocitytheme_color_scheme($output)
    {
        $color_scheme = isset($_COOKIE["color_scheme"]) ? sanitize_text_field($_COOKIE["color_scheme"]) : 'light';
        return $output . ' data-bs-theme="' . esc_attr($color_scheme) . '"';
    }

    /**
     * Blocks public frontend access for visitors who are not logged in.
     */
    public function require_login_for_public_site()
    {
        if (is_user_logged_in()) {
            return;
        }

        if (is_admin() || wp_doing_ajax() || wp_doing_cron() || is_customize_preview()) {
            return;
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return;
        }

        wp_safe_redirect(wp_login_url(admin_url()));
        exit;
    }

    /**
     * Styles the WordPress login page with a dark fullscreen layout.
     */
    public function customize_login_screen()
    {
        $logo_url = $this->get_login_logo_url();
?>
        <style>
            body.login {
                min-height: 100vh;
                margin: 0;
                padding: 110px 24px 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                background:
                    radial-gradient(circle at top, rgba(255, 255, 255, 0.08), transparent 32%),
                    linear-gradient(180deg, #111111 0%, #000000 100%);
                color: #ffffff;
            }

            body.login div#login {
                width: min(100%, 420px);
                padding: 36px 32px 28px;
                background: rgba(8, 8, 8, 0.88);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 24px;
                box-shadow: 0 24px 80px rgba(0, 0, 0, 0.45);
                backdrop-filter: blur(12px);
            }

            body.login h1 {
                margin-bottom: 20px;
            }

            body.login h1 a {
                width: 180px;
                height: 90px;
                margin: 0 auto;
                background-size: contain;
                background-position: center;
                background-repeat: no-repeat;
            }

            body.login form {
                margin-top: 0;
                padding: 26px 24px 24px;
                background: rgba(255, 255, 255, 0.02);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 18px;
                box-shadow: none;
            }

            body.login label,
            body.login .message,
            body.login #nav a,
            body.login #backtoblog a {
                color: rgba(255, 255, 255, 0.82);
            }

            body.login .message,
            body.login #login_error,
            body.login .success {
                background: rgba(255, 255, 255, 0.05);
                border-left-color: #ffffff;
                color: #ffffff;
            }

            body.login input[type="text"],
            body.login input[type="password"] {
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.14);
                color: #ffffff;
                border-radius: 12px;
                box-shadow: none;
            }

            body.login input[type="text"]:focus,
            body.login input[type="password"]:focus {
                border-color: rgba(255, 255, 255, 0.35);
                box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.18);
            }

            body.login .button-primary {
                width: 100%;
                min-height: 44px;
                border: 0;
                border-radius: 999px;
                background: #ffffff;
                color: #000000;
                text-shadow: none;
                box-shadow: none;
            }

            body.login .button-primary:hover,
            body.login .button-primary:focus {
                background: #e9e9e9;
                color: #000000;
            }

            .custom-plugin-login-topbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 9999;
                padding: 18px 24px;
                background: rgba(0, 0, 0, 0.72);
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(12px);
            }

            .custom-plugin-login-topbar__inner {
                max-width: 1180px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 24px;
            }

            .custom-plugin-login-brand {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                color: #ffffff;
                text-decoration: none;
                font-size: 18px;
                font-weight: 700;
            }

            .custom-plugin-login-brand img {
                max-height: 42px;
                width: auto;
                display: block;
            }

            .custom-plugin-login-nav,
            .custom-plugin-login-nav ul {
                display: flex;
                align-items: center;
                gap: 18px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .custom-plugin-login-nav a {
                color: rgba(255, 255, 255, 0.88);
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
            }

            .custom-plugin-login-nav a:hover,
            .custom-plugin-login-nav a:focus {
                color: #ffffff;
            }

            @media (max-width: 782px) {
                body.login {
                    padding-top: 140px;
                }

                .custom-plugin-login-topbar__inner {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .custom-plugin-login-nav,
                .custom-plugin-login-nav ul {
                    flex-wrap: wrap;
                    gap: 12px;
                }

                body.login div#login {
                    padding: 28px 20px 20px;
                }

                body.login form {
                    padding: 22px 18px 18px;
                }
            }
        </style>
        <?php if (!empty($logo_url)) : ?>
            <style>
                body.login h1 a {
                    background-image: url('<?php echo esc_url($logo_url); ?>');
                }
            </style>
        <?php endif;
    }

    /**
     * Renders a fixed top navigation bar on the login page.
     */
    public function render_login_topbar()
    {
        $logo_url = $this->get_login_logo_url();
        ?>
        <div class="custom-plugin-login-topbar">
            <div class="custom-plugin-login-topbar__inner">
                <a class="custom-plugin-login-brand" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if (!empty($logo_url)) : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    <?php endif; ?>
                    <span><?php echo esc_html(get_bloginfo('name')); ?></span>
                </a>
                <?php echo $this->get_login_menu_markup(); ?>
            </div>
        </div>
<?php
    }

    /**
     * Uses the homepage for the login logo link.
     *
     * @return string
     */
    public function get_login_header_url()
    {
        return home_url('/');
    }

    /**
     * Uses the site name as the login logo text.
     *
     * @return string
     */
    public function get_login_header_text()
    {
        return get_bloginfo('name');
    }

    /**
     * Returns the logo URL from the active theme/site settings when available.
     *
     * @return string
     */
    private function get_login_logo_url()
    {
        $custom_logo_id = get_theme_mod('custom_logo');
        if (!empty($custom_logo_id)) {
            $logo_image = wp_get_attachment_image_src($custom_logo_id, 'full');
            if (!empty($logo_image[0])) {
                return $logo_image[0];
            }
        }

        $site_icon_id = (int) get_option('site_icon');
        if ($site_icon_id > 0) {
            $site_icon = wp_get_attachment_image_src($site_icon_id, 'full');
            if (!empty($site_icon[0])) {
                return $site_icon[0];
            }
        }

        return '';
    }

    /**
     * Builds login navigation markup using the assigned theme menu when possible.
     *
     * @return string
     */
    private function get_login_menu_markup()
    {
        $menu_location = $this->get_login_menu_location();

        if (!empty($menu_location)) {
            $menu_markup = wp_nav_menu(array(
                'theme_location' => $menu_location,
                'container'      => 'nav',
                'container_class' => 'custom-plugin-login-nav',
                'menu_class'     => 'custom-plugin-login-nav__items',
                'echo'           => false,
                'fallback_cb'    => false,
                'depth'          => 1,
            ));

            if (!empty($menu_markup)) {
                return $menu_markup;
            }
        }

        $links = array(
            array(
                'label' => __('Beranda', 'custom-plugin'),
                'url'   => home_url('/'),
            ),
        );

        if (get_option('users_can_register')) {
            $links[] = array(
                'label' => __('Daftar', 'custom-plugin'),
                'url'   => wp_registration_url(),
            );
        }

        $items = '';
        foreach ($links as $link) {
            $items .= sprintf(
                '<li><a href="%1$s">%2$s</a></li>',
                esc_url($link['url']),
                esc_html($link['label'])
            );
        }

        return sprintf(
            '<nav class="custom-plugin-login-nav" aria-label="%1$s"><ul>%2$s</ul></nav>',
            esc_attr__('Login navigation', 'custom-plugin'),
            $items
        );
    }

    /**
     * Finds the most likely frontend menu location from the active theme.
     *
     * @return string
     */
    private function get_login_menu_location()
    {
        $locations = get_nav_menu_locations();
        if (empty($locations) || !is_array($locations)) {
            return '';
        }

        $preferred_locations = array('primary', 'main', 'header', 'top', 'menu-1');

        foreach ($preferred_locations as $location) {
            if (!empty($locations[$location])) {
                return $location;
            }
        }

        $available_locations = array_keys(array_filter($locations));

        return !empty($available_locations) ? $available_locations[0] : '';
    }

    /**
     * Proper way to filter data with arguments.
     * 
     * @param int $length
     * @return int
     */
    public function custom_excerpt_length($length)
    {
        // Apply logic only on specific pages
        if (is_front_page()) {
            return 20;
        }
        return $length;
    }

    /**
     * Best Practice: Wrapping output in a filter.
     * 
     * @return string
     */
    public static function get_formatted_price($price)
    {
        $formatted = 'Rp ' . number_format($price, 0, ',', '.');

        // Always provide a filter so others can modify your output
        return apply_filters('custom_plugin_format_price', $formatted, $price);
    }

    /**
     * Proper way to enqueue styles and scripts.
     */
    public function enqueue_scripts()
    {
        // Use CUSTOM_PLUGIN_URL and CUSTOM_PLUGIN_VERSION defined in main file.
        // wp_enqueue_style('custom-plugin-frontend', CUSTOM_PLUGIN_URL . 'assets/frontend/css/frontend.css', array(), CUSTOM_PLUGIN_VERSION);
        // wp_enqueue_script('custom-plugin-frontend', CUSTOM_PLUGIN_URL . 'assets/frontend/js/frontend.js', array('jquery'), CUSTOM_PLUGIN_VERSION, true);
    }

    /**
     * Example: add_action('wp_head') - adding tags to <head>.
     */
    public function add_meta_tags()
    {
        // echo '<meta name="custom-plugin" content="enabled" />' . "\n";
    }

    /**
     * Example: add_filter('the_content') - modifying content before output.
     */
    public function modify_content($content)
    {
        // Always check context if modifying core templates.
        // if (is_single()) {
        //     $content .= '<div class="custom-plugin-notice">' . __('Modified by Custom Plugin', 'custom-plugin') . '</div>';
        // }
        return $content;
    }
}
