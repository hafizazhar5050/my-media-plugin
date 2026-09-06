<?php
/**
 * Admin Page Class
 */

class MMP_Admin_Page {

    public static function render_dashboard() {
        global $wpdb;
        
        $messages_table = $wpdb->prefix . 'mmp_messages';
        $media_table = $wpdb->prefix . 'mmp_media_files';
        $streaming_table = $wpdb->prefix . 'mmp_streaming_sessions';
        
        $total_messages = $wpdb->get_var("SELECT COUNT(*) FROM $messages_table");
        $total_media = $wpdb->get_var("SELECT COUNT(*) FROM $media_table");
        $active_streams = $wpdb->get_var("SELECT COUNT(*) FROM $streaming_table WHERE is_active = 1");
        
        ?>
        <div class="wrap">
            <h1>My Media Plugin Dashboard</h1>
            
            <div class="mmp-dashboard-stats">
                <div class="stat-box">
                    <h3>Total Messages</h3>
                    <p class="stat-number"><?php echo $total_messages; ?></p>
                </div>
                
                <div class="stat-box">
                    <h3>Media Files</h3>
                    <p class="stat-number"><?php echo $total_media; ?></p>
                </div>
                
                <div class="stat-box">
                    <h3>Active Streams</h3>
                    <p class="stat-number"><?php echo $active_streams; ?></p>
                </div>
            </div>

            <h2>Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('mmp_settings_group'); ?>
                <?php do_settings_sections('mmp_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">WhatsApp API Key</th>
                        <td>
                            <input type="password" name="mmp_whatsapp_api_key" value="<?php echo get_option('mmp_whatsapp_api_key'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">WhatsApp Business Phone Number</th>
                        <td>
                            <input type="text" name="mmp_whatsapp_phone" value="<?php echo get_option('mmp_whatsapp_phone'); ?>" placeholder="+1234567890" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable Live Streaming</th>
                        <td>
                            <input type="checkbox" name="mmp_enable_streaming" value="1" <?php checked(get_option('mmp_enable_streaming'), 1); ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable Screenshots</th>
                        <td>
                            <input type="checkbox" name="mmp_enable_screenshots" value="1" <?php checked(get_option('mmp_enable_screenshots'), 1); ?> />
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>

            <h2>Recent Messages</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Content</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $messages = $wpdb->get_results("SELECT * FROM $messages_table ORDER BY created_at DESC LIMIT 10");
                    foreach ($messages as $msg) {
                        $user = get_userdata($msg->user_id);
                        echo '<tr>';
                        echo '<td>' . $user->user_login . '</td>';
                        echo '<td>' . ucfirst($msg->message_type) . '</td>';
                        echo '<td>' . substr($msg->message_content, 0, 50) . '...</td>';
                        echo '<td>' . $msg->created_at . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>

            <h2>Recent Media Files</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Capture Type</th>
                        <th>Date</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $media_files = $wpdb->get_results("SELECT * FROM $media_table ORDER BY created_at DESC LIMIT 10");
                    foreach ($media_files as $media) {
                        $user = get_userdata($media->user_id);
                        echo '<tr>';
                        echo '<td>' . $user->user_login . '</td>';
                        echo '<td>' . ucfirst($media->file_type) . '</td>';
                        echo '<td>' . ucfirst($media->capture_type) . '</td>';
                        echo '<td>' . $media->created_at . '</td>';
                        echo '<td><a href="' . $media->file_url . '" target="_blank">View</a></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>

            <h2>Live Streams</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Viewers</th>
                        <th>Started</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $streams = $wpdb->get_results("SELECT * FROM $streaming_table ORDER BY started_at DESC LIMIT 10");
                    foreach ($streams as $stream) {
                        $user = get_userdata($stream->user_id);
                        $status = $stream->is_active ? 'Active' : 'Ended';
                        echo '<tr>';
                        echo '<td>' . $user->user_login . '</td>';
                        echo '<td>' . $stream->session_title . '</td>';
                        echo '<td><span class="badge ' . ($stream->is_active ? 'badge-success' : 'badge-danger') . '">' . $status . '</span></td>';
                        echo '<td>' . $stream->viewers_count . '</td>';
                        echo '<td>' . $stream->started_at . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
