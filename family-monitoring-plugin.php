<?php
/**
 * Family Monitoring Plugin - Main File
 * Complete Surveillance & Monitoring System
 * with Camera, Location, Messages, WhatsApp & Screen Recording
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FMP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FMP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FMP_PLUGIN_VERSION', '1.0.0');

// Include required files
require_once FMP_PLUGIN_DIR . 'includes/class-monitoring-plugin.php';
require_once FMP_PLUGIN_DIR . 'includes/admin/class-admin-dashboard.php';
require_once FMP_PLUGIN_DIR . 'includes/api/class-monitoring-api.php';
require_once FMP_PLUGIN_DIR . 'includes/client/class-client-tracker.php';

// Initialize plugin
function fmp_init() {
    $plugin = new FMP_Monitoring_Plugin();
    $plugin->init();
}
add_action('plugins_loaded', 'fmp_init');

// Register settings
function fmp_register_settings() {
    register_setting('fmp_settings_group', 'fmp_enable_camera', array(
        'sanitize_callback' => 'rest_sanitize_boolean',
        'type' => 'boolean'
    ));
    
    register_setting('fmp_settings_group', 'fmp_enable_location', array(
        'sanitize_callback' => 'rest_sanitize_boolean',
        'type' => 'boolean'
    ));
    
    register_setting('fmp_settings_group', 'fmp_enable_messages', array(
        'sanitize_callback' => 'rest_sanitize_boolean',
        'type' => 'boolean'
    ));
    
    register_setting('fmp_settings_group', 'fmp_enable_screen_recording', array(
        'sanitize_callback' => 'rest_sanitize_boolean',
        'type' => 'boolean'
    ));
    
    register_setting('fmp_settings_group', 'fmp_enable_whatsapp', array(
        'sanitize_callback' => 'rest_sanitize_boolean',
        'type' => 'boolean'
    ));

    add_settings_section(
        'fmp_main_settings',
        'Family Monitoring Settings',
        'fmp_settings_section_callback',
        'fmp_settings'
    );
}
add_action('admin_init', 'fmp_register_settings');

function fmp_settings_section_callback() {
    echo '<p style="color: #d63638;"><strong>⚠️ IMPORTANT:</strong> All monitored users must provide explicit consent before monitoring begins.</p>';
}

// Activation hook
register_activation_hook(__FILE__, 'fmp_activate_plugin');
function fmp_activate_plugin() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Monitored Users Table
    $users_table = $wpdb->prefix . 'fmp_monitored_users';
    $sql_users = "CREATE TABLE IF NOT EXISTS $users_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        device_id VARCHAR(255) UNIQUE NOT NULL,
        device_name VARCHAR(255),
        device_type VARCHAR(50),
        os VARCHAR(100),
        browser VARCHAR(100),
        consent_given TINYINT(1) DEFAULT 0,
        consent_date DATETIME,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_seen DATETIME,
        KEY user_id (user_id),
        KEY device_id (device_id),
        KEY is_active (is_active)
    ) $charset_collate;";

    // Camera Captures Table
    $camera_table = $wpdb->prefix . 'fmp_camera_captures';
    $sql_camera = "CREATE TABLE IF NOT EXISTS $camera_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        device_id VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        image_url VARCHAR(500) NOT NULL,
        image_path VARCHAR(500),
        capture_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY device_id (device_id),
        KEY user_id (user_id),
        KEY capture_time (capture_time)
    ) $charset_collate;";

    // Location Data Table
    $location_table = $wpdb->prefix . 'fmp_locations';
    $sql_location = "CREATE TABLE IF NOT EXISTS $location_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        device_id VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        accuracy FLOAT,
        address VARCHAR(500),
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY device_id (device_id),
        KEY user_id (user_id),
        KEY timestamp (timestamp)
    ) $charset_collate;";

    // Messages Table
    $messages_table = $wpdb->prefix . 'fmp_messages';
    $sql_messages = "CREATE TABLE IF NOT EXISTS $messages_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        device_id VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        message_content LONGTEXT,
        sender VARCHAR(255),
        recipient VARCHAR(255),
        message_type VARCHAR(50),
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY device_id (device_id),
        KEY user_id (user_id),
        KEY timestamp (timestamp)
    ) $charset_collate;";

    // WhatsApp Messages Table
    $whatsapp_table = $wpdb->prefix . 'fmp_whatsapp_messages';
    $sql_whatsapp = "CREATE TABLE IF NOT EXISTS $whatsapp_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        device_id VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        whatsapp_number VARCHAR(20),
        message_content LONGTEXT,
        sender VARCHAR(255),
        recipient VARCHAR(255),
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY device_id (device_id),
        KEY user_id (user_id),
        KEY timestamp (timestamp)
    ) $charset_collate;";

    // Screen Recordings Table
    $recording_table = $wpdb->prefix . 'fmp_screen_recordings';
    $sql_recording = "CREATE TABLE IF NOT EXISTS $recording_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        device_id VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        recording_url VARCHAR(500),
        recording_path VARCHAR(500),
        duration INT,
        file_size BIGINT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY device_id (device_id),
        KEY user_id (user_id),
        KEY timestamp (timestamp)
    ) $charset_collate;";

    // Activity Logs Table
    $logs_table = $wpdb->prefix . 'fmp_activity_logs';
    $sql_logs = "CREATE TABLE IF NOT EXISTS $logs_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        device_id VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        activity_type VARCHAR(100),
        activity_description LONGTEXT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY device_id (device_id),
        KEY user_id (user_id),
        KEY activity_type (activity_type),
        KEY timestamp (timestamp)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_users);
    dbDelta($sql_camera);
    dbDelta($sql_location);
    dbDelta($sql_messages);
    dbDelta($sql_whatsapp);
    dbDelta($sql_recording);
    dbDelta($sql_logs);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'fmp_deactivate_plugin');
function fmp_deactivate_plugin() {
    // Cleanup
}
