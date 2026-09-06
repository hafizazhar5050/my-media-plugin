<?php
/**
 * Manage Monitored Devices
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$users_table = $wpdb->prefix . 'fmp_monitored_users';

// Handle device approval
if (isset($_POST['approve_device'])) {
    $device_id = sanitize_text_field($_POST['device_id']);
    $wpdb->update($users_table, array('consent_given' => 1, 'consent_date' => current_time('mysql')), array('device_id' => $device_id));
}

// Handle device deactivation
if (isset($_POST['deactivate_device'])) {
    $device_id = sanitize_text_field($_POST['device_id']);
    $wpdb->update($users_table, array('is_active' => 0), array('device_id' => $device_id));
}

$devices = $wpdb->get_results("SELECT * FROM $users_table ORDER BY created_at DESC");
?>

<div class="wrap">
    <h1>Monitored Devices</h1>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Device ID</th>
                <th>Device Name</th>
                <th>Type</th>
                <th>OS</th>
                <th>Browser</th>
                <th>Consent</th>
                <th>Status</th>
                <th>Last Seen</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($devices as $device) {
                echo '<tr>';
                echo '<td><code>' . esc_html(substr($device->device_id, 0, 16)) . '...</code></td>';
                echo '<td>' . esc_html($device->device_name) . '</td>';
                echo '<td>' . esc_html($device->device_type) . '</td>';
                echo '<td>' . esc_html($device->os) . '</td>';
                echo '<td>' . esc_html($device->browser) . '</td>';
                $consent_badge = $device->consent_given ? '<span class="badge" style="background:green;color:white;padding:5px 10px;border-radius:3px;">✓ Approved</span>' : '<span class="badge" style="background:orange;color:white;padding:5px 10px;border-radius:3px;">⏳ Pending</span>';
                echo '<td>' . $consent_badge . '</td>';
                $status_badge = $device->is_active ? '<span class="badge" style="background:blue;color:white;padding:5px 10px;border-radius:3px;">Active</span>' : '<span class="badge" style="background:red;color:white;padding:5px 10px;border-radius:3px;">Inactive</span>';
                echo '<td>' . $status_badge . '</td>';
                echo '<td>' . esc_html($device->last_seen ?? 'Never') . '</td>';
                echo '<td>';
                if (!$device->consent_given) {
                    echo '<form method="post" style="display:inline;">';
                    echo '<input type="hidden" name="device_id" value="' . esc_attr($device->device_id) . '">';
                    echo '<button type="submit" name="approve_device" class="button button-primary">Approve</button>';
                    echo '</form>';
                }
                if ($device->is_active) {
                    echo '<form method="post" style="display:inline;">';
                    echo '<input type="hidden" name="device_id" value="' . esc_attr($device->device_id) . '">';
                    echo '<button type="submit" name="deactivate_device" class="button button-danger" onclick="return confirm(\'Are you sure?\');">Deactivate</button>';
                    echo '</form>';
                }
                echo '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>
