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
    
    public function init(): void {
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
    
    private function is_enabled(): bool {
        return isset($this->options['device_menu_enabled']) && $this->options['device_menu_enabled'];
    }
    
    public function add_menu_item_fields(int $item_id, mixed $item, int $depth, mixed $args): void {
        $desktop_visible = get_post_meta($item_id, '_rg_show_desktop', true);
        $tablet_visible = get_post_meta($item_id, '_rg_show_tablet', true);
        $mobile_visible = get_post_meta($item_id, '_rg_show_mobile', true);
        
        // New menu items default to desktop-only: shown on desktop, hidden on tablet/mobile
        $desktop_visible = ($desktop_visible !== '') ? $desktop_visible : '1';
        $tablet_visible = ($tablet_visible !== '') ? $tablet_visible : '0';
        $mobile_visible = ($mobile_visible !== '') ? $mobile_visible : '0';
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
    
    public function save_menu_item_fields(int $menu_id, int $menu_item_db_id, mixed $args): void {
        // WordPress fires this on the AJAX "add item" request too, before our
        // checkboxes are rendered or submitted. Skip if none of our fields are
        // present at all, so a brand-new item keeps its unset (default) meta
        // instead of every field being written as '0'.
        $has_rg_fields = isset($_POST['rg_show_desktop']) || isset($_POST['rg_show_tablet']) || isset($_POST['rg_show_mobile']);
        if (!$has_rg_fields) {
            return;
        }

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
    
    public function enqueue_admin_scripts(string $hook): void {
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
    
    public function enqueue_frontend_assets(): void {
        wp_enqueue_style(
            'responsive-goodies-device-menu',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'includes/features/device-menu/device-menu.css',
            array(),
            RESPONSIVE_GOODIES_VERSION
        );
    }
    
    public function add_menu_item_classes(array $classes, mixed $item, mixed $args, int $depth): array {
        $desktop_visible = get_post_meta($item->ID, '_rg_show_desktop', true);
        $tablet_visible = get_post_meta($item->ID, '_rg_show_tablet', true);
        $mobile_visible = get_post_meta($item->ID, '_rg_show_mobile', true);
        
        // New menu items (no meta saved yet) default to desktop-only visibility
        if ($desktop_visible === '0') {
            $classes[] = 'rg-hide-desktop';
        }
        if ($tablet_visible === '0' || $tablet_visible === '') {
            $classes[] = 'rg-hide-tablet';
        }
        if ($mobile_visible === '0' || $mobile_visible === '') {
            $classes[] = 'rg-hide-mobile';
        }
        
        return $classes;
    }
}
?>
