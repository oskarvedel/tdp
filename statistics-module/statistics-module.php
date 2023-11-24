<?php

require_once dirname(__FILE__) . '/scheduled-statistics-calcs-per-geolocation.php';
require_once dirname(__FILE__) . '/scheduled-statistics-calcs-per-gd-place.php';
require_once dirname(__FILE__) . '/statistics-shortcodes.php';
require_once dirname(__FILE__) . '/gd_location_statistics_text/gd_location_statistics_text.php';


function update_statistics_data()
{
    update_statistics_data_for_all_gd_places();
    trigger_error("updated statistics data for all gd_places", E_USER_WARNING);
    update_statistics_data_for_all_geolocations();
    trigger_error("updated statistics data for all geolocations", E_USER_WARNING);
}

?>