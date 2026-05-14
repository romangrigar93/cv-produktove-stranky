```php
use Toret\ToretDraw\ToretDraw;
use Toret\ToretDraw\ToretForm;

new ToretDraw(); 

$text_field_args = [
    'id'                => 'my_text_field',
    'label'             => __('My Text Field', 'your-text-domain'),
    'default'           => __('Default Value', 'your-text-domain'),
    'placeholder'       => __('Enter text...', 'your-text-domain'),
    'description'       => __('This is the description for the text field.', 'your-text-domain'),
    'desc_tip'          => true,
    'required'          => true,
    'class'             => 'custom-text-class',
    'style'             => 'width: 50%;',
    'custom_attributes' => ['data-example' => 'attribute-value'],
];

ToretDraw::add_text($text_field_args, 'table');

$hidden_field_args = [
    'id'    => 'my_hidden_field',
    'name'  => 'my_hidden_field_name',
    'value' => 'secret_value_123',
    'class' => 'my-hidden-class',
];

ToretDraw::add_hidden($hidden_field_args);

$textarea_field_args = [
    'id'          => 'my_textarea_field',
    'label'       => __('My Textarea', 'your-text-domain'),
    'default'     => __("This is some\nmultiline text.", 'your-text-domain'),
    'placeholder' => __('Write a longer text...', 'your-text-domain'),
    'description' => __('Description for the textarea, displayed below the field.', 'your-text-domain'),
    'desc_tip'    => false,
    'rows'        => 5,
    'cols'        => 50,
    'class'       => 'large-text custom-textarea',
    'required'    => true,
];

ToretDraw::add_textarea($textarea_field_args, 'table');

$select_field_args = [
    'id'          => 'my_select_field',
    'label'       => __('Select an Option', 'your-text-domain'),
    'default'     => 'option2',
    'options'     => [
        'option1' => __('Option 1', 'your-text-domain'),
        'option2' => __('Option 2 (default)', 'your-text-domain'),
        'option3' => __('Option 3', 'your-text-domain'),
    ],
    'description' => __('Choose one of the values.', 'your-text-domain'),
    'desc_tip'    => true,
    'class'       => 'toret-draw-field-type-multiselect',
    'required'    => true,
];

ToretDraw::add_select($select_field_args, 'table');

$multiselect_field_args = [
    'id'          => 'my_multiselect_field',
    'label'       => __('Select Multiple Options', 'your-text-domain'),
    'default'     => ['val1', 'val3'], 
    'options'     => [
        'val1' => __('Value A', 'your-text-domain'),
        'val2' => __('Value B', 'your-text-domain'),
        'val3' => __('Value C', 'your-text-domain'),
        'val4' => __('Value D', 'your-text-domain'),
    ],
    'description' => __('You can select multiple items.', 'your-text-domain'),
    'desc_tip'    => false,
    'class'       => 'toret-draw-field-type-multiselect',
];

ToretDraw::add_multiselect($multiselect_field_args, 'table');

$radio_field_args = [
    'id'          => 'my_radio_field',
    'label'       => __('Select Payment Type', 'your-text-domain'),
    'default'     => 'card',
    'options'     => [
        'cash'    => __('Cash', 'your-text-domain'),
        'card'    => __('Card (default)', 'your-text-domain'),
        'paypal'  => __('PayPal', 'your-text-domain'),
    ],
    'description' => __('Choose your preferred method.', 'your-text-domain'),
    'desc_tip'    => true,
    'class'       => 'custom-radio-buttons',
];

ToretDraw::add_radio($radio_field_args, 'table');

$note_field_args = [
    'id'               => 'my_note_field',
    'label'            => __('Important Notice', 'your-text-domain'), 
    'message'          => __('This is <strong>important information</strong> you should know. It can contain <em>HTML</em>.', 'your-text-domain'),
    'label_aria_label' => __('Section with important notice', 'your-text-domain'),
    'wrapper_class'    => 'my-custom-note-wrapper',
];

ToretDraw::add_note($note_field_args, 'table');

$text_field_inline_desc_args = [
    'id'          => 'text_inline_desc',
    'label'       => __('Field with Inline Description', 'your-text-domain'),
    'default'     => __('Value', 'your-text-domain'),
    'description' => __('This description will appear directly below the field.', 'your-text-domain'),
    'desc_tip'    => false, 
];

ToretDraw::add_text($text_field_inline_desc_args, 'table');

$button_type_args = [
    'id'                => 'my_action_button',
    'value'             => __('Perform Action', 'your-text-domain'),
    'type'              => 'button',
    'class'             => 'button',
    'custom_attributes' => ['onclick' => "alert('Action performed!');"],
];

ToretDraw::add_submit($button_type_args, 'table'); 


```

## Form
```php
$myForm = new ToretForm('my-unique-form-id', admin_url('admin-post.php?action=my_form_handler'), 'post', 'table');
$myForm->add_form_class('my-custom-form-look');
$myForm->set_enctype('multipart/form-data');

$myForm->add_checkbox_field([
    'id' => 'agree_terms_after',
    'label' => 'I agree to the terms (label after)',           
]);

$myForm->add_checkbox_field([
    'id' => 'receive_updates_before',
    'label' => 'Receive updates (label before)',
    'label_position' => 'before_input',
    'wrapper_class' => 'toret-draw-checkbox-inline-group',
]);

$myForm->add_checkbox_field([
    'id' => 'enable_feature_label_above',
    'label' => 'Enable Special Feature (label above)',
    'label_position' => 'before',
]);

$myForm->add_text_field([
    'id' => 'user_name',
    'label' => __('User Name', ToretForm::TEXT_DOMAIN),
    'required' => true,
]);

$myForm->add_text_field([
    'id' => 'user_email',
    'label' => __('User Email', ToretForm::TEXT_DOMAIN),
    'type' => 'email',
    'required' => true,
]);

$myForm->start_field_group(
    __('Address Details', ToretForm::TEXT_DOMAIN),
    'fieldset',
    'address-group custom-flex-row',
    'display: flex; flex-direction: row; gap: 20px; align-items: flex-start;'
);

$myForm->add_text_field([
    'id' => 'street',
    'label' => __('Street', ToretForm::TEXT_DOMAIN),
    'wrapper_class' => 'flex-item-auto',
]);
$myForm->add_text_field([
    'id' => 'city',
    'label' => __('City', ToretForm::TEXT_DOMAIN),
    'wrapper_class' => 'flex-item-auto',
]);

$myForm->end_field_group();


$myForm->start_field_group(__('Preferences', ToretForm::TEXT_DOMAIN), 'div', 'preferences-group');
$myForm->add_checkbox_field([
    'id' => 'newsletter',
    'label' => __('Subscribe to newsletter', ToretForm::TEXT_DOMAIN),
    'checked_value' => 'yes',
    'unchecked_value' => 'no',
]);
$myForm->add_select_field([
    'id' => 'user_role',
    'label' => __('User Role', ToretForm::TEXT_DOMAIN),
    'options' => ['editor' => 'Editor', 'subscriber' => 'Subscriber'],
]);
$myForm->end_field_group();


$myForm->add_submit_button([
    'id' => 'submit_my_form',
    'value' => __('Save Changes', ToretForm::TEXT_DOMAIN),
]);

$myForm->render();

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['my-divi-style-form_nonce'])) {
    $form_to_validate = new ToretForm('my-divi-style-form', admin_url('admin-post.php?action=my_form_handler'));
  
    if ($form_to_validate->validate_nonce()) {
        $user_name = isset($_POST['user_name']) ? sanitize_text_field($_POST['user_name']) : '';
        $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
        // wp_safe_redirect(...); exit;
    } else {
        wp_die(__('Security check failed!', TOWP_SLUG), __('Error', TOWP_SLUG), ['response' => 403]);
    }
}
```
## Create auth.json

```
{
  "github-oauth": {
    "github.com": "YOUR TOKEN"
  }
}
```

## Update composer.json

```
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/Toret-plugins/toret-popup"
    }
  ],
  "require": {
    "toret/toret-popup": "dev-master"
  }
}
```

## Example of usage

### Init class
```php
require plugin_dir_path(__FILE__) . 'vendor/autoload.php';
$ToretPopup = new \Toret\Popup\ToretPopup(); // Enqueue the scripts, styles ...
```

### HTML
```html
<button id="open-toret-popup">Show popup</button>
```

### JavaScript
```js
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.ToretPopup === 'undefined') return; // Ensure ToretPopup is loaded

    const myButton = document.getElementById('open-toret-popup');
    if (myButton) {
        myButton.addEventListener('click', function() {
            window.ToretPopup.open(
                'test-popup', // Unique ID for the popup
                'Test Popup',   // Title
                '<h3>Hello from JavaScript!</h3><p>This popup was opened via code.</p>', // HTML Content
                [ // Footer buttons configuration
                    {
                        text: 'Log & Close',
                        action: function(popupId, buttonEl, event) { // Custom callback function
                            console.log('Custom action for popup:', popupId, 'Button:', buttonEl.textContent, 'Event:', event);
                            alert('Logged to console!');
                        },
                        className: ['button-special'],
                        closeAfterAction: true // Default, can be omitted
                    },
                    {
                        text: 'Just Close'
                        // No action means it will just close the popup if closeAfterAction is true (default)
                    }
                ],
                { 
                    showHeader: true,
                    //showExternalCloseButton: true, // Optional: Show the close button in the header
                    size: 'large' // 'small', 'medium', 'large', 'extra-large', 'fullscreen'
                } // Options object
            );
        });
    }
});
```

## Create trigger button

### Basic Popup
```php
use Toret\Popup\ToretPopup;
use Toret\Popup\Button\ToretPopupButton;

// new ToretPopup(); // Enqueue the scripts, styles ...

echo ToretPopupButton::create_trigger_button([
    'button_text'   => __('Show Info', 'your-text-domain'),
    'button_class'  => 'button button-secondary', // Optional: WP button classes
    'popup_id'      => 'my-info-popup', // Unique ID for this popup instance
    'popup_title'   => __('Information', 'your-text-domain'),
    'popup_content' => '<p>' . __('This is some basic information displayed in the popup.', 'your-text-domain') . '</p>',
    'footer_buttons' => [
        [
        'text'      => __('Okay', 'your-text-domain'),
        'className' => 'button-primary',
        // No 'action' means it defaults to closing the popup
        ]
    ]
]);
```

### Using Hidden Content for the Popup
```php
use Toret\Popup\ToretPopup;
use Toret\Popup\Button\ToretPopupButton;

// new ToretPopup(); // Enqueue the scripts, styles ...

// 1. Define your complex HTML content
$my_complex_content_id = 'detailed-form-content';
$form_html = '<form id="popup-contact-form">';
$form_html .= '<div><label for="p_name">' . __('Name:', 'your-text-domain') . '</label><input type="text" id="p_name" name="p_name" required></div>';
$form_html .= '<div><label for="p_email">' . __('Email:', 'your-text-domain') . '</label><input type="email" id="p_email" name="p_email" required></div>';
$form_html .= '<div><label for="p_message">' . __('Message:', 'your-text-domain') . '</label><textarea id="p_message" name="p_message" required></textarea></div>';
$form_html .= '</form>';
$form_html .= '<p><small>' . __('This form is for demonstration purposes.', 'your-text-domain') . '</small></p>';

// 2. Create the hidden div with this content
echo ToretPopupButton::create_hidden_popup_content($my_complex_content_id, $form_html);

// 3. Create the trigger button, referencing the hidden content's ID
echo ToretPopupButton::create_trigger_button([
    'button_text'             => __('Open Contact Form', 'your-text-domain'),
    'popup_size' => 'large', // Optional: 'small', 'medium', 'large', extra-large
    'popup_id'                => 'contact-form-popup',
    'popup_title'             => __('Contact Us', 'your-text-domain'),
    'popup_show_header' => true,
    'popup_content_source_id' => '#' . $my_complex_content_id, // CSS selector for the hidden div
    // 'popup_content' is now ignored by JS if popup_content_source_id is processed
    'footer_buttons'          => [
        [
            'text'      => __('Submit Form', 'your-text-domain'),
            'action'    => 'handlePopupFormSubmit', // JS function to handle form submission
            'className' => 'button-primary',
            'closeAfterAction' => false // Keep popup open to show success/error
        ],
        [
            'text' => __('Cancel', 'your-text-domain')
        ]
    ]
]);

<!-- Example JS function for the form (must be defined in your scripts) -->
<script>
function handlePopupFormSubmit(popupId, buttonElement, event) {
    const form = document.getElementById('popup-contact-form');
    if (form) {
        // Example: Basic validation or AJAX submission
        const name = form.p_name.value;
        if (!name) {
            alert('Name is required!');
            return;
        }
        console.log('Form submitted from popup:', popupId, new FormData(form));
        alert('Form would be submitted here. Check console.');
        // ToretPopup.close(); // Close manually if needed
    }
}
</script>
```

