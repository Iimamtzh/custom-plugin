<?php
/**
 * Template: Single Dokumen Viewer (PDF.js)
 *
 * Variables available:
 * @var \WP_Post $post    The dokumen post object
 * @var string  $pdf_url PDF file URL
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cp-dokumen-viewer-wrapper">

    <div class="cp-dokumen-viewer-header">
        <a href="javascript:history.back()" class="cp-dokumen-back-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Kembali
        </a>

        <h2 class="cp-dokumen-viewer-title"><?php echo esc_html($post->post_title); ?></h2>

        <?php
        $doc_cats = get_the_terms($post->ID, 'kategori_dokumen');
        if ($doc_cats && !is_wp_error($doc_cats)) :
            $cat_list = wp_list_pluck($doc_cats, 'name');
        ?>
            <div class="cp-dokumen-viewer-meta">
                <span class="cp-dokumen-viewer-cats">
                    <?php echo esc_html(implode(', ', $cat_list)); ?>
                </span>
                <span class="cp-dokumen-viewer-date">
                    Diunggah: <?php echo get_the_date('j F Y', $post->ID); ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if (!empty($post->post_content)) : ?>
            <div class="cp-dokumen-viewer-desc">
                <?php echo wp_kses_post(wpautop($post->post_content)); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($pdf_url) : ?>
        <div class="cp-dokumen-viewer-toolbar">
            <a href="<?php echo esc_url($pdf_url); ?>" download class="cp-viewer-btn cp-viewer-download">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Unduh PDF
            </a>
            <button type="button" class="cp-viewer-btn cp-viewer-fullscreen" onclick="cpToggleFullscreen()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <polyline points="9 21 3 21 3 15"></polyline>
                    <line x1="21" y1="3" x2="14" y2="10"></line>
                    <line x1="3" y1="21" x2="10" y2="14"></line>
                </svg>
                Fullscreen
            </button>
        </div>

        <div id="pdf-viewer-container" class="cp-dokumen-pdf-container">
            <canvas id="pdf-render-canvas"></canvas>
        </div>

        <div class="cp-dokumen-pdf-controls">
            <button type="button" id="pdf-prev" class="cp-pdf-nav-btn" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Sebelumnya
            </button>

            <span class="cp-pdf-page-info">
                Halaman <span id="pdf-page-num">1</span> / <span id="pdf-page-count">-</span>
            </span>

            <button type="button" id="pdf-next" class="cp-pdf-nav-btn">
                Selanjutnya
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script>
        (function() {
            var pdfUrl = <?php echo wp_json_encode($pdf_url); ?>;
            var pdfDoc = null;
            var pageNum = 1;
            var pageRendering = false;
            var pageNumPending = null;
            var scale = 1.0;

            var canvas = document.getElementById('pdf-render-canvas');
            var ctx = canvas.getContext('2d');
            var container = document.getElementById('pdf-viewer-container');

            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            function renderPage(num) {
                pageRendering = true;
                pdfDoc.getPage(num).then(function(page) {
                    var viewport = page.getViewport({scale: scale});

                    // Fit to container width
                    var containerWidth = container.clientWidth;
                    if (containerWidth > 0) {
                        var fitScale = (containerWidth - 32) / viewport.width;
                        viewport = page.getViewport({scale: fitScale});
                    }

                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    var renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };

                    var renderTask = page.render(renderContext);

                    renderTask.promise.then(function() {
                        pageRendering = false;
                        if (pageNumPending !== null) {
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                    });
                });

                document.getElementById('pdf-page-num').textContent = num;
                document.getElementById('pdf-prev').disabled = (num <= 1);
                document.getElementById('pdf-next').disabled = (num >= pdfDoc.numPages);
            }

            function queueRenderPage(num) {
                if (pageRendering) {
                    pageNumPending = num;
                } else {
                    renderPage(num);
                }
            }

            function onPrevPage() {
                if (pageNum <= 1) return;
                pageNum--;
                queueRenderPage(pageNum);
            }

            function onNextPage() {
                if (pageNum >= pdfDoc.numPages) return;
                pageNum++;
                queueRenderPage(pageNum);
            }

            pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
                pdfDoc = pdf;
                document.getElementById('pdf-page-count').textContent = pdf.numPages;
                document.getElementById('pdf-next').disabled = (pdf.numPages <= 1);
                renderPage(pageNum);
            }).catch(function(error) {
                console.error('Gagal memuat PDF:', error);
                document.getElementById('pdf-viewer-container').innerHTML =
                    '<div class="cp-dokumen-pdf-error">' +
                    '<p>Gagal memuat PDF. Silakan <a href="' + pdfUrl + '" download>unduh langsung</a>.</p>' +
                    '</div>';
            });

            document.getElementById('pdf-prev').addEventListener('click', onPrevPage);
            document.getElementById('pdf-next').addEventListener('click', onNextPage);

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') onPrevPage();
                if (e.key === 'ArrowRight') onNextPage();
            });
        })();

        function cpToggleFullscreen() {
            var el = document.getElementById('pdf-viewer-container');
            if (!document.fullscreenElement) {
                if (el.requestFullscreen) {
                    el.requestFullscreen();
                } else if (el.webkitRequestFullscreen) {
                    el.webkitRequestFullscreen();
                } else if (el.msRequestFullscreen) {
                    el.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        }
        </script>

    <?php else : ?>
        <div class="cp-dokumen-pdf-error">
            <p>Tidak ada file PDF yang tersedia untuk dokumen ini.</p>
        </div>
    <?php endif; ?>

</div>
