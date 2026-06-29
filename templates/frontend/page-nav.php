<?php
/**
 * Frontend Template: Page Navigation
 *
 * @var string $title
 * @var array  $menu_items
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="custom-page-nav">
    <div class="custom-page-nav__header">
        <h3 class="custom-page-nav__title"><?php echo esc_html($title); ?></h3>
    </div>
    <ul class="custom-page-nav__list">
        <?php foreach ($menu_items as $item): ?>
            <?php echo custom_page_nav_render_item($item); ?>
        <?php endforeach; ?>
    </ul>
</div>

<?php
/**
 * Render menu item recursively.
 */
function custom_page_nav_render_item($item) {
    $active_class = $item['active'] ? 'custom-page-nav__item--active' : '';
    $has_child    = !empty($item['children']) ? 'custom-page-nav__item--has-child' : '';
    $open_class   = ($item['active'] && !empty($item['children'])) ? 'custom-page-nav__children--open' : '';

    ob_start();
    ?>
    <li class="custom-page-nav__item <?php echo esc_attr($active_class . ' ' . $has_child); ?>">
        <a href="<?php echo esc_url($item['permalink']); ?>" class="custom-page-nav__link">
            <span class="custom-page-nav__icon">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 4h12M2 8h12M2 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="custom-page-nav__label"><?php echo esc_html($item['title']); ?></span>
        </a>
        <?php if (!empty($item['children'])): ?>
            <ul class="custom-page-nav__children <?php echo esc_attr($open_class); ?>">
                <?php foreach ($item['children'] as $child): ?>
                    <?php echo custom_page_nav_render_item($child); ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </li>
    <?php
    return ob_get_clean();
}
