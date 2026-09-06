<?php
/**
 * Main Plugin Class
 */

class MMP_Media_Plugin {

    public function init() {
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add shortcodes
        add_shortcode('media-capture', array($this, 'media_capture_shortcode'));
        add_shortcode('live-stream', array($this, 'live_stream_shortcode'));
        add_shortcode('media-gallery', array($this, 'media_gallery_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_capture_media', array($this, 'ajax_capture_media'));
        add_action('wp_ajax_get_messages', array($this, 'ajax_get_messages'));
        add_action('wp_ajax_send_message', array($this, 'ajax_send_message'));
        add_action('wp_ajax_start_stream', array($this, 'ajax_start_stream'));
        add_action('wp_ajax_get_gallery', array($this, 'ajax_get_gallery'));
        add_action('wp_ajax_whatsapp_send', array($this, 'ajax_whatsapp_send'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style('mmp-frontend-style', MMP_PLUGIN_URL . 'assets/css/frontend.css', array(), MMP_PLUGIN_VERSION);
        wp_enqueue_script('mmp-frontend-script', MMP_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), MMP_PLUGIN_VERSION, true);
        
        // Localize script with AJAX URL
        wp_localize_script('mmp-frontend-script', 'mmpAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mmp_nonce'),
            'userId' => get_current_user_id()
        ));
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style('mmp-admin-style', MMP_PLUGIN_URL . 'assets/css/admin.css', array(), MMP_PLUGIN_VERSION);
        wp_enqueue_script('mmp-admin-script', MMP_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MMP_PLUGIN_VERSION, true);
    }

    public function add_admin_menu() {
        add_menu_page(
            'My Media Plugin',
            'Media Plugin',
            'manage_options',
            'mmp-dashboard',
            array($this, 'render_admin_dashboard'),
            'dashicons-camera',
            30
        );
    }

    public function render_admin_dashboard() {
        include MMP_PLUGIN_DIR . 'includes/admin/dashboard.php';
    }

    // Shortcode: Media Capture
    public function media_capture_shortcode($atts) {
        ob_start();
        ?>
        <div class="mmp-media-capture-container">
            <h3>Media Capture</h3>
            <div id="mmp-camera-preview"></div>
            <button id="mmp-capture-btn" class="btn btn-primary">Take Photo</button>
            <button id="mmp-screenshot-btn" class="btn btn-secondary">Take Screenshot</button>
        </div>
        <?php
        return ob_get_clean();
    }

    // Shortcode: Live Stream
    public function live_stream_shortcode($atts) {
        ob_start();
        ?>
        <div class="mmp-live-stream-container">
            <h3>Live Streaming</h3>
            <div id="mmp-stream-preview"></div>
            <button id="mmp-start-stream-btn" class="btn btn-success">Start Stream</button>
            <div id="mmp-viewers-count">Viewers: 0</div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Shortcode: Media Gallery
    public function media_gallery_shortcode($atts) {
        ob_start();
        ?>
        <div class="mmp-gallery-container">
            <h3>My Media Gallery</h3>
            <div id="mmp-gallery-grid"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    // AJAX: Capture Media
    public function ajax_capture_media() {
        check_ajax_referer('mmp_nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $capture_type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'photo';
        $image_data = isset($_POST['image']) ? sanitize_text_field($_POST['image']) : '';

        if (empty($image_data)) {
            wp_send_json_error('No image data');
        }

        // Save media to database
        global $wpdb;
        $table = $wpdb->prefix . 'mmp_media_files';
        
        $uploaded = $this->save_media_file($image_data, $capture_type);
        
        if ($uploaded) {
            $wpdb->insert($table, array(
                'user_id' => get_current_user_id(),
                'file_type' => 'image',
                'file_url' => $uploaded['url'],
                'file_path' => $uploaded['path'],
                'capture_type' => $capture_type,
            ));
            wp_send_json_success(array('message' => 'Media captured successfully', 'url' => $uploaded['url']));
        } else {
            wp_send_json_error('Failed to save media');
        }
    }

    // AJAX: Get Messages
    public function ajax_get_messages() {
        check_ajax_referer('mmp_nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mmp_messages';
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d OR recipient_id = %d ORDER BY created_at DESC LIMIT 50",
            get_current_user_id(),
            get_current_user_id()
        ));

        wp_send_json_success($messages);
    }

    // AJAX: Send Message
    public function ajax_send_message() {
        check_ajax_referer('mmp_nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $message_content = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
        $recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;

        if (empty($message_content)) {
            wp_send_json_error('Message is empty');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mmp_messages';
        
        $inserted = $wpdb->insert($table, array(
            'user_id' => get_current_user_id(),
            'message_type' => 'text',
            'message_content' => $message_content,
            'recipient_id' => $recipient_id,
        ));

        if ($inserted) {
            wp_send_json_success(array('message' => 'Message sent successfully'));
        } else {
            wp_send_json_error('Failed to send message');
        }
    }

    // AJAX: Start Stream
    public function ajax_start_stream() {
        check_ajax_referer('mmp_nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $session_title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : 'Live Stream';

        global $wpdb;
        $table = $wpdb->prefix . 'mmp_streaming_sessions';
        
        $inserted = $wpdb->insert($table, array(
            'user_id' => get_current_user_id(),
            'session_title' => $session_title,
            'is_active' => 1,
        ));

        if ($inserted) {
            $session_id = $wpdb->insert_id;
            wp_send_json_success(array('session_id' => $session_id, 'message' => 'Stream started'));
        } else {
            wp_send_json_error('Failed to start stream');
        }
    }

    // AJAX: Get Gallery
    public function ajax_get_gallery() {
        check_ajax_referer('mmp_nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mmp_media_files';
        $media = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT 100",
            get_current_user_id()
        ));

        wp_send_json_success($media);
    }

    // AJAX: WhatsApp Send
    public function ajax_whatsapp_send() {
        check_ajax_referer('mmp_nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        if (empty($phone) || empty($message)) {
            wp_send_json_error('Phone or message is empty');
        }

        // WhatsApp API integration
        $whatsapp_result = $this->send_whatsapp_message($phone, $message);

        if ($whatsapp_result) {
            wp_send_json_success(array('message' => 'WhatsApp message sent successfully'));
        } else {
            wp_send_json_error('Failed to send WhatsApp message');
        }
    }

    // Helper: Save Media File
    private function save_media_file($image_data, $type) {
        $upload_dir = wp_upload_dir();
        $mmp_dir = $upload_dir['basedir'] . '/mmp-media/';
        
        if (!file_exists($mmp_dir)) {
            mkdir($mmp_dir, 0755, true);
        }

        $filename = 'media-' . time() . '-' . uniqid() . '.png';
        $filepath = $mmp_dir . $filename;
        
        // Decode base64 image
        $image_data = str_replace('data:image/png;base64,', '', $image_data);
        $image_data = base64_decode($image_data);
        
        if (file_put_contents($filepath, $image_data)) {
            return array(
                'url' => $upload_dir['baseurl'] . '/mmp-media/' . $filename,
                'path' => $filepath
            );
        }
        
        return false;
    }

    // Helper: Send WhatsApp Message
    private function send_whatsapp_message($phone, $message) {
        // Integration with WhatsApp Business API or Twilio
        // This is a placeholder - you'll need to configure with actual API
        $api_key = get_option('mmp_whatsapp_api_key');
        
        if (empty($api_key)) {
            return false;
        }

        // Example using Twilio
        // $twilio = new Twilio\Rest\Client($account_sid, $auth_token);
        // $message = $twilio->messages->create("+1" . $phone, array("body" => $message, "from" => $twilio_number));
        
        return true; // Placeholder
    }
}
