<?php

namespace ToretZasilkovna\Toret\Library;

trait SettingsFieldsTrait
{
    private static function renderField(array $field): void
    {
        $method_name = 'add_' . $field['type'];
        $callback = [self::class, $method_name];

        if (is_callable($callback)) {
            call_user_func($callback, $field, 'div', false);
        }
    }

    private static function get_button_classes(string $type = 'primary'): string
    {
        $base = 'inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium rounded-lg shadow-sm focus:outline-none transition-colors duration-200';

        if ($type === 'primary') {
            return $base . ' text-white bg-toret-600 border border-transparent hover:bg-toret-700';
        } else {
            return $base . ' text-gray-700 bg-white border border-gray-300 hover:bg-gray-50';
        }
    }

    private static function parse_common_field_args(array $field, string $default_type = 'text'): array
    {
        $defaults = [
                'placeholder' => '',
                'class' => '',
                'style' => '',
                'wrapper_class' => '',
                'value' => $field['default'] ?? null,
                'name' => $field['id'] ?? '',
                'id' => $field['id'] ?? uniqid('toret_field_'),
                'type' => $default_type,
                'description' => '',
                'required' => false,
                'label' => '',
                'custom_attributes' => [],
        ];

        if (isset($field['value'])) {
            $defaults['value'] = $field['value'];
        }

        $field = wp_parse_args($field, $defaults);

        if (empty($field['id'])) {
            $field['id'] = !empty($field['name']) ? sanitize_key($field['name']) : uniqid('toret_field_');
        }
        if (empty($field['name'])) {
            $field['name'] = $field['id'];
        }

        return $field;
    }

    private static function _get_field_opening_html(array $field, string $location): string
    {
        $is_full_width = (strpos($field['wrapper_class'] ?? '', 'full-width-field') !== false);

        if (isset($field['divider']) && $field['divider'] === 'none') {
            $divider_class = 'border-b-0 pb-0 mb-0';
        } else {
            $has_divider = $field['divider'] ?? false;
            $divider_class = $has_divider
                    ? 'pb-6 mb-6 border-b border-gray-300'
                    : 'pb-3 mb-3 border-b border-gray-200';
        }

        $wrapper_classes_from_field = $field['wrapper_class'] ?? '';
        if (!empty($field['class'])) {
            $wrapper_classes_from_field .= ' ' . $field['class'] . '-wrapper';
        }

        $wrapper_attributes = '';
        if (isset($field['wrapper_attributes']) && is_array($field['wrapper_attributes'])) {
            $wrapper_attributes = self::build_custom_attributes_html($field['wrapper_attributes']);
        }

        $output = '<div ' . $wrapper_attributes . ' class="py-5 grid grid-cols-1 md:grid-cols-3 gap-4 ' . $divider_class . ' border-gray-200 last:border-b-0 ' . esc_attr($wrapper_classes_from_field) . '">';

        if ($is_full_width) {
            $output .= '<div class="md:col-span-3">';
            if (!empty($field['label'])) {
                $output .= '<label for="' . esc_attr($field['id']) . '" class="block text-sm font-semibold text-gray-800 mb-1">' . wp_kses_post($field['label']) . '</label>';
            }
            if (!empty($field['help'])) {
                $output .= '<p class="text-xs text-gray-500 mb-2">' . wp_kses_post($field['help']) . '</p>';
            }
        } else {
            $output .= '<div class="md:col-span-1">';
            if (!empty($field['label'])) {
                $output .= '<label for="' . esc_attr($field['id']) . '" class="block text-sm font-medium text-gray-900">' . wp_kses_post($field['label']) . '</label>';
            }
            if (!empty($field['help'])) {
                $output .= '<p class="mt-1 text-xs text-gray-500">' . wp_kses_post($field['help']) . '</p>';
            }
            $output .= '</div>';
            $output .= '<div class="md:col-span-2">';
        }

        return $output;
    }

    private static function _get_field_closing_html(array $field, string $location): string
    {
        return '</div></div>';
    }

    private static function build_custom_attributes_html(array $custom_attributes): string
    {
        $attributes_html = [];
        if (!empty($custom_attributes) && is_array($custom_attributes)) {
            foreach ($custom_attributes as $attribute => $value) {
                if (is_bool($value) && $value === true) {
                    $attributes_html[] = esc_attr($attribute);
                } elseif ($value !== null && $value !== false) {
                    $attributes_html[] = esc_attr($attribute) . '="' . esc_attr($value) . '"';
                }
            }
        }
        return implode(' ', $attributes_html);
    }

    private static function _output_field(string $html, bool $return): ?string
    {
        if ($return) {
            return $html;
        }
        echo $html;
        return null;
    }

    public static function add_header(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'header');
        $title_text = !empty($field['label']) ? $field['label'] : ($field['value'] ?? '');
        $field['label'] = '';

        $field_html = sprintf(
                '<h3 class="text-lg font-bold text-gray-800 mb-2">%s</h3>',
                esc_html($title_text)
        );

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    public static function add_hidden(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'hidden');
        $field_html = sprintf(
                '<input type="hidden" name="%s" id="%s" value="%s" />',
                esc_attr($field['name']),
                esc_attr($field['id']),
                esc_attr($field['value'])
        );
        return self::_output_field($field_html, $return);
    }

    public static function get_alert_field(string $section, string $sub_section, string $id, string $message, string $style = 'warning'): array
    {
        $styles = [
                'warning' => 'text-orange-800 bg-orange-50 border-orange-200',
                'info' => 'text-toret-800 bg-toret-50 border-toret-200',
                'error' => 'text-red-800 bg-red-50 border-red-200',
                'success' => 'text-green-800 bg-green-50 border-green-200',
                'purple' => 'text-purple-800 bg-purple-50 border-purple-200',
        ];

        $color_classes = $styles[$style] ?? $styles['warning'];

        $html = sprintf(
                '<div class="text-sm border p-3 rounded-md flex items-start %s">
            <span class="dashicons dashicons-info mr-2 mt-0.5" style="font-size: 18px;"></span>
            <div>%s</div>
         </div>',
                esc_attr($color_classes),
                $message
        );

        return [
                'section' => $section,
                'sub_section' => $sub_section,
                'type' => 'html',
                'id' => $id,
                'html' => $html,
                'position' => 'top',
                'wrapper_class' => 'full-width-field border-b-0 !pt-2 !pb-4 mb-0',
                'divider' => 'none',
        ];
    }

    public static function add_text(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'text');
        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);
        $tailwind_classes = "block w-full rounded-md border border-solid border-gray-300 shadow-sm focus:border-toret-500 focus:ring-toret-500 sm:text-sm";

        $field_html = sprintf(
                '<input type="%s" class="%s %s" style="%s" name="%s" id="%s" value="%s" placeholder="%s" %s %s />',
                esc_attr($field['type']),
                esc_attr($tailwind_classes),
                esc_attr($field['class']),
                esc_attr($field['style']),
                esc_attr($field['name']),
                esc_attr($field['id']),
                esc_attr($field['value']),
                esc_attr($field['placeholder']),
                $attributes_html,
                ($field['required'] ? 'required' : '')
        );

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    public static function add_number(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'text');
        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);
        $tailwind_classes = "block w-full rounded-md border border-solid border-gray-300 shadow-sm focus:border-toret-500 focus:ring-toret-500 sm:text-sm";

        $type = $field_args['type'] === 'number' ? 'number' : $field['type'];

        $field_html = sprintf(
                '<input type="%s" class="%s %s" style="%s" name="%s" id="%s" value="%s" placeholder="%s" %s %s />',
                esc_attr($type),
                esc_attr($tailwind_classes),
                esc_attr($field['class']),
                esc_attr($field['style']),
                esc_attr($field['name']),
                esc_attr($field['id']),
                esc_attr($field['value']),
                esc_attr($field['placeholder']),
                $attributes_html,
                ($field['required'] ? 'required' : '')
        );

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    public static function add_checkbox(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'checkbox');
        $field['cbvalue'] = $field_args['cbvalue'] ?? 'ok';
        $custom_attributes = $field['custom_attributes'] ?? [];
        if (!empty($field['style'])) {
            $custom_attributes['style'] = $field['style'];
        }
        $attributes_html = self::build_custom_attributes_html($custom_attributes);
        $tailwind_checkbox_classes = "h-4 w-4 rounded border-gray-300 text-toret-600 focus:ring-toret-500";

        $checkbox_html = sprintf(
                '<input type="checkbox" name="%s" id="%s" value="%s" class="%s %s" %s %s />',
                esc_attr($field['name']),
                esc_attr($field['id']),
                esc_attr($field['cbvalue']),
                esc_attr($tailwind_checkbox_classes),
                esc_attr($field['class']),
                checked($field['value'], $field['cbvalue'], false),
                $attributes_html
        );

        $checkbox_label = $field['checkbox_label'] ?? '';
        $label_for_checkbox_html = '';
        if (!empty($checkbox_label)) {
            $label_for_checkbox_html = sprintf(
                    '<label for="%s" class="ml-3 text-sm text-gray-700">%s</label>',
                    esc_attr($field['id']),
                    wp_kses_post($checkbox_label)
            );
        }

        $field_html = '<div class="flex items-center">' . $checkbox_html . $label_for_checkbox_html . '</div>';
        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    public static function add_select(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'select');
        $field['options'] = $field_args['options'] ?? [];
        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);
        $tailwind_classes = "block w-full rounded-md border border-solid border-gray-300 shadow-sm focus:border-toret-500 focus:ring-toret-500 sm:text-sm";

        ob_start();
        ?>
        <select style="<?php echo esc_attr($field['style']); ?>" name="<?php echo esc_attr($field['name']); ?>"
                id="<?php echo esc_attr($field['id']); ?>"
                class="<?php echo esc_attr($tailwind_classes); ?> <?php echo esc_attr($field['class']); ?>" <?php echo $attributes_html; ?> <?php echo($field['required'] ? 'required' : ''); ?>>
            <?php
            foreach ($field['options'] as $key => $value_text) {
                echo '<option value="' . esc_attr($key) . '"' . selected($field['value'], $key, false) . '>' . esc_html($value_text) . '</option>';
            }
            ?>
        </select>
        <?php
        $field_html = ob_get_clean();
        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    public static function add_multiselect(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'multiselect');
        $field['options'] = $field_args['options'] ?? [];

        $raw_name = $field_args['name'] ?? $field['id'];
        if (substr($raw_name, -2) !== '[]') {
            $field['name'] = $raw_name . '[]';
        } else {
            $field['name'] = $raw_name;
        }

        $field['value'] = (array)($field['value'] ?? []);

        $inline_style = $field['style'] ?? '';

        if (!empty($inline_style)) {
            $existing_style = $field['custom_attributes']['style'] ?? '';
            $field['custom_attributes']['style'] = trim($existing_style . ' ' . $inline_style);
        }

        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);

        $tailwind_classes = "block w-full rounded-md shadow-sm focus:border-toret-500 focus:ring-toret-500 sm:text-sm";

        ob_start();
        ?>

        <input type="hidden" name="<?php echo esc_attr($field['name']); ?>" value="" />

        <select x-data="multi"
                name="<?php echo esc_attr($field['name']); ?>"
                id="<?php echo esc_attr($field['id']); ?>"
                class="<?php echo esc_attr($tailwind_classes . ' ' . $field['class'] . ' toret-multiselect'); ?>"
                multiple
                <?php echo $attributes_html;?>
                <?php echo($field['required'] ? 'required' : ''); ?>>
            <?php
            foreach ($field['options'] as $key => $option_text) {
                $is_selected = in_array((string)$key, array_map('strval', $field['value']), true);
                echo '<option value="' . esc_attr($key) . '"' . selected($is_selected, true, false) . '>' . esc_html($option_text) . '</option>';
            }
            ?>
        </select>
        <?php
        $field_html = ob_get_clean();
        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    public static function add_html(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        return self::_output_field($field_args['html'] ?? '', $return);
    }

    public static function add_radio(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'radio');
        $field['options'] = $field_args['options'] ?? [];
        $tailwind_radio_classes = "h-4 w-4 border-gray-300 text-toret-600 focus:ring-toret-500";
        $tailwind_label_classes = "ml-3 block text-sm font-medium text-gray-700";

        ob_start();
        ?>
        <div class="space-y-2 <?php echo esc_attr($field['class']); ?>">
            <?php
            foreach ($field['options'] as $key => $option_text) {
                $radio_id = esc_attr($field['id'] . '_' . sanitize_key($key));
                ?>
                <div class="flex items-center">
                    <input type="radio"
                           id="<?php echo $radio_id; ?>"
                           name="<?php echo esc_attr($field['name']); ?>"
                           value="<?php echo esc_attr($key); ?>"
                           class="<?php echo esc_attr($tailwind_radio_classes); ?>"
                           style="<?php echo esc_attr($field['style']); ?>"
                            <?php checked($field['value'], $key); ?>
                            <?php echo($field['required'] && $key === array_key_first($field['options']) ? 'required' : ''); ?>
                    >
                    <label for="<?php echo $radio_id; ?>" class="<?php echo esc_attr($tailwind_label_classes); ?>">
                        <?php echo esc_html($option_text); ?>
                    </label>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
        $field_html = ob_get_clean();
        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    public static function add_note(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field_args['wrapper_class'] = ($field_args['wrapper_class'] ?? '') . ' md:col-span-3';
        $field = self::parse_common_field_args($field_args, 'note');

        $field_html = sprintf(
                '<div class="p-4 text-sm text-toret-800 bg-toret-50 border border-toret-200 rounded-lg %s" id="%s">%s</div>',
                esc_attr($field['class']),
                esc_attr($field['id']),
                wp_kses_post($field['message'])
        );

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    public static function add_text_with_button(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'text');
        $input_attributes = '';
        if (isset($field['custom_attributes']) && is_array($field['custom_attributes'])) {
            $input_attributes = self::build_custom_attributes_html($field['custom_attributes']);
        }
        $input_classes = "relative focus:z-10 block w-full rounded-none rounded-l-md border border-gray-300 px-3 py-2 shadow-sm focus:border-toret-500 focus:ring-2 focus:ring-toret-500 focus:ring-offset-0 focus:outline-none sm:text-sm";
        if (!empty($field['class'])) {
            $input_classes .= ' ' . $field['class'];
        }

        $button_defaults = [
                'text' => 'Action', 'type' => 'button', 'name' => '', 'id' => $field['id'] . '_btn', 'class' => '', 'custom_attributes' => [],
        ];
        $btn_data = wp_parse_args($field_args['button'] ?? [], $button_defaults);

        $btn_attributes = '';
        if (isset($btn_data['custom_attributes']) && is_array($btn_data['custom_attributes'])) {
            $btn_attributes = self::build_custom_attributes_html($btn_data['custom_attributes']);
        }

        $btn_classes = "-ml-px relative inline-flex items-center space-x-2 rounded-r-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:border-toret-500 focus:outline-none focus:ring-1 focus:ring-toret-500";
        if (!empty($btn_data['class'])) {
            $btn_classes .= ' ' . $btn_data['class'];
        }

        $input_html = sprintf(
                '<input type="%s" class="%s" style="%s" name="%s" id="%s" value="%s" placeholder="%s" %s %s />',
                esc_attr($field['type']), esc_attr($input_classes), esc_attr($field['style']), esc_attr($field['name']),
                esc_attr($field['id']), esc_attr($field['value']), esc_attr($field['placeholder']), $input_attributes,
                ($field['required'] ? 'required' : '')
        );

        $button_html = sprintf(
                '<button type="%s" name="%s" id="%s" class="%s" %s>%s</button>',
                esc_attr($btn_data['type']), esc_attr($btn_data['name']), esc_attr($btn_data['id']),
                esc_attr($btn_classes), $btn_attributes, esc_html($btn_data['text'])
        );

        $inner_html = '<div class="mt-1 flex rounded-md shadow-sm">' . $input_html . $button_html . '</div>';
        $final_html = self::_get_field_opening_html($field, $location) . $inner_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($final_html, $return);
    }

    public static function add_select_with_button(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'select');
        $field['options'] = $field_args['options'] ?? [];
        $select_attributes = '';
        if (isset($field['custom_attributes']) && is_array($field['custom_attributes'])) {
            $select_attributes = self::build_custom_attributes_html($field['custom_attributes']);
        }
        $select_classes = "relative focus:z-10 block w-full !max-w-none flex-1 rounded-none rounded-l-md border border-gray-300 shadow-sm focus:border-toret-500 focus:ring-toret-500 sm:text-sm";
        if (!empty($field['class'])) {
            $select_classes .= ' ' . $field['class'];
        }
        $button_defaults = ['text' => 'Action', 'type' => 'button', 'name' => '', 'id' => $field['id'] . '_btn', 'class' => '', 'custom_attributes' => []];
        $btn_data = wp_parse_args($field_args['button'] ?? [], $button_defaults);
        $btn_attributes = isset($btn_data['custom_attributes']) ? self::build_custom_attributes_html($btn_data['custom_attributes']) : '';
        $btn_classes = "-ml-px relative inline-flex items-center space-x-2 rounded-r-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:border-toret-500 focus:outline-none focus:ring-1 focus:ring-toret-500 " . ($btn_data['class'] ?? '');

        ob_start();
        foreach ($field['options'] as $key => $value_text) {
            echo '<option value="' . esc_attr($key) . '"' . selected($field['value'], $key, false) . '>' . esc_html($value_text) . '</option>';
        }
        $options_html = ob_get_clean();

        $select_html = sprintf(
                '<select name="%s" id="%s" class="%s" style="%s" %s %s>%s</select>',
                esc_attr($field['name']), esc_attr($field['id']), esc_attr($select_classes),
                esc_attr($field['style']), $select_attributes, ($field['required'] ? 'required' : ''), $options_html
        );
        $button_html = sprintf(
                '<button type="%s" name="%s" id="%s" class="%s" %s>%s</button>',
                esc_attr($btn_data['type']), esc_attr($btn_data['name']), esc_attr($btn_data['id']),
                esc_attr($btn_classes), $btn_attributes, esc_html($btn_data['text'])
        );

        $inner_html = '<div class="mt-1 flex rounded-md w-full">' . $select_html . $button_html . '</div>';
        $final_html = self::_get_field_opening_html($field, $location) . $inner_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($final_html, $return);
    }

    public static function add_textarea(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'textarea');
        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);
        $tailwind_classes = "block w-full rounded-md border border-solid shadow-sm focus:border-toret-500 focus:ring-toret-500 sm:text-sm";
        $rows = $field['custom_attributes']['rows'] ?? 4;

        $field_html = sprintf(
                '<textarea name="%s" id="%s" class="%s %s" style="%s" rows="%d" placeholder="%s" %s %s>%s</textarea>',
                esc_attr($field['name']), esc_attr($field['id']), esc_attr($tailwind_classes), esc_attr($field['class']),
                esc_attr($field['style']), absint($rows), esc_attr($field['placeholder']), $attributes_html,
                ($field['required'] ? 'required' : ''), esc_textarea($field['value'])
        );

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    public static function get_copy_alert_field(string $section, string $sub_section, string $id, string $label, string $value_to_copy, array $texts, string $style = 'info', bool $hide_section_button = false): array
    {
        $styles = [
                'warning' => 'text-orange-800 bg-orange-50 border-orange-200',
                'info' => 'text-toret-800 bg-toret-50 border-toret-200',
                'error' => 'text-red-800 bg-red-50 border-red-200',
                'success' => 'text-green-800 bg-green-50 border-green-200',
                'purple' => 'text-purple-800 bg-purple-50 border-purple-200',
        ];
        $color_classes = $styles[$style] ?? $styles['info'];
        $esc_label = wp_kses_post($label);
        $esc_value_html = esc_html($value_to_copy);
        $esc_value_js = esc_js($value_to_copy);
        $color_attr = esc_attr($color_classes);

        $html = <<<HTML
<div class="text-sm border p-3 rounded-md flex flex-wrap items-center justify-between gap-4 {$color_attr}" x-data="{ copied: false }">
    <div class="flex items-center overflow-hidden">
        <span class="dashicons dashicons-info mr-2 flex-shrink-0" style="font-size: 18px;"></span>
        <span class="mr-2 font-medium">{$esc_label}</span>
        <code class="bg-black/5 px-2 py-1 rounded select-all font-mono text-xs break-all border border-black/10">{$esc_value_html}</code>
    </div>
    
    <button type="button"
            @click="navigator.clipboard.writeText('{$esc_value_js}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
            class="flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 transition-all shadow-sm text-xs font-semibold whitespace-nowrap ml-auto cursor-pointer"
            :class="copied ? 'text-green-600 border-green-300' : 'text-gray-700'"
            title="Copy">
        <span x-show="!copied" class="dashicons dashicons-admin-page mr-1" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
        <span x-show="!copied">{$texts['copy']}</span>
        <span x-show="copied" class="dashicons dashicons-yes mr-1" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
        <span x-show="copied">{$texts['copied']}</span>
    </button>
</div>
HTML;
        return [
                'section' => $section,
                'sub_section' => $sub_section,
                'type' => 'html',
                'id' => $id,
                'html' => $html,
                'position' => 'top',
                'wrapper_class' => 'full-width-field border-b-0 !pt-2 !pb-4 mb-0',
                'divider' => 'none',
                'hide_save_button' => $hide_section_button,
        ];
    }

    public static function add_table(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $columns = $field_args['columns'] ?? [];
        $rows = $field_args['rows'] ?? [];
        $actions = $field_args['actions'] ?? [];
        $bordered = $field_args['bordered'] ?? true;

        $table_classes = 'min-w-full divide-y divide-toret-200 rounded-lg' . ($bordered ? ' border border-toret-300' : '');

        ob_start();
        ?>
        <div class="overflow-x-auto w-full rounded-lg shadow-sm">
            <table class="<?php echo esc_attr($table_classes); ?>">
                <thead class="bg-toret-50">
                <tr class="divide-toret-200">
                    <?php foreach ($columns as $col_key => $col_label): ?>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-toret-800 uppercase tracking-wider">
                            <?php echo esc_html($col_label); ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if (!empty($actions)): ?>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-toret-800 uppercase tracking-wider"><?php _e('Action'); ?></th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-toret-200">
                <?php foreach ($rows as $row): ?>
                    <tr class=" divide-toret-200 hover:bg-toret-200/30 transition-colors">
                        <?php foreach ($columns as $col_key => $col_label): ?>
                            <td class="px-6 py-4 whitespace-normal text-sm"><?php echo $row[$col_key] ?? ''; ?></td>
                        <?php endforeach; ?>

                        <?php if (!empty($actions)): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <?php foreach ($actions as $action): ?>
                                        <?php
                                        $url = is_callable($action['url_callback']) ? call_user_func($action['url_callback'], $row) : '#';
                                        $label = $action['label'] ?? __('Edit');
                                        $icon = $action['icon'] ?? 'dashicons-edit';
                                        ?>
                                        <a href="<?php echo esc_url($url); ?>"
                                           title="<?php echo esc_attr($label); ?>"
                                           class="inline-flex items-center justify-center w-9 h-9 rounded-[3px] border bg-toret-50 text-toret-600 hover:bg-white hover:border-toret-400 hover:text-toret-800 transition-all focus:outline-none">
                                            <span class="dashicons <?php echo esc_attr($icon); ?>" style="font-size: 18px; width: 18px; height: 18px;"></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        $table_html = ob_get_clean();
        $html = '<div class="w-full">' . $table_html . '</div>';
        return self::_output_field($html, $return);
    }

    public static function add_collapsible_checkboxes(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $title = $field_args['title'] ?? '';
        $items = $field_args['items'] ?? [];
        $type = $field_args['type'] ?? '';

        if($type == 'top'){
            $wrapper_classes = 'mb-0 rounded-t-md rounded-b-none border-b-0';
        }elseif($type == 'bottom'){
            $wrapper_classes = 'mb-4 rounded-b-md rounded-t-none';
        }elseif($type == 'middle'){
            $wrapper_classes = 'mb-0 rounded-t-none border-b-0';
        }elseif(!isset($field_args['wrapper_classes'])){
            $wrapper_classes = 'mb-4 rounded-md';
        }else{
            $wrapper_classes = $field_args['wrapper_classes'];
        }

        ob_start();
        ?>
        <div x-data="{ open: false }" class="bg-white border border-toret-colborder shadow-sm w-full <?php echo esc_attr($wrapper_classes); ?>">

            <button type="button" @click="open = !open"
                    class="w-full flex justify-between items-center px-4 py-2 bg-toret-colbg hover:bg-toret-colborder first:rounded-t-[inherit] last:rounded-b-[inherit] text-left transition-colors focus:outline-none !bg-toret-colbg !text-toret-coltext !border-0">

                <span class="text-toret-coltext !w-auto !float-none !m-0">
                    <?php echo esc_html($title); ?>
                </span>

                <span class="ml-2 transform transition-transform duration-200 text-toret-colicon"
                      :class="open ? 'rotate-180' : ''">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="toret-800">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <div x-show="open" x-collapse class="border-t border-toret-colborder p-2">
                <div class="grid grid-cols-2 gap-2 w-full">
                    <?php foreach ($items as $item): ?>
                        <label class="!flex items-center !justify-start !float-none !w-full !m-0 !p-2 cursor-pointer hover:bg-toret-colbg rounded transition-colors border border-transparent hover:border-toret-colborder">
                            <input type="checkbox" name="<?php echo esc_attr($item['name']); ?>" id="<?php echo esc_attr($item['id']); ?>"
                                   value="<?php echo esc_attr($item['value']); ?>"
                                   class="!h-4 !w-4 !min-w-[16px] !m-0 !mr-3 text-toret-colicon border-gray-300 rounded focus:ring-toret-colicon"
                                    <?php checked($item['checked'], true); ?>>
                            <span class="text-sm text-gray-700 select-none !w-auto leading-normal">
                            <?php echo esc_html($item['label']); ?>
                        </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        return self::_output_field($html, $return);
    }

    public static function add_button(array $field_args, string $location = 'div', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'button');

        $button_defaults = [
                'text'              => $field_args['button_text'] ?? 'Action',
                'type'              => $field_args['button_type'] ?? 'submit',
                'name'              => $field['name'],
                'id'                => $field['id'],
                'class'             => '',
                'custom_attributes' => [],
        ];

        $source_data = isset($field_args['button']) && is_array($field_args['button']) ? $field_args['button'] : [];
        $btn_data    = wp_parse_args($source_data, $button_defaults);

        $btn_attributes = '';
        if (isset($btn_data['custom_attributes']) && is_array($btn_data['custom_attributes'])) {
            $btn_attributes = self::build_custom_attributes_html($btn_data['custom_attributes']);
        }

        $btn_classes = "relative inline-flex items-center justify-center rounded-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-toret-500 focus:ring-offset-2";
        if (!empty($btn_data['class'])) {
            $btn_classes .= ' ' . $btn_data['class'];
        }

        $button_html = sprintf(
                '<button type="%s" name="%s" id="%s" class="%s" %s>%s</button>',
                esc_attr($btn_data['type']),
                esc_attr($btn_data['name']),
                esc_attr($btn_data['id']),
                esc_attr($btn_classes),
                $btn_attributes,
                esc_html($btn_data['text'])
        );

        $inner_html = '<div class="flex items-center">' . $button_html . '</div>';
        $final_html = self::_get_field_opening_html($field, $location) . $inner_html . self::_get_field_closing_html($field, $location);

        return self::_output_field($final_html, $return);
    }

    public static function add_dynamic_table(array $args, string $location = 'div', bool $return = true): ?string
    {
        $headers     = $args['headers'] ?? [];
        $columns     = $args['columns'];
        $rows        = $args['rows'] ?? [];
        $table_id    = $args['id'];
        $buttons     = $args['buttons'] ?? [];
        $field       = self::parse_common_field_args($args, 'dynamic_table');

        $table_wrapper_class = 'overflow-x-auto border border-gray-300 rounded-lg';
        $table_class         = 'min-w-full divide-y divide-gray-200';
        $thead_class         = 'bg-gray-50';
        $th_class            = 'px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider';
        $tbody_class         = 'bg-white divide-y divide-gray-200';

        $td_class            = 'px-4 py-2 align-middle';

        $input_class         = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-toret-500 focus:ring-toret-500 sm:text-sm min-h-[38px]';

        $renderInput = function($col, $value = null) use ($input_class) {
            $name = esc_attr($col['name']);

            switch ($col['type']) {
                case 'number':
                    $step = $col['step'] ?? '0.000001';
                    $min  = $col['min'] ?? '0';
                    return sprintf(
                            '<input type="number" step="%s" min="%s" name="%s" value="%s" class="%s">',
                            esc_attr($step),
                            esc_attr($min),
                            $name,
                            esc_attr($value ?? ''),
                            $input_class
                    );

                case 'text':
                    return sprintf(
                            '<input type="text" name="%s" value="%s" class="%s">',
                            $name,
                            esc_attr($value ?? ''),
                            $input_class
                    );

                case 'hidden':
                    return sprintf(
                            '<input type="hidden" name="%s" value="%s">',
                            $name,
                            esc_attr($value ?? '')
                    );

                case 'select':
                    $options = $col['options'] ?? [];
                    $html = "<select name=\"{$name}\" class=\"w-full block\">";
                    foreach ($options as $k => $label) {
                        $selected = ((string)$value === (string)$k) ? 'selected' : '';
                        $html .= '<option value="'.esc_attr($k).'" '.$selected.'>'.esc_html($label).'</option>';
                    }
                    $html .= '</select>';
                    return $html;
            }
            return '';
        };

        ob_start();
        ?>

        <div class="<?php echo $table_wrapper_class; ?>" id="<?php echo esc_attr($table_id); ?>">
            <table class="<?php echo $table_class; ?>">
                <thead class="<?php echo $thead_class; ?>">
                <tr>
                    <?php foreach ($headers as $th): ?>
                        <th class="<?php echo $th_class; ?>"><?php echo esc_html($th); ?></th>
                    <?php endforeach; ?>
                    <!-- Prázdný header pro delete button -->
                    <th class="<?php echo $th_class; ?>"></th>
                </tr>
                </thead>

                <tbody class="<?php echo $tbody_class; ?>">
                <template>
                    <tr x-data="dynamicRow">
                        <?php foreach ($columns as $col): ?>
                            <td class="<?php echo $td_class; ?>">
                                <?php echo $renderInput($col, null); ?>
                            </td>
                        <?php endforeach; ?>

                        <td class="<?php echo $td_class; ?> text-right whitespace-nowrap">
                            <button type="button"
                                    @click="$el.closest('tr').remove()"
                                    class="inline-flex items-center text-red-600 hover:text-red-900 text-sm font-medium transition-colors">
                                <?php echo esc_html($buttons['delete'] ?? 'Delete'); ?>
                            </button>
                        </td>
                    </tr>
                </template>

                <?php foreach ($rows as $row): ?>
                    <tr x-data="dynamicRow">
                        <?php foreach ($columns as $col): ?>
                            <td class="<?php echo $td_class; ?>">
                                <?php
                                $name = $col['name'];
                                $value = $row[$name] ?? null;
                                echo $renderInput($col, $value);
                                ?>
                            </td>
                        <?php endforeach; ?>

                        <td class="<?php echo $td_class; ?> text-right whitespace-nowrap">
                            <button type="button"
                                    @click="$el.closest('tr').remove()"
                                    class="inline-flex items-center text-red-600 hover:text-red-900 text-sm font-medium transition-colors">
                                <?php echo esc_html($buttons['delete'] ?? 'Delete'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <div class="mt-2">
            <button type="button"
                    @click="const tpl = document.getElementById('<?php echo esc_attr($table_id); ?>').querySelector('template'); document.getElementById('<?php echo esc_attr($table_id); ?>').querySelector('tbody').appendChild(tpl.content.cloneNode(true))"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-toret-700 bg-toret-100 hover:bg-toret-200 focus:outline-none transition-colors">
                + <?php echo esc_html($buttons['add'] ?? "Add row"); ?>
            </button>
        </div>

        <?php
        $html = ob_get_clean();

        $final_html = self::_get_field_opening_html($field, $location) . $html . self::_get_field_closing_html($field, $location);
        return self::_output_field($final_html, $return);
    }

    public static function output_wrapper_start(string $context = 'default'): void
    {
        $classes = ['product' => 'p-4 text-left', 'variation' => 'px-4 py-2', 'category' => 'max-w-3xl', 'default' => 'p-4'];
        $extra_class = $classes[$context] ?? $classes['default'];
        echo '<div class="toret-plugin-settings ' . esc_attr($extra_class) . '">';
    }

    public static function output_wrapper_end(): void
    {
        echo '</div>';
    }
}