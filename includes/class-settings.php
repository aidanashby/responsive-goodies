<?php
/**
 * Settings page handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Settings {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('responsive_goodies_options');
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Responsive Goodies Settings',
            'Responsive Goodies',
            'manage_options',
            'responsive-goodies',
            array($this, 'settings_page')
        );
    }
    
    public function init_settings() {
        register_setting(
            'responsive_goodies_settings',
            'responsive_goodies_options',
            array($this, 'sanitize_options')
        );
        
        // Orphan Fix Section
        add_settings_section('orphan_fix_section', '', '__return_empty_string', 'responsive-goodies');
        
        add_settings_field('orphan_fix_enabled', 'Enable Orphan Fix', array($this, 'orphan_fix_enabled_callback'), 'responsive-goodies', 'orphan_fix_section');
        add_settings_field('orphan_fix_max_words', 'Maximum Hanging Words', array($this, 'orphan_fix_max_words_callback'), 'responsive-goodies', 'orphan_fix_section');
        add_settings_field('orphan_fix_exclude_class', 'Exclude CSS Class', array($this, 'orphan_fix_exclude_class_callback'), 'responsive-goodies', 'orphan_fix_section');
        add_settings_field('orphan_fix_apply_headings', 'Apply to Headings', array($this, 'orphan_fix_apply_headings_callback'), 'responsive-goodies', 'orphan_fix_section');
        
        // Device Menu Section
        add_settings_section('device_menu_section', '', '__return_empty_string', 'responsive-goodies');
        add_settings_field('device_menu_enabled', 'Enable Device Menu Controls', array($this, 'device_menu_enabled_callback'), 'responsive-goodies', 'device_menu_section');
        
        // Disable Hover Section
        add_settings_section('disable_hover_section', '', '__return_empty_string', 'responsive-goodies');
        add_settings_field('disable_hover_enabled', 'Disable Hover Effects on Touch Devices', array($this, 'disable_hover_enabled_callback'), 'responsive-goodies', 'disable_hover_section');
        
        // Prevent Scroll Section
        add_settings_section('prevent_scroll_section', '', '__return_empty_string', 'responsive-goodies');
        add_settings_field('prevent_scroll_enabled', 'Prevent Horizontal Scroll', array($this, 'prevent_scroll_enabled_callback'), 'responsive-goodies', 'prevent_scroll_section');
        
                // Back to Top Section
        add_settings_section('back_to_top_section', '', '__return_empty_string', 'responsive-goodies');
        add_settings_field('back_to_top_enabled', 'Enable Back to Top Button Control', array($this, 'back_to_top_enabled_callback'), 'responsive-goodies', 'back_to_top_section');
        add_settings_field('back_to_top_devices', 'Show Back to Top Button On', array($this, 'back_to_top_devices_callback'), 'responsive-goodies', 'back_to_top_section');
        
        // Changelog Section
        add_settings_section('changelog_section', '', '__return_empty_string', 'responsive-goodies');
        add_settings_field('changelog_display', 'Plugin Changelog', array($this, 'changelog_display_callback'), 'responsive-goodies', 'changelog_section');
    }

    
    public function sanitize_options($input) {
        $sanitized = array();
        
        $sanitized['orphan_fix_enabled'] = isset($input['orphan_fix_enabled']) ? true : false;
        $sanitized['orphan_fix_max_words'] = max(2, min(10, absint($input['orphan_fix_max_words'])));
        $sanitized['orphan_fix_exclude_class'] = sanitize_text_field($input['orphan_fix_exclude_class']);
        $sanitized['orphan_fix_apply_headings'] = isset($input['orphan_fix_apply_headings']) ? true : false;
        
        $sanitized['device_menu_enabled'] = isset($input['device_menu_enabled']) ? true : false;
        $sanitized['disable_hover_enabled'] = isset($input['disable_hover_enabled']) ? true : false;
        $sanitized['prevent_scroll_enabled'] = isset($input['prevent_scroll_enabled']) ? true : false;
        
        $sanitized['back_to_top_enabled'] = isset($input['back_to_top_enabled']) ? true : false;
        $sanitized['back_to_top_desktop'] = isset($input['back_to_top_desktop']) ? true : false;
        $sanitized['back_to_top_tablet'] = isset($input['back_to_top_tablet']) ? true : false;
        $sanitized['back_to_top_mobile'] = isset($input['back_to_top_mobile']) ? true : false;
        
        return $sanitized;
    }
    
    public function settings_page() {
        ?>
        <div class="wrap responsive-goodies-admin">
            <h1>Responsive Goodies Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('responsive_goodies_settings'); ?>
                
                <div class="rg-settings-container">
                    <div class="rg-feature-group">
                        <?php $this->render_settings_section_fields('orphan_fix_section'); ?>
                    </div>
                    
                    <div class="rg-feature-group">
                        <?php $this->render_settings_section_fields('device_menu_section'); ?>
                    </div>
                    
                    <div class="rg-feature-group">
                        <?php $this->render_settings_section_fields('disable_hover_section'); ?>
                    </div>
                    
                    <div class="rg-feature-group">
                        <?php $this->render_settings_section_fields('prevent_scroll_section'); ?>
                    </div>
                    
                    <div class="rg-feature-group">
                        <?php $this->render_settings_section_fields('back_to_top_section'); ?>
                    </div>
                    
                    <div class="rg-feature-group rg-changelog-section">
                        <?php $this->render_settings_section_fields('changelog_section'); ?>
                    </div>
                </div>
                
                <?php submit_button(); ?>

            </form>
        </div>
        <?php
    }

    
    private function render_settings_section_fields($section) {
        global $wp_settings_fields;
        
        if (!isset($wp_settings_fields['responsive-goodies'][$section])) {
            return;
        }
        
        echo '<table class="form-table" role="presentation">';
        foreach ($wp_settings_fields['responsive-goodies'][$section] as $field) {
            echo '<tr>';
            if (!empty($field['args']['label_for'])) {
                echo '<th scope="row"><label for="' . esc_attr($field['args']['label_for']) . '">' . $field['title'] . '</label></th>';
            } else {
                echo '<th scope="row">' . $field['title'] . '</th>';
            }
            echo '<td>';
            call_user_func($field['callback'], $field['args']);
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    public function orphan_fix_enabled_callback() {
        $enabled = isset($this->options['orphan_fix_enabled']) ? $this->options['orphan_fix_enabled'] : false;
        ?>
        <label class="rg-toggle-switch">
            <input type="checkbox" name="responsive_goodies_options[orphan_fix_enabled]" value="1" <?php checked($enabled); ?> />
            <span class="rg-slider"></span>
        </label>
        <div class="rg-tooltip">
            <span class="rg-tooltip-icon">?</span>
            <span class="rg-tooltip-text">Prevent orphan words at the end of text blocks by adding non-breaking spaces</span>
        </div>
        <?php
    }
    
    public function orphan_fix_max_words_callback() {
        $max_words = isset($this->options['orphan_fix_max_words']) ? $this->options['orphan_fix_max_words'] : 2;
        ?>
        <input type="number" name="responsive_goodies_options[orphan_fix_max_words]" value="<?php echo esc_attr($max_words); ?>" min="2" max="10" />
        <div class="rg-tooltip">
            <span class="rg-tooltip-icon">?</span>
            <span class="rg-tooltip-text">Maximum number of words to keep together on the last line (2-10)</span>
        </div>
        <?php
    }
    
    public function orphan_fix_exclude_class_callback() {
        $exclude_class = isset($this->options['orphan_fix_exclude_class']) ? $this->options['orphan_fix_exclude_class'] : 'no-orphan-fix';
        ?>
        <input type="text" name="responsive_goodies_options[orphan_fix_exclude_class]" value="<?php echo esc_attr($exclude_class); ?>" class="regular-text" />
        <div class="rg-tooltip">
            <span class="rg-tooltip-icon">?</span>
            <span class="rg-tooltip-text">CSS class to exclude elements and their children from orphan fix</span>
        </div>
        <?php
    }
    
    public function orphan_fix_apply_headings_callback() {
        $apply_headings = isset($this->options['orphan_fix_apply_headings']) ? $this->options['orphan_fix_apply_headings'] : true;
        ?>
        <label class="rg-toggle-switch">
            <input type="checkbox" name="responsive_goodies_options[orphan_fix_apply_headings]" value="1" <?php checked($apply_headings); ?> />
            <span class="rg-slider"></span>
        </label>
        <div class="rg-tooltip">
            <span class="rg-tooltip-icon">?</span>
            <span class="rg-tooltip-text">Apply orphan fix to headings (h1, h2, h3, h4, h5, h6)</span>
        </div>
        <?php
    }
    
    public function device_menu_enabled_callback() {
        $enabled = isset($this->options['device_menu_enabled']) ? $this->options['device_menu_enabled'] : false;
        ?>
        <label class="rg-toggle-switch">
            <input type="checkbox" name="responsive_goodies_options[device_menu_enabled]" value="1" <?php checked($enabled); ?> />
            <span class="rg-slider"></span>
        </label>
        <div class="rg-tooltip">
            <span class="rg-tooltip-icon">?</span>
            <span class="rg-tooltip-text">Add device-specific visibility controls to menu items in Appearance → Menus</span>
        </div>
        <?php
    }
    
    public function disable_hover_enabled_callback() {
        $enabled = isset($this->options['disable_hover_enabled']) ? $this->options['disable_hover_enabled'] : false;
        ?>
        <label class="rg-toggle-switch">
            <input type="checkbox" name="responsive_goodies_options[disable_hover_enabled]" value="1" <?php checked($enabled); ?> />
            <span class="rg-slider"></span>
        </label>
        <div class="rg-tooltip">
            <span class="rg-tooltip-icon">?</span>
            <span class="rg-tooltip-text">Disable hover effects on tablet and mobile devices to prevent sticky hover states</span>
        </div>
        <?php
    }
    
    public function prevent_scroll_enabled_callback() {
        $enabled = isset($this->options['prevent_scroll_enabled']) ? $this->options['prevent_scroll_enabled'] : false;
        ?>
        <label class="rg-toggle-switch">
            <input type="checkbox" name="responsive_goodies_options[prevent_scroll_enabled]" value="1" <?php checked($enabled); ?> />
            <span class="rg-slider"></span>
        </label>
        <div class="rg-tooltip">
            <span class="rg-tooltip-icon">?</span>
            <span class="rg-tooltip-text">Prevent horizontal scrolling by hiding any content that overflows horizontally</span>
        </div>
        <?php
    }
    
    public function back_to_top_enabled_callback() {
        $enabled = isset($this->options['back_to_top_enabled']) ? $this->options['back_to_top_enabled'] : false;
        ?>
        <label class="rg-toggle-switch">
            <input type="checkbox" name="responsive_goodies_options[back_to_top_enabled]" value="1" <?php checked($enabled); ?> />
            <span class="rg-slider"></span>
        </label>
        <div class="rg-tooltip">
            <span class="rg-tooltip-icon">?</span>
            <span class="rg-tooltip-text">Control back to top button visibility on different devices</span>
        </div>
        <?php
    }
    
    public function back_to_top_devices_callback() {
        $desktop = isset($this->options['back_to_top_desktop']) ? $this->options['back_to_top_desktop'] : true;
        $tablet = isset($this->options['back_to_top_tablet']) ? $this->options['back_to_top_tablet'] : true;
        $mobile = isset($this->options['back_to_top_mobile']) ? $this->options['back_to_top_mobile'] : true;
        ?>
        <div class="rg-device-checkboxes">
            <div class="rg-device-toggle">
                <label class="rg-toggle-switch">
                    <input type="checkbox" name="responsive_goodies_options[back_to_top_desktop]" value="1" <?php checked($desktop); ?> />
                    <span class="rg-slider"></span>
                </label>
                <span class="rg-device-label">Desktop</span>
            </div>
            <div class="rg-device-toggle">
                <label class="rg-toggle-switch">
                    <input type="checkbox" name="responsive_goodies_options[back_to_top_tablet]" value="1" <?php checked($tablet); ?> />
                    <span class="rg-slider"></span>
                </label>
                <span class="rg-device-label">Tablet</span>
            </div>
            <div class="rg-device-toggle">
                <label class="rg-toggle-switch">
                    <input type="checkbox" name="responsive_goodies_options[back_to_top_mobile]" value="1" <?php checked($mobile); ?> />
                    <span class="rg-slider"></span>
                </label>
                <span class="rg-device-label">Mobile</span>
            </div>
        </div>
        <?php
    }
	
	    
    public function changelog_display_callback() {
        ?>
        <div class="rg-changelog-container">
            <?php Responsive_Goodies_Changelog::display_changelog(); ?>
            <p class="rg-changelog-note">
                <small>Changelog is automatically updated from GitHub releases. 
                <a href="#" onclick="location.reload(); return false;">Refresh page</a> to see latest changes.
                </small>
            </p>
        </div>
        <?php
    }


}
?>
