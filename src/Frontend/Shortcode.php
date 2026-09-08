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
        add_shortcode('custom_hello', array($this, 'hello_shortcode'));
        add_shortcode('custom_dark_mode', array($this, 'dark_mode_shortcode'));
        // Alias biar fleksibel
        add_shortcode('dark_mode_toggle', array($this, 'dark_mode_shortcode'));
        add_shortcode('custom_risk_disclosure', array($this, 'risk_disclosure_shortcode'));
    }

    /**
     * Shortcode: [custom_dark_mode size="md" label="Toggle dark mode" class=""]
     * Tampilan hanya ikon (sun/moon) — klik untuk toggle.
     *
     * @param array $atts
     * @return string
     */
    public function dark_mode_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'size'  => 'md', // sm|md|lg
                'label' => __('Toggle dark mode', 'custom-plugin'),
                'class' => '',
            ),
            $atts,
            'custom_dark_mode'
        );

        $size  = in_array($atts['size'], array('sm', 'md', 'lg'), true) ? $atts['size'] : 'md';
        $label = sanitize_text_field($atts['label']);
        $extra = sanitize_text_field($atts['class']);

        // Ikon SVG inline (tanpa dependensi font/icon)
        $icon_sun = '<svg class="cp-darkmode-svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.42 1.41M18.36 5.64l1.42-1.42"/></svg>';
        $icon_moon = '<svg class="cp-darkmode-svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';

        ob_start();
?>
<button type="button"
    class="cp-darkmode-toggle cp-darkmode-toggle--<?php echo esc_attr($size); ?> <?php echo esc_attr($extra); ?>"
    aria-label="<?php echo esc_attr($label); ?>" aria-pressed="false" data-cp-darkmode-toggle>
    <span class="cp-darkmode-icon cp-darkmode-icon--light" aria-hidden="true"><?php echo $icon_sun; // phpcs:ignore 
                                                                                        ?></span>
    <span class="cp-darkmode-icon cp-darkmode-icon--dark" aria-hidden="true"><?php echo $icon_moon; // phpcs:ignore 
                                                                                        ?></span>
</button>
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
        $atts = shortcode_atts(
            array(
                'name' => 'User',
                'color' => 'blue'
            ),
            $atts,
            'custom_hello'
        );

        $data = array(
            'name'  => sanitize_text_field($atts['name']),
            'color' => sanitize_hex_color($atts['color']) ?: 'blue'
        );

        return Template::get('frontend/hello-message', $data);
    }

    /**
     * Shortcode: [custom_risk_disclosure button_url="" button_text="AMBIL LISENSI SOFTWARE SEKARANG"]
     * Logika validasi ada di assets/frontend/js/risk-disclosure.js (file eksternal,
     * agar tidak di-strip oleh Website Builder). HTML di sini murni markup saja.
     *
     * @param array $atts
     * @return string
     */
    public function risk_disclosure_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'button_url'  => '',
                'button_text' => 'AMBIL LISENSI SOFTWARE SEKARANG',
                'phrase'      => 'SAYA PAHAM RISIKO TRADING',
                'class'       => '',
            ),
            $atts,
            'custom_risk_disclosure'
        );

        $phrase      = sanitize_text_field($atts['phrase']) ?: 'SAYA PAHAM RISIKO TRADING';
        $button_text = sanitize_text_field($atts['button_text']);
        $button_url  = esc_url_raw(trim($atts['button_url']));
        $extra       = sanitize_text_field($atts['class']);
        $id          = 'cp-risk-' . wp_generate_uuid4();

        // Nomor WhatsApp admin dari halaman setting (custom-plugin-cf7-whatsapp).
        $wa = preg_replace('/\D/', '', (string) get_option('custom_plugin_cf7_whatsapp_number', '6285806522700'));
        if (empty($wa)) {
            $wa = '6285806522700';
        }

        ob_start();
    ?>
<div id="<?php echo esc_attr($id); ?>" class="cp-risk-disclosure <?php echo esc_attr($extra); ?>"
    data-required-phrase="<?php echo esc_attr($phrase); ?>" data-button-url="<?php echo esc_attr($button_url); ?>"
    data-wa-number="<?php echo esc_attr($wa); ?>"
    style="max-width:640px;margin:0 auto;padding:24px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;box-shadow:0 4px 16px rgba(0,0,0,.06);font-family:inherit;">
    <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.04em;color:#dc2626;">⚠️ LEMBAR PENGUNGKAPAN
        RISIKO &amp; PERNYATAAN KESADARAN PENGGUNA</p>
    <p style="margin:0 0 16px;font-size:13px;color:#4b5563;">Sebelum Anda melanjutkan transaksi lisensi software Expert
        Advisor (EA), Anda <strong>DIWAJIBKAN</strong> membaca, memahami, dan menyetujui poin-poin hukum di bawah ini:
    </p>
    <ol style="margin:0 0 16px;padding-left:20px;font-size:14px;line-height:1.7;color:#111827;">
        <li>Seluruh dana modal trading berada penuh di bawah kendali Anda pada akun terpisah (<em>segregated
                account</em>).</li>
        <li>Tradershood Community murni menyediakan lisensi perangkat lunak (<em>software</em>) sebagai alat bantu
            transaksi otomatis dan <strong>TIDAK</strong> mengelola dana pihak ketiga.</li>
        <li>Perdagangan berjangka memiliki risiko tinggi yang dapat menyebabkan kerugian sebagian atau seluruh modal
            Anda. Performa masa lalu tidak menjamin profit esok.</li>
    </ol>
    <label
        style="display:flex;gap:10px;align-items:flex-start;margin:0 0 14px;font-size:13px;font-weight:700;color:#111827;cursor:pointer;">
        <input type="checkbox" data-cp-risk-check style="margin-top:3px;width:18px;height:18px;accent-color:#16a34a;" />
        <span>SAYA TELAH MEMBACA, MEMAHAMI, DAN BERTANGGUNG JAWAB PENUH ATAS RISIKO SAYA.</span>
    </label>
    <label style="display:block;margin:0 0 6px;font-size:13px;color:#374151;">Ketik persis frasa berikut untuk
        mengaktifkan tombol: <strong
            style="background:#fef9c3;padding:2px 6px;border-radius:4px;">“<?php echo esc_html($phrase); ?>”</strong></label>
    <input type="text" data-cp-risk-input placeholder="<?php echo esc_attr($phrase); ?>" autocomplete="off"
        spellcheck="false"
        style="width:100%;box-sizing:border-box;padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;margin-bottom:6px;" />
    <p data-cp-risk-hint style="margin:0 0 14px;font-size:12px;color:#6b7280;">Tombol aktif hanya jika kotak dicentang
        DAN teks sama persis (huruf besar/kecil &amp; spasi harus sama).</p>
    <button type="button" data-cp-risk-button disabled aria-disabled="true"
        style="width:100%;padding:14px 16px;border:0;border-radius:8px;font-size:15px;font-weight:800;letter-spacing:.02em;background:#d1d5db;color:#6b7280;cursor:not-allowed;transition:all .2s;"><?php echo esc_html($button_text); ?></button>
</div>
<?php
        return ob_get_clean();
    }
}