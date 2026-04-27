<?php

if (!defined('ABSPATH')) {
    exit;
}

$modal_id = $gallery_id . '-modal';
$encoded_items = wp_json_encode(array_map(function ($item) {
    return array(
        'fullUrl' => $item['full_url'],
        'alt'     => $item['alt'],
        'caption' => $item['caption'],
    );
}, $items));
?>

<div class="custom-property-gallery" id="<?php echo esc_attr($gallery_id); ?>">
    <div class="custom-property-gallery__primary">
        <?php if (!empty($preview_items[0])) : ?>
            <button
                type="button"
                class="custom-property-gallery__card"
                data-property-gallery-trigger
                data-property-gallery-target="<?php echo esc_attr($modal_id); ?>"
                data-index="0"
                aria-label="<?php echo esc_attr(sprintf('Lihat galeri %s', $post_title)); ?>">
                <img src="<?php echo esc_url($preview_items[0]['preview_url']); ?>" alt="<?php echo esc_attr($preview_items[0]['alt']); ?>">
                <?php if ($total_images > 1) : ?>
                    <span class="custom-property-gallery__count"><?php echo esc_html($total_images); ?> foto</span>
                <?php endif; ?>
            </button>
        <?php endif; ?>
    </div>

    <?php if (count($preview_items) > 1) : ?>
        <div class="custom-property-gallery__secondary">
            <?php foreach (array_slice($preview_items, 1) as $index => $item) : ?>
                <?php $actual_index = $index + 1; ?>
                <button
                    type="button"
                    class="custom-property-gallery__card"
                    data-property-gallery-trigger
                    data-property-gallery-target="<?php echo esc_attr($modal_id); ?>"
                    data-index="<?php echo esc_attr($actual_index); ?>"
                    aria-label="<?php echo esc_attr(sprintf('Lihat foto %d dari %s', $actual_index + 1, $post_title)); ?>">
                    <img src="<?php echo esc_url($item['preview_url']); ?>" alt="<?php echo esc_attr($item['alt']); ?>">
                    <?php if ($actual_index === count($preview_items) - 1 && $total_images > count($preview_items)) : ?>
                        <span class="custom-property-gallery__overlay">See All Image</span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div
    class="custom-property-gallery__modal"
    id="<?php echo esc_attr($modal_id); ?>"
    data-property-gallery-modal
    data-current-index="0"
    data-items="<?php echo esc_attr($encoded_items); ?>"
    hidden>
    <div class="custom-property-gallery__dialog" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr($post_title); ?>">
        <div class="custom-property-gallery__viewport">
            <div class="custom-property-gallery__toolbar">
                <div class="custom-property-gallery__index" data-property-gallery-counter>1 / <?php echo esc_html($total_images); ?></div>
                <button type="button" class="custom-property-gallery__close" data-property-gallery-close aria-label="Tutup galeri">&times;</button>
            </div>

            <?php if ($total_images > 1) : ?>
                <button type="button" class="custom-property-gallery__nav custom-property-gallery__nav--prev" data-property-gallery-nav="prev" aria-label="Foto sebelumnya">&#8249;</button>
                <button type="button" class="custom-property-gallery__nav custom-property-gallery__nav--next" data-property-gallery-nav="next" aria-label="Foto berikutnya">&#8250;</button>
            <?php endif; ?>

            <img src="<?php echo esc_url($items[0]['full_url']); ?>" alt="<?php echo esc_attr($items[0]['alt']); ?>" data-property-gallery-image>
        </div>

        <div class="custom-property-gallery__meta">
            <h3 class="custom-property-gallery__title"><?php echo esc_html($post_title); ?></h3>
            <p class="custom-property-gallery__caption" data-property-gallery-caption<?php echo empty($items[0]['caption']) ? ' hidden' : ''; ?>><?php echo esc_html($items[0]['caption']); ?></p>
        </div>

        <?php if ($total_images > 1) : ?>
            <div class="custom-property-gallery__thumbs">
                <?php foreach ($items as $index => $item) : ?>
                    <button
                        type="button"
                        class="custom-property-gallery__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
                        data-property-gallery-thumb
                        data-index="<?php echo esc_attr($index); ?>"
                        aria-label="<?php echo esc_attr(sprintf('Pilih foto %d', $index + 1)); ?>">
                        <img src="<?php echo esc_url($item['thumbnail_url']); ?>" alt="<?php echo esc_attr($item['alt']); ?>">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>