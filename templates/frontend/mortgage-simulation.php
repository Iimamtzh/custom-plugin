<?php

if (!defined('ABSPATH')) {
    exit;
}

$wa_link = !empty($wa_link) ? $wa_link : '#';
$initial_values = isset($initial_values) && is_array($initial_values) ? $initial_values : array();
$initial_result = isset($initial_result) && is_array($initial_result) ? $initial_result : array();
?>

<div
    class="custom-kpr-simulator"
    id="<?php echo esc_attr($simulator_id); ?>"
    data-kpr-simulator
    data-other-costs-pct="<?php echo esc_attr($initial_values['other_costs_pct']); ?>"
>
    <div class="custom-kpr-simulator__panel">
        <h2 class="custom-kpr-simulator__title"><?php esc_html_e('Simulasi KPR', 'custom-plugin'); ?></h2>
        <p class="custom-kpr-simulator__subtitle"><?php echo esc_html($subtitle); ?></p>

        <form class="custom-kpr-simulator__form" data-kpr-form>
            <div class="custom-kpr-simulator__group">
                <div class="custom-kpr-simulator__label-row">
                    <label class="custom-kpr-simulator__label" for="<?php echo esc_attr($simulator_id); ?>-price"><?php esc_html_e('Harga Property*', 'custom-plugin'); ?></label>
                </div>
                <div class="custom-kpr-simulator__value-row">
                    <div></div>
                    <div class="custom-kpr-simulator__field">
                        <span class="custom-kpr-simulator__prefix">Rp</span>
                        <input class="custom-kpr-simulator__input" id="<?php echo esc_attr($simulator_id); ?>-price" type="text" value="<?php echo esc_attr((string) round($initial_values['price'])); ?>" data-kpr-price inputmode="numeric">
                    </div>
                </div>
            </div>

            <div class="custom-kpr-simulator__group custom-kpr-simulator__group--compact">
                <div class="custom-kpr-simulator__label-row">
                    <label class="custom-kpr-simulator__label" for="<?php echo esc_attr($simulator_id); ?>-dp-pct"><?php esc_html_e('Uang Muka', 'custom-plugin'); ?></label>
                </div>
                <div class="custom-kpr-simulator__value-row">
                    <div class="custom-kpr-simulator__field-inline">
                        <input class="custom-kpr-simulator__input" id="<?php echo esc_attr($simulator_id); ?>-dp-pct" type="text" value="<?php echo esc_attr((string) $initial_values['down_payment_pct']); ?>" data-kpr-dp-pct inputmode="decimal">
                        <span class="custom-kpr-simulator__suffix">%</span>
                    </div>
                    <div class="custom-kpr-simulator__field">
                        <span class="custom-kpr-simulator__prefix">Rp</span>
                        <input class="custom-kpr-simulator__input" type="text" value="<?php echo esc_attr((string) round($initial_values['down_payment_amt'])); ?>" data-kpr-dp-amt inputmode="numeric">
                    </div>
                </div>
                <input class="custom-kpr-simulator__slider" type="range" min="10" max="90" step="1" value="<?php echo esc_attr((string) round($initial_values['down_payment_pct'])); ?>" data-kpr-dp-slider>
                <div class="custom-kpr-simulator__scale">
                    <span>10%</span>
                    <span>90%</span>
                </div>
            </div>

            <div class="custom-kpr-simulator__group">
                <div class="custom-kpr-simulator__label-row">
                    <label class="custom-kpr-simulator__label" for="<?php echo esc_attr($simulator_id); ?>-fixed-rate"><?php esc_html_e('Pilihan Suku Bunga', 'custom-plugin'); ?></label>
                </div>
                <div class="custom-kpr-simulator__value-row">
                    <div></div>
                    <div class="custom-kpr-simulator__field-inline">
                        <input class="custom-kpr-simulator__input" id="<?php echo esc_attr($simulator_id); ?>-fixed-rate" type="text" value="<?php echo esc_attr((string) $initial_values['fixed_rate']); ?>" data-kpr-fixed-rate inputmode="decimal">
                        <span class="custom-kpr-simulator__suffix">%</span>
                    </div>
                </div>
            </div>

            <div class="custom-kpr-simulator__group custom-kpr-simulator__group--compact">
                <div class="custom-kpr-simulator__label-row">
                    <label class="custom-kpr-simulator__label" for="<?php echo esc_attr($simulator_id); ?>-fixed-years"><?php esc_html_e('Jangka Waktu Suku Bunga Fix', 'custom-plugin'); ?></label>
                </div>
                <div class="custom-kpr-simulator__value-row">
                    <div></div>
                    <div class="custom-kpr-simulator__field-inline">
                        <input class="custom-kpr-simulator__input" id="<?php echo esc_attr($simulator_id); ?>-fixed-years" type="text" value="<?php echo esc_attr((string) $initial_values['fixed_years']); ?>" data-kpr-fixed-years inputmode="numeric">
                        <span class="custom-kpr-simulator__suffix"><?php esc_html_e('Tahun', 'custom-plugin'); ?></span>
                    </div>
                </div>
                <input class="custom-kpr-simulator__slider" type="range" min="1" max="25" step="1" value="<?php echo esc_attr((string) $initial_values['fixed_years']); ?>" data-kpr-fixed-years-slider>
                <div class="custom-kpr-simulator__scale">
                    <span>1 tahun</span>
                    <span>25 tahun</span>
                </div>
            </div>

            <div class="custom-kpr-simulator__group custom-kpr-simulator__group--compact">
                <div class="custom-kpr-simulator__label-row">
                    <label class="custom-kpr-simulator__label" for="<?php echo esc_attr($simulator_id); ?>-loan-years"><?php esc_html_e('Jangka Waktu KPR', 'custom-plugin'); ?></label>
                </div>
                <div class="custom-kpr-simulator__value-row">
                    <div></div>
                    <div class="custom-kpr-simulator__field-inline">
                        <input class="custom-kpr-simulator__input" id="<?php echo esc_attr($simulator_id); ?>-loan-years" type="text" value="<?php echo esc_attr((string) $initial_values['loan_years']); ?>" data-kpr-loan-years inputmode="numeric">
                        <span class="custom-kpr-simulator__suffix"><?php esc_html_e('Tahun', 'custom-plugin'); ?></span>
                    </div>
                </div>
                <input class="custom-kpr-simulator__slider" type="range" min="1" max="25" step="1" value="<?php echo esc_attr((string) $initial_values['loan_years']); ?>" data-kpr-loan-years-slider>
                <div class="custom-kpr-simulator__scale">
                    <span>1 tahun</span>
                    <span>25 tahun</span>
                </div>
            </div>

            <input type="hidden" value="<?php echo esc_attr((string) $initial_values['floating_rate']); ?>" data-kpr-floating-rate>

            <button class="custom-kpr-simulator__button" type="submit"><?php echo esc_html($button_label); ?></button>
        </form>
    </div>

    <div class="custom-kpr-simulator__summary">
        <div class="custom-kpr-simulator__summary-badge"><?php esc_html_e('Ringkasan Simulasi', 'custom-plugin'); ?></div>

        <div class="custom-kpr-simulator__summary-card">
            <div class="custom-kpr-simulator__summary-meta">
                <div>
                    <span data-kpr-period><?php echo esc_html($initial_result['fixed_period_label']); ?></span>
                </div>
                <div>
                    <?php esc_html_e('Suku Bunga', 'custom-plugin'); ?>
                    <strong data-kpr-fixed-rate-output><?php echo esc_html(rtrim(rtrim(number_format((float) $initial_result['fixed_rate'], 2, '.', ''), '0'), '.')); ?>%</strong>
                </div>
                <div>
                    <?php esc_html_e('Masa Fix', 'custom-plugin'); ?>
                    <strong data-kpr-fixed-years-output><?php echo esc_html((int) $initial_result['fixed_years']); ?> Tahun</strong>
                </div>
                <div>
                    <?php esc_html_e('Tenor', 'custom-plugin'); ?>
                    <strong data-kpr-loan-years-output><?php echo esc_html((int) $initial_result['loan_years']); ?> Tahun</strong>
                </div>
            </div>
        </div>

        <div class="custom-kpr-simulator__hero">
            <h3 class="custom-kpr-simulator__hero-title"><?php esc_html_e('Angsuran/bulan Fix', 'custom-plugin'); ?></h3>
            <div class="custom-kpr-simulator__hero-row">
                <div class="custom-kpr-simulator__hero-copy">
                    <?php esc_html_e('Tahun ke-1', 'custom-plugin'); ?><br>
                    <?php esc_html_e('Bunga', 'custom-plugin'); ?> <span data-kpr-fixed-rate-output><?php echo esc_html(rtrim(rtrim(number_format((float) $initial_result['fixed_rate'], 2, '.', ''), '0'), '.')); ?>%</span>
                    <strong data-kpr-fixed-payment><?php echo esc_html('Rp' . number_format((float) $initial_result['fixed_monthly_payment'], 0, ',', '.')); ?></strong>
                </div>
                <a class="custom-kpr-simulator__contact" href="<?php echo esc_url($wa_link); ?>"<?php echo '#' === $wa_link ? ' role="button"' : ' target="_blank" rel="noopener noreferrer"'; ?>>
                    <?php echo esc_html($contact_label); ?>
                </a>
            </div>
        </div>

        <h3 class="custom-kpr-simulator__details-title"><?php esc_html_e('Detail Pembayaran', 'custom-plugin'); ?></h3>

        <div class="custom-kpr-simulator__detail-card">
            <div class="custom-kpr-simulator__detail-head">
                <span><?php esc_html_e('Estimasi Bunga Floating', 'custom-plugin'); ?></span>
                <span data-kpr-floating-payment><?php echo esc_html('Rp' . number_format((float) $initial_result['floating_monthly_payment'], 0, ',', '.')); ?></span>
            </div>
            <div class="custom-kpr-simulator__detail-item">
                <span><?php esc_html_e('Tahun berikutnya setelah masa fix berakhir', 'custom-plugin'); ?></span>
                <span><?php echo esc_html('Rp' . number_format((float) $initial_result['floating_monthly_payment'], 0, ',', '.')); ?></span>
            </div>
        </div>

        <div class="custom-kpr-simulator__detail-card">
            <div class="custom-kpr-simulator__detail-head">
                <span><?php esc_html_e('Estimasi Pembayaran Pertama', 'custom-plugin'); ?></span>
                <span data-kpr-first-total><?php echo esc_html('Rp' . number_format((float) $initial_result['first_payment_total'], 0, ',', '.')); ?></span>
            </div>
            <div class="custom-kpr-simulator__detail-item">
                <span><?php esc_html_e('Uang Muka', 'custom-plugin'); ?></span>
                <span data-kpr-down-payment-output><?php echo esc_html('Rp' . number_format((float) $initial_result['down_payment_amount'], 0, ',', '.')); ?></span>
            </div>
            <div class="custom-kpr-simulator__detail-item">
                <span><?php esc_html_e('Angsuran Pertama', 'custom-plugin'); ?></span>
                <span data-kpr-first-installment-output><?php echo esc_html('Rp' . number_format((float) $initial_result['first_installment'], 0, ',', '.')); ?></span>
            </div>
            <div class="custom-kpr-simulator__detail-item">
                <span><?php esc_html_e('Estimasi Biaya Lainnya', 'custom-plugin'); ?></span>
                <span data-kpr-other-costs-output><?php echo esc_html('Rp' . number_format((float) $initial_result['other_costs'], 0, ',', '.')); ?></span>
            </div>
        </div>

        <div class="custom-kpr-simulator__detail-card">
            <div class="custom-kpr-simulator__detail-head">
                <span><?php esc_html_e('Detail Pinjaman', 'custom-plugin'); ?></span>
                <span data-kpr-total-loan-cost><?php echo esc_html('Rp' . number_format((float) $initial_result['total_loan_cost'], 0, ',', '.')); ?></span>
            </div>
            <div class="custom-kpr-simulator__detail-item">
                <span><?php esc_html_e('Pinjaman Pokok', 'custom-plugin'); ?></span>
                <span data-kpr-principal-output><?php echo esc_html('Rp' . number_format((float) $initial_result['principal'], 0, ',', '.')); ?></span>
            </div>
            <div class="custom-kpr-simulator__detail-item">
                <span><?php esc_html_e('Estimasi Bunga Pinjaman', 'custom-plugin'); ?></span>
                <span data-kpr-interest-output><?php echo esc_html('Rp' . number_format((float) $initial_result['total_interest'], 0, ',', '.')); ?></span>
            </div>
        </div>

        <p class="custom-kpr-simulator__disclaimer">
            <?php esc_html_e('Disclaimer: Hasil di atas merupakan angka estimasi. Perhitungan dapat berubah sesuai suku bunga bank, tenor, dan biaya tambahan yang berlaku.', 'custom-plugin'); ?>
        </p>
    </div>
</div>
