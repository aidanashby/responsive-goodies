<?php
/**
 * Uninstall cleanup for Responsive Goodies.
 *
 * Runs only when the plugin is deleted from the Plugins screen. Removes every
 * piece of persistent data the plugin creates: its options row, cached
 * transients, and all per-item / per-menu meta.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Settings option.
delete_option('responsive_goodies_options');

// Cached update/changelog transients.
delete_transient('rg_github_changelog');
delete_transient('rg_update_check');
delete_transient('rg_changelog_info');

// Device-menu per-item post meta.
delete_post_meta_by_key('_rg_show_desktop');
delete_post_meta_by_key('_rg_show_tablet');
delete_post_meta_by_key('_rg_show_mobile');

// Mobile-hamburger per-menu term meta.
delete_metadata('term', 0, '_rg_show_mobile_hamburger', '', true);
