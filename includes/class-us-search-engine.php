<?php
/**
 * Search Engine Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class US_Search_Engine {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Constructor
    }
    
    /**
     * Perform search
     */
    public function search($query, $settings = array()) {
        global $wpdb;
        
        if (empty($query) || strlen($query) < 2) {
            return array();
        }
        
        $search_settings = get_option('unified_search_settings', array());
        $settings = wp_parse_args($settings, $search_settings);
        
        $table_name = $wpdb->prefix . 'unified_search_index';
        $results = array();
        
        // Sanitize search query
        $search_query = sanitize_text_field($query);
        
        // Split into individual words for better matching
        $search_words = array_filter(explode(' ', $search_query));
        
        // Build WHERE clause
        $post_types = array();
        
        if (!empty($settings['search_products'])) {
            $post_types[] = 'product';
        }
        if (!empty($settings['search_posts'])) {
            $post_types[] = 'post';
        }
        
        if (empty($post_types)) {
            return array();
        }
        
        $post_type_sql = "post_type IN ('" . implode("','", array_map('esc_sql', $post_types)) . "')";
        
        // Build search conditions - search for ANY word match
        $search_conditions = array();
        
        foreach ($search_words as $word) {
            $word = esc_sql($wpdb->esc_like($word));
            $word_conditions = array();
            
            // Always search in title (most important)
            $word_conditions[] = "title LIKE '%%$word%%'";
            
            if (!empty($settings['search_in_content'])) {
                $word_conditions[] = "content LIKE '%%$word%%'";
            }
            if (!empty($settings['search_in_excerpt'])) {
                $word_conditions[] = "excerpt LIKE '%%$word%%'";
            }
            if (!empty($settings['search_in_sku'])) {
                $word_conditions[] = "sku LIKE '%%$word%%'";
            }
            if (!empty($settings['search_in_categories'])) {
                $word_conditions[] = "categories LIKE '%%$word%%'";
            }
            if (!empty($settings['search_in_tags'])) {
                $word_conditions[] = "tags LIKE '%%$word%%'";
            }
            
            if (!empty($word_conditions)) {
                // OR condition for each word (find if ANY field matches this word)
                $search_conditions[] = '(' . implode(' OR ', $word_conditions) . ')';
            }
        }
        
        if (empty($search_conditions)) {
            return array();
        }
        
        // Use OR between words for broader matching
        $search_sql = implode(' OR ', $search_conditions);
        
        // Get max results
        $max_results = isset($settings['max_results']) ? intval($settings['max_results']) : 10;
        
        // Build full SQL with relevance scoring
        $full_search_term = esc_sql($wpdb->esc_like($search_query));
        
        $sql = "SELECT *, 
                CASE 
                    WHEN title LIKE '$full_search_term' THEN 100
                    WHEN title LIKE '$full_search_term%%' THEN 90
                    WHEN title LIKE '%%$full_search_term%%' THEN 80
                    WHEN tags LIKE '%%$full_search_term%%' THEN 70
                    WHEN categories LIKE '%%$full_search_term%%' THEN 60
                    WHEN content LIKE '%%$full_search_term%%' THEN 50
                    ELSE relevance_score
                END as calculated_score
                FROM $table_name 
                WHERE $post_type_sql 
                AND ($search_sql)
                ORDER BY calculated_score DESC, indexed_date DESC
                LIMIT $max_results";
        
        $results_raw = $wpdb->get_results($sql);
        
        if (empty($results_raw)) {
            return array();
        }
        
        // Format results
        foreach ($results_raw as $row) {
            $post = get_post($row->post_id);
            
            if (!$post || $post->post_status !== 'publish') {
                continue;
            }
            
            $result = array(
                'id' => $row->post_id,
                'type' => $row->post_type,
                'title' => $row->title,
                'excerpt' => wp_trim_words($row->excerpt ?: $row->content, 20),
                'url' => get_permalink($row->post_id),
                'image' => get_the_post_thumbnail_url($row->post_id, 'thumbnail'),
                'date' => get_the_date('', $row->post_id),
                'author' => get_the_author_meta('display_name', $post->post_author)
            );
            
            // Product-specific data
            if ($row->post_type === 'product' && class_exists('WooCommerce')) {
                $product = wc_get_product($row->post_id);
                
                if ($product) {
                    $result['price'] = $product->get_price_html();
                    $result['add_to_cart'] = $product->is_purchasable() && $product->is_in_stock();
                    $result['add_to_cart_url'] = $product->add_to_cart_url();
                    
                    if (!$result['image']) {
                        $result['image'] = wc_placeholder_img_src('thumbnail');
                    }
                }
            }
            
            if (!$result['image']) {
                $result['image'] = US_PLUGIN_URL . 'assets/images/placeholder.png';
            }
            
            $results[] = $result;
        }
        
        return $results;
    }
        $results_per_type = isset($settings['results_per_type']) ? intval($settings['results_per_type']) : 5;
        
        // Search products
        if (in_array('product', $post_types)) {
            $product_sql = "SELECT * FROM $table_name 
                           WHERE post_type = 'product' 
                           AND ($search_sql) 
                           ORDER BY relevance_score DESC, id DESC 
                           LIMIT $results_per_type";
            
            $product_results = $wpdb->get_results($product_sql);
            
            if ($product_results) {
                foreach ($product_results as $result) {
                    $results[] = $this->format_result($result, 'product', $settings);
                }
            }
        }
        
        // Search posts
        if (in_array('post', $post_types)) {
            $post_sql = "SELECT * FROM $table_name 
                        WHERE post_type = 'post' 
                        AND ($search_sql) 
                        ORDER BY relevance_score DESC, id DESC 
                        LIMIT $results_per_type";
            
            $post_results = $wpdb->get_results($post_sql);
            
            if ($post_results) {
                foreach ($post_results as $result) {
                    $results[] = $this->format_result($result, 'post', $settings);
                }
            }
        }
        
        // Calculate relevance scores
        $results = $this->calculate_relevance($results, $search_query);
        
        // Sort by relevance
        usort($results, function($a, $b) {
            return $b['relevance'] - $a['relevance'];
        });
        
        // Limit total results
        $results = array_slice($results, 0, $max_results);
        
        return $results;
    }
    
    /**
     * Format search result
     */
    private function format_result($result, $type, $settings) {
        $post = get_post($result->post_id);
        
        if (!$post) {
            return null;
        }
        
        $formatted = array(
            'id' => $result->post_id,
            'type' => $type,
            'title' => $result->title,
            'url' => get_permalink($result->post_id),
            'relevance' => 0
        );
        
        // Get thumbnail
        if (!empty($settings['show_images'])) {
            if ($type === 'product' && class_exists('WooCommerce')) {
                $product = wc_get_product($result->post_id);
                if ($product) {
                    $formatted['image'] = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                }
            } else {
                $formatted['image'] = get_the_post_thumbnail_url($result->post_id, 'thumbnail');
            }
            
            if (empty($formatted['image'])) {
                $formatted['image'] = US_PLUGIN_URL . 'assets/images/placeholder.png';
            }
        }
        
        // Get price for products
        if ($type === 'product' && !empty($settings['show_price']) && class_exists('WooCommerce')) {
            $product = wc_get_product($result->post_id);
            if ($product) {
                $formatted['price'] = $product->get_price_html();
                $formatted['price_value'] = $product->get_price();
            }
        }
        
        // Get excerpt
        if (!empty($settings['show_excerpt'])) {
            $excerpt = !empty($result->excerpt) ? $result->excerpt : wp_trim_words($result->content, 20);
            $formatted['excerpt'] = wp_strip_all_tags($excerpt);
        }
        
        // Add to cart for products
        if ($type === 'product' && !empty($settings['show_add_to_cart']) && class_exists('WooCommerce')) {
            $product = wc_get_product($result->post_id);
            if ($product && $product->is_in_stock()) {
                $formatted['add_to_cart'] = true;
                $formatted['add_to_cart_url'] = $product->add_to_cart_url();
            }
        }
        
        // Post meta
        if ($type === 'post') {
            $formatted['date'] = get_the_date('', $result->post_id);
            $formatted['author'] = get_the_author_meta('display_name', $post->post_author);
        }
        
        return $formatted;
    }
    
    /**
     * Calculate relevance score
     */
    private function calculate_relevance($results, $query) {
        $query_lower = strtolower($query);
        $query_terms = explode(' ', $query_lower);
        
        foreach ($results as &$result) {
            $score = 0;
            $title_lower = strtolower($result['title']);
            
            // Exact match in title gets highest score
            if (strpos($title_lower, $query_lower) !== false) {
                $score += 100;
                
                // Exact match at start of title
                if (strpos($title_lower, $query_lower) === 0) {
                    $score += 50;
                }
            }
            
            // Individual term matches
            foreach ($query_terms as $term) {
                if (empty($term)) continue;
                
                // Title matches
                if (strpos($title_lower, $term) !== false) {
                    $score += 10;
                }
                
                // Excerpt matches
                if (isset($result['excerpt']) && strpos(strtolower($result['excerpt']), $term) !== false) {
                    $score += 5;
                }
            }
            
            // Boost for products (if searching primarily for products)
            if ($result['type'] === 'product') {
                $score += 2;
            }
            
            $result['relevance'] = $score;
        }
        
        return $results;
    }
}
