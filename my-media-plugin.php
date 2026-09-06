<?php
/**
 * Plugin Name: My Media Plugin
 * Plugin URI: https://github.com/hafizazhar5050/my-media-plugin
 * Description: Comprehensive WordPress Plugin for Camera Access, Live Streaming, Screenshots, Messages, WhatsApp Integration, and Media Gallery
 * Version: 1.0.0
 * Author: Hafiz Azhar
 * Author URI: https://github.com/hafizazhar5050
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: my-media-plugin
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MMP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MMP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MMP_PLUGIN_VERSION', '1.0.0');

// Include required files
require_once MMP_PLUGIN_DIR . 'includes/class-media-plugin.php';
require_once MMP_PLUGIN_DIR . 'includes/admin/class-admin-page.php';
require_once MMP_PLUGIN_DIR . 'includes/api/class-api-handler.php';

// Initialize plugin
function mmp_init() {
    $plugin = new MMP_Media_Plugin();
    $plugin->init();
}
add_action('plugins_loaded', 'mmp_init');

// Activation hook
register_activation_hook(__FILE__, 'mmp_activate_plugin');
function mmp_activate_plugin() {
    global $wpdb;
    
    // Create custom database tables
    $charset_collate = $wpdb->get_charset_collate();
    
    // Messages table
    $messages_table = $wpdb->prefix . 'mmp_messages';
    $sql_messages = "CREATE TABLE IF NOT EXISTS $messages_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        message_type VARCHAR(50) NOT NULL,
        message_content LONGTEXT NOT NULL,
        media_url VARCHAR(500),
        recipient_id BIGINT(20) UNSIGNED,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY user_id (user_id),
        KEY message_type (message_type),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    // Media files table
    $media_table = $wpdb->prefix . 'mmp_media_files';
    $sql_media = "CREATE TABLE IF NOT EXISTS $media_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        file_url VARCHAR(500) NOT NULL,
        file_path VARCHAR(500),
        capture_type VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY user_id (user_id),
        KEY file_type (file_type),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    // Live streaming sessions table
    $streaming_table = $wpdb->prefix . 'mmp_streaming_sessions';
    $sql_streaming = "CREATE TABLE IF NOT EXISTS $streaming_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        session_title VARCHAR(255),
        stream_url VARCHAR(500),
        is_active TINYINT(1) DEFAULT 1,
        started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        ended_at DATETIME,
        viewers_count INT DEFAULT 0,
        KEY user_id (user_id),
        KEY is_active (is_active)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_messages);
    dbDelta($sql_media);
    dbDelta($sql_streaming);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'mmp_deactivate_plugin');
function mmp_deactivate_plugin() {
    // Clean up options if needed
    delete_option('mmp_settings');
}
