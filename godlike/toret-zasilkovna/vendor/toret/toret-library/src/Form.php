<?php

namespace ToretZasilkovna\Toret\Library;

class Form extends Draw
{
    private string $form_id;
    private string $action;
    private string $method;
    private string $layout_type; // 'table' or 'div'
    private string $enctype = '';
    private array $form_attributes = [];
    private array $form_elements = [];
    private array $group_tag_stack = [];

    /**
     * Constructor for ToretForm.
     *
     * @param string $id ID of the form.
     * @param string $action Form action URL. Defaults to current page.
     * @param string $method Form method ('post' or 'get'). Defaults to 'post'.
     * @param string $layout_type Main layout for fields ('table' or 'div'). Defaults to 'table'.
     */
    public function __construct(string $id, string $action = '', string $method = 'post', string $layout_type = 'table')
    {
        parent::__construct();

        $this->form_id = sanitize_key($id);
        $this->action = empty($action) ? esc_url($_SERVER['REQUEST_URI']) : esc_url($action);
        $this->method = in_array(strtolower($method), ['post', 'get']) ? strtolower($method) : 'post';
        $this->layout_type = in_array($layout_type, ['table', 'div']) ? $layout_type : 'table';

        // Base attributes for the form tag
        $this->form_attributes['id'] = $this->form_id;
        $this->form_attributes['class'] = Draw::TORET_LIBRARY_PREFIX . 'form ' . Draw::TORET_LIBRARY_PREFIX . 'form-layout-' . $this->layout_type;
    }

    /**
     * Set the enctype for the form (e.g., 'multipart/form-data').
     */
    public function set_enctype(string $enctype): self
    {
        $this->enctype = $enctype;
        return $this;
    }

    /**
     * Add a custom attribute to the <form> tag.
     */
    public function add_form_attribute(string $key, string $value): self
    {
        $this->form_attributes[sanitize_key($key)] = $value;
        return $this;
    }

    /**
     * Add a CSS class to the <form> tag.
     */
    public function add_form_class(string $class_name): self
    {
        $existing_classes = isset($this->form_attributes['class']) ? $this->form_attributes['class'] : '';
        $this->form_attributes['class'] = trim($existing_classes . ' ' . sanitize_html_class($class_name));
        return $this;
    }

    /**
     * Helper to add a field element to the form.
     *
     * @param string $toret_draw_method The static method name from Draw (e.g., 'add_text').
     * @param array $args Arguments for the Draw method.
     */
    private function add_form_element(string $toret_draw_method, array $args): void
    {
        $this->form_elements[] = [
            'type' => 'field',
            'draw_method' => $toret_draw_method,
            'args' => $args,
        ];
    }

    // --- Methods for adding specific field types ---

    public function add_text_field(array $args): self
    {
        $this->add_form_element('add_text', $args);
        return $this;
    }

    public function add_textarea_field(array $args): self
    {
        $this->add_form_element('add_textarea', $args);
        return $this;
    }

    public function add_select_field(array $args): self
    {
        $this->add_form_element('add_select', $args);
        return $this;
    }

    public function add_multiselect_field(array $args): self
    {
        $this->add_form_element('add_multiselect', $args);
        return $this;
    }

    public function add_checkbox_field(array $args): self
    {
        $this->add_form_element('add_checkbox', $args);
        return $this;
    }

    public function add_radio_field(array $args): self
    {
        $this->add_form_element('add_radio', $args);
        return $this;
    }

    public function add_hidden_field(array $args): self
    {
        $this->add_form_element('add_hidden', $args);
        return $this;
    }

    public function add_note_field(array $args): self
    {
        $this->add_form_element('add_note', $args);
        return $this;
    }

    public function add_submit_button(array $args): self
    {
        $this->add_form_element('add_submit', $args);
        return $this;
    }

    /**
     * Start a group of fields.
     *
     * @param string $title Optional title for the group.
     * @param string $group_tag HTML tag for the group wrapper ('fieldset' or 'div'). Defaults to 'fieldset'.
     * @param string $group_class Custom CSS classes for the group wrapper.
     * @param string $group_style Inline CSS styles for the group wrapper.
     */
    public function start_field_group(string $title = '', string $group_tag = 'fieldset', string $group_class = '', string $group_style = ''): self
    {
        $valid_tags = ['fieldset', 'div'];
        $tag = in_array(strtolower($group_tag), $valid_tags) ? strtolower($group_tag) : 'fieldset';

        $this->form_elements[] = [
            'type' => 'group_start',
            'title' => $title,
            'tag'   => $tag,
            'class' => trim(Draw::TORET_LIBRARY_PREFIX . 'field-group ' . Draw::TORET_LIBRARY_PREFIX . 'group-' . $tag . ' ' . sanitize_html_class($group_class)),
            'style' => $group_style,
        ];
        return $this;
    }

    /**
     * End the current group of fields.
     */
    public function end_field_group(): self
    {
        $this->form_elements[] = [
            'type' => 'group_end',
        ];
        return $this;
    }

    /**
     * Opens the <form> tag.
     */
    private function open_form_tag(): void
    {
        $attributes_string = '';
        foreach ($this->form_attributes as $key => $value) {
            $attributes_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
        if (!empty($this->enctype)) {
            $attributes_string .= ' enctype="' . esc_attr($this->enctype) . '"';
        }

        printf(
            '<form action="%s" method="%s"%s>',
            esc_url($this->action),
            esc_attr($this->method),
            $attributes_string
        );

        if ('post' === $this->method) {
            wp_nonce_field($this->action, $this->form_id . '_nonce');
        }
    }

    /**
     * Closes the </form> tag.
     */
    private function close_form_tag(): void
    {
        echo '</form>';
    }

    /**
     * Renders the entire form.
     */
    public function render(): void
    {
        $this->open_form_tag();

        $is_table_layout_active = ($this->layout_type === 'table' && empty($this->group_tag_stack));

        if ($is_table_layout_active) {
            echo '<table class="' . Draw::TORET_LIBRARY_PREFIX . 'form-table">';
            echo '<tbody>';
        }

        foreach ($this->form_elements as $element) {
            switch ($element['type']) {
                case 'group_start':
                    if ($is_table_layout_active && empty($this->group_tag_stack)) {
                        echo '</tbody></table>';
                        $is_table_layout_active = false;
                    }

                    $tag = esc_attr($element['tag']);
                    array_push($this->group_tag_stack, $tag);

                    echo '<' . $tag . ' class="' . esc_attr($element['class']) . '" style="' . esc_attr($element['style']) . '">';
                    if (!empty($element['title'])) {
                        if ($tag === 'fieldset') {
                            echo '<legend class="' . Draw::TORET_LIBRARY_PREFIX . 'group-title">' . esc_html($element['title']) . '</legend>';
                        } else { // div
                            echo '<h3 class="' . Draw::TORET_LIBRARY_PREFIX . 'group-title">' . esc_html($element['title']) . '</h3>';
                        }
                    }
                    break;

                case 'group_end':
                    if (!empty($this->group_tag_stack)) {
                        $tag_to_close = array_pop($this->group_tag_stack);
                        echo '</' . esc_attr($tag_to_close) . '>';

                        if (empty($this->group_tag_stack) && $this->layout_type === 'table') {
                            echo '<table class="' . Draw::TORET_LIBRARY_PREFIX . 'form-table">';
                            echo '<tbody>';
                            $is_table_layout_active = true;
                        }
                    }
                    break;

                case 'field':
                    $draw_method = $element['draw_method'];
                    $args = $element['args'];
                    $location_for_draw = !empty($this->group_tag_stack) ? 'div' : $this->layout_type;

                    if (method_exists(Draw::class, $draw_method)) {
                        if ($draw_method === 'add_hidden') { // Hidden field doesn't take 'location'
                            Draw::$draw_method($args);
                        } else {
                            Draw::$draw_method($args, $location_for_draw);
                        }
                    }
                    break;
            }
        }

        while (!empty($this->group_tag_stack)) {
            $tag_to_close = array_pop($this->group_tag_stack);
            echo '</' . esc_attr($tag_to_close) . '>';
            if (empty($this->group_tag_stack) && $this->layout_type === 'table' && !$is_table_layout_active) {
                echo '<table class="' . Draw::TORET_LIBRARY_PREFIX . 'form-table">';
                echo '<tbody>';
                $is_table_layout_active = true;
            }
        }

        if ($is_table_layout_active) {
            echo '</tbody>';
            echo '</table>';
        }

        $this->close_form_tag();
    }

    /**
     * Validates the nonce for the form on submission.
     * Call this at the beginning of your form processing logic.
     *
     * @return bool True if nonce is valid, false otherwise.
     */
    public function validate_nonce(): bool
    {
        $nonce_action = $this->action;
        $nonce_name = $this->form_id . '_nonce';

        if (isset($_POST[$nonce_name]) && wp_verify_nonce(sanitize_text_field($_POST[$nonce_name]), $nonce_action)) {
            return true;
        }
        if (isset($_GET[$nonce_name]) && wp_verify_nonce(sanitize_text_field($_GET[$nonce_name]), $nonce_action)) {
            return true;
        }
        return false;
    }
}