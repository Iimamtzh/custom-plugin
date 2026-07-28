<?php
/**
 * Template: Daftar Dokumen
 *
 * Variables available:
 * @var \WP_Query $query          The documents query
 * @var array     $categories     Array of category terms
 * @var string    $current_kat    Currently active category slug
 * @var string    $current_search Current search keyword
 * @var string    $show_filter    'yes' or 'no'
 * @var string    $show_search    'yes' or 'no'
 * @var int       $paged          Current page number
 */

if (!defined('ABSPATH')) {
    exit;
}

// Determine current page URL (without existing query params for dokumen)
$current_url = get_permalink();
if (!$current_url) {
    $current_url = home_url($_SERVER['REQUEST_URI']);
}
?>

<div class="cp-dokumen-wrapper">

    <?php if ($show_search === 'yes' || $show_filter === 'yes') : ?>
        <div class="cp-dokumen-toolbar">
            <?php if ($show_search === 'yes') : ?>
                <form method="get" action="<?php echo esc_url($current_url); ?>" class="cp-dokumen-search">
                    <input
                        type="text"
                        name="cari_dokumen"
                        placeholder="Cari dokumen..."
                        value="<?php echo esc_attr($current_search); ?>"
                    />
                    <button type="submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                    <?php if (!empty($current_search)) : ?>
                        <a href="<?php echo esc_url($current_url); ?>" class="cp-dokumen-search-clear" title="Reset pencarian">&times;</a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>

            <?php if ($show_filter === 'yes' && !empty($categories)) : ?>
                <div class="cp-dokumen-filter">
                    <a
                        href="<?php echo esc_url($current_url); ?>"
                        class="cp-filter-btn <?php echo empty($current_kat) ? 'active' : ''; ?>"
                    >
                        Semua
                    </a>
                    <?php foreach ($categories as $cat) : ?>
                        <a
                            href="<?php echo esc_url(add_query_arg('kat_dokumen', $cat->slug, $current_url)); ?>"
                            class="cp-filter-btn <?php echo $current_kat === $cat->slug ? 'active' : ''; ?>"
                        >
                            <?php echo esc_html($cat->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($query->have_posts()) : ?>
        <div class="cp-dokumen-grid">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                $pdf_id  = get_post_meta(get_the_ID(), '_dokumen_pdf_id', true);
                $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';

                // Get categories for this document
                $doc_cats = get_the_terms(get_the_ID(), 'kategori_dokumen');
                $cat_names = array();
                if ($doc_cats && !is_wp_error($doc_cats)) {
                    $cat_names = wp_list_pluck($doc_cats, 'name');
                }
                ?>
                <div class="cp-dokumen-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="cp-dokumen-card-thumb">
                            <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
                        </div>
                    <?php else : ?>
                        <div class="cp-dokumen-card-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d63638" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                    <?php endif; ?>

                    <div class="cp-dokumen-card-body">
                        <h3 class="cp-dokumen-card-title">
                            <?php if ($pdf_url) : ?>
                                <a href="<?php echo esc_url($pdf_url); ?>" target="_blank" rel="noopener">
                                    <?php the_title(); ?>
                                </a>
                            <?php else : ?>
                                <?php the_title(); ?>
                            <?php endif; ?>
                        </h3>

                        <?php if (!empty($cat_names)) : ?>
                            <div class="cp-dokumen-card-cats">
                                <?php foreach ($cat_names as $cat_name) : ?>
                                    <span class="cp-dokumen-card-cat"><?php echo esc_html($cat_name); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (get_the_excerpt()) : ?>
                            <p class="cp-dokumen-card-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 15)); ?></p>
                        <?php endif; ?>

                        <div class="cp-dokumen-card-footer">
                            <span class="cp-dokumen-card-date">
                                <?php echo get_the_date('j M Y'); ?>
                            </span>
                            <?php if ($pdf_url) : ?>
                                <a href="<?php echo esc_url($pdf_url); ?>" target="_blank" rel="noopener" class="cp-dokumen-card-btn">
                                    Baca
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php
        // Pagination
        $big = 999999999;
        $paginate_links = paginate_links(array(
            'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format'    => '?halaman=%#%',
            'current'   => max(1, $paged),
            'total'     => $query->max_num_pages,
            'prev_text' => '&laquo; Sebelumnya',
            'next_text' => 'Selanjutnya &raquo;',
            'type'      => 'plain',
        ));

        if ($paginate_links) : ?>
            <div class="cp-dokumen-pagination">
                <?php echo $paginate_links; ?>
            </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>

    <?php else : ?>
        <div class="cp-dokumen-empty">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="9" y1="15" x2="15" y2="15"></line>
            </svg>
            <p>Tidak ada dokumen ditemukan.</p>
            <?php if (!empty($current_search) || !empty($current_kat)) : ?>
                <a href="<?php echo esc_url($current_url); ?>" class="cp-dokumen-reset-btn">Reset Filter</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
