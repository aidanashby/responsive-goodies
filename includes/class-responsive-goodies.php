<?php
/**
 * Main plugin class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies {
    
    private $settings;
    private $features = array();
    
    public function __construct() {
        $this->load_dependencies();
    }
    
    private function load_dependencies() {
        require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/class-settings.php';
        require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/features/orphan-fix/class-orphan-fix.php';
        require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/features/device-menu/class-device-menu.php';
        require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/features/disable-hover/class-disable-hover.php';
        require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/features/prevent-scroll/class-prevent-scroll.php';
        require_once RESPONSIVE_GOODIES_PLUGIN_DIR . 'includes/features/back-to-top/class-back-to-top.php';
    }
    
    public function run() {
        $this->settings = new Responsive_Goodies_Settings();
        
        // Always allow admin settings
        add_action('admin_menu', array($this->settings, 'add_admin_menu'));
        add_action('admin_init', array($this->settings, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Don't initialize features if Divi builder is active
        if ($this->is_divi_builder_active()) {
            return;
        }
        
        // Initialize features
        $this->features['orphan_fix'] = new Responsive_Goodies_Orphan_Fix();
        $this->features['device_menu'] = new Responsive_Goodies_Device_Menu();
        $this->features['disable_hover'] = new Responsive_Goodies_Disable_Hover();
        $this->features['prevent_scroll'] = new Responsive_Goodies_Prevent_Scroll();
        $this->features['back_to_top'] = new Responsive_Goodies_Back_To_Top();
        
        // Initialize features
        foreach ($this->features as $feature) {
            if (method_exists($feature, 'init')) {
                $feature->init();
            }
        }
        
        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
	
	    
    /**
     * Check if Divi builder is currently active
     */
    private function is_divi_builder_active() {
        // Check for Divi builder URL parameter
        if (isset($_GET['et_fb']) && $_GET['et_fb'] == '1') {
            return true;
        }
        
        // Check for Divi builder in admin
        if (is_admin() && isset($_GET['et_fb'])) {
            return true;
        }
        
        // Check if we're in a Divi builder context
        if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
            return true;
        }
        
        return false;
    }


    
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'responsive-goodies-frontend',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            RESPONSIVE_GOODIES_VERSION
        );
        
        wp_enqueue_script(
            'responsive-goodies-frontend',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            RESPONSIVE_GOODIES_VERSION,
            true
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if ('settings_page_responsive-goodies' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'responsive-goodies-admin',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            RESPONSIVE_GOODIES_VERSION
        );
        
        wp_enqueue_script(
            'responsive-goodies-admin',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            RESPONSIVE_GOODIES_VERSION,
            true
        );
    }
    
    public static function activate() {
        // Set default options
        $default_options = array(
            'orphan_fix_enabled' => false,
            'orphan_fix_max_words' => 2,
            'orphan_fix_exclude_class' => 'no-orphan-fix',
            'orphan_fix_apply_headings' => true,
            'device_menu_enabled' => false,
            'disable_hover_enabled' => false,
            'prevent_scroll_enabled' => false,
            'back_to_top_enabled' => false,
            'back_to_top_desktop' => true,
            'back_to_top_tablet' => true,
            'back_to_top_mobile' => true
        );
        
        add_option('responsive_goodies_options', $default_options);
        
        // Clear changelog cache on activation
        Responsive_Goodies_Changelog::clear_changelog_cache();
    }

    
    public static function deactivate() {
        // Clean up if needed
    }
}
?>
