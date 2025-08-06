<?php
/**
 * Disable Hover Effects on Touch Devices Feature
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Disable_Hover {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('responsive_goodies_options');
    }
    
    public function init() {
        if ($this->is_enabled()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        }
    }
    
    private function is_enabled() {
        return isset($this->options['disable_hover_enabled']) && $this->options['disable_hover_enabled'];
    }
    
    public function enqueue_styles() {
        wp_enqueue_style(
            'responsive-goodies-disable-hover',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'includes/features/disable-hover/disable-hover.css',
            array(),
            RESPONSIVE_GOODIES_VERSION
        );
    }
}
?>
