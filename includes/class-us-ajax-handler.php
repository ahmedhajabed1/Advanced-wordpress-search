<?php
/**
 * AJAX Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class US_Ajax_Handler {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_us_search', array($this, 'handle_search'));
        add_action('wp_ajax_nopriv_us_search', array($this, 'handle_search'));
        add_action('wp_ajax_us_reindex', array($this, 'handle_reindex'));
    }
    
    /**
     * Handle search AJAX request
     */
    public function handle_search() {
        check_ajax_referer('us-search-nonce', 'nonce');
        
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (empty($query)) {
            wp_send_json_error(array(
                'message' => __('Empty search query', 'unified-search')
            ));
        }
        
        $search_engine = US_Search_Engine::get_instance();
        $results = $search_engine->search($query);
        
        if (empty($results)) {
            wp_send_json_success(array(
                'results' => array(),
                'message' => __('No results found', 'unified-search')
            ));
        }
        
        wp_send_json_success(array(
            'results' => $results,
            'count' => count($results)
        ));
    }
    
    /**
     * Handle reindex AJAX request
     */
    public function handle_reindex() {
        check_ajax_referer('us-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('Unauthorized access', 'unified-search')
            ));
        }
        
        $indexer = US_Indexer::get_instance();
        $result = $indexer->reindex_all();
        
        if ($result) {
            wp_send_json_success(array(
                'message' => sprintf(__('Successfully indexed %d items', 'unified-search'), $result),
                'count' => $result
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to index content', 'unified-search')
            ));
        }
    }
}
