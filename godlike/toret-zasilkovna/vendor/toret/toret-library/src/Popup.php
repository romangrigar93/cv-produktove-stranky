<?php

namespace ToretZasilkovna\Toret\Library;

class Popup
{
    /**
     * @var string
     */
    const TORET_POPUP_VERSION = '1.0.0';

    const PLUGIN_PREFIX = 'toret-popup-';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'toret_popup_enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'toret_popup_enqueue_assets'));
    }

    /**
     * @return void
     */
    function toret_popup_enqueue_assets(): void
    {
        $plugin_dir_url = plugin_dir_url(__FILE__);

        // Styles
        wp_enqueue_style('toret-popup-styles', $plugin_dir_url . 'assets/css/toret-popup.css', array(), self::TORET_POPUP_VERSION);

        // Script
        wp_enqueue_script('toret-popup-handler', $plugin_dir_url . 'assets/js/toret-popup.js', array('jquery'), self::TORET_POPUP_VERSION);

        // Pass data to JavaScript
        wp_localize_script('toret-popup-handler', 'toretPopupData',
            array(
                'ajax_url' => admin_url('admin-ajax.php')
            )
        );
    }

    /**
     * Generates HTML for a button that triggers a ToretPopup.
     *
     * @param array $args {
     *     Configuration for the trigger button and the popup it opens.
     *
     * @type string $button_text Text for the trigger button. Defaults to "Open Popup".
     * @type string $button_id Optional ID for the trigger button.
     * @type string $button_class Optional CSS classes for the trigger button. Defaults to 'button'.
     * @type string $popup_id ID for the popup. Defaults to a unique ID.
     * @type string $popup_title Title for the popup. Defaults to "Popup".
     * @type string $popup_content HTML content for the popup. Defaults to "No content provided.".
     *                                       If 'popup_content_source_id' is provided, this is ignored.
     * @type string $popup_content_source_id Optional. CSS selector (e.g., '#my-content-div') for a hidden
     *                                       element whose innerHTML will be used as popup content.
     *                                       Requires corresponding JS modification to read this.
     * @type bool $popup_show_header Whether to show the popup header. Defaults to true.
     * @type array $footer_buttons Array of button configurations for the popup footer.
     *                                       Each button is an array:
     *                                       'text' (string) Text for the footer button.
     *                                       'action' (string|null) JS action (e.g., 'ToretPopup.actions.myAction',
     *                                                                a global function name, or direct JS function as string).
     *                                       'className' (string) CSS class for the footer button.
     *                                       'closeAfterAction' (bool) Whether to close popup after action. Defaults to true.
     * }
     * @return string HTML for the trigger button.
     */
    public static function create_trigger_button(array $args): string
    {
        $defaults = [
            'button_text' => '',
            'button_id' => '',
            'button_class' => 'button',
            'popup_id' => self::PLUGIN_PREFIX . 'default-popup-' . uniqid(),
            'popup_title' => '',
            'popup_content' => '',
            'popup_content_source_id' => '',
            'popup_show_header' => true,
            'footer_buttons' => [],
            'popup_size' => 'medium',
        ];

        $args = wp_parse_args($args, $defaults);

        $data_attributes = [
            'data-popup-id' => esc_attr($args['popup_id']),
            'data-popup-title' => esc_attr($args['popup_title']),
        ];

        if (!empty($args['popup_content_source_id'])) {
            $data_attributes['data-popup-content-source-id'] = esc_attr('#' . $args['popup_content_source_id']);
        } else {
            $data_attributes['data-popup-content'] = esc_attr($args['popup_content']);
        }

        if ($args['popup_show_header'] === false) {
            $data_attributes['data-popup-show-header'] = 'false';
        }

        if (!empty($args['popup_size'])) {
            $data_attributes['data-popup-size'] = 'large';
        }

        if (!empty($args['footer_buttons']) && is_array($args['footer_buttons'])) {
            foreach ($args['footer_buttons'] as $index => $btnConfig) {
                $btn_index = $index + 1;
                if (!empty($btnConfig['text'])) {
                    $data_attributes["data-button{$btn_index}-text"] = esc_attr($btnConfig['text']);
                    if (isset($btnConfig['action'])) {
                        $data_attributes["data-button{$btn_index}-action"] = esc_attr($btnConfig['action']);
                    }
                    if (!empty($btnConfig['className'])) {
                        $data_attributes["data-button{$btn_index}-class"] = esc_attr($btnConfig['className']);
                    }
                    if (isset($btnConfig['closeAfterAction']) && $btnConfig['closeAfterAction'] === false) {
                        $data_attributes["data-button{$btn_index}-close"] = 'false';
                    }
                }
            }
        }

        $data_attr_string = '';
        foreach ($data_attributes as $key => $value) {
            $data_attr_string .= ' ' . $key . '="' . $value . '"';
        }

        $button_id_attr = !empty($args['button_id']) ? ' id="' . esc_attr($args['button_id']) . '"' : '';

        $button_classes = self::PLUGIN_PREFIX . 'trigger-button ' . sanitize_html_class($args['button_class']);

        return sprintf(
            '<button type="button" class="%s"%s%s>%s</button>',
            esc_attr(trim($button_classes)),
            $button_id_attr,
            $data_attr_string,
            esc_html($args['button_text'])
        );
    }

    /**
     * Generates a hidden div containing HTML content for a popup.
     *
     * @param string $content_id The ID for the hidden content div. This should be unique.
     * @param string $html_content The HTML content to be placed inside the div.
     * @return string HTML for the hidden content div.
     */
    public static function create_hidden_popup_content(string $content_id, string $html_content): string
    {
        return sprintf(
            '<div id="%s" style="display:none;" class="%scontent-source">%s</div>',
            esc_attr($content_id),
            self::PLUGIN_PREFIX,
            $html_content
        );
    }
}