<?php
/**
 * Plugin Name: Responsive Goodies
 * Plugin URI: https://lucidrhino.design
 * Description: A collection of responsive design utilities for WordPress sites.
 * Version: 0.5.2
 * Author: Aidan Ashby
 * License: MIT
 * Requires PHP: 8.0
 * Text Domain: responsive-goodies
 * Update URI: https://github.com/aidanashby/responsive-goodies
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RESPONSIVE_GOODIES_VERSION', '0.5.2');
define('RESPONSIVE_GOODIES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RESPONSIVE_GOODIES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main plugin class
require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/class-responsive-goodies.php';

// Include changelog
require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/class-changelog.php';

// Updates from GitHub releases. Runs in every context so WP-Cron auto-updates see new versions.
add_action('plugins_loaded', function () {
    require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
    $checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/aidanashby/responsive-goodies/',
        __FILE__,
        'responsive-goodies'
    );
    $checker->getVcsApi()->enableReleaseAssets();
});

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
