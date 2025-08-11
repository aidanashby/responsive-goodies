<?php
/**
 * Changelog handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Changelog {
    
    public static function get_changelog() {
        return array(
            '0.3.4' => array(
                'date' => '2024-12-19',
                'changes' => array(
                    'Fixed: Disabled all plugin features when Divi builder is active (?et_fb=1) to prevent conflicts',
                    'Fixed: Orphan fix feature no longer interferes with Divi 5 visual builder',
                    'Improved: Better detection of Divi builder contexts including admin and frontend'
                )
            ),
            '0.3.3' => array(
                'date' => '2024-12-19',
                'changes' => array(
                    'Fixed: Divi 5 compatibility issue with Prevent Horizontal Scroll feature',
                    'Fixed: Content width restrictions that interfered with Divi\'s responsive layout system',
                    'Fixed: Header layout issues in Divi 5 caused by overly aggressive CSS rules',
                    'Improved: More targeted CSS rules for preventing horizontal scroll'
                )
            ),
            '0.3.2' => array(
                'date' => '2024-12-18',
                'changes' => array(
                    'Fixed: Plugin settings link in WordPress plugins page',
                    'Improved: GitHub updater integration for automatic plugin updates'
                )
            ),
            '0.3.1' => array(
                'date' => '2024-12-18',
                'changes' => array(
                    'Fixed: Settings page layout improvements and bug fixes'
                )
            ),
            '0.3' => array(
                'date' => '2024-12-18',
                'changes' => array(
                    'Added: Disable hover effects on touch devices feature',
                    'Added: Prevent horizontal scroll feature',
                    'Added: Back to top button visibility controls with device-specific options',
                    'Improved: Settings page UI with toggle switches and hover tooltips',
                    'Improved: All features organized into sectioned layout with horizontal dividers',
                    'Updated: All features now use Divi\'s responsive breakpoints consistently'
                )
            ),
            '0.2' => array(
                'date' => '2024-12-17',
                'changes' => array(
                    'Added: Device-based menu display feature for controlling menu item visibility',
                    'Added: Menu admin interface with device checkboxes in Appearance → Menus',
                    'Improved: Settings page styling with individual feature sections',
                    'Fixed: Orphan text logic to properly handle word count (now uses maxWords - 1 spaces)',
                    'Updated: Orphan fix word range changed from 1-10 to 2-10'
                )
            ),
            '0.1' => array(
                'date' => '2024-12-16',
                'changes' => array(
                    'Initial release',
                    'Added: Orphan text fix functionality with configurable word limits',
                    'Added: CSS class exclusion for orphan fix',
                    'Added: Option to apply orphan fix to headings',
                    'Added: Basic plugin settings page',
                    'Added: Modular plugin architecture for future feature additions'
                )
            )
        );
    }
    
    public static function display_changelog() {
        $changelog = self::get_changelog();
        
        echo '<div class="rg-changelog">';
        foreach ($changelog as $version => $data) {
            echo '<div class="rg-changelog-version">';
            echo '<h3>Version ' . esc_html($version) . ' <span class="rg-changelog-date">(' . esc_html($data['date']) . ')</span></h3>';
            echo '<ul>';
            foreach ($data['changes'] as $change) {
                echo '<li>' . esc_html($change) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        echo '</div>';
    }
}
?>
