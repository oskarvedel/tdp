<?php

function geolocation_statistics_text_func($atts)
{
     //get data
     $current_pod = pods();
     $geolocation_id = get_the_ID();

     $gd_places = $current_pod->field("gd_place_list");

     $num_of_gd_places = count($gd_places);
     //$num_of_gd_places = get_post_meta($geolocation_id, 'num of gd_places', true);

     $post_title = get_the_title();

     //$gd_place_names = get_post_meta($geolocation_id, 'gd_place_names', true);

     //get the names of the gd_places
     $gd_place_names = [];
     foreach ($gd_places as $gd_place) {
          $gd_place_names[] = get_the_title($gd_place['ID']);
     }

     //return if not enough data
     if ($num_of_gd_places <= 2) {
          return;
     }

     //set variables
     global $statistics_data_fields;
     global $text_template;
     global $first_paragraph;
     global $second_paragraph;
     global $price_table;
     global $third_paragraph;
     $output = "";
     global $statistics_data_fields_texts;

     //add content to output
     $output .= $first_paragraph;
     $output .= generate_price_table();
     $output .= $second_paragraph;
     $output .= $price_table;
     $output .= $third_paragraph;
     $output .= generate_selfstorage_provider_list($gd_place_names);


     //relace variable placeholders with data
     $output = str_replace("[num of gd_places]", $num_of_gd_places, $output);

     $output = str_replace("[location]", $post_title, $output);

     foreach ($statistics_data_fields as $field) {
          $value = get_post_meta($geolocation_id, $field, true);
          if (!empty($value)) {
               $rounded = floatval(round($value, 2));
               $numberformat = number_format($value, 0, ',', '.');
               $output = str_replace("[$field]", $numberformat, $output);
          } else {
               $output = str_replace("[$field]", "Ukendt", $output);
          }
     }

     echo $output;
}

add_shortcode('geolocation_statistics_text_shortcode', 'geolocation_statistics_text_func');

function generate_selfstorage_provider_list_geolocation_duplicate($gd_place_names)
{
     if (!empty($gd_place_names)) {
          $return_text = '<h4>Der er i alt [num of gd_places] udbydere af depotrum i [location]:</h4>';
          $return_text .= '<p class="three-columns"><small>';
          foreach ($gd_place_names as $place_name) {
               $return_text .=  $place_name  . '<br>';
          }
          $return_text .= '</small></ul>';
          return $return_text;
     }
}

function generate_price_table_geolocation_duplicate()
{
     $price_table = '
               <table>
               <thead>
               <tr>
               <th class="left-align"><strong>Størrelse</strong></th>
               <th class="right-align"><strong>Laveste pris</strong></th>
               <th class="right-align"><strong>Gennemsnitpris</strong></th>
               <th class="right-align"><strong>Højeste pris</strong></th>
               </tr>
               </thead>
               <tbody>
               <tr>
               <td>Mini (fra 0 til 2 m²)</td>
               <td class="right-align">[mini size lowest price]</td>
               <td class="right-align">[mini size average price]</td>
               <td class="right-align">[mini size highest price]</td>
               </tr>
               <tr>
               <td>Lille (2 til 7 m²)</td>
               <td class="right-align">[small size lowest price]</td>
               <td class="right-align">[small size average price]</td>
               <td class="right-align">[small size highest price]</td>
               </tr>
               <tr>
               <td>Mellem (7 til 18 m²)</td>
               <td class="right-align">[medium size lowest price]</td>
               <td class="right-align">[medium size average price]</td>
               <td class="right-align">[medium size highest price]</td>
               </tr>
               <tr>
               <td>Stort (18 til 25 m²)</td>
               <td class="right-align">[large size lowest price]</td>
               <td class="right-align">[large size average price]</td>
               <td class="right-align">[large size highest price]</td>
               </tr>
               <tr>
               <td>Meget stort (over 25 m²)</td>
               <td class="right-align">[very large size lowest price]</td>
               <td class="right-align">[very large size average price]</td>
               <td class="right-align">[very large size highest price]</td>
               </tr>
               </tbody>
               </table>';

     return $price_table;
}
