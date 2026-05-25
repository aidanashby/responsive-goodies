<?php
/**
 * GitHub-based changelog handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class Responsive_Goodies_Changelog {
    
    private static $github_username = 'aidanashby';
    private static $github_repo = 'responsive-goodies';
    
    /**
     * Renders the changelog HTML directly to the page.
     *
     * Falls back to a plain error message if the GitHub API is unreachable.
     */
    public static function display_changelog(): void {
        $changelog_html = self::get_github_changelog();
        
        if ($changelog_html) {
            echo $changelog_html;
        } else {
            echo '<p>Unable to load changelog.</p>';
        }
    }
    
    /**
     * Fetches and builds changelog HTML from the GitHub Releases API.
     *
     * Result is transient-cached for 12 hours to prevent per-pageload API calls.
     * Returns the first 3 releases as sanitised HTML, or false on any failure.
     *
     * @return string|false Sanitised HTML string, or false if the API is unavailable.
     */
    private static function get_github_changelog(): string|false {
        // Don't run if we can't make HTTP requests
        if (!function_exists('wp_remote_get')) {
            return false;
        }
        
        // Check for cached changelog
        $cached = get_transient('rg_github_changelog');
        if ($cached !== false && $cached !== '') {
            return $cached;
        }
        
        try {
            $request = wp_remote_get("https://api.github.com/repos/" . self::$github_username . "/" . self::$github_repo . "/releases", array(
                'timeout' => 10,
                'sslverify' => true
            ));
            
            if (is_wp_error($request)) {
                return false;
            }
            
            if (wp_remote_retrieve_response_code($request) !== 200) {
                return false;
            }
            
            $body = wp_remote_retrieve_body($request);
            $releases = json_decode($body, true);
            
            if (!$releases || !is_array($releases) || json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
            
            $releases = array_slice( $releases, 0, 3 );

            $html = '';
            foreach ( $releases as $release ) {
                $html .= '<div class="rg-changelog-release">';
                $html .= '<h3>' . esc_html( $release['tag_name'] ) . '</h3>';
                if ( ! empty( $release['body'] ) ) {
                    $html .= self::convert_markdown_to_html( $release['body'] );
                }
                $html .= '</div>';
            }

            $html = wp_kses_post( $html );
            set_transient( 'rg_github_changelog', $html, 12 * HOUR_IN_SECONDS );
            return $html;
        } catch (Exception $e) {
            return false;
        }
    }

    
    /**
     * Converts a subset of Markdown to HTML suitable for the changelog modal.
     *
     * Handles ## and ### headers, unordered lists, bold, and line breaks.
     * Not a full Markdown parser — only covers patterns used in GitHub release bodies.
     *
     * @param string $text Raw Markdown text from a GitHub release body.
     * @return string HTML string (not yet sanitised — caller must run wp_kses_post).
     */
    private static function convert_markdown_to_html(string $text): string {
        // Convert markdown headers
        $text = preg_replace('/^## (.+)$/m', '<h4>$1</h4>', $text);
        $text = preg_replace('/^### (.+)$/m', '<h5>$1</h5>', $text);
        
        // Convert markdown lists
        $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
        $text = preg_replace('/(?:<li>.+<\/li>\n?)+/', '<ul>$0</ul>', $text);
        
        // Convert bold text
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        
        // Convert line breaks
        $text = nl2br($text);
        
        return $text;
    }
    
    public static function clear_changelog_cache(): void {
        delete_transient('rg_github_changelog');
    }
}
?>
