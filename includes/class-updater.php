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
                'package' => "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/tags/v{$remote_version}.zip"
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
}
?>
