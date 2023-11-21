<?php

require_once dirname(__FILE__) . '/statistics-common.php';

/**
 * Retrieves all gd_place IDs from the current archive result.
 *
 * @return array An array of gd_place IDs.
 */
function get_all_gd_place_ids_from_archive_result()
{
    $all_post_ids = array();

    // Loop through each post in the current archive result
    if (have_posts()) :
        while (have_posts()) : the_post();
            // Check if the post type is 'gd_place'
            if (get_post_type() === 'gd_place') {
                // Add gd_place ID to the array
                $all_post_ids[] = get_the_ID();
            }
        endwhile;
    endif;

    return $all_post_ids;
}

/**
 * Retrieves depotrum data for a list of gd_places.
 *
 * @param array $gd_place_ids_list An array of gd_place IDs.
 * @return array An array containing combined depotrum data for the specified list of gd_places.
 */
function get_statistics_data_for_list_of_gd_places($gd_place_ids_list)
{
    $statistics_data = [];
    $counter = 0;

    // Loop through each gd_place ID in the provided list
    foreach ($gd_place_ids_list as $gd_place_id) {
        // Get depotrum data for a single gd_place
        $statistics_data_for_single_gd_place = get_statistics_data_for_single_gd_place($gd_place_id);

        // Check if depotrum data is available for the gd_place
        if ($statistics_data_for_single_gd_place) {
            // Add depotrum data to the existing statistics data
            foreach ($statistics_data_for_single_gd_place as $field => $value) {
                if (isset($statistics_data[$field])) {
                    $statistics_data[$field] += $value;
                } else {
                    $statistics_data[$field] = $value;
                }
            }
            $counter++;
        }
    }

    // Calculate averages
    foreach ($statistics_data as $field => $value) {
        if (strpos($field, 'average') !== false) {
            $statistics_data[$field] = $value / $counter;
        }
    }

    return $statistics_data;
}

function get_statistics_data_for_single_gd_place($gd_place_id)
{
    $fields_array = array(
        'num of units available',
        'num of m2 available',
        'num of m3 available',
        'average price',
        'average m2 price',
        'average m3 price',
        'mini size average price',
        'mini size average m2 price',
        'mini size average m3 price',
        'small size average price',
        'small size average m2 price',
        'small size average m3 price',
        'medium size average price',
        'medium size average m2 price',
        'medium size average m3 price',
        'large size average price',
        'large size average m2 price',
        'large size average m3 price',
        'very large size average price',
        'very large size average m2 price',
        'very large size average m3 price'
    );

    $return_array = [];

    foreach ($fields_array as $field) {
        $value = get_post_meta($gd_place_id, $field, true);
        $return_array[$field] = $value;
    }

    return $return_array;
}

function update_gd_place_list_for_geolocation_func()
{
    //get current list of geolocation ids
    $geolocation_id = extract_geolocation_id_via_url();
    $current_gd_place_list = get_post_meta($geolocation_id, 'gd_place_list', false);

    //get list of place ids from archive result
    $new_gd_place_list = get_all_gd_place_ids_from_archive_result();

    /*
    echo "current gd_place_list var_dump:";
    var_dump($current_gd_place_list);
    echo "<br>";

    echo "new_gd_place_ids_list var_dump:";
    var_dump($new_gd_place_list);
    */

    $geolocation_slug = extract_geolocation_slug_via_url();

    // Check if the lists are different
    if ($current_gd_place_list !== $new_gd_place_list) {
        // Find the added IDs
        $added_ids = array_diff($new_gd_place_list, $current_gd_place_list);
        if (!empty($added_ids)) {
            $message = 'gd_place_ids updated for location ' . $geolocation_slug . '/' . $geolocation_id . "\n";
            $message .= 'New gd_place_list: ' . implode(', ', $new_gd_place_list) . "\n";
            $message .= 'Added IDs: ' . implode(', ', $added_ids) . "\n";
            trigger_error($message, E_USER_WARNING);
        }
    }

    update_post_meta($geolocation_id, 'gd_place_list', $new_gd_place_list);
}

add_shortcode("update_gd_place_list_for_geolocation", "update_gd_place_list_for_geolocation_func");

function update_statistics_data_for_all_geolocations()
{
        $geolocations = get_posts(array('post_type' => 'geolocations', 'posts_per_page' => -1));

        foreach ($geolocations as $geolocation) {
            $geolocation_id = $geolocation->ID;
            echo "updating data for geolocation: " . $geolocation_id . "<br>";

            $gd_place_ids_list = get_post_meta($geolocation_id, 'gd_place_list', false);

            $depotrum_data = get_statistics_data_for_list_of_gd_places($gd_place_ids_list);

            foreach ($depotrum_data as $field => $value) {
                // Generate and execute the update_post_meta line
                update_post_meta($geolocation_id, $field, $value);
                echo $field . ": " . $value . "<br>";
        }
    }  
}

?>