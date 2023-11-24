<?php

function gd_location_statistics_text_func($atts) {

     $gd_location_id = extract_geolocation_id_via_url();

     global $fulltext;  

     global $statistics_data_fields;

      $return_array = [];
  
      foreach ($statistics_data_fields as $field) {
          $value = get_post_meta($gd_location_id, $field, true);
          echo $field . ': ' . $value . '<br>';
          $fulltext = str_replace("[$field]", $value, $fulltext);
      }

     $archive_title_trimmed = substr(get_the_archive_title(),2);

     $num_of_gd_places = get_post_meta($gd_location_id, 'num of gd_places', true);

     if (!$num_of_gd_places <= 2) {
          return;
     }

     $fulltext = str_replace("[num of gd_places]", $num_of_gd_places, $fulltext);

     $fulltext = str_replace("[location]", $archive_title_trimmed, $fulltext);

     echo $fulltext;
      
}

add_shortcode('gd_location_statistics_text_shortcode', 'gd_location_statistics_text_func');

$fulltext = '<h3>Statistik over ledige depotrum i [location]</h3>

     <p><strong>Der er [num of gd_places] udbydere af depotrum i [location], og der er lige nu ledige depotrum fra [smallest size] m² op til [largest size] m²</strong></p>

     <h4>Priser og størrelser:</h4>

     <p><strong>Den gennemsnitlige pris for et ledigt depotrum i [location] er: [average price] kr</strong></p>

     <p><strong>Den gennemsnitlige pris pr. kvadratmeter for et ledigt depotrum i [location] er: [average m2 price] kr/m²</strong></p>

     <p><strong>Den gennemsnitlige pris pr. kubikmeter for et ledigt depotrum i [location] er: [average m3 price] kr/m³</strong></p>

     <h4>Priser efter størrelse:</h4>

     <p><strong>Et ledigt mini depotrum (op til 2 m²) koster i gennemsnit: [mini size average price] kr i [location]</strong></p>

     <p><strong>Et ledigt lille depotrum (mellem 2 og 7 m²) i gennemsnit: [small size average price] kr i [location]</strong></p>

     <p><strong>Et ledigt mellem depotrum (mellem 7 og 18 m²) i gennemsnit: [medium size average price] kr i [location]</strong></p>

     <p><strong>Et ledigt stort depotrum (mellem 18 og 25 m²) i gennemsnit: [large size average price] kr i [location]</strong></p>

     <p><strong>Et ledigt meget stort depotrum (over 25 m²) i gennemsnit: [very large size average price] kr i [location]</strong></p>';
?>