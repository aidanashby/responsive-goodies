<?php
/**
 * Plugin Name: Responsive Goodies
 * Plugin URI: https://lucidrhino.design
 * Description: A collection of responsive design utilities for WordPress sites.
 * Version: 0.3.2
 * Author: Aidan Ashby
 * License: GPL v2 or later
 * Text Domain: responsive-goodies
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RESPONSIVE_GOODIES_VERSION', '0.3.2');
define('RESPONSIVE_GOODIES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RESPONSIVE_GOODIES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main plugin class
require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/class-responsive-goodies.php';

// Include updater
require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/class-updater.php';

// Initialize updater
if (is_admin()) {
    new Responsive_Goodies_Updater(__FILE__, 'aidanashby', 'responsive-goodies');
}

// Initialize the plugin
function responsive_goodies_init() {
    $plugin = new Responsive_Goodies();
    $plugin->run();
}
add_action('plugins_loaded', 'responsive_goodies_init');

// Add settings link to plugin page
function responsive_goodies_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=responsive-goodies') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'responsive_goodies_settings_link');

// Activation hook
register_activation_hook(__FILE__, array('Responsive_Goodies', 'activate'));

// Deactivation hook
register_deactivation_hook(__FILE__, array('Responsive_Goodies', 'deactivate'));
?>
