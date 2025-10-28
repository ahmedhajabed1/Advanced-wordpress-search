<?php
/**
 * Search Results Page Template
 * 
 * This template displays search results for both products and posts
 * in a beautiful grid layout
 */

get_header(); 

$search_query = get_search_query();
$settings = get_option('unified_search_settings', array());

// Use our custom search function for better results
$search_results = US_Search_Results_Page::get_search_results($search_query, 100);
$has_results = !empty($search_results);
$results_count = count($search_results);
?>

<div class="us-search-results-page">
    <div class="us-search-page-container">
        
        <!-- Search Header -->
        <div class="us-search-page-header">
            <h1 class="us-search-page-title">
                <?php printf(__('Search Results for: %s', 'unified-search'), '<span>' . esc_html($search_query) . '</span>'); ?>
            </h1>
            
            <?php if ($has_results) : ?>
                <p class="us-search-page-count">
                    <?php printf(__('Found %d results', 'unified-search'), $results_count); ?>
                </p>
            <?php endif; ?>
            
            <!-- Search Form -->
            <div class="us-search-page-form">
                <?php echo do_shortcode('[unified_search]'); ?>
            </div>
        </div>
        
        <?php if ($has_results) : ?>
            
            <?php
            // Separate products and posts
            $products = array();
            $posts = array();
            
            foreach ($search_results as $result) {
                if ($result->post_type === 'product') {
                    $products[] = $result;
                } else {
                    $posts[] = $result;
                }
            }
            ?>
            
            <!-- Products Section -->
            <?php if (!empty($products)) : ?>
                <div class="us-search-section">
                    <h2 class="us-search-section-title">
                        🛍️ <?php printf(__('Products (%d)', 'unified-search'), count($products)); ?>
                    </h2>
                    
                    <div class="us-search-results-grid">
                        <?php foreach ($products as $result) : 
                            $product_id = $result->ID;
                            
                            // Skip if WooCommerce not available
                            if (!function_exists('wc_get_product')) {
                                continue;
                            }
                            
                            $product = wc_get_product($product_id);
                            if (!$product) continue;
                            
                            $product_title = get_the_title($product_id);
                            $product_link = get_permalink($product_id);
                            $product_image = get_the_post_thumbnail_url($product_id, 'medium');
                            if (!$product_image && function_exists('wc_placeholder_img_src')) {
                                $product_image = wc_placeholder_img_src('medium');
                            }
                        ?>
                            
                            <div class="us-search-result-card product">
                                <a href="<?php echo esc_url($product_link); ?>" class="us-card-link">
                                    
                                    <!-- Product Image -->
                                    <div class="us-card-image">
                                        <img src="<?php echo esc_url($product_image); ?>" 
                                             alt="<?php echo esc_attr($product_title); ?>" />
                                    </div>
                                    
                                    <!-- Product Content -->
                                    <div class="us-card-content">
                                        <span class="us-card-badge product">
                                            <?php _e('PRODUCT', 'unified-search'); ?>
                                        </span>
                                        
                                        <h3 class="us-card-title">
                                            <?php echo esc_html($product_title); ?>
                                        </h3>
                                        
                                        <?php if (!empty($settings['show_excerpt'])) : ?>
                                            <p class="us-card-excerpt">
                                                <?php 
                                                $excerpt = $product->get_short_description();
                                                if (empty($excerpt)) {
                                                    $excerpt = $product->get_description();
                                                }
                                                echo wp_trim_words($excerpt, 15); 
                                                ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <!-- Price Section -->
                                        <?php if (!empty($settings['show_price'])) : ?>
                                            <div class="us-card-price-section">
                                                <div class="us-card-price">
                                                    <?php echo $product->get_price_html(); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                
                                <!-- Add to Cart -->
                                <?php if (!empty($settings['show_add_to_cart']) && $product->is_purchasable() && $product->is_in_stock()) : ?>
                                    <div class="us-card-actions">
                                        <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" 
                                           class="button us-add-to-cart-btn"
                                           data-product-id="<?php echo esc_attr($product_id); ?>"
                                           data-product-type="<?php echo esc_attr($product->get_type()); ?>">
                                            🛒 <?php echo esc_html($product->add_to_cart_text()); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Posts Section -->
            <?php if (!empty($posts)) : ?>
                <div class="us-search-section">
                    <h2 class="us-search-section-title">
                        📝 <?php printf(__('Blog Posts (%d)', 'unified-search'), count($posts)); ?>
                    </h2>
                    
                    <div class="us-search-results-grid">
                        <?php foreach ($posts as $result) : 
                            $post_id = $result->ID;
                            $post_obj = get_post($post_id);
                            
                            $post_title = get_the_title($post_id);
                            $post_link = get_permalink($post_id);
                            $post_image = get_the_post_thumbnail_url($post_id, 'medium');
                            if (!$post_image) {
                                $post_image = US_PLUGIN_URL . 'assets/images/placeholder.png';
                            }
                            $post_date = get_the_date('', $post_id);
                            $post_author = get_the_author_meta('display_name', $post_obj->post_author);
                        ?>
                            
                            <div class="us-search-result-card post">
                                <a href="<?php echo esc_url($post_link); ?>" class="us-card-link">
                                    
                                    <!-- Post Image -->
                                    <div class="us-card-image">
                                        <img src="<?php echo esc_url($post_image); ?>" 
                                             alt="<?php echo esc_attr($post_title); ?>" />
                                    </div>
                                    
                                    <!-- Post Content -->
                                    <div class="us-card-content">
                                        <span class="us-card-badge post">
                                            <?php _e('POST', 'unified-search'); ?>
                                        </span>
                                        
                                        <h3 class="us-card-title">
                                            <?php echo esc_html($post_title); ?>
                                        </h3>
                                        
                                        <!-- Post Meta -->
                                        <div class="us-card-meta">
                                            <span class="us-meta-date">
                                                <?php echo esc_html($post_date); ?>
                                            </span>
                                            <span class="us-meta-author">
                                                <?php echo esc_html($post_author); ?>
                                            </span>
                                        </div>
                                        
                                        <?php if (!empty($settings['show_excerpt'])) : ?>
                                            <p class="us-card-excerpt">
                                                <?php 
                                                $excerpt = $post_obj->post_excerpt;
                                                if (empty($excerpt)) {
                                                    $excerpt = $post_obj->post_content;
                                                }
                                                echo wp_trim_words($excerpt, 20); 
                                                ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                            
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else : ?>
            
            <!-- No Results -->
            <div class="us-no-results-page">
                <div class="us-no-results-icon">🔍</div>
                <h2 class="us-no-results-title">
                    <?php _e('No Results Found', 'unified-search'); ?>
                </h2>
                <p class="us-no-results-text">
                    <?php printf(__('Sorry, no results found for "%s". Please try a different search.', 'unified-search'), esc_html($search_query)); ?>
                </p>
                
                <div class="us-search-suggestions">
                    <h3><?php _e('Search Suggestions:', 'unified-search'); ?></h3>
                    <ul>
                        <li><?php _e('Check your spelling', 'unified-search'); ?></li>
                        <li><?php _e('Try different keywords', 'unified-search'); ?></li>
                        <li><?php _e('Try more general keywords', 'unified-search'); ?></li>
                        <li><?php _e('Try fewer keywords', 'unified-search'); ?></li>
                    </ul>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</div>

<?php get_footer(); ?>
