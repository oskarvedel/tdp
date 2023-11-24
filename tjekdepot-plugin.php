<?php

/**
 * Plugin Name: Tjekdepot Plugin
 * Version: 1.0
 */

require_once dirname(__FILE__) . '/unit-list-module/unit-list-module.php';
require_once dirname(__FILE__) . '/statistics-module//statistics-module.php';

// Define the activation function
function tjekdepot_plugin_activation_function() {
    // Check if the scheduled event is already set
        wp_schedule_event(time(), 'daily', 'tjekdepot_daily_event');
}
// Define the deactivation function
function tjekdepot_plugin_deactivation_function() {
    // Unschedule the daily event when the plugin or theme is deactivated
    trigger_error("tjekdepot_daily_function deactivated", E_USER_WARNING);
    wp_clear_scheduled_hook('tjekdepot_daily_event');
}

// Hook the activation and deactivation functions
register_activation_hook(__FILE__, 'tjekdepot_plugin_activation_function');
register_deactivation_hook(__FILE__, 'tjekdepot_plugin_deactivation_function');

// Define the function to be executed daily
function tjekdepot_daily_function() {
    trigger_error("tjekdepot_daily_function activated", E_USER_WARNING);
    update_statistics_data();
}

// Hook the daily function to the scheduled event
add_action('tjekdepot_daily_event', 'tjekdepot_daily_function');