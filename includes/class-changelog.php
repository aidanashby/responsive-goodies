<?php
/**
 * GitHub-based changelog handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Changelog {
    
    private static $github_username = 'aidnashby';
    private static $github_repo = 'responsive-goodies';
    
    public static function display_changelog() {
        $changelog_html = self::get_github_changelog();
        
        if ($changelog_html) {
            echo $changelog_html;
        } else {
            echo '<p>Unable to load changelog. <a href="https://github.com/' . self::$github_username . '/' . self::$github_repo . '/releases" target="_blank">View on GitHub</a></p>';
        }
    }
    
    private static function get_github_changelog() {
        // Check for cached changelog
        $cached = get_transient('rg_github_changelog');
        if ($cached !== false) {
            return $cached;
        }
        
        $request = wp_remote_get("https://api.github.com/repos/" . self::$github_username . "/" . self::$github_repo . "/releases");
        
        if (is_wp_error($request)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($request);
        $releases = json_decode($body, true);
        
        if (!$releases || !is_array($releases)) {
            return false;
        }
        
        $html = '<div class="rg-changelog">';
        
        foreach ($releases as $release) {
            if ($release['draft'] || $release['prerelease']) {
                continue;
            }
            
            $version = ltrim($release['tag_name'], 'v');
            $date = date('Y-m-d', strtotime($release['published_at']));
            $body = $release['body'];
            
            $html .= '<div class="rg-changelog-version">';
            $html .= '<h3>Version ' . esc_html($version) . ' <span class="rg-changelog-date">(' . esc_html($date) . ')</span></h3>';
            
            // Convert markdown-style content to HTML
            $body = self::convert_markdown_to_html($body);
            $html .= '<div class="rg-changelog-content">' . wp_kses_post($body) . '</div>';
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // Cache for 1 hour
        set_transient('rg_github_changelog', $html, HOUR_IN_SECONDS);
        
        return $html;
    }
    
    private static function convert_markdown_to_html($text) {
        // Convert markdown headers
        $text = preg_replace('/^## (.+)$/m', '<h4>$1</h4>', $text);
        $text = preg_replace('/^### (.+)$/m', '<h5>$1</h5>', $text);
        
        // Convert markdown lists
        $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
        $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
        
        // Convert bold text
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        
        // Convert line breaks
        $text = nl2br($text);
        
        return $text;
    }
    
    public static function clear_changelog_cache() {
        delete_transient('rg_github_changelog');
    }
}
?>
