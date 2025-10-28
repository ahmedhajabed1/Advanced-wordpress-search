<?php
/**
 * Search Results Page Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class US_Search_Results_Page {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Modify search query to include products - HIGH PRIORITY
        add_action('pre_get_posts', array($this, 'modify_search_query'), 1);
        
        // Add custom template for search results
        add_filter('template_include', array($this, 'search_template'), 99);
        
        // Enqueue styles for search page
        add_action('wp_enqueue_scripts', array($this, 'enqueue_search_page_styles'));
        
        // Debug: Add admin notice to show what's being searched (delayed to avoid early call)
        add_action('init', array($this, 'setup_debug'));
    }
    
    /**
     * Setup debug functionality after init
     */
    public function setup_debug() {
        if (current_user_can('manage_options')) {
            add_action('wp_footer', array($this, 'debug_search_query'));
        }
    }
    
    /**
     * Debug search query (only for admins)
     */
    public function debug_search_query() {
        if (is_search() && isset($_GET['debug_search'])) {
            global $wp_query;
            echo '<!-- DEBUG: Search Query -->';
            echo '<!-- Post Types: ' . implode(', ', (array)$wp_query->get('post_type')) . ' -->';
            echo '<!-- Found Posts: ' . $wp_query->found_posts . ' -->';
            echo '<!-- Search Term: ' . get_search_query() . ' -->';
            echo '<!-- SQL: ' . $wp_query->request . ' -->';
            
            // Show actual posts in query
            if ($wp_query->posts) {
                echo '<!-- Posts in Query: -->';
                foreach ($wp_query->posts as $post) {
                    echo '<!-- - ' . $post->post_title . ' (' . $post->post_type . ') -->';
                }
            }
            echo '<!-- END DEBUG -->';
        }
    }
    
    /**
     * Modify WordPress search query to include products
     */
    public function modify_search_query($query) {
        // Only modify main search query on frontend
        if (is_admin() || !$query->is_main_query() || !$query->is_search()) {
            return $query;
        }
        
        $settings = get_option('unified_search_settings', array());
        
        // Build post types array
        $post_types = array();
        
        // Check settings or default to both
        $search_posts = isset($settings['search_posts']) ? $settings['search_posts'] : 1;
        $search_products = isset($settings['search_products']) ? $settings['search_products'] : 1;
        
        if ($search_posts) {
            $post_types[] = 'post';
        }
        
        if ($search_products && class_exists('WooCommerce')) {
            $post_types[] = 'product';
        }
        
        // Fallback to both if nothing selected
        if (empty($post_types)) {
            $post_types = array('post');
            if (class_exists('WooCommerce')) {
                $post_types[] = 'product';
            }
        }
        
        // Set the post types
        $query->set('post_type', $post_types);
        $query->set('posts_per_page', 24);
        $query->set('post_status', 'publish');
        
        // Important: Remove WooCommerce's product query modifications
        if (class_exists('WooCommerce') && function_exists('WC') && WC()->query) {
            remove_action('pre_get_posts', array(WC()->query, 'product_query'));
        }
        
        return $query;
    }
    
    /**
     * Use custom search template
     */
    public function search_template($template) {
        if (is_search()) {
            // Check if theme has search.php, if not use our template
            $theme_template = locate_template(array('search.php'));
            
            // Always use our custom template to ensure proper styling
            $custom_template = US_PLUGIN_DIR . 'templates/search-results.php';
            
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Get comprehensive search results using direct SQL
     */
    public static function get_search_results($search_query, $limit = 100) {
        global $wpdb;
        
        if (empty($search_query)) {
            return array();
        }
        
        // Sanitize search query
        $search_term = sanitize_text_field($search_query);
        $search_like = '%' . $wpdb->esc_like($search_term) . '%';
        
        // Get settings
        $settings = get_option('unified_search_settings', array());
        $search_posts = isset($settings['search_posts']) ? $settings['search_posts'] : 1;
        $search_products = isset($settings['search_products']) ? $settings['search_products'] : 1;
        
        $post_types = array();
        if ($search_posts) $post_types[] = 'post';
        if ($search_products && class_exists('WooCommerce')) $post_types[] = 'product';
        
        if (empty($post_types)) {
            return array();
        }
        
        $post_types_str = "'" . implode("','", array_map('esc_sql', $post_types)) . "'";
        
        // COMPREHENSIVE SQL QUERY
        // Searches: title, content, excerpt, tags, categories, meta
        $sql = "SELECT DISTINCT p.ID, p.post_title, p.post_type, p.post_date
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_status = 'publish'
                AND p.post_type IN ($post_types_str)
                AND (
                    p.post_title LIKE %s
                    OR p.post_content LIKE %s
                    OR p.post_excerpt LIKE %s
                    OR t.name LIKE %s
                    OR pm.meta_value LIKE %s
                )
                ORDER BY 
                    CASE 
                        WHEN p.post_title LIKE %s THEN 1
                        WHEN p.post_title LIKE %s THEN 2
                        ELSE 3
                    END,
                    p.post_date DESC
                LIMIT %d";
        
        $exact_match = $wpdb->esc_like($search_term);
        $partial_match = '%' . $wpdb->esc_like($search_term) . '%';
        
        try {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    $sql,
                    $search_like, // title
                    $search_like, // content
                    $search_like, // excerpt
                    $search_like, // tags
                    $search_like, // meta
                    $exact_match, // exact title match (highest priority)
                    $partial_match, // partial title match
                    $limit
                )
            );
            
            return is_array($results) ? $results : array();
        } catch (Exception $e) {
            error_log('Unified Search Error: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Enqueue search page styles
     */
    public function enqueue_search_page_styles() {
        if (is_search()) {
            wp_enqueue_style('us-search-page-css', US_PLUGIN_URL . 'assets/css/search-page.css', array(), US_VERSION);
        }
    }
}
