<?php

/**
 * Frontend Template: Harga Paket
 *
 * @var int    $harga
 * @var int    $harga_coret
 * @var string $style
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="harga-paket-wrapper<?php echo $style === 'coret' ? ' harga-paket--coret' : ''; ?>">

    <?php if ($style === 'coret' && $harga_coret): ?>
    <span class="harga-paket__coret">
        Rp <?php echo esc_html(number_format($harga_coret, 0, ',', '.')); ?>
    </span>
    <?php endif; ?>

    <span class="harga-paket__harga">
        Rp <?php echo esc_html(number_format($harga, 0, ',', '.')); ?>
    </span>

</div>

<style>
.harga-paket-wrapper {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 10px;
}

.harga-paket__harga {
    font-weight: 700;
    color: #d32f2f;
    line-height: 1.2;
}

.harga-paket--coret .harga-paket__coret {
    color: #999;
    text-decoration: line-through;
    font-weight: 400;
}
</style>