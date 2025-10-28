# Unified Search - Products & Posts

A powerful WordPress plugin that provides advanced AJAX search functionality for both WooCommerce products and WordPress posts with live search results, thumbnails, and prices.

## Features

### Search Capabilities
- ✅ **Dual Search**: Search both WooCommerce products and WordPress posts simultaneously
- ✅ **AJAX Live Search**: Instant results as you type without page reload
- ✅ **Smart Relevance**: Intelligent search algorithm with relevance scoring
- ✅ **Multiple Fields**: Search in title, content, excerpt, SKU, categories, and tags
- ✅ **Fast Performance**: Custom database index for lightning-fast searches

### Display Features
- 🖼️ **Product Images & Post Thumbnails**: Visual search results
- 💰 **Product Prices**: Display WooCommerce product prices
- 📝 **Excerpts**: Show content previews
- 🛒 **Add to Cart**: Optional add-to-cart buttons in search results
- 📅 **Post Meta**: Display post dates and authors

### Customization
- ⚙️ **Comprehensive Settings**: Full control over search behavior
- 🎨 **Modern UI**: Clean, responsive design that works on all devices
- 🔧 **Easy Integration**: Simple shortcode and widget support
- 🌐 **Translation Ready**: Fully translatable

## Installation

### Method 1: Upload via WordPress Admin

1. Download the `unified-search` folder
2. Compress it into a ZIP file (`unified-search.zip`)
3. Go to WordPress Admin → Plugins → Add New
4. Click "Upload Plugin"
5. Choose the ZIP file and click "Install Now"
6. Activate the plugin
7. Go to Settings → Unified Search to configure

### Method 2: Manual Installation

1. Upload the `unified-search` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings → Unified Search to configure

## Initial Setup

After activation:

1. **Go to Settings → Unified Search**
2. **Click on "Index Management" tab**
3. **Click "Reindex All Content" button** - This builds the search index (required!)
4. **Configure your search settings** in the General and Search Settings tabs
5. **Customize display options** in the Display Settings tab

## Usage

### Using the Shortcode

Add the search form anywhere using the shortcode:

```
[unified_search]
```

You can add this to:
- Posts and pages
- Widgets (using a text widget)
- Theme files (using `do_shortcode()`)

### Using in Theme Files

Add to your theme's PHP files:

```php
<?php echo do_shortcode('[unified_search]'); ?>
```

Or use the function directly:

```php
<?php
$frontend = US_Frontend::get_instance();
echo $frontend->render_search_form();
?>
```

## Settings

### General Settings

- **Search Placeholder**: Text shown in empty search box
- **Minimum Characters**: Minimum characters before search starts (default: 3)
- **Search Delay**: Delay in milliseconds before search starts (default: 300)
- **Max Results**: Maximum total results to display (default: 10)
- **Results Per Type**: Maximum results per post type (default: 5)

### Search Settings

**Search In:**
- ☑️ Title
- ☑️ Content
- ☑️ Excerpt
- ☑️ SKU (Products only)
- ☑️ Categories
- ☑️ Tags

**Post Types:**
- ☑️ WooCommerce Products
- ☑️ Blog Posts

### Display Settings

**Show in Results:**
- ☑️ Product Images / Post Thumbnails
- ☑️ Product Price
- ☑️ Excerpt/Description
- ☑️ Add to Cart Button (Products)

### Index Management

- View indexed items count
- Reindex all content with one click
- Automatic indexing when posts/products are updated

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 5.0 or higher (for product search)

## Frequently Asked Questions

### How do I rebuild the search index?

Go to Settings → Unified Search → Index Management tab and click "Reindex All Content"

### Can I search only products or only posts?

Yes! In Settings → Unified Search → Search Settings, uncheck the post types you don't want to search.

### Does it work with my theme?

Yes! The plugin is designed to work with any WordPress theme. The search form styling is minimal and adapts to most themes.

### Will it slow down my site?

No! The plugin uses a custom database index table for extremely fast searches. The AJAX requests are optimized and cached.

### Can I customize the appearance?

Yes! You can override the CSS by adding your own styles. The plugin uses class names prefixed with `us-` for easy targeting.

### How do I add the search to my header?

Use the shortcode `[unified_search]` in a widget area in your header, or add it directly to your theme's header.php file using:

```php
<?php echo do_shortcode('[unified_search]'); ?>
```

### Does it support variable products?

Yes! The plugin indexes all WooCommerce product types including variable products.

### Can I exclude certain products or posts?

Currently, only published products and posts are indexed. You can set products to "hidden" in WooCommerce to exclude them from search.

## Customization

### Custom CSS

Add custom styles to your theme's style.css or use the Customizer:

```css
/* Change search box colors */
.us-search-input-wrapper {
    border-color: #your-color;
}

/* Change result hover color */
.us-result-item:hover {
    background: #your-color;
}
```

### Hooks and Filters

The plugin provides several hooks for developers:

```php
// Modify search results
add_filter('us_search_results', 'my_custom_search_results', 10, 2);

// Modify indexed data
add_filter('us_index_data', 'my_custom_index_data', 10, 2);
```

## Support

For support, feature requests, or bug reports, please contact the plugin developer.

## Changelog

### Version 1.0.0
- Initial release
- AJAX live search for products and posts
- Custom search index
- Comprehensive admin settings
- Responsive design
- Shortcode support

## Credits

Developed with ❤️ for WordPress and WooCommerce users who need powerful search functionality.

## License

GPL v2 or later
