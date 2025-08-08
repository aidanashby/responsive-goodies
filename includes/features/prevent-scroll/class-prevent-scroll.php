<?php
/**
 * Prevent Horizontal Scroll Feature
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Prevent_Scroll {
    
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
        return isset($this->options['prevent_scroll_enabled']) && $this->options['prevent_scroll_enabled'];
    }
    
    public function enqueue_styles() {
        wp_enqueue_style(
            'responsive-goodies-prevent-scroll',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'includes/features/prevent-scroll/prevent-scroll.css',
            array(),
            RESPONSIVE_GOODIES_VERSION
        );
    }
}
?>
