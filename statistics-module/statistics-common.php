<?php

/**
 * Extracts the geolocation slug from the current URL.
 *
 * This function uses the global $wp object to get the current request URL,
 * parses the URL to get the path, and then extracts the last part of the path
 * as the geolocation slug.
 *
 * @return string The geolocation slug extracted from the URL.
 */
function extract_geolocation_slug_via_url() {

   // Access the global $wp object
   global $wp;

   // Get the current request URL
   $url = home_url( $wp->request );

   // Parse the URL to get the path
   $parsedUrl = parse_url($url);

   // Split the path into parts
   $pathParts = explode('/', trim($parsedUrl['path'], '/'));

   // Get the last part of the path as the slug
   $slug = end($pathParts);

   // Return the slug
   return $slug;
}

/**
 * Extracts the geolocation ID from the current URL.
 *
 * This function first calls the extract_geolocation_slug_via_url function to get
 * the geolocation slug from the URL. It then uses the Pods plugin to get the
 * geolocation object associated with the slug, and extracts the ID of the
 * geolocation object.
 *
 * @return int|null The geolocation ID if found, null otherwise.
 */
function extract_geolocation_id_via_url() {

    // Get the geolocation slug from the URL
    $slug = extract_geolocation_slug_via_url();

    // Use the Pods plugin to get the geolocation object associated with the slug
    $slug_test = pods( 'geolocations', $slug );

    // Initialize the geolocation ID as null
    $geolocation_id = null;

    // If the geolocation object exists, extract its ID
    if ($slug_test && $slug_test->exists() ) {
        $geolocation_id = $slug_test->field('ID');
    }

    // Return the geolocation ID
    return $geolocation_id;
}
?>