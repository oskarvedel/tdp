<?php

function sort_depotrum_by_price($depotrum_items)
{
    // Get all depotrum and sort by price
    $AllDepotrumArray = [];

    foreach ($depotrum_items as $depotrum) {
        $id = $depotrum['ID'];
        $arrayObject = (object) [
            'id' => $id,
            'price' => get_post_meta($id, 'price', true),
        ];

        array_push($AllDepotrumArray, $arrayObject);
    }

    usort($AllDepotrumArray, function ($a, $b) {
        return $a->price > $b->price ? 1 : -1;
    });

    return $AllDepotrumArray;
}

// Define the shortcode and the function to execute when the shortcode is used.
function custom_depotrum_list_shortcode()
{
    $current_pod = pods();

    // Check if the Pod object exists and the field "partner" is set
    if ($current_pod && $current_pod->exists()) {
        $depotrum_items = $current_pod->field("depotrum");
        $lokationId = $current_pod->field("id");
        $hide_units = $current_pod->field("hide_units");

        if ($depotrum_items && !empty($depotrum_items) && !$hide_units) {
            $IdsSortedByPrice = sort_depotrum_by_price($depotrum_items);
            $OutputArray = [];
            $finalOutput = '<div class="depotrum-list">';
            $partner = $current_pod->field("partner");
            
            $output = '';

            if ($partner == 1)
            {
                foreach ($IdsSortedByPrice as $depotrum) {
                    $id = $depotrum->id;
                    if (get_post_meta($id, 'available', true)) {
                        $relTypeId = get_post_meta($id, 'rel_type', true);
                        $output = '<div class="depotrum-row">';
                        $output .= '<div class="flex-container">';
                        $output .= '<div class="m2-column vertical-center">';
                        $output .= '<span class="m2size">' . get_post_meta($relTypeId, 'm2', true) . '</span>';
                        $output .= '<span class="m2label"> m2</span>';
                        $output .= '</div>';

                        /*$output .= '<div class="placement-column vertical-center">';
                        $placement = get_post_meta($relTypeId, 'placement', true);

                        if ($placement == 'indoor') {
                            $output .= '<div class="img vertical-center">';
                            $output .= '<img src="https://tjekdepot.dk/wp-content/uploads/2023/11/indoor.png" alt="Icon of an indoor storage facility" width="35" height="35">';
                            $output .= '</div>';
                            $output .= '<div class="placement-text-div">';
                            $output .= '<span class="placement-text">Placering:</span>';
                            $output .= '<p class="placement-heading">Indendørs</p>';
                            $output .= '</div>';
                        } elseif ($placement == 'container') {
                            $output .= '<div class="img vertical-center">';
                            $output .= '<img src="https://tjekdepot.dk/wp-content/uploads/2023/11/container.png" alt="Icon of a container" width="35" height="35">';
                            $output .= '</div>';
                            $output .= '<div class="placement-text-div">';
                            $output .= '<span class="placement-text">Placering:</span>';
                            $output .= '<p class="placement-heading">I container</p>';
                            $output .= '</div>';
                        } elseif ($placement == 'isolated_container') {
                            $output .= '<div class="img vertical-center">';
                            $output .= '<img src="https://tjekdepot.dk/wp-content/uploads/2023/11/container.png" alt="Icon of a container" width="35" height="35">';
                            $output .= '</div>';
                            $output .= '<div class="placement-text-div">';
                            $output .= '<span class="placement-text">Placering:</span>';
                            $output .= '<p class="placement-heading">Isoleret container</p>';
                            $output .= '</div>';
                        }
                        $output .= '</div>';*/
                        $output .= '</div>';

                        $output .= '<div class="price-column vertical-center">';
                        if (get_post_meta($id, 'price', true)) {
                            $output .= '<span class="price">' . round(get_post_meta($id, 'price', true),2) . ' kr.</span>';
                            //$output .= '<span class="month">/måned</span>';
                        } else {
                            $output .= '<span class="month">Pris ukendt</span>';
                        }
                        $output .= '</div>';

                        $output .= '<div class="navigation-column vertical-center">';
                        if ($partner && !geodir_is_page('post_type')) {
                            $output .= do_shortcode('[gd_ninja_forms form_id="5" text="Fortsæt" post_contact="1" output="button" bg_color="#FF3369" txt_color="#ffffff" size="h5" css_class="ninja-forms-book-button"]');
                        } else {
                            $output .= '<a href="' . get_permalink($lokationId) . '">';
                            $output .= '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25">';
                            $output .= '<path d="M7.293 4.707 14.586 12l-7.293 7.293 1.414 1.414L17.414 12 8.707 3.293 7.293 4.707z" />';
                            $output .= '</svg>';
                            $output .= '</a>';
                        }
                        $output .= '</div>';
                        $output .= '</div>';

                        array_push($OutputArray, $output);

                        if (geodir_is_page('post_type')) {
                            //if (++$i >= 4) break;
                        }
                    }
                }
            } else {
                $statistics_data_fields = get_statistics_data_for_single_gd_place($current_pod->field("id"));
                $finalOutput .= '<p><small>[location] tilbyder depotrum fra [smallest m2 size] m² til [largest m2 size] m² i prislejet  [lowest price] kr til [highest price] kr. </small></p>';

                $gd_place_title = ($current_pod->field("post_title"));

                $finalOutput = str_replace("[location]", $gd_place_title, $finalOutput);

                foreach ($statistics_data_fields as $field => $value) {
                    if (!empty($value)) {
                        $rounded = floatval(round($value,2));
                        $numberformat = number_format($value,0,',', '.');
                        $finalOutput = str_replace("[$field]", $numberformat, $finalOutput);
                    }
            }
        }
            
            $counter = 0;
            foreach ($OutputArray as $arrayItem) {
                if (++$counter >= 4) break;
                $finalOutput .= $arrayItem;
            }
            
            if (geodir_is_page('post_type')) {
                //if (++$i >= 4) break;
            }

            foreach ($OutputArray as $arrayItem) {
                $finalOutput .= $arrayItem;
            }

            $finalOutput .= "</div>";
            if (geodir_is_page('post_type') && $partner) {
                $finalOutput .= '<form action="' . get_permalink($lokationId) . '">';
                $finalOutput .= '<input type="submit" class="view-all-button" value="Se alle priser" />';
                $finalOutput .= '</form>';
            }
            return $finalOutput;
        }
    }
}

// Register the shortcode.
add_shortcode("custom_depotrum_list", "custom_depotrum_list_shortcode");
