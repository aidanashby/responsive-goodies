<?php
/**
 * Back to Top Button Visibility Feature
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Back_To_Top {
    
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
        return isset($this->options['back_to_top_enabled']) && $this->options['back_to_top_enabled'];
    }
    
    public function enqueue_styles() {
        wp_enqueue_style(
            'responsive-goodies-back-to-top',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'includes/features/back-to-top/back-to-top.css',
            array(),
            RESPONSIVE_GOODIES_VERSION
        );
        
        // Add inline CSS for device-specific visibility
        $custom_css = $this->generate_device_css();
        if ($custom_css) {
            wp_add_inline_style('responsive-goodies-back-to-top', $custom_css);
        }
    }
    
    private function generate_device_css() {
        $css = '';
        
        $desktop = isset($this->options['back_to_top_desktop']) ? $this->options['back_to_top_desktop'] : true;
        $tablet = isset($this->options['back_to_top_tablet']) ? $this->options['back_to_top_tablet'] : true;
        $mobile = isset($this->options['back_to_top_mobile']) ? $this->options['back_to_top_mobile'] : true;
        
        // Desktop: 981px and above
        if (!$desktop) {
            $css .= '@media (min-width: 981px) { .et_pb_scroll_top { display: none !important; } }';
        }
        
        // Tablet: 768px to 980px
        if (!$tablet) {
            $css .= '@media (min-width: 768px) and (max-width: 980px) { .et_pb_scroll_top { display: none !important; } }';
        }
        
        // Mobile: 767px and below
        if (!$mobile) {
            $css .= '@media (max-width: 767px) { .et_pb_scroll_top { display: none !important; } }';
        }
        
        return $css;
    }
}
?>
