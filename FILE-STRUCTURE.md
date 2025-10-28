# Unified Search - Plugin File Structure

```
unified-search/
│
├── unified-search.php              # Main plugin file (required)
│   └── Plugin header, initialization, activation/deactivation hooks
│
├── README.md                       # Main documentation
├── INSTALLATION.md                 # Installation & quick start guide
├── CHANGELOG.md                    # Version history and changes
├── USAGE-EXAMPLES.md              # Code examples and customization
│
├── includes/                       # PHP Classes (core functionality)
│   ├── class-us-settings.php      # Settings page and admin interface
│   ├── class-us-search-engine.php # Search logic and relevance scoring
│   ├── class-us-ajax-handler.php  # AJAX request handling
│   ├── class-us-frontend.php      # Frontend display and rendering
│   ├── class-us-indexer.php       # Database indexing functionality
│   └── class-us-shortcodes.php    # Shortcode registration
│
├── assets/                         # Static assets
│   ├── css/
│   │   ├── admin.css              # Admin panel styles
│   │   └── frontend.css           # Frontend search form & results styles
│   │
│   ├── js/
│   │   ├── admin.js               # Admin panel JavaScript
│   │   └── frontend.js            # Frontend AJAX search functionality
│   │
│   └── images/
│       ├── placeholder.png        # Default product/post image
│       └── placeholder.svg        # SVG fallback image
│
└── languages/                      # Translation files (create as needed)
    └── unified-search.pot         # Translation template (to be generated)

```

## File Descriptions

### Root Files

**unified-search.php**
- Main plugin file with WordPress header
- Plugin initialization and bootstrapping
- Loads all required classes
- Registers activation/deactivation hooks
- Creates database table on activation

**README.md**
- Complete plugin documentation
- Features list and requirements
- Installation instructions
- Configuration guide
- FAQ section

**INSTALLATION.md**
- Step-by-step installation guide
- Quick start instructions
- Troubleshooting section
- Integration examples

**CHANGELOG.md**
- Version history
- Feature additions
- Bug fixes
- Upgrade notices

**USAGE-EXAMPLES.md**
- Code snippets and examples
- Custom styling examples
- PHP customization hooks
- JavaScript event handling

### PHP Classes (includes/)

**class-us-settings.php** (285 lines)
- Admin menu registration
- Settings page rendering
- Tabbed interface (General, Search, Display, Index)
- Settings sanitization and validation
- Option storage

**class-us-search-engine.php** (234 lines)
- Core search functionality
- Database query construction
- Relevance scoring algorithm
- Result formatting
- Multi-field search support

**class-us-ajax-handler.php** (54 lines)
- AJAX endpoint registration
- Search request handling
- Reindex request handling
- Security nonce verification
- JSON response formatting

**class-us-frontend.php** (133 lines)
- Search form HTML generation
- Result item rendering
- Template functions
- Shortcode output

**class-us-indexer.php** (173 lines)
- Database index management
- Automatic post/product indexing
- Manual reindex functionality
- WooCommerce integration
- Index table operations

**class-us-shortcodes.php** (27 lines)
- Shortcode registration
- Shortcode attribute parsing
- Integration with frontend class

### CSS Files (assets/css/)

**admin.css** (131 lines)
- Admin panel styling
- Tabbed interface styles
- Form field styling
- Button and action styles
- Responsive admin layout

**frontend.css** (355 lines)
- Search form styling
- Live results dropdown
- Result item layout
- Loading animations
- Responsive design
- Mobile optimizations

### JavaScript Files (assets/js/)

**admin.js** (55 lines)
- Tab switching functionality
- Reindex button handler
- AJAX request for reindexing
- Status message display
- Error handling

**frontend.js** (252 lines)
- Live search functionality
- AJAX request handling
- Result rendering
- Debouncing/throttling
- Keyboard navigation
- Loading states

### Image Files (assets/images/)

**placeholder.png/svg**
- Default image for products/posts without thumbnails
- Fallback image for broken images
- Lightweight SVG and PNG formats

## Database Schema

### Table: wp_unified_search_index

```sql
CREATE TABLE wp_unified_search_index (
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
);
```

## Hooks and Filters

### Actions
- `init` - Plugin initialization
- `admin_menu` - Add settings page
- `admin_enqueue_scripts` - Load admin assets
- `wp_enqueue_scripts` - Load frontend assets
- `save_post` - Auto-index on post save
- `delete_post` - Remove from index
- `woocommerce_update_product` - Index product updates
- `woocommerce_new_product` - Index new products

### Filters
- `us_search_results` - Modify search results
- `us_index_data` - Modify indexed data
- `us_settings_default` - Default settings

### AJAX Actions
- `wp_ajax_us_search` - Handle search (logged in)
- `wp_ajax_nopriv_us_search` - Handle search (public)
- `wp_ajax_us_reindex` - Handle reindex (admin only)

## Shortcodes

**[unified_search]**
- Renders the search form
- Accepts attributes: placeholder, class

## JavaScript Events

**Custom Events:**
- `us-search-start` - Fired when search begins
- `us-search-complete` - Fired when results are received
- `us-search-error` - Fired on search error

## Constants

```php
US_VERSION           // Plugin version
US_PLUGIN_DIR        // Plugin directory path
US_PLUGIN_URL        // Plugin URL
US_PLUGIN_BASENAME   // Plugin basename
```

## WordPress Options

**unified_search_settings** (array)
- Stores all plugin settings
- Auto-loaded for performance

## Security Features

- Nonce verification on all AJAX requests
- Capability checks for admin functions
- SQL injection protection via $wpdb->prepare
- XSS protection via esc_* functions
- Sanitization of all user inputs

## Performance Considerations

- Custom index table for fast searches
- FULLTEXT index on searchable fields
- JavaScript debouncing (300ms default)
- Request abortion on new searches
- Minimal CSS/JS (total ~15KB)
- No external dependencies

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## WordPress Compatibility

- Tested up to: WordPress 6.4
- Requires at least: WordPress 5.8
- Requires PHP: 7.4
- WooCommerce: 5.0+

## File Sizes

```
Total plugin size: ~50KB (uncompressed)

PHP Files: ~25KB
CSS Files: ~12KB
JS Files: ~10KB
Images: ~3KB
Docs: Variable
```

## Development

To modify the plugin:

1. **Edit PHP files** in includes/ for functionality changes
2. **Edit CSS files** in assets/css/ for styling
3. **Edit JS files** in assets/js/ for behavior
4. **Test thoroughly** after changes
5. **Reindex** after modifying index structure
6. **Clear cache** (browser and WordPress)

## Best Practices

- Always sanitize user input
- Use WordPress coding standards
- Escape all output
- Use translation functions (__(), _e(), etc.)
- Comment complex logic
- Test on different themes
- Check multisite compatibility
- Verify mobile responsiveness

---

This structure ensures:
✅ Clean separation of concerns
✅ Easy maintainability
✅ WordPress coding standards
✅ Security best practices
✅ Performance optimization
✅ Extensibility
✅ Documentation completeness
