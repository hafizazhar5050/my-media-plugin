<?php
/**
 * Activity Logs
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$logs_table = $wpdb->prefix . 'fmp_activity_logs';
$logs = $wpdb->get_results("SELECT * FROM $logs_table ORDER BY timestamp DESC LIMIT 200");
?>

<div class="wrap">
    <h1>📊 Activity Logs</h1>
    
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Device</th>
                <th>Activity Type</th>
                <th>Description</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($logs as $log) {
                echo '<tr>';
                echo '<td>' . esc_html(substr($log->device_id, 0, 16)) . '...</td>';
                echo '<td><span class="badge" style="background:#007cba;color:white;padding:3px 8px;border-radius:3px;">' . esc_html($log->activity_type) . '</span></td>';
                echo '<td>' . esc_html(substr($log->activity_description, 0, 100)) . '...</td>';
                echo '<td>' . esc_html($log->timestamp) . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>
