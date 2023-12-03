<?php

function consolidate_geolocations()
{
    global $wpdb;
    $geodir_post_locations = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}geodir_post_locations", OBJECT);
    $geodir_post_neighbourhoods = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}geodir_post_neighbourhood", OBJECT);
    $geolocations = get_posts(array('post_type' => 'geolocations', 'posts_per_page' => -1));

    if (empty($geodir_post_locations) || empty($geodir_post_neighbourhoods)) {
        trigger_error("No geodir_post_locations or geodir_post_neighbourhoods found", E_USER_WARNING);
        return;
    }

    if (empty($geolocations)) {
        trigger_error("No geolocations found", E_USER_WARNING);
        return;
    }

    $geodir_post_locations_ids = array_map(function ($item) {
        return $item->location_id;
    }, $geodir_post_locations);

    $geodir_post_neighbourhoods_ids = array_map(function ($item) {
        return $item->hood_id;
    }, $geodir_post_neighbourhoods);

    $geolocations_ids = array_map(function ($item) {
        return $item->gd_location_id;
    }, $geolocations);

    create_missing_geolocations($geodir_post_locations_ids, $geodir_post_neighbourhoods_ids, $geolocations_ids, $geodir_post_locations, $geodir_post_neighbourhoods);
    find_duplicate_geolocations($geolocations);
    geolocations_sanity_check($geodir_post_locations, $geodir_post_neighbourhoods, $geolocations);
}

function geolocations_sanity_check($geodir_post_locations, $geodir_post_neighbourhoods, $geolocations)
{
    $emailoutput = "";

    // var_dump($geodir_post_neighbourhoods);

    //check if geolocation post title matches display_name
    foreach ($geolocations as $geolocation) {
        if ($geolocation->post_title !== $geolocation->display_name) {
            $message = "Geolocation title: " . $geolocation->post_title . " does not match own display_name: " . $geolocation->display_name . "<br>";
            trigger_error($message, E_USER_WARNING);
            $emailoutput .= $message;
        }
    }

    //check if geolocation post title matches geodir_post_location city or geodir_post_neighbourhood hood_name
    foreach ($geolocations as $geolocation) {
        $titles = array_column($geolocations, 'post_title');
        if ($geolocation->post_title === $geolocation->display_name) {
            //var_dump($geolocation->gd_location_id);
            $geodir_post_location = $geodir_post_locations[array_search($geolocation->gd_location_id, array_column($geodir_post_locations, 'location_id'))]->city;
            $geodir_post_neighbourhood = $geodir_post_neighbourhoods[array_search($geolocation->gd_location_id, array_column($geodir_post_neighbourhoods, 'hood_id'))]->hood_name;
            if ($geodir_post_location !== $geolocation->post_title && $geodir_post_neighbourhood !== $geolocation->post_title) {
                $message = "Geolocation title: " . $geolocation->post_title . " does not match name of associated gd_location: " . $geodir_post_location . " or gd_neighbourhood: " . $geodir_post_neighbourhood . "\r\n";
                trigger_error($message, E_USER_WARNING);
                $emailoutput .= $message;
            }
        }
    }

    if ($emailoutput != "") {
        send_email($emailoutput, 'Mismatching geolocation title(s) found');
    }
}

function find_duplicate_geolocations($geolocations)
{
    $emailoutput = "";

    $titles = array_column($geolocations, 'post_title');
    //var_dump($titles);
    $duplicate_titles = array_filter(array_count_values($titles), function ($count) {
        return $count > 1;
    });

    foreach ($duplicate_titles as $title => $count) {
        $message = "Duplicate geolocation titles found: $title, count: $count\n";
        trigger_error($message, E_USER_WARNING);
        $emailoutput .= $message;
    }

    if ($emailoutput != "") {
        send_email($emailoutput, 'Duplicate geolocation(s) found');
    }
}

function create_missing_geolocations($geodir_post_locations_ids, $geodir_post_neighbourhoods_ids, $geolocations_ids, $geodir_post_locations, $geodir_post_neighbourhoods)
{
    $emailoutput = "";

    foreach ($geodir_post_locations_ids as $id) {
        if (!in_array($id, $geolocations_ids)) {
            $missing_geodir_post_location_title = $geodir_post_locations[array_search($id, array_column($geodir_post_locations, 'location_id'))]->city;
            $missing_geodir_post_location_slug = $geodir_post_locations[array_search($id, array_column($geodir_post_locations, 'location_id'))]->city_slug;
            $missing_geodir_post_location_latitude = $geodir_post_locations[array_search($id, array_column($geodir_post_locations, 'location_id'))]->latitude;
            $missing_geodir_post_location_longitude = $geodir_post_locations[array_search($id, array_column($geodir_post_locations, 'location_id'))]->longitude;
            $message = "geodir_post_location id: " . $id . " and name: " .  $missing_geodir_post_location_title   . " not found in geolocations_ids. Creating new geolocation.\r\n";
            trigger_error($message, E_USER_WARNING);
            $emailoutput .= $message;
            $new_post = wp_insert_post(array(
                'post_title' => $missing_geodir_post_location_title,
                'post_type' => 'geolocations',
                'post_status' => 'publish',
            ));
            update_post_meta($new_post, 'gd_location_id', $id);
            update_post_meta($new_post, 'gd_location_slug', $missing_geodir_post_location_slug);
            update_post_meta($new_post, 'latitude', $missing_geodir_post_location_latitude);
            update_post_meta($new_post, 'longtitude', $missing_geodir_post_location_longitude);
            update_post_meta($new_post, 'display_name', $missing_geodir_post_location_title);
            update_post_meta($new_post, 'gd_place_list', []);
        }
    }

    foreach ($geodir_post_neighbourhoods_ids as $id) {
        if (!in_array($id, $geolocations_ids)) {
            $missing_geodir_post_hood_title = $geodir_post_neighbourhoods[array_search($id, array_column($geodir_post_neighbourhoods, 'hood_id'))]->hood_name;
            $missing_geodir_post_hood_slug = $geodir_post_neighbourhoods[array_search($id, array_column($geodir_post_neighbourhoods, 'hood_id'))]->hood_slug;
            $missing_geodir_post_hood_latitude = $geodir_post_neighbourhoods[array_search($id, array_column($geodir_post_neighbourhoods, 'hood_id'))]->hood_latitude;
            $missing_geodir_post_hood_longitude = $geodir_post_neighbourhoods[array_search($id, array_column($geodir_post_neighbourhoods, 'hood_id'))]->hood_longitude;
            $missing_geodir_post_hood_parent_location = $geodir_post_neighbourhoods[array_search($id, array_column($geodir_post_neighbourhoods, 'hood_id'))]->hood_location_id;
            $message = "geodir_hood_location id: " . $id . " and name: " .  $missing_geodir_post_hood_title   . " not found in geolocations_ids. Creating new geolocation.\r\n";
            trigger_error($message, E_USER_WARNING);
            $emailoutput .= $message;
            $new_post = wp_insert_post(array(
                'post_title' => $missing_geodir_post_hood_title,
                'post_type' => 'geolocations',
                'post_status' => 'publish',
            ));
            update_post_meta($new_post, 'gd_location_id', $id);
            update_post_meta($new_post, 'gd_location_slug', $missing_geodir_post_hood_slug);
            update_post_meta($new_post, 'latitude', $missing_geodir_post_hood_latitude);
            update_post_meta($new_post, 'longtitude', $missing_geodir_post_hood_longitude);
            update_post_meta($new_post, 'display_name', $missing_geodir_post_hood_title);
            update_post_meta($new_post, 'parent_location', $missing_geodir_post_hood_parent_location);
            update_post_meta($new_post, 'gd_place_list', []);
        }
    }

    if ($emailoutput != "") {
        send_email($emailoutput, 'Geolocation(s) created');
    }
}

function send_email($body, $subject)
{
    $to = get_option('admin_email');
    $subject = 'Geolocation(s) created';
    $headers = 'From: system@tjekdepot.dk <system@tjekdepot.dk>' . "\r\n";

    wp_mail($to, $subject, $body, $headers);
}
