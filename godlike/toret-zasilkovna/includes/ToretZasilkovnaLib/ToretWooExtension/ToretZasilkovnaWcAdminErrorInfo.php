<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class ToretZasilkovnaWcAdminErrorInfo extends WC_Email
{
    /**
     * @var string note
     */
    private $note;

    /**
     * Set email defaults
     *
     * @since 0.1
     */
    public function __construct() {

        $this->id             = 'wc_zasilkovna_admin_error_info';
        $this->customer_email = true;
        $this->title          = __( 'Error sending to Packeta', WOOZASILKOVNASLUG );
        $this->description    = __( 'E-mail notification upon parcel error', WOOZASILKOVNASLUG );
        $this->heading        = __( 'Error sending to Packeta', WOOZASILKOVNASLUG );
        $this->subject        = __( 'Error sending to Zasilkovna from {site_title} shop', WOOZASILKOVNASLUG );


        // these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
        $this->template_html  = 'zasilkovna-admin-error-info.php';
        $this->template_plain = 'zasilkovna-admin-error-info-plain.php';

        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();

    }

    /**
     * Determine if the email should actually be sent and setup email merge variables
     *
     * @param int $order_id
     *
     * @param string $note
     *
     * @return bool
     * @since 0.1
     */
    public function trigger( int $order_id, string $note ): bool {

        if ( $order_id ) {
            $this->object    = wc_get_order( $order_id );
            $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
            $this->note      = $note;

            $this->find['order-date']   = '{order_date}';
            $this->find['order-number'] = '{order_number}';

            $this->replace['order-date']   = date_i18n( wc_date_format(), strtotime( $this->object->get_date_created() ) );
            $this->replace['order-number'] = $this->object->get_order_number();
        }

        if ( $this->is_enabled() && $this->get_recipient() ) {
            return !empty($this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments()));
        } else {
            return false;
        }
    }


    /**
     * get_content_html function.
     *
     * @return string
     * @since 0.1
     */
    public function get_content_html(): string {

        return wc_get_template_html( $this->template_html, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => true,
            'plain_text'    => false,
            'email'         => $this,
            'note'          => $this->note
        ) );

    }


    /**
     * get_content_plain function.
     *
     * @return string
     * @since 0.1
     */
    public function get_content_plain(): string {

        return wc_get_template_html( $this->template_plain, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => true,
            'plain_text'    => true,
            'email'         => $this,
            'note'          => $this->note
        ) );

    }


    /**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields(): void {
        $types = array(
            'plain' => __( 'Plain text', WOOZASILKOVNASLUG )
        );

        if ( class_exists( 'DOMDocument' ) ) {
            $types['html']      = __( 'HTML', WOOZASILKOVNASLUG );
            $types['multipart'] = __( 'Multipart', WOOZASILKOVNASLUG );
        }


        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __( 'Enable / Disable', WOOZASILKOVNASLUG ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this e-mail notification', WOOZASILKOVNASLUG ),
                'default' => 'yes'
            ),
            'recipient'  => array(
                'title'       => __( 'Recipient(s)', WOOZASILKOVNASLUG ),
                'type'        => 'text',
                /* translators: %s: admin email */
                'description' => sprintf( __( 'Enter recipient (separate with comma) of this e-mail. The default is %s.', WOOZASILKOVNASLUG ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'subject'    => array(
                'title'       => __( 'E-mail subject', WOOZASILKOVNASLUG ),
                'type'        => 'text',
                'placeholder' => '',
                'default'     => ''
            ),
            'heading'    => array(
                'title'       => __( 'Email title', WOOZASILKOVNASLUG ),
                'type'        => 'text',
                'placeholder' => '',
                'default'     => ''
            ),
            'email_type' => array(
                'title'       => __( 'E-mail type', WOOZASILKOVNASLUG ),
                'type'        => 'select',
                'description' => __( 'Select the format.', WOOZASILKOVNASLUG ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $types
            )
        );
    }
}