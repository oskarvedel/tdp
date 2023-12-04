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
    consolidate_gd_places($geolocations, $geodir_post_locations, $geodir_post_neighbourhoods);
}

function consolidate_gd_places($geolocations, $geodir_post_locations, $geodir_post_neighbourhoods)
{
    global $wpdb;
    $geodir_post_locations_table = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}geodir_gd_place_detail", OBJECT);

    $all_gd_places = get_posts(array('post_type' => 'gd_place', 'posts_per_page' => -1));

    //echo "gd_places count: " . count($all_gd_places);
    //echo "gd_places_table count: " . count($geodir_post_locations_table);

    $emailoutput = "";
    foreach ($geolocations as $geolocation) {
        $new_gd_place_list = array();
        //echo $geolocation->post_title . "\n";
        foreach ($all_gd_places as $gd_place) {
            //print_r($gd_place);
            //echo $gd_place->ID . "\n";
            $filtered_locations = array_filter($geodir_post_locations_table, function ($location) use ($gd_place) {
                return $location->post_id == $gd_place->ID;
            });
            //print_r($filtered_locations);
            if (count($filtered_locations) == 1) {
                $firstLocation = reset($filtered_locations);

                //echo $firstLocation->city;
                if ($firstLocation->city == $geolocation->post_title) {
                    $new_gd_place_list[] = $gd_place->ID;
                }
                //break;
            }
            if (count($filtered_locations) > 1) {
                echo "found multiple gd_places for: " . $gd_place->ID . "id:"  . "<br>";
                //var_dump($filtered_locations);
            }
            if (count($filtered_locations) == 0) {
                echo "found zero gd_places for: " . $gd_place->ID . "id:"  . "<br>";
                var_dump($filtered_locations);
            }
            //echo $gd_place->post_title . "\n";
            if ($gd_place->post_title == $geolocation->post_title) {
                $new_gd_place_list[] = $gd_place->ID;
            }
            //break;
        }
        //echo $geolocation->ID;
        //print_r($new_gd_place_list);
        $current_gd_place_list = get_post_meta($geolocation->ID, 'gd_place_list', false);

        $current_gd_place_id_list = array_map(function ($post) {
            return $post['ID'];
        }, $current_gd_place_list);

        $emailoutput = update_gd_place_list($current_gd_place_id_list, $new_gd_place_list, $geolocation, $emailoutput);
    }
    if ($emailoutput != "") {
        send_email($emailoutput, 'gd_place list(s) updated for geolocation(s)');
    }
}

function update_gd_place_list($current_gd_place_id_list, $new_gd_place_list, $geolocation, $emailoutput)
{
    //var_dump($current_gd_place_id_list);
    //var_dump($new_gd_place_list);
    //Check if the lists are different
    if ($current_gd_place_id_list !== $new_gd_place_list) {
        //if current_gd_place_list is unitialized, initialize it to prevent an error in the array_diff call
        $current_gd_place_id_list = is_bool($current_gd_place_id_list) ? [] : $current_gd_place_id_list;
        //var_dump($current_gd_place_id_list);
        // Find the added IDs
        $different_ids = array_diff($new_gd_place_list, $current_gd_place_id_list);
        if (!empty($added_ids)) {
            $message = 'gd_place_ids updated for location ' . $geolocation->post_title . '/' . $geolocation->ID . "\n";
            $message .= 'New gd_place_list:';
            foreach ($new_gd_place_list as $item) {
                $message .= "\n" . $item;
            }
            $message .= "\n";
            $message .= 'Added or removed IDs: ' . implode(', ', $different_ids) . "\n";
            trigger_error($message, E_USER_WARNING);
            $emailoutput .= $message;
        }
    }
    update_post_meta($geolocation->ID, 'gd_place_list', $new_gd_place_list);
    update_post_meta($geolocation->ID, 'num of gd_places', count($new_gd_place_list));

    $gd_place_names = array();
    foreach ($new_gd_place_list as $gd_place_id) {
        $gd_place = get_post($gd_place_id);
        $gd_place_names[] = $gd_place->post_title;
    }

    update_post_meta($geolocation->ID, 'gd_place_names', $gd_place_names);

    return $emailoutput;
}


function geolocations_sanity_check($geodir_post_locations, $geodir_post_neighbourhoods, $geolocations)
{
    $emailoutput = "";
    //check if geolocation post title matches geodir_post_location city or geodir_post_neighbourhood hood_name
    foreach ($geolocations as $geolocation) {
        $titles = array_column($geolocations, 'post_title');
        //var_dump($geolocation->gd_location_id);
        $geodir_post_location = $geodir_post_locations[array_search($geolocation->gd_location_id, array_column($geodir_post_locations, 'location_id'))]->city;
        $geodir_post_neighbourhood = $geodir_post_neighbourhoods[array_search($geolocation->gd_location_id, array_column($geodir_post_neighbourhoods, 'hood_id'))]->hood_name;
        if ($geodir_post_location !== $geolocation->post_title && $geodir_post_neighbourhood !== $geolocation->post_title) {
            $message = "Geolocation title: " . $geolocation->post_title . " does not match name of associated gd_location: " . $geodir_post_location . " or gd_neighbourhood: " . $geodir_post_neighbourhood . "\r\n";
            //trigger_error($message, E_USER_WARNING); FIX
            $emailoutput .= $message;
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
