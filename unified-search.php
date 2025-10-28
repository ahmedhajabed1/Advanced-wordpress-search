<?php
/**
 * Plugin Name: Unified Search - Products & Posts
 * Plugin URI: https://example.com/unified-search
 * Description: Advanced AJAX search for WooCommerce products and WordPress posts with live search results, thumbnails, and prices
 * Version: 1.0.0
 * Author: Custom Development
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: unified-search
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('US_VERSION', '1.0.0');
define('US_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('US_PLUGIN_URL', plugin_dir_url(__FILE__));
define('US_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Unified Search Class
 */
class Unified_Search {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once US_PLUGIN_DIR . 'includes/class-us-settings.php';
        require_once US_PLUGIN_DIR . 'includes/class-us-search-engine.php';
        require_once US_PLUGIN_DIR . 'includes/class-us-ajax-handler.php';
        require_once US_PLUGIN_DIR . 'includes/class-us-frontend.php';
        require_once US_PLUGIN_DIR . 'includes/class-us-indexer.php';
        require_once US_PLUGIN_DIR . 'includes/class-us-shortcodes.php';
        require_once US_PLUGIN_DIR . 'includes/class-us-search-results-page.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('unified-search', false, dirname(US_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize classes
        US_Settings::get_instance();
        US_Search_Engine::get_instance();
        US_Ajax_Handler::get_instance();
        US_Frontend::get_instance();
        US_Indexer::get_instance();
        US_Shortcodes::get_instance();
        US_Search_Results_Page::get_instance();
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_scripts($hook) {
        if ('settings_page_unified-search' !== $hook) {
            return;
        }
        
        wp_enqueue_style('us-admin-css', US_PLUGIN_URL . 'assets/css/admin.css', array(), US_VERSION);
        wp_enqueue_script('us-admin-js', US_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), US_VERSION, true);
        
        wp_localize_script('us-admin-js', 'usAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('us-admin-nonce'),
            'strings' => array(
                'indexing' => __('Indexing...', 'unified-search'),
                'indexed' => __('Indexed successfully!', 'unified-search'),
                'error' => __('Error occurred during indexing', 'unified-search')
            )
        ));
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function frontend_scripts() {
        wp_enqueue_style('us-frontend-css', US_PLUGIN_URL . 'assets/css/frontend.css', array(), US_VERSION);
        wp_enqueue_script('us-frontend-js', US_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), US_VERSION, true);
        
        $settings = get_option('unified_search_settings', array());
        
        wp_localize_script('us-frontend-js', 'unifiedSearch', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('us-search-nonce'),
            'minChars' => isset($settings['min_chars']) ? intval($settings['min_chars']) : 3,
            'delay' => isset($settings['search_delay']) ? intval($settings['search_delay']) : 300,
            'maxResults' => isset($settings['max_results']) ? intval($settings['max_results']) : 10,
            'strings' => array(
                'noResults' => __('No results found', 'unified-search'),
                'searching' => __('Searching...', 'unified-search'),
                'viewAll' => __('View all results', 'unified-search')
            )
        ));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create index table
        global $wpdb;
        $table_name = $wpdb->prefix . 'unified_search_index';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_type varchar(20) NOT NULL,
            title text NOT NULL,
            content longtext NOT NULL,
            excerpt text,
            sku varchar(100),
            categories text,
            tags text,
            price decimal(10,2),
            in_stock tinyint(1) DEFAULT 1,
            relevance_score int(11) DEFAULT 0,
            indexed_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY post_type (post_type),
            KEY in_stock (in_stock),
            FULLTEXT KEY search_index (title, content, excerpt, sku)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        $default_settings = array(
            'search_in_title' => 1,
            'search_in_content' => 1,
            'search_in_excerpt' => 1,
            'search_in_sku' => 1,
            'search_in_categories' => 1,
            'search_in_tags' => 1,
            'search_products' => 1,
            'search_posts' => 1,
            'show_images' => 1,
            'show_price' => 1,
            'show_excerpt' => 1,
            'min_chars' => 3,
            'search_delay' => 300,
            'max_results' => 10,
            'results_per_type' => 5
        );
        
        add_option('unified_search_settings', $default_settings);
        
        // Schedule indexing
        set_transient('us_needs_indexing', 1, HOUR_IN_SECONDS);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up transients
        delete_transient('us_needs_indexing');
    }
}

/**
 * Initialize the plugin safely
 */
function unified_search_init() {
    try {
        return Unified_Search::get_instance();
    } catch (Exception $e) {
        // Log the error
        if (function_exists('error_log')) {
            error_log('Unified Search Plugin Error: ' . $e->getMessage());
        }
        
        // Show admin notice
        if (is_admin()) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>Unified Search Error:</strong> ' . esc_html($e->getMessage());
                echo '</p></div>';
            });
        }
        
        return null;
    }
}

// Start the plugin
add_action('plugins_loaded', 'unified_search_init', 10);
