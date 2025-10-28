<?php
/**
 * Indexer Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class US_Indexer {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Auto-index on post save/update
        add_action('save_post', array($this, 'index_post'), 10, 2);
        add_action('delete_post', array($this, 'delete_from_index'));
        
        // WooCommerce product hooks
        add_action('woocommerce_update_product', array($this, 'index_product'));
        add_action('woocommerce_new_product', array($this, 'index_product'));
    }
    
    /**
     * Reindex all content
     */
    public function reindex_all() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'unified_search_index';
        
        // Clear existing index
        $wpdb->query("TRUNCATE TABLE $table_name");
        
        $settings = get_option('unified_search_settings', array());
        $indexed_count = 0;
        
        // Index products
        if (!empty($settings['search_products']) && class_exists('WooCommerce')) {
            $products = get_posts(array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ));
            
            foreach ($products as $product_id) {
                if ($this->index_post($product_id, get_post($product_id))) {
                    $indexed_count++;
                }
            }
        }
        
        // Index posts
        if (!empty($settings['search_posts'])) {
            $posts = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ));
            
            foreach ($posts as $post_id) {
                if ($this->index_post($post_id, get_post($post_id))) {
                    $indexed_count++;
                }
            }
        }
        
        return $indexed_count;
    }
    
    /**
     * Index a single post
     */
    public function index_post($post_id, $post) {
        // Skip auto-saves and revisions
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return false;
        }
        
        // Only index published posts
        if ($post->post_status !== 'publish') {
            return false;
        }
        
        // Check if post type is enabled
        $settings = get_option('unified_search_settings', array());
        
        if ($post->post_type === 'product' && empty($settings['search_products'])) {
            return false;
        }
        
        if ($post->post_type === 'post' && empty($settings['search_posts'])) {
            return false;
        }
        
        // Only index products and posts
        if (!in_array($post->post_type, array('product', 'post'))) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'unified_search_index';
        
        // Delete existing entry
        $wpdb->delete($table_name, array('post_id' => $post_id));
        
        // Prepare data
        $data = array(
            'post_id' => $post_id,
            'post_type' => $post->post_type,
            'title' => $post->post_title,
            'content' => wp_strip_all_tags($post->post_content),
            'excerpt' => wp_strip_all_tags($post->post_excerpt),
            'indexed_date' => current_time('mysql')
        );
        
        // Get categories
        $categories = wp_get_post_terms($post_id, $post->post_type === 'product' ? 'product_cat' : 'category', array('fields' => 'names'));
        if (!is_wp_error($categories) && !empty($categories)) {
            $data['categories'] = implode(', ', $categories);
        }
        
        // Get tags
        $tags = wp_get_post_terms($post_id, $post->post_type === 'product' ? 'product_tag' : 'post_tag', array('fields' => 'names'));
        if (!is_wp_error($tags) && !empty($tags)) {
            $data['tags'] = implode(', ', $tags);
        }
        
        // Product-specific data - ENHANCED
        if ($post->post_type === 'product' && class_exists('WooCommerce')) {
            $product = wc_get_product($post_id);
            
            if ($product) {
                // Basic product data
                $data['sku'] = $product->get_sku();
                $data['price'] = $product->get_price();
                $data['in_stock'] = $product->is_in_stock() ? 1 : 0;
                
                // IMPORTANT: Add short description to content for better search
                $short_desc = $product->get_short_description();
                if (!empty($short_desc)) {
                    $data['content'] .= ' ' . wp_strip_all_tags($short_desc);
                }
                
                // Add product attributes to tags for better searchability
                $attribute_values = array();
                
                // Safe attribute extraction with error handling
                try {
                    $attributes = $product->get_attributes();
                    
                    if (!empty($attributes) && is_array($attributes)) {
                        foreach ($attributes as $attribute) {
                            if (is_object($attribute) && method_exists($attribute, 'is_taxonomy')) {
                                if ($attribute->is_taxonomy() && method_exists($attribute, 'get_name')) {
                                    // Get taxonomy terms
                                    $terms = wp_get_post_terms($post_id, $attribute->get_name(), array('fields' => 'names'));
                                    if (!is_wp_error($terms) && !empty($terms)) {
                                        $attribute_values = array_merge($attribute_values, $terms);
                                    }
                                } elseif (method_exists($attribute, 'get_options')) {
                                    // Custom attribute
                                    $values = $attribute->get_options();
                                    if (!empty($values) && is_array($values)) {
                                        $attribute_values = array_merge($attribute_values, $values);
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Silently fail attribute extraction if there's an error
                    error_log('Unified Search: Could not extract product attributes for post ' . $post_id);
                }
                
                // Append attributes to tags for searchability
                if (!empty($attribute_values)) {
                    $existing_tags = !empty($data['tags']) ? $data['tags'] : '';
                    $data['tags'] = $existing_tags . ', ' . implode(', ', $attribute_values);
                }
                
                // Add meta keywords if they exist (common in SEO plugins)
                $meta_keywords = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
                if (!empty($meta_keywords)) {
                    $data['tags'] = (!empty($data['tags']) ? $data['tags'] . ', ' : '') . $meta_keywords;
                }
                
                // Add custom searchable meta fields (common pain points, benefits, etc.)
                $searchable_meta_keys = array(
                    '_product_benefits',
                    '_pain_points',
                    '_use_cases',
                    '_target_audience',
                    '_custom_keywords'
                );
                
                foreach ($searchable_meta_keys as $meta_key) {
                    $meta_value = get_post_meta($post_id, $meta_key, true);
                    if (!empty($meta_value)) {
                        $data['content'] .= ' ' . wp_strip_all_tags($meta_value);
                    }
                }
            }
        }
        
        // Calculate relevance score (basic)
        $data['relevance_score'] = strlen($post->post_title) + (strlen($post->post_content) / 100);
        
        // Insert into database
        $result = $wpdb->insert($table_name, $data);
        
        return $result !== false;
    }
    
    /**
     * Index a product
     */
    public function index_product($product_id) {
        $post = get_post($product_id);
        if ($post) {
            return $this->index_post($product_id, $post);
        }
        return false;
    }
    
    /**
     * Delete from index
     */
    public function delete_from_index($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'unified_search_index';
        
        $wpdb->delete($table_name, array('post_id' => $post_id));
    }
}
