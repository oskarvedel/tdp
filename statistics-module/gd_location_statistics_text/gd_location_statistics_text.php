<?php

function gd_location_statistics_text_func($atts) {

     $gd_location_id = extract_geolocation_id_via_url();

     global $text_template;

     $output = "";


     global $statistics_data_fields;
     global $statistics_data_fields_texts;

     $num_of_gd_places = get_post_meta($gd_location_id, 'num of gd_places', true);

     if ($num_of_gd_places <= 2) {
          return;
     }

     foreach ($statistics_data_fields_texts as $label => $text) {
          $value = get_post_meta($gd_location_id, $label, true);
          if (!empty($value)) { 
               $text_template = str_replace("[$label]", $text , $text_template);
          } else {
               $text_template = str_replace("[$label]", '', $text_template);
          }
     }

     $archive_title_trimmed = substr(get_the_archive_title(),2);


     $gd_place_names = get_post_meta($gd_location_id, 'gd_place_names', true);

     if (!empty($gd_place_names)) {
          $text_template .= '<h5>Der er i alt [num of gd_places] udbydere af depotrum i [location]:</h5>';
          $text_template .= '<ul>';
          foreach ($gd_place_names as $place_name) {
               $text_template .= '<li><small>' . $place_name . '</small></li>';
          }
          $text_template .= '</ul>';
     }
     

     $text_template = str_replace("[num of gd_places]", $num_of_gd_places, $text_template);

     $text_template = str_replace("[location]", $archive_title_trimmed, $text_template);


     foreach ($statistics_data_fields as $field) {
          $value = get_post_meta($gd_location_id, $field, true);
          if (!empty($value)) {
               $rounded = floatval(round($value,2));
               $numberformat = number_format($value,0,',', '.');
               $text_template = str_replace("[$field]", $numberformat, $text_template);
          }
     }

     echo $text_template;
     }

     add_shortcode('gd_location_statistics_text_shortcode', 'gd_location_statistics_text_func');

     $text_template = '<h2>Priser på depotrum i [location]</h2>
     <p><small>Vi har indsamlet data om depotrum i hele Danmark, og derfor kan vi oplyse dig om hvad opbevaring koster i [location].</small></p>
          <p>[average price]
          [smallest m2 size]
          [lowest price]
          [average m2 price]
          [average m3 price]</p>
          <h3>Priser på detotrum i [location] fordelt efter størrelse</h3>
          <p>[mini size average price]
          [small size average price]
          [medium size average price]
          [large size average price]
          [very large size average price]</p>
           ';


     $statistics_data_fields_texts = array(
     'average price' => 'Den gennemsnitlige pris for et ledigt depotrum i [location] er <strong>[average price] kr.</strong>',
     'smallest m2 size' => 'Der er er lige nu ledige depotrum fra <strong>[smallest m2 size] m² op til [largest m2 size] m², </strong>',
     'lowest price' => 'og prisen er mellem <strong>[lowest price] kr og [highest price] kr.</strong>',
     'average m2 price' => 'Kvadratmeterprisen er i gennemsnit <strong>[average m2 price] kr/m²,</strong>  og ',
     'average m3 price' => 'kubikmeterprisen er i gennemsnit <strong>[average m3 price] kr/m³.</strong>',
     'mini size average price' =>  'Et mini depotrum (op til 2 m²) koster i gennemsnit: <strong>[mini size average price] kr. </strong>',
     'small size average price' => 'Et lille depotrum (mellem 2 og 7 m²) koster i gennemsnit: <strong>[small size average price] kr. </strong>',
     'medium size average price' =>  'Et mellem depotrum (mellem 7 og 18 m²) koster i gennemsnit: <strong>[medium size average price] kr. </strong>',
     'large size average price' => 'Et stort depotrum (mellem 18 og 25 m²) koster i gennemsnit: <strong>[large size average price] kr. </strong>',
     'very large size average price' => 'Et meget stort depotrum (over 25 m²) koster i gennemsnit: <strong>[very large size average price] kr. </strong>',
     );

           


