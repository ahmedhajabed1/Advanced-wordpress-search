<?php
/**
 * Frontend Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class US_Frontend {
    
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
     * Render search form
     */
    public function render_search_form($atts = array()) {
        $settings = get_option('unified_search_settings', array());
        
        $defaults = array(
            'placeholder' => isset($settings['placeholder']) ? $settings['placeholder'] : __('Search products and posts...', 'unified-search'),
            'class' => 'us-search-form'
        );
        
        $atts = shortcode_atts($defaults, $atts);
        
        ob_start();
        ?>
        <div class="us-search-wrapper <?php echo esc_attr($atts['class']); ?>">
            <form class="us-search-form" role="search">
                <div class="us-search-input-wrapper">
                    <input 
                        type="search" 
                        class="us-search-input" 
                        name="s" 
                        placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                        autocomplete="off"
                    />
                    <button type="submit" class="us-search-submit">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M19 19L14.65 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span class="us-search-loader"></span>
                </div>
                
                <div class="us-search-results" style="display: none;">
                    <div class="us-results-container"></div>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render result item
     */
    public static function render_result_item($result) {
        $settings = get_option('unified_search_settings', array());
        
        ob_start();
        ?>
        <div class="us-result-item" data-type="<?php echo esc_attr($result['type']); ?>">
            <a href="<?php echo esc_url($result['url']); ?>" class="us-result-link">
                
                <?php if (!empty($settings['show_images']) && !empty($result['image'])) : ?>
                    <div class="us-result-image">
                        <img src="<?php echo esc_url($result['image']); ?>" alt="<?php echo esc_attr($result['title']); ?>" />
                    </div>
                <?php endif; ?>
                
                <div class="us-result-content">
                    <div class="us-result-header">
                        <span class="us-result-type <?php echo esc_attr($result['type']); ?>">
                            <?php echo $result['type'] === 'product' ? __('Product', 'unified-search') : __('Post', 'unified-search'); ?>
                        </span>
                        <h4 class="us-result-title"><?php echo esc_html($result['title']); ?></h4>
                    </div>
                    
                    <?php if (!empty($settings['show_excerpt']) && !empty($result['excerpt'])) : ?>
                        <p class="us-result-excerpt"><?php echo esc_html($result['excerpt']); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($result['type'] === 'post' && (!empty($result['date']) || !empty($result['author']))) : ?>
                        <div class="us-result-meta">
                            <?php if (!empty($result['date'])) : ?>
                                <span class="us-result-date"><?php echo esc_html($result['date']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($result['author'])) : ?>
                                <span class="us-result-author"><?php echo esc_html($result['author']); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($result['type'] === 'product' && !empty($settings['show_price']) && !empty($result['price'])) : ?>
                        <div class="us-result-price-section">
                            <div class="us-result-price">
                                <?php echo $result['price']; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
            
            <?php if ($result['type'] === 'product' && !empty($settings['show_add_to_cart']) && !empty($result['add_to_cart'])) : ?>
                <div class="us-result-actions">
                    <a href="<?php echo esc_url($result['add_to_cart_url']); ?>" 
                       class="button us-add-to-cart" 
                       data-product-id="<?php echo esc_attr($result['id']); ?>">
                        <?php _e('🛒 Add to Cart', 'unified-search'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
