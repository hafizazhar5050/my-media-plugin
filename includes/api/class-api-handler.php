<?php
/**
 * REST API Handler Class
 */

class MMP_API_Handler {

    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    public static function register_routes() {
        // Messages endpoint
        register_rest_route('mmp/v1', '/messages', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_messages'),
            'permission_callback' => array(__CLASS__, 'check_permission')
        ));

        register_rest_route('mmp/v1', '/messages', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'create_message'),
            'permission_callback' => array(__CLASS__, 'check_permission')
        ));

        // Media endpoint
        register_rest_route('mmp/v1', '/media', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_media'),
            'permission_callback' => array(__CLASS__, 'check_permission')
        ));

        register_rest_route('mmp/v1', '/media', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'upload_media'),
            'permission_callback' => array(__CLASS__, 'check_permission')
        ));

        // Streaming endpoint
        register_rest_route('mmp/v1', '/streams', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_streams'),
            'permission_callback' => array(__CLASS__, 'check_permission')
        ));

        register_rest_route('mmp/v1', '/streams', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'start_stream'),
            'permission_callback' => array(__CLASS__, 'check_permission')
        ));
    }

    public static function check_permission() {
        return is_user_logged_in();
    }

    public static function get_messages(WP_REST_Request $request) {
        global $wpdb;
        $table = $wpdb->prefix . 'mmp_messages';
        $user_id = get_current_user_id();

        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d OR recipient_id = %d ORDER BY created_at DESC LIMIT 50",
            $user_id,
            $user_id
        ));

        return rest_ensure_response($messages);
    }

    public static function create_message(WP_REST_Request $request) {
        global $wpdb;
        $table = $wpdb->prefix . 'mmp_messages';
        $params = $request->get_json_params();

        $wpdb->insert($table, array(
            'user_id' => get_current_user_id(),
            'message_type' => sanitize_text_field($params['message_type']),
            'message_content' => sanitize_textarea_field($params['message_content']),
            'recipient_id' => intval($params['recipient_id']),
        ));

        return rest_ensure_response(array('success' => true, 'message_id' => $wpdb->insert_id));
    }

    public static function get_media(WP_REST_Request $request) {
        global $wpdb;
        $table = $wpdb->prefix . 'mmp_media_files';
        $user_id = get_current_user_id();

        $media = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT 100",
            $user_id
        ));

        return rest_ensure_response($media);
    }

    public static function upload_media(WP_REST_Request $request) {
        global $wpdb;
        $table = $wpdb->prefix . 'mmp_media_files';
        $params = $request->get_json_params();

        $upload_dir = wp_upload_dir();
        $mmp_dir = $upload_dir['basedir'] . '/mmp-media/';

        if (!file_exists($mmp_dir)) {
            mkdir($mmp_dir, 0755, true);
        }

        $filename = 'media-' . time() . '-' . uniqid() . '.png';
        $filepath = $mmp_dir . $filename;

        $image_data = str_replace('data:image/png;base64,', '', $params['image']);
        $image_data = base64_decode($image_data);

        if (file_put_contents($filepath, $image_data)) {
            $wpdb->insert($table, array(
                'user_id' => get_current_user_id(),
                'file_type' => 'image',
                'file_url' => $upload_dir['baseurl'] . '/mmp-media/' . $filename,
                'file_path' => $filepath,
                'capture_type' => sanitize_text_field($params['capture_type']),
            ));

            return rest_ensure_response(array(
                'success' => true,
                'media_id' => $wpdb->insert_id,
                'url' => $upload_dir['baseurl'] . '/mmp-media/' . $filename
            ));
        }

        return new WP_Error('upload_failed', 'Failed to upload media', array('status' => 500));
    }

    public static function get_streams(WP_REST_Request $request) {
        global $wpdb;
        $table = $wpdb->prefix . 'mmp_streaming_sessions';
        $user_id = get_current_user_id();

        $streams = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY started_at DESC",
            $user_id
        ));

        return rest_ensure_response($streams);
    }

    public static function start_stream(WP_REST_Request $request) {
        global $wpdb;
        $table = $wpdb->prefix . 'mmp_streaming_sessions';
        $params = $request->get_json_params();

        $wpdb->insert($table, array(
            'user_id' => get_current_user_id(),
            'session_title' => sanitize_text_field($params['title']),
            'is_active' => 1,
        ));

        return rest_ensure_response(array(
            'success' => true,
            'session_id' => $wpdb->insert_id,
            'message' => 'Stream started successfully'
        ));
    }
}

// Initialize REST API
add_action('rest_api_init', array('MMP_API_Handler', 'init'));
