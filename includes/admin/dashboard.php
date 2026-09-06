<?php
/**
 * Admin Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check user capability
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access');
}

MMP_Admin_Page::render_dashboard();
