<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * @package   Woo Zasilkovna
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      https://toret.cz
 * @copyright 2019 Toret.cz
 */

class ToretZasilkovnaLog
{


    /**
     * Instance of this class.
     *
     * @since    2.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Log table name.
     *
     * @since    2.0.0
     *
     * @var      string
     */
    protected $table_name = 'zasilkovna_log';

    /**
     * Limit
     *
     * @since    2.0.0
     *
     * @var      string
     */
    protected $limit = 100;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since     2.0.0
     */
    private function __construct() {

    }

    /**
     * Return an instance of this class.
     *
     * @return    object    A single instance of this class.
     * @since     2.0.0
     *
     */
    public static function get_instance() {


        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Clear old logs
     * @return void
     */
    public function clear_old_logs()
    {

        global $wpdb;

        $table_name = $wpdb->prefix . $this->table_name;
        $date_column = 'date';
        $target_date = date('Y-m-d', strtotime('-14 days'));

        $query = $wpdb->prepare("
    DELETE FROM $table_name
    WHERE STR_TO_DATE($date_column, '%%a, %%d %%b %%Y %%H:%%i:%%s') < %s
", $target_date);

        $wpdb->query($query);

    }

    /**
     * Render table
     *
     * @since 2.0.0
     */
    public function render_table(): string {

        if ( ! empty( $_GET['order_id'] ) ) {

            $logs = $this->get_order_logs( $_GET['order_id'] );

        } else {

            $logs = $this->get_logs();

        }

        if (!$logs) {

            $html = '<p>' . __( 'No logs found', WOOZASILKOVNASLUG ) . '</p>';

        } else {

            $html = '<table class="tzas-log-table" style="table-layout:fixed;">';

            $html .= $this->table_head();

            foreach ( $logs as $log ) {

                $html .= $this->render_table_line( $log );

            }

            $html .= '</table>';

        }

        return $html;

    }

    /**
     * Get logs for order
     *
     * @param int $order_id
     *
     * @return array
     * @since 2.0.0
     */
    public function get_order_logs( int $order_id ): array {

        global $wpdb;

        $logs = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . $this->table_name . " WHERE order_id = '" . $order_id . "' ORDER BY date DESC " );

        if ( ! empty( $logs ) ) {

            return $logs;

        } else {

            return array();

        }

    }

    /**
     * Get logs for table
     * @return array
     * @since 2.0.0
     */
    public function get_logs(): array {

        global $wpdb;

        if ( isset( $_GET['offset'] ) && $_GET['offset'] > 1 ) {

            $offset = esc_attr( $_GET['offset'] );
            $start  = ( $offset * $this->limit ) - $this->limit;

            $logs = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . $this->table_name . " ORDER BY datetime DESC LIMIT " . $this->limit . " OFFSET " . $start );

        } else {

            $logs = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . $this->table_name . " ORDER BY datetime DESC LIMIT " . $this->limit );

        }

        if ( ! empty( $logs ) ) {

            return $logs;

        } else {

            return array();

        }

    }

    /**
     * Render table head
     *
     * @return string
     * @since 2.0.0
     */
    public function table_head(): string {

        return '
    		<tr>
              <th>' . __( 'Order ID', WOOZASILKOVNASLUG ) . '</th>
              <th>' . __( 'Date', WOOZASILKOVNASLUG ) . '</th>
              <th>' . __( 'Context', WOOZASILKOVNASLUG ) . '</th>
            </tr>
    	';

    }

    /**
     * Render table line
     *
     * @param object $log
     *
     * @return string
     * @since 2.0.0
     */
    public function render_table_line( object $log ): string {

        return '
            <tr>
              <td style="word-wrap:break-word;font-weight:bold;background:#f3f2f2;">' . $log->order_id . '</td>
              <td style="word-wrap:break-word;font-weight:bold;background:#f3f2f2;">' . $log->date . '</td>
              <td style="word-wrap:break-word;font-weight:bold;background:#f3f2f2;">' . $log->context . '</td>
            </tr>
            <tr>
              <td colspan="3" style="word-wrap:break-word;">' . $log->log . '</td>
            </tr>
        ';

    }

    /**
     * Save log
     *
     *
     * @param $data
     *
     * @return string
     * @since 2.0.0
     */
    public function save_log( $data ): string {

        $return = '';
        if ( ! empty( $data['order_id'] ) ) {

            if ( ! empty( $data['status'] ) ) {
                $status = $data['status'];
            } else {
                $status = '---';
            }
            if ( ! empty( $data['context'] ) ) {
                $context = $data['context'];
            } else {
                $context = '---';
            }
            if ( ! empty( $data['note'] ) ) {
                $note = $data['note'];
            } else {
                $note = '---';
            }

            $data = array(
                'order_id' => $data['order_id'],
                'date'     => date( 'D, d M Y H:i:s' ),
                'datetime' => time(),
                'log'      => $data['log'],
                'status'   => $status,
                'context'  => $context,
                'note'     => $note
            );

            global $wpdb;

            $wpdb->insert( $wpdb->prefix . $this->table_name, $data );

            $return = $wpdb->last_query;

        }

        return $return;
    }

    /**
     * Empty table
     *
     * @since 2.0.0
     */
    public function delete_logs(): void {

        global $wpdb;

        $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $this->table_name );

    }

    /**
     * Pagination
     *
     * @return string
     * @since 2.0.0
     */
    public function pagination(): string {

        global $wpdb;

        if ( ! empty( $_GET['order_id'] ) ) {
            $order_id = sanitize_text_field( $_GET['order_id'] );
            $logs     = $this->get_order_logs( $order_id );
        } else {
            $logs = $wpdb->get_results( "SELECT ID FROM " . $wpdb->prefix . $this->table_name . " ORDER BY date DESC" );
        }

        if ( empty( $logs ) ) {
            return '';
        }


        $all   = count( $logs );
        $pages = ceil( $all / $this->limit );
        if ( ! empty( $_GET['offset'] ) ) {
            $current = $_GET['offset'];
        } else {
            $current = 1;
        }

        $html = '<div class="log-pagination">';

        $query_string = $_SERVER['QUERY_STRING'];

        if ( $pages != 1 ) {

            for ( $i = 1; $i <= $pages; $i ++ ) {
                if ( $current == $i ) {
                    $html .= '<span class="btn btn-default">' . $i . '</span>';
                } else {
                    $html .= '<a class="btn btn-primary" href="' . admin_url() . 'admin.php?' . $query_string . '&offset=' . $i . '">' . $i . '</a>';
                }
            }

        }

        $html .= '</div>';

        return $html;

    }
}