<?php

namespace ToretZasilkovna\Toret\Library;

class Log
{
    private const LOG_RETENTION_DAYS = 14;

    protected int $limit = 30;
    private string $textDomain;
    private string $tableName;
    private string $tableVersion;

    public function __construct(string $domain, string $tableName, string $tableVersion)
    {
        $this->textDomain = $domain;
        $this->tableVersion = $tableVersion;
        $this->tableName = $tableName;

        add_action('plugins_loaded', [$this, 'createLogTable']);
        add_action('pre_uninstall_plugin', [$this, 'deleteLogtable']);
        add_action('pre_uninstall_plugin', [$this, 'clearOldLogs']);
        add_action('admin_init', [$this, 'clearOldLogs']);

        add_action('admin_init', [$this, 'processLogExport']);
    }

    public function checkIfTableExists()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . $this->tableName;

        return $wpdb->get_var("SHOW TABLES LIKE '$tableName'") === $tableName;
    }

    public function clearOldLogs(): void
    {
        global $wpdb;

        if (!$this->checkIfTableExists()) {
            return;
        }

        $tableName = $wpdb->prefix . $this->tableName;
        $retention_days = intval(apply_filters('toret_log_retention_days', self::LOG_RETENTION_DAYS));
        $target_date = date('Y-m-d H:i:s', strtotime('-' . $retention_days . ' days'));
        $query = $wpdb->prepare("DELETE FROM {$tableName} WHERE `datetime` < %s", $target_date);
        $wpdb->query($query);
    }

    public function deleteLogtable(): void
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$this->tableName}");
    }

    public function createLogTable(): void
    {
        $option_name = $this->tableName . '_db_version';
        if (get_option($option_name) != $this->tableVersion || !$this->checkIfTableExists()) {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $tableName = $wpdb->prefix . $this->tableName;
            $sql = "CREATE TABLE $tableName (
                ID mediumint(9) NOT NULL AUTO_INCREMENT,
                orderid varchar(20) DEFAULT '' NOT NULL,
                datetime datetime NOT NULL,
                context varchar(500) DEFAULT '' NOT NULL,
                log longtext,
                type smallint(1) DEFAULT 0 NOT NULL,
                PRIMARY KEY  (ID),
                KEY datetime (datetime),
                KEY orderid (orderid),
                KEY type (type)
            ) $charset_collate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
            update_option($option_name, $this->tableVersion);
        }
    }

    /**
     * Získá ID všech objednávek souvisejících s předplatným.
     */
    private function getSubscriptionRelatedOrderIds(int $subscription_id): array
    {
        if (!function_exists('wcs_get_subscription')) {
            return [];
        }

        $subscription = wcs_get_subscription($subscription_id);
        if (!$subscription) {
            return [];
        }

        $related_orders_ids = $subscription->get_related_orders('ids');
        $parent_id = $subscription->get_parent_id();
        if ($parent_id) {
            $related_orders_ids[] = $parent_id;
        }

        return array_filter(array_unique($related_orders_ids));
    }

    /**
     * Pomocná metoda pro sestavení WHERE klauzule (aby se neopakovala logika).
     */
    private function buildWhereClause(): string
    {
        global $wpdb;
        $where = '';

        if (!empty($_GET['subscription_id']) && class_exists('WC_Subscriptions')) {
            $subscription_id = absint($_GET['subscription_id']);
            $order_ids = $this->getSubscriptionRelatedOrderIds($subscription_id);

            if (!empty($order_ids)) {
                $placeholders = implode(',', array_fill(0, count($order_ids), '%d'));
                $where = $wpdb->prepare("WHERE orderid IN ($placeholders)", $order_ids);
            } else {
                $where = "WHERE 1=0";
            }
        } elseif (!empty($_GET['order_id'])) {
            $order_id = sanitize_text_field($_GET['order_id']);
            $where = $wpdb->prepare("WHERE orderid = %s", $order_id);
        }

        return $where;
    }

    public function getLogs(): array
    {
        global $wpdb;
        $where = $this->buildWhereClause();
        $current_page = $this->getCurrentPage();
        $offset = ($current_page - 1) * $this->limit;

        $query = "SELECT * FROM {$wpdb->prefix}{$this->tableName} $where ORDER BY datetime DESC LIMIT {$this->limit} OFFSET {$offset}";
        return $wpdb->get_results($query) ?: [];
    }

    /**
     * Metoda pro zpracování exportu do CSV.
     */
    public function processLogExport(): void
    {
        if (
                !isset($_GET['toret_log_action']) ||
                $_GET['toret_log_action'] !== 'export_csv' ||
                !isset($_GET['_wpnonce']) ||
                !wp_verify_nonce($_GET['_wpnonce'], 'toret_log_filter_nonce')
        ) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $where = $this->buildWhereClause();

        $query = "SELECT * FROM {$wpdb->prefix}{$this->tableName} $where ORDER BY datetime DESC";
        $logs = $wpdb->get_results($query, ARRAY_A);

        if (empty($logs)) {
            return;
        }

        $filename = 'log-export-' . date('Y-m-d-H-i-s') . '.csv';

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, [
                __('ID', $this->textDomain),
                __('Date', $this->textDomain),
                __('Type', $this->textDomain),
                __('Order ID', $this->textDomain),
                __('Context', $this->textDomain),
                __('Log Content', $this->textDomain)
        ]);

        foreach ($logs as $log) {
            $type = (int)$log['type'];

            switch ($type) {
                case 1:
                    $type_label = 'Error';
                    break;
                case 2:
                    $type_label = 'Warning';
                    break;
                case 3:
                    $type_label = 'Success';
                    break;
                default:
                    $type_label = 'Info';
                    break;
            }

            fputcsv($output, [
                    $log['ID'],
                    $log['datetime'],
                    $type_label,
                    $log['orderid'],
                    $log['context'],
                    $log['log']
            ]);
        }

        fclose($output);
        exit;
    }

    private function getLogTypeInfo(int $type): array
    {
        switch ($type) {
            case 1:
                return ['label' => __('Error', $this->textDomain), 'class' => 'type-error'];
            case 2:
                return ['label' => __('Warning', $this->textDomain), 'class' => 'type-warning'];
            case 3:
                return ['label' => __('Success', $this->textDomain), 'class' => 'type-success'];
            case 4:
            default:
                return ['label' => __('Info', $this->textDomain), 'class' => 'type-info'];
        }
    }

    public function renderTable(): string
    {
        $logs = $this->getLogs();
        ob_start();
        ?>
        <table class="toret-log-table wp-list-table widefat striped">
            <thead>
            <tr>
                <th style="width: 120px;"><?php echo esc_html__('Type', $this->textDomain); ?></th>
                <th style="width: 170px;"><?php echo esc_html__('Date', $this->textDomain); ?></th>
                <th><?php echo esc_html__('Order ID', $this->textDomain); ?></th>
                <th><?php echo esc_html__('Context', $this->textDomain); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!$logs) {
                echo '<tr><td colspan="4">' . esc_html__('Log is empty', $this->textDomain) . '</td></tr>';
            } else {
                foreach ($logs as $log) {
                    echo $this->renderTableLine($log);
                }
            }
            ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    public function renderTableLine(object $log): string
    {
        $type_info = $this->getLogTypeInfo(intval($log->type));

        $order_id_display = esc_html($log->orderid);
        if($log->orderid == '0' || empty($log->orderid)) {
            $order_id_display = 'General';
        }else if ($log->orderid !== 'Plugin' && function_exists('wc_get_order')) {
            $order_ids = array_map('trim', explode(',', $log->orderid));
            $links = array_map(function($oid) {
                if (!is_numeric($oid) || $oid <= 0) {
                    return esc_html($oid);
                }
                $order = wc_get_order($oid);
                return $order
                        ? '<a href="' . esc_url($order->get_edit_order_url()) . '" target="_blank">' . esc_html($order->get_order_number()) . '</a>'
                        : esc_html($oid);
            }, $order_ids);
            $order_id_display = implode(', ', $links);
        }

        $timestamp = strtotime($log->datetime);
        $localized_date = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);

        $content = trim($log->log);

        if ($this->isJson($content)) {
            $decoded = json_decode($content, true);
            $log_display = '<pre>' . esc_html(print_r($decoded, true)) . '</pre>';
        } elseif ($this->isSerialized($content)) {
            $decoded = maybe_unserialize($content);
            $log_display = '<pre>' . esc_html(print_r($decoded, true)) . '</pre>';
        } else {
            $log_display = '<pre>' . make_clickable(esc_html($content)) . '</pre>';
        }

        ob_start();
        ?>
        <tr class="log-summary-row <?php echo esc_attr($type_info['class']); ?>">
            <td class="log-type-cell">
                <div class="log-type-indicator">
                    <?php echo esc_html($type_info['label']); ?>
                </div>
            </td>
            <td><?php echo esc_html($localized_date); ?></td>
            <td><?php echo $order_id_display; ?></td>
            <td><?php echo wp_kses($log->context, ['a' => ['href' => [], 'target' => [], 'rel' => []]]); ?></td>
        </tr>
        <tr class="log-details-row">
            <td colspan="4"><?php echo $log_display; ?></td>
        </tr>
        <?php
        return ob_get_clean();
    }

    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE && is_array(json_decode($string, true));
    }

    private function isSerialized(string $string): bool
    {
        return (@unserialize($string) !== false || $string === 'b:0;');
    }

    public function saveLog(array $data)
    {
        global $wpdb;
        return $wpdb->insert(
                $wpdb->prefix . $this->tableName,
                [
                        'datetime' => current_time('mysql'),
                        'context' => wp_kses($data['context'] ?? '---', ['a' => ['href' => [], 'target' => [], 'rel' => []]]),
                        'orderid'  => sanitize_text_field($data['order_id'] ?? 'General'),
                        'log'      => isset($data['log']) ? (is_scalar($data['log']) ? sanitize_textarea_field($data['log']) : wp_json_encode($data['log'])) : '---',
                        'type'     => absint($data['type'] ?? 0),
                ]
        );
    }

    public function deleleAllLogs(): void
    {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}{$this->tableName}");
    }

    private function getTotalLogCount(): int
    {
        global $wpdb;
        $where = $this->buildWhereClause();
        return (int)$wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->prefix}{$this->tableName} {$where}");
    }

    private function getCurrentPage(): int
    {
        return !empty($_GET['paged']) ? absint($_GET['paged']) : 1;
    }

    public function getPagination(): string
    {
        $total_items = $this->getTotalLogCount();

        if ($total_items === 0) {
            return '';
        }

        $total_pages = ceil($total_items / $this->limit);
        $current_page = $this->getCurrentPage();

        $output = sprintf(
                '<span class="displaying-num">%s</span>',
                sprintf(
                        _n('%s item', '%s items', $total_items, $this->textDomain),
                        number_format_i18n($total_items)
                )
        );

        if ($total_pages > 1) {

            $get_page_link = function($page) {
                return esc_url(add_query_arg('paged', $page));
            };

            $navigation = '';

            $margin_right = 'style="margin-right: 5px;"';
            $margin_left  = 'style="margin-left: 5px;"';

            if ($current_page > 1) {
                $navigation .= sprintf(
                        '<a class="first-page button" href="%s" %s><span class="screen-reader-text">%s</span><span aria-hidden="true">&laquo;</span></a>',
                        $get_page_link(1),
                        $margin_right,
                        __('First page')
                );
            } else {
                $navigation .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true" ' . $margin_right . '>&laquo;</span>';
            }

            if ($current_page > 1) {
                $navigation .= sprintf(
                        '<a class="prev-page button" href="%s" %s><span class="screen-reader-text">%s</span><span aria-hidden="true">&lsaquo;</span></a>',
                        $get_page_link($current_page - 1),
                        $margin_right,
                        __('Previous page')
                );
            } else {
                $navigation .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true" ' . $margin_right . '>&lsaquo;</span>';
            }

            $paging_text = sprintf(
                    _x('%1$s of %2$s', 'paging', $this->textDomain),
                    '<span class="current-page">' . $current_page . '</span>',
                    '<span class="total-pages">' . $total_pages . '</span>'
            );

            $navigation .= sprintf(
                    '<span class="paging-input" style="display: inline-block; vertical-align: middle; margin: 0 5px;">%s</span>',
                    $paging_text
            );

            if ($current_page < $total_pages) {
                $navigation .= sprintf(
                        '<a class="next-page button" href="%s" %s><span class="screen-reader-text">%s</span><span aria-hidden="true">&rsaquo;</span></a>',
                        $get_page_link($current_page + 1),
                        $margin_left,
                        __('Next page')
                );
            } else {
                $navigation .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true" ' . $margin_left . '>&rsaquo;</span>';
            }

            if ($current_page < $total_pages) {
                $navigation .= sprintf(
                        '<a class="last-page button" href="%s" %s><span class="screen-reader-text">%s</span><span aria-hidden="true">&raquo;</span></a>',
                        $get_page_link($total_pages),
                        $margin_left,
                        __('Last page')
                );
            } else {
                $navigation .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true" ' . $margin_left . '>&raquo;</span>';
            }

            $output .= '<span class="pagination-links">' . $navigation . '</span>';
        }

        return "<div class='tablenav-pages'>$output</div>";
    }

    private function renderDeleteForm(string $button_id): string
    {
        $confirm_text = esc_js(__('Are you sure you want to delete all logs? This action cannot be undone.', $this->textDomain));
        ob_start();
        ?>
        <form method="POST" action="" style="display: inline-block;">
            <?php wp_nonce_field('toret_delete_all_logs_nonce'); ?>
            <input type="hidden" name="action" value="delete_all_logs">
            <?php submit_button(__('Delete all logs', $this->textDomain), 'delete', $button_id, false, ['onclick' => "return confirm('{$confirm_text}');"]); ?>
        </form>
        <?php
        return ob_get_clean();
    }

    private function renderFilterForm(): string
    {
        $current_order_id = !empty($_GET['order_id']) ? sanitize_text_field($_GET['order_id']) : '';
        $current_subscription_id = !empty($_GET['subscription_id']) ? sanitize_text_field($_GET['subscription_id']) : '';
        $page_slug = !empty($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        ob_start();
        ?>
        <form method="get" class="toret-log-filters">
            <input type="hidden" name="page" value="<?php echo esc_attr($page_slug); ?>" />
            <?php wp_nonce_field('toret_log_filter_nonce'); ?>

            <label for="order_id_filter" class="screen-reader-text"><?php _e('Filter by Order ID', $this->textDomain); ?></label>
            <input type="search" id="order_id_filter" name="order_id" value="<?php echo esc_attr($current_order_id); ?>" placeholder="<?php esc_attr_e('Order ID', $this->textDomain); ?>" />

            <?php if (class_exists('WC_Subscriptions')): ?>
                <label for="subscription_id_filter" class="screen-reader-text"><?php _e('Filter by Subscription ID', $this->textDomain); ?></label>
                <input type="search" id="subscription_id_filter" name="subscription_id" value="<?php echo esc_attr($current_subscription_id); ?>" placeholder="<?php esc_attr_e('Subscription ID', $this->textDomain); ?>" />
            <?php endif; ?>

            <?php submit_button(__('Filter'), 'primary', 'filter_action', false); ?>

            <button type="submit" name="toret_log_action" value="export_csv" class="button button-secondary">
                <?php _e('Export CSV', $this->textDomain); ?>
            </button>

            <?php if (!empty($current_order_id) || !empty($current_subscription_id)): ?>
                <a href="<?php echo esc_url(remove_query_arg(['order_id', 'subscription_id'])); ?>" class="button"><?php _e('Clear Filter', $this->textDomain); ?></a>
            <?php endif; ?>
        </form>
        <?php
        return ob_get_clean();
    }

    public function renderLogPage(): void
    {
        if (
                isset($_POST['action'], $_POST['_wpnonce']) &&
                $_POST['action'] === 'delete_all_logs' &&
                wp_verify_nonce($_POST['_wpnonce'], 'toret_delete_all_logs_nonce')
        ) {
            $this->deleleAllLogs();
            add_settings_error('toret_log_notices', 'logs_deleted', __('All logs have been successfully deleted.', $this->textDomain), 'updated');
            set_transient('settings_errors', get_settings_errors(), 30);
            wp_safe_redirect(remove_query_arg(['action', '_wpnonce'], wp_get_referer()));
            exit;
        }

        settings_errors('toret_log_notices');
        ?>
        <div class="wrap toret-log-page">
            <style>

                @media (max-width: 782px) {
                    .toret-log-page .toret-log-filters {
                        flex-wrap: wrap;
                        width: 100%;
                    }

                    .toret-log-page .toret-log-filters input[type="search"] {
                        width: 100%;
                    }

                    .toret-log-page .toret-log-filters .button,
                    .toret-log-page .toret-log-filters button {
                        width: 100%;
                        text-align: center;
                    }

                    .toret-log-page .tablenav .actions.bulkactions {
                        flex-wrap: wrap;
                        width: 100%;
                    }

                    .toret-log-page .tablenav .actions.bulkactions > * {
                        width: 100%;
                    }
                }

                .toret-log-page .tablenav .actions.bulkactions { display: flex; gap: 10px; align-items: center; }
                .toret-log-page .toret-log-filters { display: flex; gap: 8px; align-items: center; }

                .toret-log-table .log-summary-row { cursor: pointer; }
                .toret-log-table .log-summary-row:hover { background-color: #f0f0f1; }
                .toret-log-table .log-details-row { display: none; }
                .toret-log-table .log-details-row td { background-color: #f6f7f7; padding: 15px; }

                .toret-log-table pre {
                    background: #23282d; color: #f0f0f1; padding: 15px;
                    border-radius: 4px; font-size: 13px; max-height: 400px;
                    overflow: auto; margin: 0; white-space: pre-wrap; word-break: break-all;
                }

                .toret-log-table .log-type-cell { padding: 0 !important; width: 120px; }
                .log-type-indicator {
                    padding: 10px 12px; height: 100%; box-sizing: border-box;
                    border-left: 5px solid; font-weight: 600;
                }
                .type-error .log-type-indicator { border-color: #d63638; color: #d63638; }
                .type-warning .log-type-indicator { border-color: #f5a623; color: #f5a623; }
                .type-success .log-type-indicator { border-color: #4ab866; color: #4ab866; }
                .type-info .log-type-indicator { border-color: #007cba; color: #007cba; }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const table = document.querySelector('.toret-log-table');
                    if (table) {
                        table.addEventListener('click', function(e) {
                            const summaryRow = e.target.closest('.log-summary-row');
                            if (summaryRow) {
                                if (e.target.tagName.toLowerCase() === 'a') {
                                    return;
                                }
                                const detailsRow = summaryRow.nextElementSibling;
                                if (detailsRow && detailsRow.classList.contains('log-details-row')) {
                                    detailsRow.style.display = detailsRow.style.display === 'table-row' ? 'none' : 'table-row';
                                }
                            }
                        });
                    }
                });
            </script>

            <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
            <hr class="wp-header-end">

            <div class="tablenav top" style="margin-bottom: 10px;">
                <div class="alignleft actions bulkactions">
                    <?php echo $this->renderFilterForm(); ?>
                    <?php echo $this->renderDeleteForm('delete_all_top'); ?>
                </div>
                <?php echo $this->getPagination(); ?>
            </div>

            <?php echo $this->renderTable(); ?>

            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <?php echo $this->renderDeleteForm('delete_all_bottom'); ?>
                </div>
                <?php echo $this->getPagination(); ?>
            </div>
        </div>
        <?php
    }
}