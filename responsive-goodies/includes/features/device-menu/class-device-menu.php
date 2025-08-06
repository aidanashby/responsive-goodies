<?php
/**
 * Device-Based Menu Display Feature
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Device_Menu {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('responsive_goodies_options');
    }
    
    public function init() {
        if ($this->is_enabled()) {
            // Admin hooks
            add_action('wp_nav_menu_item_custom_fields', array($this, 'add_menu_item_fields'), 10, 4);
            add_action('wp_update_nav_menu_item', array($this, 'save_menu_item_fields'), 10, 3);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            
            // Frontend hooks
            add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
            add_filter('nav_menu_css_class', array($this, 'add_menu_item_classes'), 10, 4);
        }
    }
    
    private function is_enabled() {
        return isset($this->options['device_menu_enabled']) && $this->options['device_menu_enabled'];
    }
    
    public function add_menu_item_fields($item_id, $item, $depth, $args) {
        $desktop_visible = get_post_meta($item_id, '_rg_show_desktop', true);
        $tablet_visible = get_post_meta($item_id, '_rg_show_tablet', true);
        $mobile_visible = get_post_meta($item_id, '_rg_show_mobile', true);
        
        // Default to visible if not set
        $desktop_visible = ($desktop_visible !== '') ? $desktop_visible : '1';
        $tablet_visible = ($tablet_visible !== '') ? $tablet_visible : '1';
        $mobile_visible = ($mobile_visible !== '') ? $mobile_visible : '1';
        ?>
        <div class="rg-device-visibility">
            <h4>Device Visibility</h4>
            <label>
                <input type="checkbox" name="rg_show_desktop[<?php echo $item_id; ?>]" value="1" <?php checked($desktop_visible, '1'); ?> />
                Desktop
            </label>
            <label>
                <input type="checkbox" name="rg_show_tablet[<?php echo $item_id; ?>]" value="1" <?php checked($tablet_visible, '1'); ?> />
                Tablet
            </label>
            <label>
                <input type="checkbox" name="rg_show_mobile[<?php echo $item_id; ?>]" value="1" <?php checked($mobile_visible, '1'); ?> />
                Mobile
            </label>

        </div>
        <?php
    }
    
    public function save_menu_item_fields($menu_id, $menu_item_db_id, $args) {
        if (isset($_POST['rg_show_desktop'][$menu_item_db_id])) {
            update_post_meta($menu_item_db_id, '_rg_show_desktop', '1');
        } else {
            update_post_meta($menu_item_db_id, '_rg_show_desktop', '0');
        }
        
        if (isset($_POST['rg_show_tablet'][$menu_item_db_id])) {
            update_post_meta($menu_item_db_id, '_rg_show_tablet', '1');
        } else {
            update_post_meta($menu_item_db_id, '_rg_show_tablet', '0');
        }
        
        if (isset($_POST['rg_show_mobile'][$menu_item_db_id])) {
            update_post_meta($menu_item_db_id, '_rg_show_mobile', '1');
        } else {
            update_post_meta($menu_item_db_id, '_rg_show_mobile', '0');
        }
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'nav-menus.php') {
            wp_enqueue_script(
                'responsive-goodies-device-menu-admin',
                RESPONSIVE_GOODIES_PLUGIN_URL . 'includes/features/device-menu/device-menu-admin.js',
                array('jquery'),
                RESPONSIVE_GOODIES_VERSION,
                true
            );
        }
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'responsive-goodies-device-menu',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'includes/features/device-menu/device-menu.css',
            array(),
            RESPONSIVE_GOODIES_VERSION
        );
    }
    
    public function add_menu_item_classes($classes, $item, $args, $depth) {
        $desktop_visible = get_post_meta($item->ID, '_rg_show_desktop', true);
        $tablet_visible = get_post_meta($item->ID, '_rg_show_tablet', true);
        $mobile_visible = get_post_meta($item->ID, '_rg_show_mobile', true);
        
        // Add device-specific classes
        if ($desktop_visible === '0') {
            $classes[] = 'rg-hide-desktop';
        }
        if ($tablet_visible === '0') {
            $classes[] = 'rg-hide-tablet';
        }
        if ($mobile_visible === '0') {
            $classes[] = 'rg-hide-mobile';
        }
        
        return $classes;
    }
}
?>
