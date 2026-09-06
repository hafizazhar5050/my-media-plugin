<?php
/**
 * Locations Map View
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

global $wpdb;
$location_table = $wpdb->prefix . 'fmp_locations';
$locations = $wpdb->get_results("SELECT * FROM $location_table ORDER BY timestamp DESC LIMIT 100");
?>

<div class="wrap">
    <h1>🗺️ Device Locations</h1>
    
    <div id="fmp-map" style="height:600px;border:1px solid #ddd;border-radius:5px;margin:20px 0;"></div>
    
    <h2>Location History</h2>
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Device</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Address</th>
                <th>Accuracy</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($locations as $location) {
                echo '<tr>';
                echo '<td>' . esc_html(substr($location->device_id, 0, 16)) . '...</td>';
                echo '<td>' . esc_html(number_format($location->latitude, 6)) . '</td>';
                echo '<td>' . esc_html(number_format($location->longitude, 6)) . '</td>';
                echo '<td>' . esc_html($location->address) . '</td>';
                echo '<td>' . esc_html($location->accuracy . ' m') . '</td>';
                echo '<td>' . esc_html($location->timestamp) . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY"></script>
    <script>
    var map = new google.maps.Map(document.getElementById('fmp-map'), {
        zoom: 10,
        center: {lat: 24.8607, lng: 67.0011}
    });
    
    var locations = <?php echo json_encode($locations); ?>;
    locations.forEach(function(location) {
        new google.maps.Marker({
            position: {lat: parseFloat(location.latitude), lng: parseFloat(location.longitude)},
            map: map,
            title: location.address
        });
    });
    </script>
</div>
