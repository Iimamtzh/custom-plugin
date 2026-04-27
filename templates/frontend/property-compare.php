<?php

/**
 * Frontend Template: Property Compare
 *
 * @var array $properties
 * @var array $selected_ids
 * @var array $compare_inputs
 * @var int $current_property_id
 * @var array $compare_rows
 */

if (!defined('ABSPATH')) {
    exit;
}

$selected_ids = isset($selected_ids) && is_array($selected_ids) ? $selected_ids : array();
$compare_inputs = isset($compare_inputs) && is_array($compare_inputs) ? $compare_inputs : array();
$current_property_id = isset($current_property_id) ? (int) $current_property_id : 0;
$selection_map = array(
    1 => isset($compare_inputs[1]) ? (int) $compare_inputs[1] : 0,
    2 => isset($compare_inputs[2]) ? (int) $compare_inputs[2] : 0,
    3 => isset($compare_inputs[3]) ? (int) $compare_inputs[3] : 0,
);
$required_count = $current_property_id ? 2 : 2;
$filled_inputs = array_filter($selection_map);
$has_duplicate_selection = count($filled_inputs) !== count(array_unique($filled_inputs));
?>

<div class="custom-property-compare">
    <style>
        .custom-property-compare {
            margin: 24px 0;
        }

        .custom-property-compare__form {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
            align-items: end;
            margin-bottom: 24px;
        }

        .custom-property-compare__field label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .custom-property-compare__field select,
        .custom-property-compare__field-input {
            width: 100%;
            min-height: 42px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
        }

        .custom-property-compare__field-input {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #111827;
            background: #f8fafc;
        }

        .custom-property-compare__button {
            min-height: 42px;
            padding: 8px 18px;
            border: 0;
            border-radius: 10px;
            background: #1f2937;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }

        .custom-property-compare__notice {
            padding: 14px 16px;
            border-left: 4px solid #1f2937;
            background: #f8fafc;
        }

        .custom-property-compare__table-wrap {
            overflow-x: auto;
        }

        .custom-property-compare__table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-property-compare__table th,
        .custom-property-compare__table td {
            padding: 14px;
            border: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        .custom-property-compare__table th {
            background: #f8fafc;
            font-weight: 700;
        }

        .custom-property-compare__label {
            min-width: 180px;
        }

        .custom-property-compare__image {
            display: block;
            width: 100%;
            max-width: 260px;
            height: auto;
            border-radius: 12px;
        }

        @media (max-width: 720px) {
            .custom-property-compare__form {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <?php if (empty($properties)) : ?>
        <div class="custom-property-compare__notice">
            <?php esc_html_e('Belum ada properti yang tersedia untuk dibandingkan.', 'custom-plugin'); ?>
        </div>
    <?php else : ?>
        <form class="custom-property-compare__form" method="get" action="<?php echo esc_url(get_permalink()); ?>">
            <div class="custom-property-compare__field">
                <label for="compare_property_1"><?php esc_html_e('Properti Pertama', 'custom-plugin'); ?></label>
                <?php if ($current_property_id) : ?>
                    <div class="custom-property-compare__field-input"><?php echo esc_html(get_the_title($current_property_id)); ?></div>
                    <input type="hidden" id="compare_property_1" name="compare_property_1" value="<?php echo esc_attr($current_property_id); ?>">
                <?php else : ?>
                    <select id="compare_property_1" name="compare_property_1">
                        <option value=""><?php esc_html_e('Pilih properti', 'custom-plugin'); ?></option>
                        <?php foreach ($properties as $property) : ?>
                            <option value="<?php echo esc_attr($property->ID); ?>" <?php selected($selection_map[1], $property->ID); ?>>
                                <?php echo esc_html(get_the_title($property)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="custom-property-compare__field">
                <label for="compare_property_2"><?php esc_html_e('Properti Kedua', 'custom-plugin'); ?></label>
                <select id="compare_property_2" name="compare_property_2">
                    <option value=""><?php esc_html_e('Pilih properti', 'custom-plugin'); ?></option>
                    <?php foreach ($properties as $property) : ?>
                        <?php if ($current_property_id && (int) $property->ID === $current_property_id) : ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <option value="<?php echo esc_attr($property->ID); ?>" <?php selected($selection_map[2], $property->ID); ?>>
                            <?php echo esc_html(get_the_title($property)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="custom-property-compare__field">
                <label for="compare_property_3"><?php esc_html_e('Properti Ketiga', 'custom-plugin'); ?></label>
                <select id="compare_property_3" name="compare_property_3">
                    <option value=""><?php esc_html_e('Opsional', 'custom-plugin'); ?></option>
                    <?php foreach ($properties as $property) : ?>
                        <?php if ($current_property_id && (int) $property->ID === $current_property_id) : ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <option value="<?php echo esc_attr($property->ID); ?>" <?php selected($selection_map[3], $property->ID); ?>>
                            <?php echo esc_html(get_the_title($property)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="custom-property-compare__button" type="submit">
                <?php esc_html_e('Bandingkan', 'custom-plugin'); ?>
            </button>
        </form>

        <?php if ($has_duplicate_selection) : ?>
            <div class="custom-property-compare__notice">
                <?php esc_html_e('Pilih properti yang berbeda untuk setiap kolom perbandingan.', 'custom-plugin'); ?>
            </div>
        <?php elseif (count($selected_ids) >= $required_count && !empty($compare_rows)) : ?>
            <div class="custom-property-compare__table-wrap">
                <table class="custom-property-compare__table">
                    <thead>
                        <tr>
                            <th class="custom-property-compare__label"><?php esc_html_e('Detail', 'custom-plugin'); ?></th>
                            <?php foreach ($selected_ids as $selected_id) : ?>
                                <th>
                                    <a href="<?php echo esc_url(get_permalink($selected_id)); ?>">
                                        <?php echo esc_html(get_the_title($selected_id)); ?>
                                    </a>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compare_rows as $row) : ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($row['label']); ?></th>
                                <?php foreach ($row['values'] as $value) : ?>
                                    <td><?php echo wp_kses_post($value); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (!empty(array_filter($selection_map))) : ?>
            <div class="custom-property-compare__notice">
                <?php if ($current_property_id) : ?>
                    <?php esc_html_e('Pilih minimal satu properti tambahan untuk melihat perbandingan.', 'custom-plugin'); ?>
                <?php else : ?>
                    <?php esc_html_e('Pilih minimal dua properti untuk melihat perbandingan.', 'custom-plugin'); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
