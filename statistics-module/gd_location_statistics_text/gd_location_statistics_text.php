<?php

function gd_location_statistics_text_func($atts) {

     $gd_location_id = extract_geolocation_id_via_url();

     global $fulltext;  

     global $statistics_data_fields;

     $num_of_gd_places = get_post_meta($gd_location_id, 'num of gd_places', true);

     if ($num_of_gd_places <= 2) {
          return;
     }

      $return_array = [];
  
      foreach ($statistics_data_fields as $field) {
          $value = get_post_meta($gd_location_id, $field, true);
          $fulltext = str_replace("[$field]", $value, $fulltext);
      }

     $archive_title_trimmed = substr(get_the_archive_title(),2);


     $gd_place_names = get_post_meta($gd_location_id, 'gd_place_names', true);

     if (!empty($gd_place_names)) {
          $fulltext .= '<h5>Der er i alt [num of gd_places] udbydere af depotrum i [location]</h5>';
          $fulltext .= '<ul>';
          foreach ($gd_place_names as $place_name) {
               $fulltext .= '<li>' . $place_name . '</li>';
          }
          $fulltext .= '</ul>';
     }

     $fulltext = str_replace("[num of gd_places]", $num_of_gd_places, $fulltext);

     $fulltext = str_replace("[location]", $archive_title_trimmed, $fulltext);

     foreach ($statistics_data_fields as $field) {
          $value = get_post_meta($gd_location_id, $field, true);
          $fulltext = str_replace("[$field]", $value, $fulltext);
     }


     echo $fulltext;
     }

     add_shortcode('gd_location_statistics_text_shortcode', 'gd_location_statistics_text_func');

     $fulltext = '<h3>Statistik over ledige depotrum i [location]</h3>

           <p>Der er <strong>[num of gd_places] udbydere af depotrum</strong> i [location], og der er lige nu ledige depotrum fra <strong>[smallest size] m² op til [largest size] m²</strong></p>

           <h4>Hvad koster et ledigt depotrum i [location]?</h4>

           <p>Den gennemsnitlige pris for et depotrum i [location] er: <strong>[average price] kr</strong></p>

           <p>Den gennemsnitlige pris pr. kvadratmeter for et depotrum i [location] er: <strong>[average m2 price] kr/m²</strong></p>

           <p>Den gennemsnitlige pris pr. kubikmeter for et depotrum i [location] er: <strong>[average m3 price] kr/m³</strong></p>

           <h4>Priser på ledige depotrum i [location]</h4>

           <p>Et mini depotrum (op til 2 m²) koster i gennemsnit: <strong>[mini size average price] kr</strong></p>

           <p>Et lille depotrum (mellem 2 og 7 m²) i gennemsnit: <strong>[small size average price] kr</strong></p>

           <p>Et mellem depotrum (mellem 7 og 18 m²) i gennemsnit: <strong>[medium size average price]kr</strong></p>

           <p>Et stort depotrum (mellem 18 og 25 m²) i gennemsnit: <strong>[large size average price]kr</strong></p>

           <p>Et meget stort depotrum (over 25 m²) i gennemsnit: <strong>[very large size average price] kr</strong></p>';

