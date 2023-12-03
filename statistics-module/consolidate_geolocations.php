<?php

function consolidate_geolocations()
{
    global $wpdb;
    $geodir_post_locations = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}geodir_post_locations", OBJECT);
    $geolocations = get_posts(array('post_type' => 'geolocations', 'posts_per_page' => -1));

    $geodir_post_locations_ids = array_map(function ($item) {
        return $item->location_id;
    }, $geodir_post_locations);

    $geolocations_ids = array_map(function ($item) {
        return $item->gd_location_id;
    }, $geolocations);

    create_missing_geolocations($geodir_post_locations_ids, $geolocations_ids, $geodir_post_locations);
}

function create_missing_geolocations($geodir_post_locations_ids, $geolocations_ids, $geodir_post_locations)
{
    $emailoutput = "";

    foreach ($geodir_post_locations_ids as $id) {
        if (!in_array($id, $geolocations_ids)) {

            echo "geodir_post_location id: " . $id . " not found in geolocations_ids";
            $missing_geodir_post_location_city = $geodir_post_locations[array_search($id, array_column($geodir_post_locations, 'location_id'))]->city;
            $missing_geodir_post_location_city_slug = $geodir_post_locations[array_search($id, array_column($geodir_post_locations, 'location_id'))]->city_slug;
            $missing_geodir_post_location_latitude = $geodir_post_locations[array_search($id, array_column($geodir_post_locations, 'location_id'))]->latitude;
            $missing_geodir_post_location_longitude = $geodir_post_locations[array_search($id, array_column($geodir_post_locations, 'location_id'))]->longitude;
            $message = "geodir_post_location id: " . $id . " and name: " .  $missing_geodir_post_location_city   . " not found in geolocations_ids. Creating new geolocation.";
            trigger_error($message, E_USER_WARNING);
            $emailoutput .= $message;
            $new_post = wp_insert_post(array(
                'post_title' => $missing_geodir_post_location_city,
                'post_type' => 'geolocations',
                'post_status' => 'publish',
            ));
            update_post_meta($new_post, 'gd_location_id', $id);
            update_post_meta($new_post, 'gd_location_slug', $missing_geodir_post_location_city_slug);
            update_post_meta($new_post, 'latitude', $missing_geodir_post_location_latitude);
            update_post_meta($new_post, 'longtitude', $missing_geodir_post_location_longitude);
            update_post_meta($new_post, 'display_name', $missing_geodir_post_location_city);
            update_post_meta($new_post, 'gd_place_list', []);
        }
    }

    if ($emailoutput != "") {
        $to = get_option('admin_email');
        $subject = 'Geolocation(s) created';
        $headers = 'From: system@tjekdepot.dk <system@tjekdepot.dk>' . "\r\n";

        wp_mail($to, $subject, $emailoutput, $headers);
    }
}
