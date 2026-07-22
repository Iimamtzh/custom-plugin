<?php
/**
 * Frontend Template: Jadwal Keberangkatan
 *
 * @var array  $rows
 * @var string $kategori_label
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="jadwal-keberangkatan-wrapper">

    <?php if ($kategori_label): ?>
        <h3 class="jadwal-kategori-title">
            <?php echo esc_html(sprintf(__('Jadwal Keberangkatan: %s', 'custom-plugin'), $kategori_label)); ?>
        </h3>
    <?php endif; ?>

    <div class="jadwal-table-responsive">
        <table class="jadwal-keberangkatan-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Paket', 'custom-plugin'); ?></th>
                    <th><?php esc_html_e('Berangkat', 'custom-plugin'); ?></th>
                    <th><?php esc_html_e('Pulang', 'custom-plugin'); ?></th>
                    <th><?php esc_html_e('Durasi', 'custom-plugin'); ?></th>
                    <th><?php esc_html_e('Maskapai', 'custom-plugin'); ?></th>
                    <th><?php esc_html_e('Harga', 'custom-plugin'); ?></th>
                    <th><?php esc_html_e('Status', 'custom-plugin'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td data-label="<?php esc_attr_e('Paket', 'custom-plugin'); ?>">
                            <a href="<?php echo esc_url($row['url']); ?>">
                                <?php echo esc_html($row['paket']); ?>
                            </a>
                        </td>
                        <td data-label="<?php esc_attr_e('Berangkat', 'custom-plugin'); ?>">
                            <?php echo esc_html($row['tanggal']); ?>
                        </td>
                        <td data-label="<?php esc_attr_e('Pulang', 'custom-plugin'); ?>">
                            <?php echo esc_html($row['pulang']); ?>
                        </td>
                        <td data-label="<?php esc_attr_e('Durasi', 'custom-plugin'); ?>">
                            <?php echo esc_html($row['durasi']); ?>
                        </td>
                        <td data-label="<?php esc_attr_e('Maskapai', 'custom-plugin'); ?>">
                            <?php echo esc_html($row['maskapai']); ?>
                        </td>
                        <td data-label="<?php esc_attr_e('Harga', 'custom-plugin'); ?>">
                            <?php if ($row['harga_coret']): ?>
                                <span class="harga-coret"><?php echo esc_html($row['harga_coret']); ?></span>
                            <?php endif; ?>
                            <span class="harga"><?php echo esc_html($row['harga']); ?></span>
                        </td>
                        <td data-label="<?php esc_attr_e('Status', 'custom-plugin'); ?>">
                            <?php echo $row['tersedia']; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.jadwal-keberangkatan-wrapper {
    margin: 20px 0;
}

.jadwal-kategori-title {
    margin-bottom: 16px;
    font-size: 1.25em;
}

.jadwal-table-responsive {
    overflow-x: auto;
}

.jadwal-keberangkatan-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    background: #fff;
}

.jadwal-keberangkatan-table thead th {
    background: #f5f5f5;
    padding: 12px 10px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #ddd;
    white-space: nowrap;
}

.jadwal-keberangkatan-table tbody td {
    padding: 12px 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.jadwal-keberangkatan-table tbody tr:hover {
    background: #fafafa;
}

.jadwal-keberangkatan-table .harga-coret {
    text-decoration: line-through;
    color: #999;
    font-size: 0.9em;
    margin-right: 8px;
}

.jadwal-keberangkatan-table .harga {
    color: #d32f2f;
    font-weight: 600;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.badge-tersedia {
    background: #e8f5e9;
    color: #2e7d32;
}

.badge-habis {
    background: #ffebee;
    color: #c62828;
}

@media (max-width: 768px) {
    .jadwal-keberangkatan-table thead {
        display: none;
    }

    .jadwal-keberangkatan-table tbody td {
        display: block;
        text-align: right;
        padding: 8px 10px;
    }

    .jadwal-keberangkatan-table tbody td::before {
        content: attr(data-label);
        float: left;
        font-weight: 600;
        color: #555;
    }

    .jadwal-keberangkatan-table tbody tr {
        display: block;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
}
</style>
