<?php
/**
 * Screen Recordings
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$recording_table = $wpdb->prefix . 'fmp_screen_recordings';
$recordings = $wpdb->get_results("SELECT * FROM $recording_table ORDER BY timestamp DESC LIMIT 50");
?>

<div class="wrap">
    <h1>🎬 Screen Recordings</h1>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(400px,1fr));gap:20px;margin:20px 0;">
        <?php
        foreach ($recordings as $recording) {
            echo '<div style="border:1px solid #ddd;border-radius:5px;padding:15px;background:#f9f9f9;">';
            echo '<video width="100%" height="300" controls style="border-radius:5px;">';
            echo '<source src="' . esc_url($recording->recording_url) . '" type="video/webm">';
            echo 'Your browser does not support the video tag.';
            echo '</video>';
            echo '<div style="margin-top:10px;">';
            echo '<p><strong>Device:</strong> ' . esc_html(substr($recording->device_id, 0, 16)) . '...</p>';
            echo '<p><strong>Duration:</strong> ' . esc_html($recording->duration) . ' seconds</p>';
            echo '<p><strong>Size:</strong> ' . esc_html(size_format($recording->file_size)) . '</p>';
            echo '<p><strong>Time:</strong> ' . esc_html($recording->timestamp) . '</p>';
            echo '<a href="' . esc_url($recording->recording_url) . '" class="button button-primary" download>Download</a>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>
