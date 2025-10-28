<?php
/**
 * Settings page handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class US_Settings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu
     */
    public function add_menu() {
        add_options_page(
            __('Unified Search Settings', 'unified-search'),
            __('Unified Search', 'unified-search'),
            'manage_options',
            'unified-search',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('unified_search_options', 'unified_search_settings', array($this, 'sanitize_settings'));
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Checkboxes
        $checkboxes = array(
            'search_in_title', 'search_in_content', 'search_in_excerpt', 
            'search_in_sku', 'search_in_categories', 'search_in_tags',
            'search_products', 'search_posts', 'show_images', 
            'show_price', 'show_excerpt', 'show_add_to_cart'
        );
        
        foreach ($checkboxes as $key) {
            $sanitized[$key] = isset($input[$key]) ? 1 : 0;
        }
        
        // Numbers
        $sanitized['min_chars'] = isset($input['min_chars']) ? absint($input['min_chars']) : 3;
        $sanitized['search_delay'] = isset($input['search_delay']) ? absint($input['search_delay']) : 300;
        $sanitized['max_results'] = isset($input['max_results']) ? absint($input['max_results']) : 10;
        $sanitized['results_per_type'] = isset($input['results_per_type']) ? absint($input['results_per_type']) : 5;
        
        // Text fields
        $sanitized['placeholder'] = isset($input['placeholder']) ? sanitize_text_field($input['placeholder']) : __('Search products and posts...', 'unified-search');
        $sanitized['no_results_text'] = isset($input['no_results_text']) ? sanitize_text_field($input['no_results_text']) : __('No results found', 'unified-search');
        
        return $sanitized;
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = get_option('unified_search_settings', array());
        ?>
        <div class="wrap us-settings-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors('unified_search_messages'); ?>
            
            <div class="us-admin-container">
                <div class="us-admin-tabs">
                    <button class="us-tab-button active" data-tab="general"><?php _e('General', 'unified-search'); ?></button>
                    <button class="us-tab-button" data-tab="search"><?php _e('Search Settings', 'unified-search'); ?></button>
                    <button class="us-tab-button" data-tab="display"><?php _e('Display Settings', 'unified-search'); ?></button>
                    <button class="us-tab-button" data-tab="index"><?php _e('Index Management', 'unified-search'); ?></button>
                </div>
                
                <form method="post" action="options.php">
                    <?php settings_fields('unified_search_options'); ?>
                    
                    <!-- General Tab -->
                    <div class="us-tab-content active" id="general-tab">
                        <h2><?php _e('General Settings', 'unified-search'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Search Placeholder', 'unified-search'); ?></th>
                                <td>
                                    <input type="text" name="unified_search_settings[placeholder]" 
                                           value="<?php echo esc_attr(isset($settings['placeholder']) ? $settings['placeholder'] : __('Search products and posts...', 'unified-search')); ?>" 
                                           class="regular-text" />
                                    <p class="description"><?php _e('Text shown in the search box when empty', 'unified-search'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('Minimum Characters', 'unified-search'); ?></th>
                                <td>
                                    <input type="number" name="unified_search_settings[min_chars]" 
                                           value="<?php echo esc_attr(isset($settings['min_chars']) ? $settings['min_chars'] : 3); ?>" 
                                           min="1" max="10" />
                                    <p class="description"><?php _e('Minimum characters before search starts', 'unified-search'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('Search Delay (ms)', 'unified-search'); ?></th>
                                <td>
                                    <input type="number" name="unified_search_settings[search_delay]" 
                                           value="<?php echo esc_attr(isset($settings['search_delay']) ? $settings['search_delay'] : 300); ?>" 
                                           min="0" max="2000" step="100" />
                                    <p class="description"><?php _e('Delay before search starts after typing stops', 'unified-search'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('Max Results', 'unified-search'); ?></th>
                                <td>
                                    <input type="number" name="unified_search_settings[max_results]" 
                                           value="<?php echo esc_attr(isset($settings['max_results']) ? $settings['max_results'] : 10); ?>" 
                                           min="5" max="50" />
                                    <p class="description"><?php _e('Maximum total results to display', 'unified-search'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('Results Per Type', 'unified-search'); ?></th>
                                <td>
                                    <input type="number" name="unified_search_settings[results_per_type]" 
                                           value="<?php echo esc_attr(isset($settings['results_per_type']) ? $settings['results_per_type'] : 5); ?>" 
                                           min="1" max="20" />
                                    <p class="description"><?php _e('Maximum results per post type (products/posts)', 'unified-search'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Search Settings Tab -->
                    <div class="us-tab-content" id="search-tab">
                        <h2><?php _e('Search Settings', 'unified-search'); ?></h2>
                        
                        <h3><?php _e('Search In', 'unified-search'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Search Fields', 'unified-search'); ?></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[search_in_title]" value="1" 
                                                   <?php checked(isset($settings['search_in_title']) ? $settings['search_in_title'] : 1, 1); ?> />
                                            <?php _e('Title', 'unified-search'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[search_in_content]" value="1" 
                                                   <?php checked(isset($settings['search_in_content']) ? $settings['search_in_content'] : 1, 1); ?> />
                                            <?php _e('Content', 'unified-search'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[search_in_excerpt]" value="1" 
                                                   <?php checked(isset($settings['search_in_excerpt']) ? $settings['search_in_excerpt'] : 1, 1); ?> />
                                            <?php _e('Excerpt', 'unified-search'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[search_in_sku]" value="1" 
                                                   <?php checked(isset($settings['search_in_sku']) ? $settings['search_in_sku'] : 1, 1); ?> />
                                            <?php _e('SKU (Products only)', 'unified-search'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[search_in_categories]" value="1" 
                                                   <?php checked(isset($settings['search_in_categories']) ? $settings['search_in_categories'] : 1, 1); ?> />
                                            <?php _e('Categories', 'unified-search'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[search_in_tags]" value="1" 
                                                   <?php checked(isset($settings['search_in_tags']) ? $settings['search_in_tags'] : 1, 1); ?> />
                                            <?php _e('Tags', 'unified-search'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                        
                        <h3><?php _e('Post Types', 'unified-search'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Include in Search', 'unified-search'); ?></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[search_products]" value="1" 
                                                   <?php checked(isset($settings['search_products']) ? $settings['search_products'] : 1, 1); ?> />
                                            <?php _e('WooCommerce Products', 'unified-search'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[search_posts]" value="1" 
                                                   <?php checked(isset($settings['search_posts']) ? $settings['search_posts'] : 1, 1); ?> />
                                            <?php _e('Blog Posts', 'unified-search'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Display Settings Tab -->
                    <div class="us-tab-content" id="display-tab">
                        <h2><?php _e('Display Settings', 'unified-search'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Show in Results', 'unified-search'); ?></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[show_images]" value="1" 
                                                   <?php checked(isset($settings['show_images']) ? $settings['show_images'] : 1, 1); ?> />
                                            <?php _e('Product Images / Post Thumbnails', 'unified-search'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[show_price]" value="1" 
                                                   <?php checked(isset($settings['show_price']) ? $settings['show_price'] : 1, 1); ?> />
                                            <?php _e('Product Price', 'unified-search'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[show_excerpt]" value="1" 
                                                   <?php checked(isset($settings['show_excerpt']) ? $settings['show_excerpt'] : 1, 1); ?> />
                                            <?php _e('Excerpt/Description', 'unified-search'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="unified_search_settings[show_add_to_cart]" value="1" 
                                                   <?php checked(isset($settings['show_add_to_cart']) ? $settings['show_add_to_cart'] : 0, 1); ?> />
                                            <?php _e('Add to Cart Button (Products)', 'unified-search'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('No Results Text', 'unified-search'); ?></th>
                                <td>
                                    <input type="text" name="unified_search_settings[no_results_text]" 
                                           value="<?php echo esc_attr(isset($settings['no_results_text']) ? $settings['no_results_text'] : __('No results found', 'unified-search')); ?>" 
                                           class="regular-text" />
                                    <p class="description"><?php _e('Message displayed when no results are found', 'unified-search'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Index Management Tab -->
                    <div class="us-tab-content" id="index-tab">
                        <h2><?php _e('Index Management', 'unified-search'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Search Index', 'unified-search'); ?></th>
                                <td>
                                    <?php
                                    global $wpdb;
                                    $table_name = $wpdb->prefix . 'unified_search_index';
                                    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                                    ?>
                                    <p><?php printf(__('Currently indexed items: %d', 'unified-search'), $count); ?></p>
                                    <button type="button" class="button button-primary" id="us-reindex-btn">
                                        <?php _e('Reindex All Content', 'unified-search'); ?>
                                    </button>
                                    <span class="spinner" style="float: none; margin: 0 10px;"></span>
                                    <span id="us-index-status"></span>
                                    <p class="description"><?php _e('Rebuild the search index for all products and posts. This may take a few moments.', 'unified-search'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <h3><?php _e('Shortcode', 'unified-search'); ?></h3>
                        <p><?php _e('Use the following shortcode to display the search form:', 'unified-search'); ?></p>
                        <code>[unified_search]</code>
                        
                        <h3><?php _e('Widget', 'unified-search'); ?></h3>
                        <p><?php _e('You can also add the search form as a widget from Appearance → Widgets', 'unified-search'); ?></p>
                    </div>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <?php
    }
}
