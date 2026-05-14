<?php

namespace ToretZasilkovna\Toret\Library;

class Draw extends LibraryManager
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts_and_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts_and_styles'));
    }

    function isLoadEnabled(): bool
    {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        $disabledPlugins = ['toret-plugins_page_toret-eu-vat', 'toret-plugins_page_toret-ceskaposta', 'toret-plugins_page_toret-ceskaposta-export'];
        $isDisabled = false;

        if (in_array($screen_id, $disabledPlugins)) {
            $isDisabled = true;
        }

        return !$isDisabled;
    }

    public function enqueue_admin_scripts_and_styles(): void
    {
        if(is_admin() && $this->isLoadEnabled()) {
            $this->load_styles_and_scripts(true);
        }
    }

    public function enqueue_frontend_scripts_and_styles(): void
    {
        $this->load_styles_and_scripts(false);
    }

    private function load_styles_and_scripts(bool $is_admin)
    {
        $plugin_dir_url = plugin_dir_url(__FILE__);
        $plugin_dir_path = plugin_dir_path(__FILE__);

        $version = filemtime($plugin_dir_path . 'assets/css/toret-draw.css');

        $file = $plugin_dir_url . 'assets/css/toret-draw.css';
        wp_enqueue_style(self::TORET_LIBRARY_PREFIX . '-draw-css', $file, array(),$version);

        $file = $plugin_dir_url . 'assets/css/toret-draw-form.css';
        wp_enqueue_style(self::TORET_LIBRARY_PREFIX . '-form-css', $file, array(), $version);

        $file = $plugin_dir_url . 'assets/js/toret-draw.js';
        wp_enqueue_script(self::TORET_LIBRARY_PREFIX . '-js', $file, array('jquery'),$version, true);

        wp_localize_script(self::TORET_LIBRARY_PREFIX . '-js', 'ToretZasilkovna', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'plugin' => 'ToretZasilkovna',
        ));
    }

    /**
     * Handles the output of a given HTML string.
     * Either echoes it or returns it based on the $return parameter.
     *
     * @param string $html The HTML to output.
     * @param bool $return If true, return the HTML. If false, echo it.
     * @return string|null Null if echoed, string if returned.
     */
    private static function _output_field(string $html, bool $return): ?string
    {
        if ($return) {
            return $html;
        }
        echo $html;
        return null;
    }

    /**
     * @param array $field
     * @param string $default_type
     * @return array
     */
    private static function parse_common_field_args(array $field, string $default_type = 'text'): array
    {
        $field_type_class = self::TORET_LIBRARY_PREFIX . 'field-type-' . str_replace('_', '-', $default_type);
        $base_input_class = self::TORET_LIBRARY_PREFIX . 'input';

        $defaults = [
                'placeholder' => '',
                'class' => 'short',
                'style' => '',
                'wrapper_class' => '',
                'value' => $field['default'] ?? null,
                'name' => $field['id'] ?? '',
                'id' => $field['id'] ?? uniqid(self::TORET_LIBRARY_PREFIX . 'field_'),
                'type' => $default_type,
                'desc_tip' => false,
                'required' => false,
                'label' => '',
                'description' => '',
                'custom_attributes' => [],
                'label_position' => 'before',
                '_toret_draw_base_input_class' => $base_input_class,
                '_toret_draw_field_type_class' => $field_type_class,
                'divider' => $field['divider'] ?? false,
        ];

        if (isset($field['value'])) {
            $defaults['value'] = $field['value'];
        }

        $field = wp_parse_args($field, $defaults);

        if (empty($field['id'])) {
            $field['id'] = !empty($field['name']) ? sanitize_key($field['name']) : uniqid(self::TORET_LIBRARY_PREFIX . 'field_');
        }
        if (empty($field['name'])) {
            $field['name'] = $field['id'];
        }

        $field['wrapper_class'] = trim(self::TORET_LIBRARY_PREFIX . 'field-wrapper ' . $field['_toret_draw_field_type_class'] . '-wrapper ' . $field['wrapper_class']);
        $field['class'] = trim($field['_toret_draw_base_input_class'] . ' ' . $field['_toret_draw_field_type_class'] . ' ' . $field['class']);

        return $field;
    }

    /**
     * @param array $field
     * @param string $location
     * @return string
     */
    private static function _get_field_opening_html(array $field, string $location): string
    {
        $output = '';
        $label_html = '';
        if (!empty($field['label']) && $field['type'] !== 'button_link') {
            $label_html = '<label class="' . self::TORET_LIBRARY_PREFIX . 'label" for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . ($field['required'] ? '<span class="' . self::TORET_LIBRARY_PREFIX . 'required">*</span>' : '') . '</label>';
        }

        $tooltip_html = (!empty($field['description']) && false !== $field['desc_tip']) ? self::add_help_tip($field['description'], true) : '';

        $base_wrapper_class = self::TORET_LIBRARY_PREFIX . 'field';
        $location_wrapper_class = self::TORET_LIBRARY_PREFIX . $location . '-layout-item';
        $setting_divider = ($field['divider'] ? 'setting-divider' : '');
        $wrapper_classes = esc_attr($base_wrapper_class . ' ' . $location_wrapper_class . ' ' . $field['wrapper_class'] . ' ' . $setting_divider);

        if ($location === 'table') {
            $output .= '<tr class="' . $wrapper_classes . '" id="' . esc_attr($field['id']) . '_row">';
            $output .= '<th scope="row" class="' . self::TORET_LIBRARY_PREFIX . 'table-header titledesc">';
            if ($label_html) $output .= $label_html;
            if ($tooltip_html) $output .= $tooltip_html;
            $output .= '</th>';

            $output .= '<td class="' . self::TORET_LIBRARY_PREFIX . 'table-cell forminp forminp-' . esc_attr(str_replace('_', '-', $field['type'])) . '">';
        } else { // div, p
            $output .= '<div class="' . $wrapper_classes . ' ' . esc_attr($field['id']) . '_field" id="' . esc_attr($field['id']) . '_wrapper">';
            if ($label_html) $output .= $label_html;
            if ($tooltip_html) $output .= $tooltip_html;
        }
        return $output;
    }

    public static function getWrapperClasses($location)
    {
        $base_wrapper_class = self::TORET_LIBRARY_PREFIX . 'field';
        $location_wrapper_class = self::TORET_LIBRARY_PREFIX . $location . '-layout-item';
        return esc_attr($base_wrapper_class . ' ' . $location_wrapper_class);
    }

    private static function _get_field_closing_html(array $field, string $location): string
    {
        $output = '';
        if (!empty($field['description']) && false === $field['desc_tip']) {
            $output .= '<br><span class="' . self::TORET_LIBRARY_PREFIX . 'description description">' . wp_kses_post($field['description']) . '</span>';
        }

        if ($location === 'table') {
            $output .= '</td></tr>';
        } else { // div, p
            $output .= '</div>';
        }
        return $output;
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

    /**
     * Generate Text Input Field.
     * @param array $field_args
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_text(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'text');
        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);

        $field_html = sprintf(
                '<input type="%s" class="%s" style="%s" name="%s" id="%s" value="%s" placeholder="%s" %s %s />',
                esc_attr($field['type']),
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

    /**
     * Generate Hidden Input Field.
     * @param array $field_args
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_hidden(array $field_args, bool $return = true): ?string
    {
        $base_input_class = self::TORET_LIBRARY_PREFIX . 'input';
        $field_type_class = self::TORET_LIBRARY_PREFIX . 'field-type-hidden';

        $field = wp_parse_args($field_args, [
                'value' => $field_args['default'] ?? null,
                'class' => '',
                'id' => $field_args['id'] ?? uniqid(self::TORET_LIBRARY_PREFIX . 'hidden_field_'),
                'name' => $field_args['name'] ?? $field_args['id'] ?? uniqid(self::TORET_LIBRARY_PREFIX . 'hidden_field_name_')
        ]);
        if (isset($field_args['value'])) {
            $field['value'] = $field_args['value'];
        }
        if (empty($field['name'])) {
            $field['name'] = $field['id'];
        }
        $field['class'] = trim($base_input_class . ' ' . $field_type_class . ' ' . $field['class']);

        $html = sprintf(
                '<input type="hidden" class="%s" name="%s" id="%s" value="%s" />',
                esc_attr($field['class']),
                esc_attr($field['name']),
                esc_attr($field['id']),
                esc_attr($field['value'])
        );
        return self::_output_field($html, $return);
    }

    /**
     * Generate Textarea Field.
     * @param array $field_args
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_textarea(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'textarea');
        $field['rows'] = $field_args['rows'] ?? 2;
        $field['cols'] = $field_args['cols'] ?? 20;
        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);

        $field_html = sprintf(
                '<textarea class="%s" style="%s" name="%s" id="%s" placeholder="%s" rows="%s" cols="%s" %s %s>%s</textarea>',
                esc_attr($field['class']),
                esc_attr($field['style']),
                esc_attr($field['name']),
                esc_attr($field['id']),
                esc_attr($field['placeholder']),
                esc_attr($field['rows']),
                esc_attr($field['cols']),
                $attributes_html,
                ($field['required'] ? 'required' : ''),
                esc_textarea($field['value'])
        );

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    /**
     * Generate Checkbox Field.
     * @param array $field_args
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_checkbox(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        if (isset($field_args['rightlabel']) && $field_args['rightlabel'] === true) {
            $field_args['label_position'] = 'input_first';
        }
        $field_args['label_position'] = $field_args['label_position'] ?? 'input_first';

        $field = self::parse_common_field_args($field_args, 'checkbox');
        $field['class'] = str_replace('short', '', $field['class']);
        if (strpos($field['class'], self::TORET_LIBRARY_PREFIX . 'input-checkbox') === false) {
            $field['class'] = trim(self::TORET_LIBRARY_PREFIX . 'input-checkbox ' . $field['class']);
        }

        $field['cbvalue'] = $field_args['cbvalue'] ?? 'yes';
        $field['checked_value'] = $field_args['checked_value'] ?? $field['cbvalue'];
        $field['unchecked_value'] = $field_args['unchecked_value'] ?? null;
        $custom_attributes = $field['custom_attributes'];
        if (!empty($field['style'])) $custom_attributes['style'] = $field['style'];

        $field_html = '';
        if (!is_null($field['unchecked_value'])) {
            $field_html .= sprintf('<input type="hidden" name="%s" value="%s" class="%s" />',
                    esc_attr($field['name']),
                    esc_attr($field['unchecked_value']),
                    esc_attr(self::TORET_LIBRARY_PREFIX . 'hidden-for-checkbox')
            );
        }

        $checkbox_html = sprintf(
                '<input type="checkbox" name="%s" id="%s" value="%s" class="%s" %s %s />',
                esc_attr($field['name']),
                esc_attr($field['id']),
                esc_attr($field['checked_value']),
                esc_attr($field['class']),
                checked($field['value'], $field['checked_value'], false),
                self::build_custom_attributes_html($custom_attributes)
        );

        $label_for_checkbox_html = '';
        // If a description is provided and it's not intended as a tooltip,
        // use it as the label next to the checkbox.
        if (!empty($field['description']) && false === $field['desc_tip']) {
            $label_for_checkbox_html = sprintf(
                    '<label for="%s" class="%s %slabel">%s</label>',
                    esc_attr($field['id']),
                    self::TORET_LIBRARY_PREFIX . 'label',
                    ($field['label_position'] === 'input_first' ? self::TORET_LIBRARY_PREFIX . 'label-after ' : self::TORET_LIBRARY_PREFIX . 'label-before '),
                    wp_kses_post($field['description'])
            );

            // Prevent this description from being displayed again by _get_field_closing_html.
            $field['description'] = '';
        }

        if ($field['label_position'] === 'input_first') {
            $field_html .= $checkbox_html . ($label_for_checkbox_html ? ' ' . $label_for_checkbox_html : '');
        } elseif ($field['label_position'] === 'before_input') {
            $field_html .= ($label_for_checkbox_html ? $label_for_checkbox_html . ' ' : '') . $checkbox_html;
        } else {
            $field_html .= $checkbox_html;
        }

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    /**
     * Generate Select/Dropdown Field.
     * @param array $field_args
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_select(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'select');
        $field['options'] = $field_args['options'] ?? [];
        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);

        ob_start();
        ?>
        <select style="<?php echo esc_attr($field['style']); ?>" name="<?php echo esc_attr($field['name']); ?>"
                id="<?php echo esc_attr($field['id']); ?>"
                class="<?php echo esc_attr($field['class']); ?>" <?php echo $attributes_html; ?> <?php echo($field['required'] ? 'required' : ''); ?>>
            <?php
            if (!empty($field['placeholder']) && is_string($field['placeholder']) && !array_key_exists('', $field['options']) && !array_key_exists(null, $field['options'])) {
                echo '<option value="" disabled' . (empty($field['value']) ? ' selected' : '') . '>' . esc_html($field['placeholder']) . '</option>';
            }
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

    /**
     * Generate Multiselect Field.
     * @param array $field_args
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_multiselect(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'multiselect');
        $field['options'] = $field_args['options'] ?? [];
        $field['name'] = rtrim(($field_args['name'] ?? $field['id']), '[]') . '[]';
        if (!is_array($field['value'])) {
            $field['value'] = (array)($field['value'] ?? []);
        }
        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);

        ob_start();
        ?>
        <select style="<?php echo esc_attr($field['style']); ?>" name="<?php echo esc_attr($field['name']); ?>"
                id="<?php echo esc_attr($field['id']); ?>"
                class="<?php echo esc_attr($field['class']); ?>" <?php echo $attributes_html; ?>
                multiple="multiple" <?php echo($field['required'] ? 'required' : ''); ?>>
            <?php
            foreach ($field['options'] as $key => $value_option) {
                $is_selected = in_array((string)$key, array_map('strval', $field['value']), true) ? ' selected="selected"' : '';
                echo '<option value="' . esc_attr($key) . '"' . $is_selected . '>' . esc_html($value_option) . '</option>';
            }
            ?>
        </select>
        <?php
        $field_html = ob_get_clean();

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    /**
     * Generate Radio Button Group.
     * @param array $field_args
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_radio(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'radio');
        $field['options'] = $field_args['options'] ?? [];
        $radio_input_class = self::TORET_LIBRARY_PREFIX . 'input-radio';
        if (strpos($field['class'], 'short') !== false) {
            $field['class'] = str_replace('short', '', $field['class']);
        }
        $user_defined_class_for_input = $field['class'];

        ob_start();
        ?>
        <fieldset
                class="<?php echo self::TORET_LIBRARY_PREFIX; ?>fieldset <?php echo self::TORET_LIBRARY_PREFIX; ?>radio-group <?php echo esc_attr($field['id']); ?>_fieldset">
            <legend class="screen-reader-text"><span><?php echo esc_html($field['label']); ?></span></legend>
            <ul class="<?php echo self::TORET_LIBRARY_PREFIX; ?>radio-list wc-radios">
                <?php
                foreach ($field['options'] as $key => $value_option) {
                    $radio_id = esc_attr($field['id'] . '_' . sanitize_key($key));
                    printf(
                            '<li class="%1$sradio-list-item"><label for="%2$s"><input name="%3$s" value="%4$s" type="radio" class="%5$s %6$s" style="%7$s" id="%2$s" %8$s %9$s/> %10$s</label></li>',
                            self::TORET_LIBRARY_PREFIX,
                            $radio_id,
                            esc_attr($field['name']),
                            esc_attr($key),
                            esc_attr($radio_input_class),
                            esc_attr(str_replace(self::TORET_LIBRARY_PREFIX . 'field-type-radio', '', $user_defined_class_for_input)),
                            esc_attr($field['style']),
                            checked($field['value'], $key, false),
                            ($field['required'] && $key === array_key_first($field['options']) ? 'required' : ''),
                            esc_html($value_option)
                    );
                }
                ?>
            </ul>
        </fieldset>
        <?php
        $field_html = ob_get_clean();

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    /**
     * Generate a Note or informational text block.
     * @param array $field_args
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_note(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        $note_defaults = [
                'id' => $field_args['id'] ?? uniqid(self::TORET_LIBRARY_PREFIX . 'note_'),
                'label' => '',
                'message' => '',
                'wrapper_class' => '',
                'label_aria_label' => '',
                'type' => 'note'
        ];
        $field = wp_parse_args($field_args, $note_defaults);

        $opening_html = '';
        $closing_html = '';

        $field['wrapper_class'] = trim(self::TORET_LIBRARY_PREFIX . 'field-wrapper ' . self::TORET_LIBRARY_PREFIX . 'note-wrapper ' . $field['wrapper_class']);
        $base_wrapper_class = self::TORET_LIBRARY_PREFIX . 'field';
        $location_wrapper_class = self::TORET_LIBRARY_PREFIX . $location . '-layout-item';
        $wrapper_classes = esc_attr($base_wrapper_class . ' ' . $location_wrapper_class . ' ' . $field['wrapper_class']);

        $label_html = '';
        if (!empty($field['label'])) {
            $label_html = '<span id="' . esc_attr($field['id']) . '-label" class="' . self::TORET_LIBRARY_PREFIX . 'label" ' .
                    (!empty($field['label_aria_label']) ? 'aria-label="' . esc_attr($field['label_aria_label']) . '"' : '') .
                    '>' . esc_html($field['label']) . '</span>';
        }

        if ($location === 'table') {
            $opening_html = '<tr class="' . $wrapper_classes . '" id="' . esc_attr($field['id']) . '_row">' .
                    '<th scope="row" class="' . self::TORET_LIBRARY_PREFIX . 'table-header titledesc">' . $label_html . '</th>' .
                    '<td class="' . self::TORET_LIBRARY_PREFIX . 'table-cell forminp forminp-note">';
            $closing_html = '</td></tr>';
        } else {
            $opening_html = '<div class="' . $wrapper_classes . ' ' . esc_attr($field['id']) . '_field" id="' . esc_attr($field['id']) . '_wrapper">' . $label_html;
            $closing_html = '</div>';
        }

        $field_html = sprintf(
                '<div class="%1$snote-content" id="%2$s">%3$s</div>',
                self::TORET_LIBRARY_PREFIX,
                esc_attr($field['id']),
                wp_kses_post($field['message'])
        );

        $html = $opening_html . $field_html . $closing_html;
        return self::_output_field($html, $return);
    }

    /**
     * Generate a Submit, Button or Reset button.
     * @param array $field_args
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_submit(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        $submit_defaults = [
                'label' => '',
                'default' => '',
                'type' => 'submit',
                'class' => 'button button-primary ' . self::TORET_LIBRARY_PREFIX . 'button-submit',
                'name' => 'submit',
                'desc_tip' => false,
                'description' => '',
        ];

        $field_args_with_defaults = wp_parse_args($field_args, $submit_defaults);
        $field = self::parse_common_field_args($field_args_with_defaults, $field_args_with_defaults['type']);

        $button_base_classes = '';
        if ($field['type'] === 'submit' && (strpos($field_args_with_defaults['class'], 'button-primary') !== false)) {
            $button_base_classes = 'button button-primary';
        } elseif (strpos($field_args_with_defaults['class'], 'button') !== false) {
            $button_base_classes = 'button';
            if (strpos($field_args_with_defaults['class'], 'button-secondary') !== false) $button_base_classes = 'button button-secondary';
        }

        $non_button_toret_classes = [self::TORET_LIBRARY_PREFIX . 'input', self::TORET_LIBRARY_PREFIX . 'field-type-submit', self::TORET_LIBRARY_PREFIX . 'field-type-button', self::TORET_LIBRARY_PREFIX . 'field-type-reset', 'short'];
        $user_class = str_replace($non_button_toret_classes, '', $field['class']);
        $user_class = trim(str_replace(explode(' ', $button_base_classes), '', $user_class));
        $final_button_classes = trim($button_base_classes . ' ' . self::TORET_LIBRARY_PREFIX . 'button ' . self::TORET_LIBRARY_PREFIX . 'button-' . $field['type'] . ' ' . $user_class);

        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);

        $field_html = sprintf(
                '<button type="%s" class="%s" style="%s" name="%s" id="%s" value="%s" %s %s>%s</button>',
                esc_attr($field['type']),
                esc_attr($final_button_classes),
                esc_attr($field['style']),
                esc_attr($field['name']),
                esc_attr($field['id']),
                esc_attr($field['value']),
                $attributes_html,
                ($field['required'] ? 'required' : ''),
                esc_html($field['value'])
        );

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    /**
     * Generate Text Input Field with an adjacent Button.
     * @param array $field_args Arguments for the text input.
     * @param array $button_args Arguments for the button.
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_text_with_button(array $field_args, array $button_args, string $location = 'table', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'text');
        $attributes_html = self::build_custom_attributes_html($field['custom_attributes']);

        $input_html = sprintf(
                '<input type="%s" class="%s" style="%s" name="%s" id="%s" value="%s" placeholder="%s" %s %s />',
                esc_attr($field['type']),
                esc_attr($field['class']),
                esc_attr($field['style']),
                esc_attr($field['name']),
                esc_attr($field['id']),
                esc_attr($field['value']),
                esc_attr($field['placeholder']),
                $attributes_html,
                ($field['required'] ? 'required' : '')
        );

        $button_defaults = [
                'text' => __('Select', 'toret-draw'),
                'class' => 'button button-secondary',
                'style' => '',
                'custom_attributes' => [],
                'id' => '',
                'type' => 'button' // Může být 'button', 'submit', 'reset'
        ];
        $button = wp_parse_args($button_args, $button_defaults);
        $button_attributes_html = self::build_custom_attributes_html($button['custom_attributes']);

        $button_html = sprintf(
                '<button type="%s" class="%s" style="%s" %s %s>%s</button>',
                esc_attr($button['type']),
                esc_attr($button['class']),
                esc_attr($button['style']),
                $button['id'] ? 'id="' . esc_attr($button['id']) . '"' : '',
                $button_attributes_html,
                esc_html($button['text'])
        );

        $field['wrapper_class'] .= ' ' . self::TORET_LIBRARY_PREFIX . 'input-group';
        if ($location === 'table') {
            $opening_html = self::_get_field_opening_html($field, $location);
            $opening_html = str_replace('<td class="', '<td style="display: flex; align-items: center; gap: 5px;" class="', $opening_html);
        } else {
            $opening_html = self::_get_field_opening_html($field, $location);
        }

        $combined_html = $input_html . ' ' . $button_html;

        $html = $opening_html . $combined_html . self::_get_field_closing_html($field, $location);

        return self::_output_field($html, $return);
    }

    /**
     * Output a settings box
     */
    function draw_settings_box($content, $title, $save = true, $attributes = [], $noTable = false, $type = 'box', $return = false)
    {
        $type_map = [
                'box' => self::TORET_LIBRARY_SETTINGS_PREFIX . 'settings-box',
                'section' => self::TORET_LIBRARY_SETTINGS_PREFIX . 'settings-section-box',
                'sub_section' => self::TORET_LIBRARY_SETTINGS_PREFIX . 'settings-subsection-box'
        ];

        $base_class = $type_map[$type] ?? $type_map['box'];

        $wrap_class = $attributes['wrap_class'] ?? [];
        $body_class = $attributes['body_class'] ?? [];
        $header_class = $attributes['header_class'] ?? [];
        $footer_class = $attributes['footer_class'] ?? [];
        $button_class = $attributes['button_class'] ?? [];
        $title_class = $attributes['title_class'] ?? [];
        $table_class = $attributes['table_class'] ?? [];
        $title_tag = $attributes['title_tag'] ?? 'h3';
        $button_text = $attributes['button_text'] ?? '';
        // HTML výstup
        $html = '<div class="' . $base_class . '-wrap ' . implode(' ', $wrap_class) . '">
                <div class="' . self::TORET_LIBRARY_SETTINGS_PREFIX . 'settings-box-header ' . $base_class . '-header ' . implode(' ', $header_class) . '">
                    <' . $title_tag . ' class="' . $base_class . '-title ' . implode(' ', $title_class) . '">' . $title . '</' . $title_tag . '>
                </div>
                <div class="' . $base_class . '-body ' . implode(' ', $body_class) . '">';

        if ($noTable) {
            $html .= $content;
        } else {
            $html .= '<table class="form-table ' . $base_class . '-table ' . implode(' ', $table_class) . '">
                    ' . $content . '
                  </table>';
        }

        $html .= '</div>';

        if ($save) {
            $html .= '<div class="' . $base_class . '-footer ' . implode(' ', $footer_class) . '">
                    <input type="submit" 
                           class="button button-primary toret-save-btn ' . implode(' ', $button_class) . '"
                           value="' . $button_text . '"/>
                  </div>';
        }

        $html .= '</div>';

        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * Generate an anchor (<a>) tag styled as a button.
     * @param array $field_args Arguments for the link.
     *   - 'label': (string) Text to display on the button.
     *   - 'href': (string) The URL for the link.
     *   - 'id': (string, optional) HTML ID for the button link.
     *   - 'class': (string, optional) HTML class for the button link. Defaults to 'button button-secondary'.
     *   - 'style': (string, optional) Inline CSS styles.
     *   - 'custom_attributes': (array, optional) Additional custom HTML attributes.
     *   - 'target': (string, optional) The target attribute for the link (e.g., '_blank').
     * @param string $location
     * @param bool $return True to return as string, false to echo.
     * @return string|null
     */
    public static function add_button_link(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        $link_defaults = [
                'href' => '#',
                'id' => uniqid(self::TORET_LIBRARY_PREFIX . 'button_link_'),
                'style' => '',
                'custom_attributes' => [],
                'target' => '_self',
                'description' => '',
                'desc_tip' => false,
                'wrapper_class' => '',
        ];


        $field = self::parse_common_field_args($field_args, 'button_link');
        $field = wp_parse_args($field, $link_defaults);

        $final_button_classes = 'button toret-secondary';

        $custom_attributes = $field['custom_attributes'];
        if (!empty($field['target'])) {
            $custom_attributes['target'] = $field['target'];
        }
        $attributes_html = self::build_custom_attributes_html($custom_attributes);

        $field_html = sprintf(
                '<a href="%s" id="%s" target="_blank" class="%s" style="%s" %s>%s</a>',
                esc_url($field['href']),
                esc_attr($field['id']),
                esc_attr($final_button_classes),
                esc_attr($field['style']),
                $attributes_html,
                esc_html($field['label'])
        );

        $html = self::_get_field_opening_html($field, $location) . $field_html . self::_get_field_closing_html($field, $location);
        return self::_output_field($html, $return);
    }

    /**
     * @param array $field_args
     * @param string $location
     * @param bool $return
     * @return string|null
     */
    public static function add_custom(array $field_args, string $location = 'table', bool $return = true): ?string
    {
        $field = self::parse_common_field_args($field_args, 'custom');
        $custom_html = isset($field['html']) ? ($field['html']) : '';

        $html = self::_get_field_opening_html($field, $location)
                . $custom_html
                . self::_get_field_closing_html($field, $location);

        return self::_output_field($html, $return);
    }

    /**
     * @param $url
     * @param bool $return
     * @return string|void
     */
    public function add_copy_link($url, bool $return = true)
    {
        $html = '<span class="dashicons dashicons-admin-page toret-copy-link" data-link="' . $url . '"></span>';

        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * @param $tip
     * @param bool $allow_html
     * @return string
     */
    static function add_help_tip($tip, bool $allow_html = false): string
    {
        if ($allow_html) {
            $sanitized_tip = self::sanitize_tooltip($tip);
        } else {
            $sanitized_tip = esc_attr($tip);
        }

        $aria_label = wp_strip_all_tags($tip);

        return '<span class="toret-draw-help-tip" tabindex="0" aria-label="' . esc_attr($aria_label) . '" data-tip="' . $sanitized_tip . '"></span>';
    }

    /**
     * @param $var
     * @return string
     */
    static function sanitize_tooltip($var): string
    {
        return htmlspecialchars(
                wp_kses(
                        html_entity_decode($var ?? ''),
                        array(
                                'br' => array(),
                                'em' => array(),
                                'strong' => array(),
                                'small' => array(),
                                'span' => array(),
                                'ul' => array(),
                                'li' => array(),
                                'ol' => array(),
                                'p' => array(),
                        )
                )
        );
    }

    /**
     * @param array $raw_attributes
     * @return string
     */
    static function implode_html_attributes(array $raw_attributes): string
    {
        $attributes = array();
        foreach ($raw_attributes as $name => $value) {
            $attributes[] = esc_attr($name) . '="' . esc_attr($value) . '"';
        }
        return implode(' ', $attributes);
    }

    /**
     * @param $value
     * @param $options
     * @return string
     */
    static function selected($value, $options): string
    {
        if (is_array($options)) {
            $options = array_map('strval', $options);
            return selected(in_array((string)$value, $options, true), true, false);
        }

        return selected($value, $options, false);
    }

    /**
     * @param array $args
     * @return string|void
     */
    static function add_popup(array $args = []) {
        $defaults = [
                'id' => 'torlib-popup',
                'title' => 'Popup titulek',
                'content' => '',
                'buttons' => [],
                'show_close' => true,
                'auto_open' => false,
                'class' => '',
                'return' => false,
        ];

        $args = wp_parse_args($args, $defaults);

        $buttons_html = '';
        if (!empty($args['buttons'])) {
            foreach ($args['buttons'] as $btn) {
                $btn_id = $btn['id'] ?? '';
                $btn_class = $btn['class'] ?? 'torlib-popup-btn';
                $btn_text = $btn['text'] ?? 'OK';
                $btn_type = $btn['type'] ?? 'button';
                $buttons_html .= "<button type='{$btn_type}' id='{$btn_id}' class='{$btn_class}'>{$btn_text}</button>";
            }
        }

        $auto_open_class = $args['auto_open'] ? 'torlib-popup-auto-open' : '';

        $html = "<div class='torlib-popup-overlay {$auto_open_class}' id='{$args['id']}-overlay'>
            <div class='torlib-popup {$args['class']}'>
                " . ($args['show_close'] ? "<span class='torlib-popup-close'>&times;</span>" : "") . "
                <h2 class='torlib-popup-title'>{$args['title']}</h2>
                <div class='torlib-popup-content'>{$args['content']}</div>
                <div class='torlib-popup-buttons'>{$buttons_html}</div>
            </div>
        </div>";

        if($args['return']) {
            return $html;
        }else {
            echo $html;
        }
    }
}