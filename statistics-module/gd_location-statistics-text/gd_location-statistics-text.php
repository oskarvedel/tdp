<?php

function gd_location_statistics_text_func($atts)
{
     //get data
     $gd_location_id = extract_geolocation_id_via_url();

     $num_of_gd_places = get_post_meta($gd_location_id, 'num of gd_places', true);

     $archive_title_trimmed = substr(get_the_archive_title(), 2);

     $gd_place_names = get_post_meta($gd_location_id, 'gd_place_names', true);
     $schools = get_post_meta($gd_location_id, 'schools', false);

     //return if not enough data
     if ($num_of_gd_places <= 2) {
          echo (get_post_meta($gd_location_id, 'description', true));
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
     $output .= '<hr class="line">';
     $output .= generate_price_table();
     $output .= '<hr class="line">';
     $output .= $second_paragraph;
     $output .= '<hr class="line">';
     $output .= $third_paragraph;
     $output .= '<hr class="line">';
     $output .= generate_schools_paragraph($schools);
     $output .= '<hr class="line">';
     $output .= generate_selfstorage_provider_list($gd_place_names);

     //relace variable placeholders with data
     $output = str_replace("[num of gd_places]", $num_of_gd_places, $output);

     $output = str_replace("[location]", $archive_title_trimmed, $output);

     foreach ($statistics_data_fields as $field) {
          $value = get_post_meta($gd_location_id, $field, true);
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

add_shortcode('gd_location_statistics_text_shortcode', 'gd_location_statistics_text_func');

function generate_selfstorage_provider_list($gd_place_names)
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

$first_paragraph = '<h2>Hvad koster et depotrum i [location]?</h2>
     <p class="three-columns">Du kan opbevare ejendele i et depotrum i [location] for i gennemsnit [average price] kr pr. måned. Her på tjekdepot kan du finde ledige depotrum med et bredt udvalg af egenskaber og fordele, herunder klimakontrol, personlig betjening, døgnalarm, videoovervågning, direkte adgang med bil med flere. For at reservere et opbevaringsrum i [location] kan du her på siden nemt få et overblik over ledige depotrum og vælge et rum med den rette størrelse og de nødvendige egenskaber. Afhængigt af dine behov kan du nemt finde det rigtige depotrum i [location]. Vi har et væld af muligheder for dig at vælge imellem. Du kan vælge et mindre lagerrum og derefter se, hvor meget du har brug for at opbevare, eller du kan vælge et større depotrum for at få al den plads, du har brug for til alle slags ejendele, selv motorcykler og biler. I [location] starter priserne for et depotrum ved [lowest price] kr og går op til [highest price] kr, afhængigt af rummets størrelse og egenskaber.</p>';

$second_paragraph = '<h2>Find opbevaring i [location]</h2>
     <p class="three-columns">Hvis du leder efter opbevaring i [location], er du kommet til det rette sted. Her på tjekdepot har vi registreret [num of gd_places] udbydere af opbevaring placeret i [location], og de tilbyder alle sikre og tilgængelige depotrum. Du kan sortere alle depotrum i dit område efter pris, størrelse og egenskaber. Her kan du også finde mere information om de forskellige egenskaber ved et depotrum såsom klimakontrol, adgangsforhold og sikkerhedsforanstaltninger. Se vores opslag i [location], vælg et depotrum, der passer til dine behov, og lej den med det samme. Når du reserverer opbevaring, har du et rigtig godt sted at placere dine ting, både på lang og kort sigt. Langt de fleste opbevaringsrum udlejes på månedsbasis, hvilket betyder, at du har mulighed for at flytte ind og ud når som helst. Du kan leje opbevaring i [location] i en måned eller et helt år - uanset hvad, kan du forlænge din lejeperiode og skifte til et depotrum på en anden størrelse, når du ønsker det. På denne måde er det nemt at vælge den bedste opbevaring til dine ejendele!</p>';

function generate_price_table()
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

$third_paragraph = '<h2>Hvordan finder jeg et sted at opmagasinere mine ting?</h2>
     <p class="three-columns">Hvis du leder efter et sikkert sted at opbevare mindre brugte genstande og personlige ejendele af alle typer og størrelser, er det oplagt at bruge opmagasinering, depotrum eller et opbevaringsrum tæt på dit hjem eller din virksomhed, så du har adgang til dine ting når som helst uden besvær. Her på tjekdepot får du et overblik over priser på opmagasinering og opbevaring og over rumstørrelser, egenskaber og fordele afhængigt af dine behov og dit budget. Du kan nemt finde opmagasinering i dit nabolag eller din by, uanset hvor du befinder dig i Danmark. Du er kun én søgning væk fra at finde det rigtige sted til dine ejendele i [location].</p>';

function generate_schools_paragraph($schools)
{
     $schools_first_paragraph = '<h2>Udvid din studiebolig i [location] med et depotrum</h2>
     <p class="three-columns">Hvis du skal flytte til [location] for at studere studere på ';
     $schools_second_paragraph = 'kan du få mere plads i din studiebolig med et depotrum. Studieboliger har ofte meget begrænset plads, og du kan undgå at rod af tøj og bøger, du ikke bruger hele året, ligger og roder. Et depotrum kan også være en idé for internationale studerende, der skal have opbevaret deres ejendele imens de er hjemme på sommerferie.</p>';
     if (!empty($schools)) {
          $return_text = '';
          $return_text .=  $schools_first_paragraph;
          foreach ($schools as $key => $school) {
               if ($key === count($schools) - 2) {
                    $return_text .=  $school  . ' eller ';
               } elseif ($key === count($schools) - 1) {
                    $return_text .=  $school . ' ';
               } else {
                    $return_text .=  $school  . ', ';
               }
          }
          $return_text .=  $schools_second_paragraph;
          return $return_text;
     }
}

function generate_related_locations_text($schools)
{
     $schools_first_paragraph = '<h2>Udvid din studiebolig i [location] med et depotrum</h2>
     <p class="three-columns">Hvis du skal flytte til [location] for at studere studere på ';
     $schools_second_paragraph = 'kan du få mere plads i din studiebolig med et depotrum. Studieboliger har ofte meget begrænset plads, og du kan undgå at rod af tøj og bøger, du ikke bruger hele året, ligger og roder. Et depotrum kan også være en idé for internationale studerende, der skal have opbevaret deres ejendele imens de er hjemme på sommerferie.</p>';
     if (!empty($schools)) {
          $return_text = '';
          $return_text .=  $schools_first_paragraph;
          foreach ($schools as $key => $school) {
               if ($key === count($schools) - 2) {
                    $return_text .=  $school  . ' eller ';
               } elseif ($key === count($schools) - 1) {
                    $return_text .=  $school . ' ';
               } else {
                    $return_text .=  $school  . ', ';
               }
          }
          $return_text .=  $schools_second_paragraph;
          return $return_text;
     }
}


$text_template = '
          <p>[average price]
          [smallest m2 size]
          [lowest price]
          [average m2 price]
          [average m3 price]</p>
          <h3>Priser på depotrum i [location] fordelt efter størrelse</h3>
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
