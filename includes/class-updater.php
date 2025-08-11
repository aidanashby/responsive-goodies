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
    
    public function check_for_update($transient) {
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
    
    private function get_remote_version() {
        $request = wp_remote_get("https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest");
        
        if (is_wp_error($request)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);
        
        if (isset($data['tag_name'])) {
            return ltrim($data['tag_name'], 'v');
        }
        
        return false;
    }
    
    public function plugin_info($res, $action, $args) {
        if ($action !== 'plugin_information') {
            return false;
        }
        
        if ($args->slug !== dirname($this->plugin_slug)) {
            return false;
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
    
    private function get_changelog() {
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
        
        return $changelog;
    }
	    
    public function post_install($response, $hook_extra, $result) {
        global $wp_filesystem;
        
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_slug) {
            return $response;
        }
        
        // Move from GitHub folder structure to correct plugin folder
        $correct_folder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->plugin_slug);
        if ($result['destination'] !== $correct_folder) {
            $wp_filesystem->move($result['destination'], $correct_folder);
            $result['destination'] = $correct_folder;
        }
        
        return $response;
    }

}

?>
