<?php
/**
 * GitHub-based plugin updater
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Updater {
    
    private $plugin_slug;
    private $plugin_file;
    private $version;
    private $github_username;
    private $github_repo;
    
    public function __construct($plugin_file, $github_username, $github_repo) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->version = RESPONSIVE_GOODIES_VERSION;
        $this->github_username = $github_username;
        $this->github_repo = $github_repo;
        
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_filter('upgrader_post_install', array($this, 'post_install'), 10, 3);
    }
    
    /**
     * Injects update data into the WordPress plugin transient when a newer version exists.
     *
     * Hooked to pre_set_site_transient_update_plugins. Only runs when WordPress has
     * already populated the checked list; short-circuits on empty transient to avoid
     * redundant API calls during the initial plugin list build.
     *
     * @param mixed $transient The current update_plugins transient value.
     * @return mixed The transient, modified with update data if a newer version is found.
     */
    public function check_for_update(mixed $transient): mixed {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        $remote_version = $this->get_remote_version();
        
        if (version_compare($this->version, $remote_version, '<')) {
            $transient->response[$this->plugin_slug] = (object) array(
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $remote_version,
                'url' => "https://github.com/{$this->github_username}/{$this->github_repo}",
                'package' => "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/tags/v{$remote_version}.zip",
                'upgrade_notice' => 'Backup your site before updating.'
            );
        }
        
        return $transient;
    }
    
    /**
     * Fetches the latest release version string from the GitHub Releases API.
     *
     * Result is transient-cached for 6 hours to prevent per-pageload API calls.
     * Strips the leading 'v' from the tag name before returning.
     *
     * @return string|false Version string (e.g. "0.4.0"), or false on failure.
     */
    private function get_remote_version(): string|false {
        $cached = get_transient( 'rg_update_check' );
        if ( false !== $cached ) {
            return $cached;
        }

        $request = wp_remote_get("https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest");

        if (is_wp_error($request)) {
            return false;
        }

        if (200 !== (int) wp_remote_retrieve_response_code($request)) {
            return false;
        }

        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);

        if (isset($data['tag_name'])) {
            $version = ltrim($data['tag_name'], 'v');
            set_transient( 'rg_update_check', $version, 6 * HOUR_IN_SECONDS );
            return $version;
        }

        return false;
    }
    
    public function plugin_info(mixed $res, string $action, mixed $args): mixed {
        if ($action !== 'plugin_information') {
            return $res;
        }

        if ($args->slug !== dirname($this->plugin_slug)) {
            return $res;
        }
        
        $remote_version = $this->get_remote_version();
        
        if (!$remote_version) {
            return false;
        }
        
        $res = new stdClass();
        $res->name = 'Responsive Goodies';
        $res->slug = dirname($this->plugin_slug);
        $res->version = $remote_version;
        $res->tested = '6.4';
        $res->requires = '5.0';
        $res->author = 'Aidan Ashby';
        $res->author_profile = 'https://lucidrhino.design';
        $res->download_link = "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/tags/v{$remote_version}.zip";
        $res->trunk = "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/heads/main.zip";
        $res->homepage = "https://github.com/{$this->github_username}/{$this->github_repo}";
        $res->last_updated = date('Y-m-d');
        $res->sections = array(
            'description' => 'A comprehensive WordPress plugin that provides essential responsive design utilities for modern websites.',
            'installation' => 'This plugin updates automatically. If you experience issues, deactivate and reactivate the plugin.',
            'changelog' => $this->get_changelog()
        );

        
        return $res;
    }
    
    /**
     * Fetches release notes from GitHub to populate the WordPress update modal.
     *
     * Returns the last 5 releases as HTML. Result is transient-cached for 12 hours.
     * Falls back to a plain "View on GitHub" string if the API call fails.
     *
     * @return string HTML string of release notes, or a fallback message on failure.
     */
    private function get_changelog(): string {
        $cached = get_transient( 'rg_changelog_info' );
        if ( false !== $cached ) {
            return $cached;
        }

        $request = wp_remote_get("https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases");

        if (is_wp_error($request)) {
            return 'View changelog on GitHub.';
        }

        $body = wp_remote_retrieve_body($request);
        $releases = json_decode($body, true);

        if (!$releases) {
            return 'View changelog on GitHub.';
        }

        $changelog = '<div>';
        foreach (array_slice($releases, 0, 5) as $release) {
            $changelog .= '<h4>' . esc_html($release['name']) . '</h4>';
            $changelog .= '<p>' . wp_kses_post($release['body']) . '</p>';
        }
        $changelog .= '</div>';

        set_transient( 'rg_changelog_info', $changelog, 12 * HOUR_IN_SECONDS );
        return $changelog;
    }
	    
    /**
     * Moves the installed plugin files to the correct directory after a GitHub ZIP update.
     *
     * GitHub archives unzip into a folder named after the tag (e.g. responsive-goodies-0.4.0),
     * not the plugin slug. This hook renames the destination to match the slug so WordPress
     * can locate the plugin after the update completes.
     *
     * @param mixed $response  The upgrader response object passed through.
     * @param array $hook_extra Extra data provided by the upgrader (includes plugin slug).
     * @param array $result    Install result array including the destination path.
     * @return mixed The response, unchanged (side-effect: directory rename).
     */
    public function post_install(mixed $response, array $hook_extra, array $result): mixed {
        global $wp_filesystem;
        
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_slug) {
            return $response;
        }
        
        // Move from GitHub folder structure to correct plugin folder
        $correct_folder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->plugin_slug);
        if ($result['destination'] !== $correct_folder) {
            $moved = $wp_filesystem->move($result['destination'], $correct_folder);
            if (!$moved) {
                return new WP_Error(
                    'rg_move_failed',
                    sprintf(
                        /* translators: 1: source path, 2: destination path */
                        __('Responsive Goodies update failed: could not move plugin files from %1$s to %2$s.', 'responsive-goodies'),
                        $result['destination'],
                        $correct_folder
                    )
                );
            }
            $result['destination'] = $correct_folder;
        }

        return $response;
    }

}

?>
