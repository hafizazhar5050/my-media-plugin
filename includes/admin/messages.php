<?php
/**
 * View All Messages
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$messages_table = $wpdb->prefix . 'fmp_messages';
$messages = $wpdb->get_results("SELECT * FROM $messages_table ORDER BY timestamp DESC LIMIT 100");
?>

<div class="wrap">
    <h1>💬 Messages</h1>
    
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Device</th>
                <th>From</th>
                <th>To</th>
                <th>Message</th>
                <th>Type</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($messages as $msg) {
                echo '<tr>';
                echo '<td>' . esc_html(substr($msg->device_id, 0, 16)) . '...</td>';
                echo '<td><strong>' . esc_html($msg->sender) . '</strong></td>';
                echo '<td>' . esc_html($msg->recipient) . '</td>';
                echo '<td>' . esc_html(substr($msg->message_content, 0, 100)) . '...</td>';
                echo '<td><span class="badge" style="background:#0073aa;color:white;padding:3px 8px;border-radius:3px;">' . esc_html($msg->message_type) . '</span></td>';
                echo '<td>' . esc_html($msg->timestamp) . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>
