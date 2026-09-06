<?php
/**
 * WhatsApp Messages
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$whatsapp_table = $wpdb->prefix . 'fmp_whatsapp_messages';
$messages = $wpdb->get_results("SELECT * FROM $whatsapp_table ORDER BY timestamp DESC LIMIT 100");
?>

<div class="wrap">
    <h1>💚 WhatsApp Messages</h1>
    
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Device</th>
                <th>WhatsApp Number</th>
                <th>From</th>
                <th>To</th>
                <th>Message</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($messages as $msg) {
                echo '<tr>';
                echo '<td>' . esc_html(substr($msg->device_id, 0, 16)) . '...</td>';
                echo '<td>' . esc_html($msg->whatsapp_number) . '</td>';
                echo '<td><strong>' . esc_html($msg->sender) . '</strong></td>';
                echo '<td>' . esc_html($msg->recipient) . '</td>';
                echo '<td>' . esc_html(substr($msg->message_content, 0, 100)) . '...</td>';
                echo '<td>' . esc_html($msg->timestamp) . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>
