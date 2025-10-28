<?php
/**
 * Shortcodes Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class US_Shortcodes {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('unified_search', array($this, 'search_form_shortcode'));
    }
    
    /**
     * Search form shortcode
     */
    public function search_form_shortcode($atts) {
        $frontend = US_Frontend::get_instance();
        return $frontend->render_search_form($atts);
    }
}
