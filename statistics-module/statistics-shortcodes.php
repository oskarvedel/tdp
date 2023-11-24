<?php

require_once dirname(__FILE__) . '/statistics-common.php';

function gd_location_metadata_shortcode_func($atts) {
     extract(shortcode_atts(array(
          'post_id' => NULL,
     ), $atts));

     if (!isset($atts[0])) {
          return;
     }
     $geolocation_id = extract_geolocation_id_via_url();

     $field = esc_attr($atts[0]);
     global $post;
     $post_id = (NULL === $post_id) ? $post->ID : $post_id;
     return get_post_meta($geolocation_id, $field, true);
}

add_shortcode('gd_location_statistics_field_shortcode', 'gd_location_metadata_shortcode_func');

?>
