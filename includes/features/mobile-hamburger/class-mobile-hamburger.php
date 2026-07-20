<?php
/**
 * Mobile Hamburger Control Feature
 *
 * Adds a per-menu "Show hamburger menu on mobile" checkbox to the Menu Settings
 * box in Appearance → Menus. When unticked, the menu's Divi module keeps its
 * items visible on mobile instead of collapsing to a hamburger.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Mobile_Hamburger {

    const META_KEY = '_rg_show_mobile_hamburger';

    private $options;

    public function __construct() {
        $this->options = get_option('responsive_goodies_options');
    }

    public function init(): void {
        if (!$this->is_enabled()) {
            return;
        }

        // Admin: render the checkbox in Menu Settings and save it.
        add_action('admin_footer-nav-menus.php', array($this, 'render_menu_setting'));
        add_action('wp_update_nav_menu', array($this, 'save_menu_setting'));

        // Frontend: mark menus with the toggle off, then enqueue assets.
        add_filter('wp_nav_menu_args', array($this, 'mark_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }

    private function is_enabled(): bool {
        return !empty($this->options['mobile_hamburger_enabled']);
    }

    /**
     * Injects the checkbox into the Menu Settings box.
     *
     * WordPress provides no hook inside that box, so the field is printed into a
     * hidden container and moved into .menu-settings (inside the #update-nav-menu
     * form) with a small inline script so it posts with the menu.
     */
    public function render_menu_setting(): void {
        global $nav_menu_selected_id;

        if (empty($nav_menu_selected_id)) {
            return;
        }

        $show = get_term_meta($nav_menu_selected_id, self::META_KEY, true);
        $checked = ($show !== '0'); // Absent or '1' = checked by default.

        ob_start();
        wp_nonce_field('rg_hamburger_save', 'rg_hamburger_nonce');
        $nonce = ob_get_clean();
        ?>
        <div id="rg-hamburger-setting" style="display:none;">
            <div class="menu-settings-group rg-hamburger-group">
                <span class="menu-settings-group-name">Responsive Goodies</span>
                <div class="menu-settings-input">
                    <label>
                        <input type="hidden" name="rg_hamburger_field" value="1" />
                        <?php echo $nonce; ?>
                        <input type="checkbox" name="rg_show_mobile_hamburger" value="1" <?php checked($checked); ?> />
                        Show hamburger menu on mobile
                    </label>
                </div>
            </div>
        </div>
        <script>
        (function () {
            var src = document.getElementById('rg-hamburger-setting');
            var box = document.querySelector('#update-nav-menu .menu-settings');
            if (src && box) {
                box.appendChild(src.firstElementChild);
                src.parentNode.removeChild(src);
            }
        })();
        </script>
        <?php
    }

    /**
     * Persists the checkbox as term meta on menu save.
     *
     * Only runs when our field was actually rendered (marker + nonce present),
     * so unrelated menu updates never write the meta.
     */
    public function save_menu_setting(int $menu_id): void {
        if (!isset($_POST['rg_hamburger_field'])) {
            return;
        }

        if (!isset($_POST['rg_hamburger_nonce']) || !wp_verify_nonce($_POST['rg_hamburger_nonce'], 'rg_hamburger_save')) {
            return;
        }

        if (!current_user_can('edit_theme_options')) {
            return;
        }

        $value = isset($_POST['rg_show_mobile_hamburger']) ? '1' : '0';
        update_term_meta($menu_id, self::META_KEY, $value);
    }

    /**
     * Adds the rg-nmh marker class to the menu <ul> when its toggle is off.
     *
     * Runs for every wp_nav_menu() call, including Divi menu modules, so the
     * frontend script can find the module wrapper regardless of Divi version.
     */
    public function mark_menu(array $args): array {
        $menu_id = $this->resolve_menu_id($args);

        if ($menu_id && get_term_meta($menu_id, self::META_KEY, true) === '0') {
            $args['menu_class'] = trim(($args['menu_class'] ?? '') . ' rg-nmh');
        }

        return $args;
    }

    private function resolve_menu_id(array $args): int {
        if (!empty($args['menu'])) {
            $menu = wp_get_nav_menu_object($args['menu']);
            if ($menu) {
                return (int) $menu->term_id;
            }
        }

        if (!empty($args['theme_location'])) {
            $locations = get_nav_menu_locations();
            if (isset($locations[$args['theme_location']])) {
                return (int) $locations[$args['theme_location']];
            }
        }

        return 0;
    }

    public function enqueue_frontend_assets(): void {
        wp_enqueue_style(
            'responsive-goodies-mobile-hamburger',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'includes/features/mobile-hamburger/mobile-hamburger.css',
            array(),
            RESPONSIVE_GOODIES_VERSION
        );

        wp_enqueue_script(
            'responsive-goodies-mobile-hamburger',
            RESPONSIVE_GOODIES_PLUGIN_URL . 'includes/features/mobile-hamburger/mobile-hamburger-frontend.js',
            array(),
            RESPONSIVE_GOODIES_VERSION,
            array('strategy' => 'defer')
        );
    }
}
?>
