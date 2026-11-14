<?php

/**
 * Entries analytics (stat cards + charts) for Elementor Forms submissions.
 *
 * Data comes from Elementor Pro's own submissions table (`e_submissions`), the
 * same table this plugin already reads for feeds. The plugin's Dashboard tab
 * shows stat cards, an "Entries Over Time" chart and an "Entries by Status"
 * doughnut, filtered by form.
 *
 * @since 1.3.9
 */

if (!defined('ABSPATH')) {
    exit();
}
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
class GSC_Elementor_Entries_Analytics
{
    const PERIODS = array('30days', 'daily', 'weekly', 'monthly', 'last_month', 'yearly');

    private static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('wp_ajax_gselef_dashboard_stats_query', array($this, 'stats_query'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    private function table()
    {
        global $wpdb;
        return $wpdb->prefix . 'e_submissions';
    }

    private function table_exists()
    {
        global $wpdb;
        $table = $this->table();
        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
    }

    /**
     * Live submissions only: Elementor Pro excludes trashed ones everywhere.
     */
    private function where($form = '')
    {
        global $wpdb;
        $sql   = "status != 'trash'";
        $parts = explode('|', (string) $form);
        if (2 === count($parts) && absint($parts[0]) > 0 && '' !== $parts[1]) {
            $sql .= $wpdb->prepare(' AND post_id = %d AND element_id = %s', absint($parts[0]), sanitize_text_field($parts[1]));
        }
        return $sql;
    }

    /**
     * @return array{all:int,unread:int,read:int}
     */
    public function get_status_counts($form = '')
    {
        global $wpdb;

        $counts = array('all' => 0, 'unread' => 0, 'read' => 0);
        if (!$this->table_exists()) {
            return $counts;
        }

        $table = $this->table();
        $row   = $wpdb->get_row(
            "SELECT COUNT(*) AS total, SUM(IF(is_read = 1, 1, 0)) AS read_count FROM {$table} WHERE " . $this->where($form),
            OBJECT
        );
        if ($row) {
            $counts['all']    = (int) $row->total;
            $counts['read']   = (int) $row->read_count;
            $counts['unread'] = max(0, $counts['all'] - $counts['read']);
        }

        return $counts;
    }

    /**
     * Forms for the Form filter: every Elementor form found on the site (like
     * Fluent, forms without entries are listed too, with a count of 0), plus
     * any form that only exists in the submissions table (e.g. since deleted).
     *
     * @return array<int, array{key:string,title:string,count:int}>
     */
    public function get_forms_with_entries()
    {
        global $wpdb;

        // Entry counts per form from Elementor Pro's submissions.
        $counts = array();
        $names  = array();
        if ($this->table_exists()) {
            $table = $this->table();
            $rows  = $wpdb->get_results(
                "SELECT post_id, element_id, MAX(form_name) AS form_name, COUNT(*) AS total FROM {$table}
                WHERE " . $this->where() . ' GROUP BY post_id, element_id',
                OBJECT
            );
            foreach ((array) $rows as $row) {
                $key          = (int) $row->post_id . '|' . $row->element_id;
                $counts[$key] = (int) $row->total;
                $names[$key]  = '' !== (string) $row->form_name ? $row->form_name : get_the_title((int) $row->post_id);
            }
        }

        $forms = array();
        $seen  = array();

        // Every form that exists in Elementor content.
        if (class_exists('GSC_Elementor_Privacy_Notices')) {
            foreach (GSC_Elementor_Privacy_Notices::get_forms() as $form) {
                $seen[$form['value']] = true;
                $forms[]              = array(
                    'key'   => $form['value'],
                    'title' => $form['name'] . ('' !== (string) $form['page'] ? ' – ' . $form['page'] : ''),
                    'count' => isset($counts[$form['value']]) ? $counts[$form['value']] : 0,
                );
            }
        }

        // Forms with entries that are no longer found in content.
        foreach ($counts as $key => $total) {
            if (!isset($seen[$key])) {
                $forms[] = array(
                    'key'   => $key,
                    'title' => '' !== (string) $names[$key] ? $names[$key] : $key,
                    'count' => $total,
                );
            }
        }

        return $forms;
    }

    public function get_new_today_count($form = '')
    {
        global $wpdb;

        if (!$this->table_exists()) {
            return 0;
        }

        $table = $this->table();
        return (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE " . $this->where($form) . ' AND DATE(created_at) = %s', current_time('Y-m-d'))
        );
    }

    /**
     * Submission counts bucketed for the selected period.
     *
     * 30days = today and the previous 29 days, one point per day (the Entries Over Time chart);
     * daily = today (1 point); weekly = last 8 weeks; monthly = last 12 months;
     * yearly = last 10 years; last_month = per day.
     *
     * @return array{labels:array,values:array}
     */
    public function get_period_counts($period = '30days', $form = '')
    {
        global $wpdb;

        if (!in_array($period, self::PERIODS, true)) {
            $period = '30days';
        }

        $labels = array();
        $values = array();

        if (!$this->table_exists()) {
            return array('labels' => $labels, 'values' => $values);
        }

        $table = $this->table();
        $where = $this->where($form);
        $today = current_time('Y-m-d');

        // Last 30 days: today plus the previous 29 calendar days, grouped by day in the site's time zone.
        // Days without entries are kept with a value of 0 so the timeline is continuous.
        if ('30days' === $period) {
            $start = gmdate('Y-m-d', strtotime('-29 days', strtotime($today)));
            $rows  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM {$table}
                    WHERE {$where} AND DATE(created_at) BETWEEN %s AND %s
                    GROUP BY d",
                    $start,
                    $today
                ),
                OBJECT
            );
            $counts = array();
            foreach ((array) $rows as $row) {
                $counts[$row->d] = (int) $row->c;
            }
            for ($i = 29; $i >= 0; $i--) {
                $day      = gmdate('Y-m-d', strtotime("-{$i} days", strtotime($today)));
                $labels[] = mysql2date('M j', $day);
                $values[] = isset($counts[$day]) ? $counts[$day] : 0;
            }

            return array('labels' => $labels, 'values' => $values);
        }

        if ('monthly' === $period || 'yearly' === $period) {
            $is_year = 'yearly' === $period;
            $format  = $is_year ? '%Y' : '%Y-%m';
            $rows    = $wpdb->get_results(
                "SELECT DATE_FORMAT(created_at, '{$format}') AS d, COUNT(*) AS c FROM {$table}
                WHERE {$where} AND created_at >= DATE_SUB(CURDATE(), INTERVAL " . ($is_year ? '10 YEAR' : '13 MONTH') . ')
                GROUP BY d',
                OBJECT
            );
            $counts = array();
            foreach ((array) $rows as $row) {
                $counts[$row->d] = (int) $row->c;
            }

            if ($is_year) {
                $year = (int) current_time('Y');
                for ($i = 9; $i >= 0; $i--) {
                    $labels[] = (string) ($year - $i);
                    $values[] = isset($counts[(string) ($year - $i)]) ? $counts[(string) ($year - $i)] : 0;
                }
            } else {
                for ($i = 11; $i >= 0; $i--) {
                    $month    = gmdate('Y-m', strtotime("-{$i} months", strtotime(gmdate('Y-m-01', strtotime($today)))));
                    $labels[] = mysql2date('M Y', $month . '-01');
                    $values[] = isset($counts[$month]) ? $counts[$month] : 0;
                }
            }

            return array('labels' => $labels, 'values' => $values);
        }

        if ('daily' === $period) {
            return array(
                'labels' => array(mysql2date('M j', $today)),
                'values' => array($this->get_new_today_count($form)),
            );
        }

        if ('weekly' === $period) {
            $end   = $today;
            $start = gmdate('Y-m-d', strtotime('-55 days', strtotime($end)));
        } else { // last_month
            $start = gmdate('Y-m-01', strtotime('first day of last month', strtotime($today)));
            $end   = gmdate('Y-m-t', strtotime('last day of last month', strtotime($today)));
        }

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM {$table}
                WHERE {$where} AND DATE(created_at) BETWEEN %s AND %s
                GROUP BY d",
                $start,
                $end
            ),
            OBJECT
        );
        $counts = array();
        foreach ((array) $rows as $row) {
            $counts[$row->d] = (int) $row->c;
        }

        $days = (int) round((strtotime($end) - strtotime($start)) / DAY_IN_SECONDS) + 1;

        if ('weekly' === $period) {
            for ($w = 7; $w >= 0; $w--) {
                $w_end   = gmdate('Y-m-d', strtotime('-' . ($w * 7) . ' days', strtotime($end)));
                $w_start = gmdate('Y-m-d', strtotime('-6 days', strtotime($w_end)));
                $sum     = 0;
                for ($d = 0; $d < 7; $d++) {
                    $day  = gmdate('Y-m-d', strtotime("+{$d} days", strtotime($w_start)));
                    $sum += isset($counts[$day]) ? $counts[$day] : 0;
                }
                $labels[] = mysql2date('M j', $w_start) . ' – ' . mysql2date('M j', $w_end);
                $values[] = $sum;
            }
            return array('labels' => $labels, 'values' => $values);
        }

        for ($i = 0; $i < $days; $i++) {
            $day      = gmdate('Y-m-d', strtotime("+{$i} days", strtotime($start)));
            $labels[] = mysql2date('M j', $day);
            $values[] = isset($counts[$day]) ? $counts[$day] : 0;
        }

        return array('labels' => $labels, 'values' => $values);
    }

    /**
     * JSON payload read by gsc-elementor-charts.js.
     */
    private function chart_payload($status, $series)
    {
        return wp_json_encode(array(
            'labels' => $series['labels'],
            'values' => $series['values'],
            'read'   => $status['read'],
            'unread' => $status['unread'],
            'i18n'   => array(
                'entries' => __('Entries', 'gsheetconnector-for-elementor-forms'),
                'read'    => __('Read', 'gsheetconnector-for-elementor-forms'),
                'unread'  => __('Unread', 'gsheetconnector-for-elementor-forms'),
                'total'   => __('Total', 'gsheetconnector-for-elementor-forms'),
            ),
        ));
    }

    /**
     * AJAX: stat counts + chart data for the period selector.
     */
    public function stats_query()
    {
        check_ajax_referer('gselef-dashboard-stats', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'gsheetconnector-for-elementor-forms')));
            return;
        }

        $period = isset($_POST['period']) ? sanitize_key(wp_unslash($_POST['period'])) : '30days';

        $form   = isset($_POST['form']) ? sanitize_text_field(wp_unslash($_POST['form'])) : '';

        $status = $this->get_status_counts($form);
        $series = $this->get_period_counts($period, $form);

        wp_send_json_success(array(
            'total'     => $status['all'],
            'unread'    => $status['unread'],
            'read'      => $status['read'],
            'new_today' => $this->get_new_today_count($form),
            'labels'    => $series['labels'],
            'values'    => $series['values'],
        ));
    }

    /**
     * Chart.js + chart script, only on the plugin's Dashboard tab.
     */
    public function enqueue_assets($hook)
    {
        $on_plugin_dashboard = false;
        if ('toplevel_page_gsheetconnector-elementor-config' === $hook || (isset($_GET['page']) && 'gsheetconnector-elementor-config' === sanitize_text_field(wp_unslash($_GET['page'])))) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
            $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
            $on_plugin_dashboard = 'dashboard' === $tab;
        }

        if (!$on_plugin_dashboard) {
            return;
        }

        wp_enqueue_script('gselef-chart-js', GS_CONN_ELE_URL . 'assets/js/chart.umd.min.js', array(), '4.4.0', true);
        wp_enqueue_script('gselef-charts-js', GS_CONN_ELE_URL . 'assets/js/gsc-elementor-charts.js', array('gselef-chart-js'), GS_CONN_ELE_VERSION, true);
    }

    /**
     * Render the "Entries Dashboard" block (header, form filter, stat cards, charts).
     */
    public function render_entries_dashboard()
    {
        $status    = $this->get_status_counts();
        $new_today = $this->get_new_today_count();
        $forms     = $this->get_forms_with_entries();
        $series    = $this->get_period_counts('30days');
        $base_url  = admin_url('admin.php?page=e-form-submissions');

        $cards = array(
            array('total', 'gselef-stat-icon-total', 'dashicons-list-view', __('Total Entries', 'gsheetconnector-for-elementor-forms'), $status['all'], true),
            array('unread', 'gselef-stat-icon-unread', 'dashicons-email-alt', __('Unread', 'gsheetconnector-for-elementor-forms'), $status['unread'], true),
            array('read', 'gselef-stat-icon-read', 'dashicons-yes', __('Read', 'gsheetconnector-for-elementor-forms'), $status['read'], true),
            array('new-today', 'gselef-stat-icon-new', 'dashicons-plus-alt2', __('New Today', 'gsheetconnector-for-elementor-forms'), $new_today, false),
        );
        ?>
        <div class="welcome-wrapper w-100 p-40 mb-30 mt-30">
            <div class="gselef-entries-dashboard">
                <div class="welcome-heading mb-20">
                    <span><?php esc_html_e('Entries Dashboard', 'gsheetconnector-for-elementor-forms'); ?></span>
                </div>
                <p class="mb-30"><?php esc_html_e('Track how your Elementor Forms submissions are coming in and which ones still need attention.', 'gsheetconnector-for-elementor-forms'); ?></p>

                <?php if (0 === $status['all']) : ?>
                    <p class="gsc-hint mb-20">
                        <?php esc_html_e('No Elementor form submissions found yet. Entries are read from Elementor Pro’s Submissions list, so keep “Collect Submissions” enabled on your forms.', 'gsheetconnector-for-elementor-forms'); ?>
                    </p>
                <?php endif; ?>

                <div class="gselef-filter-form mb-20">
                    <label for="gselef-entries-filter-form"><?php esc_html_e('Form', 'gsheetconnector-for-elementor-forms'); ?></label>
                    <select id="gselef-entries-filter-form" class="gselef-select">
                        <option value="">
                            <?php
                            /* translators: %s: total entry count across every form. */
                            echo esc_html(sprintf(__('All Forms (%s)', 'gsheetconnector-for-elementor-forms'), number_format_i18n($status['all'])));
                            ?>
                        </option>
                        <?php foreach ($forms as $form) : ?>
                            <option value="<?php echo esc_attr($form['key']); ?>">
                                <?php
                                /* translators: 1: form name, 2: entry count. */
                                echo esc_html(sprintf(__('%1$s (%2$s)', 'gsheetconnector-for-elementor-forms'), $form['title'], number_format_i18n($form['count'])));
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span id="gselef-entries-loader" class="loading d-none" aria-hidden="true"></span>
                </div>

                <input type="hidden" id="gselef-dashboard-stats-nonce" value="<?php echo esc_attr(wp_create_nonce('gselef-dashboard-stats')); ?>">

                <div class="gselef-stat-grid mb-30">
                    <?php foreach ($cards as $card) : ?>
                        <?php if ($card[5]) : ?>
                            <a target="_blank" href="<?php echo esc_url($base_url); ?>" class="text-decoration-none">
                        <?php endif; ?>
                        <div class="gselef-stat-card">
                            <div class="gselef-stat-icon <?php echo esc_attr($card[1]); ?>">
                                <span class="dashicons <?php echo esc_attr($card[2]); ?>"></span>
                            </div>
                            <div class="gselef-stat-body">
                                <div class="gselef-stat-label"><?php echo esc_html($card[3]); ?></div>
                                <div class="gselef-stat-number" id="gselef-stat-<?php echo esc_attr($card[0]); ?>"><?php echo esc_html(number_format_i18n($card[4])); ?></div>
                            </div>
                        </div>
                        <?php if ($card[5]) : ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="gselef-chart-grid mb-30">
                    <div class="gselef-chart-card inner-wrap p-20">
                        <div class="d-flex justify-between align-center flex-wrap gap-10 mb-20">
                            <div class="para-heading fw-600"><?php esc_html_e('Entries Over Time (Last 30 Days)', 'gsheetconnector-for-elementor-forms'); ?></div>
                        </div>
                        <div class="gselef-chart-canvas-wrap">
                            <canvas id="gselef-entries-line-chart" height="110"></canvas>
                            <div class="gselef-chart-loading" id="gselef-line-chart-loading" aria-hidden="true"><span class="gselef-chart-spinner"></span></div>
                        </div>
                    </div>

                    <div class="gselef-chart-card gselef-chart-card-pie inner-wrap p-20">
                        <div class="d-flex justify-between align-center flex-wrap gap-10 mb-20">
                            <div class="para-heading fw-600"><?php esc_html_e('Entries by Status', 'gsheetconnector-for-elementor-forms'); ?></div>
                        </div>
                        <div class="gselef-chart-canvas-wrap">
                            <canvas id="gselef-entries-pie-chart" height="180"></canvas>
                            <div class="gselef-chart-loading" id="gselef-pie-chart-loading" aria-hidden="true"><span class="gselef-chart-spinner"></span></div>
                        </div>
                    </div>
                </div>

                <script type="application/json" id="gselef-dashboard-chart-data"><?php echo $this->chart_payload($status, $series); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output. ?></script>

                <div class="text-right">
                    <a href="<?php echo esc_url($base_url); ?>" target="_blank" class="button gselef-view-all-entries"><?php esc_html_e('View All Entries', 'gsheetconnector-for-elementor-forms'); ?></a>
                </div>
            </div>
        </div>
        <?php
    }
}

GSC_Elementor_Entries_Analytics::instance();
