<?php
/**
 * Plugin Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}
?>

<div class="wrap">
    <h1>⚙️ Family Guard Settings</h1>
    
    <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:5px;padding:15px;margin:20px 0;">
        <p><strong>⚠️ IMPORTANT NOTICE:</strong></p>
        <p>This is a powerful monitoring tool. Ensure all monitored users have given explicit consent before enabling monitoring. Unauthorized surveillance may be illegal in your jurisdiction.</p>
    </div>
    
    <form method="post" action="options.php">
        <?php settings_fields('fmp_settings_group'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">Enable Camera Monitoring</th>
                <td>
                    <input type="checkbox" name="fmp_enable_camera" value="1" <?php checked(get_option('fmp_enable_camera'), 1); ?> />
                    <p class="description">Allow monitoring of camera feeds</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Enable Location Tracking</th>
                <td>
                    <input type="checkbox" name="fmp_enable_location" value="1" <?php checked(get_option('fmp_enable_location'), 1); ?> />
                    <p class="description">Allow GPS location tracking</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Enable Message Monitoring</th>
                <td>
                    <input type="checkbox" name="fmp_enable_messages" value="1" <?php checked(get_option('fmp_enable_messages'), 1); ?> />
                    <p class="description">Monitor SMS and other messages</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Enable Screen Recording</th>
                <td>
                    <input type="checkbox" name="fmp_enable_screen_recording" value="1" <?php checked(get_option('fmp_enable_screen_recording'), 1); ?> />
                    <p class="description">Record screen activity</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Enable WhatsApp Monitoring</th>
                <td>
                    <input type="checkbox" name="fmp_enable_whatsapp" value="1" <?php checked(get_option('fmp_enable_whatsapp'), 1); ?> />
                    <p class="description">Monitor WhatsApp conversations</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
