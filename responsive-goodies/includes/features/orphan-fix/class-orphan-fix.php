<?php
/**
 * Orphan Fix Feature
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Orphan_Fix {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('responsive_goodies_options');
    }
    
    public function init() {
        if ($this->is_enabled()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        }
    }
    
    private function is_enabled() {
        return isset($this->options['orphan_fix_enabled']) && $this->options['orphan_fix_enabled'];
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script(
            'responsive-goodies-orphan-fix',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'includes/features/orphan-fix/orphan-fix.js',
            array('jquery'),
            RESPONSIVE_GOODIES_VERSION,
            true
        );
        
        // Pass settings to JavaScript
        $script_data = array(
            'maxWords' => isset($this->options['orphan_fix_max_words']) ? $this->options['orphan_fix_max_words'] : 2,
            'excludeClass' => isset($this->options['orphan_fix_exclude_class']) ? $this->options['orphan_fix_exclude_class'] : 'no-orphan-fix',
            'applyHeadings' => isset($this->options['orphan_fix_apply_headings']) ? $this->options['orphan_fix_apply_headings'] : true
        );
        
        wp_localize_script('responsive-goodies-orphan-fix', 'responsiveGoodiesOrphanFix', $script_data);
    }
}
?>
