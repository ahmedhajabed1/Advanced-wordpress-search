<?php
/**
 * Search Results Page Handler - STABLE VERSION
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
        // Simple hook - just modify the query to include products
        add_action('pre_get_posts', array($this, 'include_products_in_search'));
    }
    
    /**
     * Include products in WordPress search
     */
    public function include_products_in_search($query) {
        // Only modify main search query
        if (!is_admin() && $query->is_main_query() && $query->is_search()) {
            // Add product post type to search
            $post_types = array('post');
            
            if (class_exists('WooCommerce')) {
                $post_types[] = 'product';
            }
            
            $query->set('post_type', $post_types);
        }
    }
}
