# Usage Examples & Code Snippets

## Basic Usage

### 1. Simple Shortcode
The easiest way to add the search form:

```
[unified_search]
```

Place this in any:
- Post content
- Page content
- Text widget
- Custom HTML widget

### 2. In Page Builder (Elementor, Divi, etc.)

**Elementor:**
```
Add "Shortcode" widget → Paste: [unified_search]
```

**Divi:**
```
Add "Code" module → Content tab → Paste: [unified_search]
```

**Gutenberg:**
```
Add "Shortcode" block → Paste: [unified_search]
```

## Advanced Usage

### 3. In Theme Files

**In Header (header.php):**
```php
<div class="custom-search-header">
    <?php echo do_shortcode('[unified_search]'); ?>
</div>
```

**In Sidebar (sidebar.php):**
```php
<div class="widget">
    <h3 class="widget-title">Search Our Store</h3>
    <?php echo do_shortcode('[unified_search]'); ?>
</div>
```

**In Footer (footer.php):**
```php
<div class="footer-search">
    <h4>Find What You're Looking For</h4>
    <?php echo do_shortcode('[unified_search]'); ?>
</div>
```

### 4. Using PHP Function Directly

```php
<?php
// Get the frontend instance
$frontend = US_Frontend::get_instance();

// Render with default settings
echo $frontend->render_search_form();

// Or with custom attributes
echo $frontend->render_search_form(array(
    'placeholder' => 'What are you looking for?',
    'class' => 'my-custom-class'
));
?>
```

### 5. Conditional Display

**Only on WooCommerce Pages:**
```php
<?php if (function_exists('is_woocommerce') && is_woocommerce()) : ?>
    <?php echo do_shortcode('[unified_search]'); ?>
<?php endif; ?>
```

**Only on Mobile Devices:**
```php
<?php if (wp_is_mobile()) : ?>
    <div class="mobile-search">
        <?php echo do_shortcode('[unified_search]'); ?>
    </div>
<?php endif; ?>
```

**Only for Logged-in Users:**
```php
<?php if (is_user_logged_in()) : ?>
    <?php echo do_shortcode('[unified_search]'); ?>
<?php endif; ?>
```

## Styling Examples

### 6. Custom CSS Styling

**Place in Appearance → Customize → Additional CSS**

**Full-width search bar:**
```css
.us-search-wrapper {
    max-width: 100%;
}
```

**Change colors to match brand:**
```css
/* Primary brand color */
.us-search-input-wrapper:focus-within {
    border-color: #e91e63;
    box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
}

.us-search-submit:hover {
    color: #e91e63;
}

.us-result-link:hover .us-result-title {
    color: #e91e63;
}

.us-result-price {
    color: #e91e63;
}

.us-add-to-cart {
    background: #e91e63;
}

.us-add-to-cart:hover {
    background: #c2185b;
}
```

**Rounded search box:**
```css
.us-search-input-wrapper {
    border-radius: 50px;
}

.us-search-results {
    border-radius: 20px;
}
```

**Dark mode styling:**
```css
.us-search-input-wrapper {
    background: #2d2d2d;
    border-color: #444;
}

.us-search-input {
    color: #fff;
}

.us-search-input::placeholder {
    color: #999;
}

.us-search-results {
    background: #2d2d2d;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.us-result-item:hover {
    background: #3a3a3a;
}

.us-result-title {
    color: #fff;
}
```

**Compact results:**
```css
.us-result-link {
    padding: 10px 15px;
    gap: 10px;
}

.us-result-image {
    width: 40px;
    height: 40px;
}

.us-result-title {
    font-size: 14px;
}

.us-result-excerpt {
    display: none;
}
```

### 7. Custom Layout Examples

**Centered in page with background:**
```css
.search-page-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 80px 20px;
    text-align: center;
}

.search-page-wrapper .us-search-wrapper {
    max-width: 700px;
    margin: 0 auto;
}

.search-page-wrapper .us-search-input-wrapper {
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border-radius: 50px;
}
```

## PHP Customization

### 8. Modify Search Results with Filters

```php
// Boost certain products in search results
add_filter('us_search_results', 'boost_featured_products', 10, 2);
function boost_featured_products($results, $query) {
    foreach ($results as &$result) {
        if ($result['type'] === 'product') {
            $product = wc_get_product($result['id']);
            if ($product && $product->is_featured()) {
                $result['relevance'] += 50; // Boost featured products
            }
        }
    }
    return $results;
}
```

### 9. Add Custom Data to Index

```php
// Add custom field to search index
add_filter('us_index_data', 'add_custom_field_to_index', 10, 2);
function add_custom_field_to_index($data, $post_id) {
    $custom_value = get_post_meta($post_id, 'custom_field_key', true);
    if ($custom_value) {
        $data['content'] .= ' ' . $custom_value;
    }
    return $data;
}
```

### 10. Customize Result Display

```php
// Add custom content to search results
add_action('us_after_result_title', 'add_custom_result_content', 10, 1);
function add_custom_result_content($result) {
    if ($result['type'] === 'product') {
        $product = wc_get_product($result['id']);
        if ($product && $product->is_on_sale()) {
            echo '<span class="sale-badge">ON SALE!</span>';
        }
    }
}
```

## JavaScript Customization

### 11. Custom JavaScript Events

```javascript
// Listen for search results
jQuery(document).on('us-search-complete', function(e, results) {
    console.log('Search returned ' + results.length + ' results');
    
    // Track with Google Analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'search', {
            'search_term': jQuery('.us-search-input').val()
        });
    }
});

// Listen for search start
jQuery(document).on('us-search-start', function(e, query) {
    console.log('Searching for: ' + query);
});
```

### 12. Modify Search Behavior

```javascript
// Change minimum characters required
jQuery(document).ready(function($) {
    unifiedSearch.minChars = 2; // Start search at 2 characters
});

// Disable search delay
jQuery(document).ready(function($) {
    unifiedSearch.delay = 0; // Instant search
});
```

## WordPress Multisite

### 13. Network-wide Activation

```php
// In wp-config.php
define('US_NETWORK_WIDE', true);

// In your theme or custom plugin
if (is_multisite()) {
    // Get search results from all sites
    $sites = get_sites();
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);
        // Perform search
        restore_current_blog();
    }
}
```

## Integration Examples

### 14. With Contact Form 7

```html
<div class="search-and-contact">
    [unified_search]
    <div class="or-divider">OR</div>
    [contact-form-7 id="123"]
</div>
```

### 15. With WooCommerce Product Categories

```php
<div class="shop-header">
    <div class="categories">
        <?php woocommerce_product_categories(); ?>
    </div>
    <div class="search">
        <?php echo do_shortcode('[unified_search]'); ?>
    </div>
</div>
```

## Mobile-Specific

### 16. Mobile Menu Integration

```php
// In your theme's functions.php
add_action('wp_nav_menu_items', 'add_search_to_mobile_menu', 10, 2);
function add_search_to_mobile_menu($items, $args) {
    if ($args->theme_location == 'mobile-menu') {
        $items .= '<li class="menu-search">' . do_shortcode('[unified_search]') . '</li>';
    }
    return $items;
}
```

## Performance Optimization

### 17. Lazy Load Results

```javascript
// Only show first 3 results, load more on scroll
jQuery('.us-search-results').on('scroll', function() {
    if (jQuery(this).scrollTop() + jQuery(this).innerHeight() >= 
        jQuery(this)[0].scrollHeight - 50) {
        // Load more results
        loadMoreResults();
    }
});
```

## Accessibility

### 18. Enhanced Keyboard Navigation

```javascript
// Add keyboard navigation
jQuery('.us-search-input').on('keydown', function(e) {
    if (e.keyCode === 40) { // Down arrow
        e.preventDefault();
        jQuery('.us-result-item:first a').focus();
    }
});
```

---

## Tips for Best Results

1. **Keep it visible**: Place search prominently in header
2. **Test thoroughly**: Search for common and uncommon terms
3. **Monitor performance**: Check search speed with many products
4. **Customize appearance**: Match your brand colors and style
5. **Mobile first**: Ensure great mobile experience
6. **Regular reindexing**: Reindex after bulk imports
7. **Clear instructions**: Add placeholder text that guides users
8. **Analytics**: Track what users search for
9. **Feedback**: Ask users about their search experience
10. **Updates**: Keep plugin updated for best performance

---

For more advanced customization, refer to the plugin source code or contact support.
