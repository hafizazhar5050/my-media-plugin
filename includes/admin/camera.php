<?php
/**
 * Camera Feed View
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$camera_table = $wpdb->prefix . 'fmp_camera_captures';
$cameras = $wpdb->get_results("SELECT * FROM $camera_table ORDER BY capture_time DESC LIMIT 50");
?>

<div class="wrap">
    <h1>📷 Camera Feed</h1>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;margin:20px 0;">
        <?php
        foreach ($cameras as $camera) {
            echo '<div style="border:1px solid #ddd;border-radius:5px;overflow:hidden;background:#f9f9f9;">';
            echo '<img src="' . esc_url($camera->image_url) . '" style="width:100%;height:300px;object-fit:cover;" alt="Camera" />';
            echo '<div style="padding:15px;">';
            echo '<p><strong>Device:</strong> ' . esc_html(substr($camera->device_id, 0, 16)) . '...</p>';
            echo '<p><strong>Time:</strong> ' . esc_html($camera->capture_time) . '</p>';
            echo '<a href="' . esc_url($camera->image_url) . '" class="button button-primary" target="_blank">Full View</a>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>
