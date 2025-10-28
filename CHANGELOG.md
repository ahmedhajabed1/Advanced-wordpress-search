# Changelog

All notable changes to the Unified Search plugin will be documented in this file.

## [1.0.0] - 2025-01-27

### Added
- Initial release of Unified Search - Products & Posts plugin
- AJAX live search functionality for WooCommerce products and WordPress posts
- Custom database index table for fast search performance
- Comprehensive admin settings interface with tabs:
  - General Settings (placeholder, min characters, delays, result limits)
  - Search Settings (search fields, post types)
  - Display Settings (images, prices, excerpts, add to cart)
  - Index Management (reindex functionality, shortcode info)
- Search in multiple fields:
  - Product/Post title
  - Product/Post content
  - Product/Post excerpt
  - Product SKU
  - Categories (product_cat and category)
  - Tags (product_tag and post_tag)
- Display features:
  - Product images and post thumbnails
  - Product prices with WooCommerce formatting
  - Post metadata (date, author)
  - Content excerpts
  - Optional add-to-cart buttons
- Smart relevance scoring algorithm
- Responsive design that works on all devices
- Automatic indexing when posts/products are created or updated
- Manual reindex functionality
- Shortcode support: `[unified_search]`
- Clean, modern UI with smooth animations
- Loading states and error handling
- Result grouping by post type
- Security features:
  - Nonce verification for AJAX requests
  - SQL injection protection
  - XSS protection with proper escaping
  - Capability checks for admin functions

### Technical Features
- Object-oriented PHP architecture
- Singleton pattern for class instances
- WordPress coding standards compliant
- Translation ready with text domain
- Proper enqueue of CSS and JavaScript
- Optimized database queries
- AJAX request debouncing
- Request abortion on new searches
- Browser compatibility (modern browsers)

### Documentation
- Comprehensive README.md file
- Detailed INSTALLATION.md guide
- Inline code documentation
- Usage examples

### Performance
- Custom index table for fast searches
- Minimal database queries
- Efficient JavaScript with debouncing
- Lightweight CSS (~5KB)
- No external dependencies

### Requirements
- WordPress 5.8+
- PHP 7.4+
- WooCommerce 5.0+ (for product search)
- MySQL 5.6+

---

## Future Enhancements (Planned)

### Version 1.1.0 (Planned)
- [ ] Widget support for easy drag-and-drop
- [ ] Custom post type support beyond products and posts
- [ ] Search filters (price range, categories, tags)
- [ ] Search analytics dashboard
- [ ] Export search queries to CSV

### Version 1.2.0 (Planned)
- [ ] Fuzzy search / typo tolerance
- [ ] Synonym support
- [ ] Stop words configuration
- [ ] Multi-language support (WPML, Polylang)
- [ ] Search suggestions/autocomplete

### Version 1.3.0 (Planned)
- [ ] Search history for users
- [ ] Popular searches display
- [ ] Voice search support
- [ ] Advanced search page
- [ ] Customizable result templates

---

## Bug Fixes

None yet - initial release

---

## Known Issues

None currently reported

---

## Upgrade Notice

### 1.0.0
Initial release. After installation, please go to Settings → Unified Search → Index Management and click "Reindex All Content" to build the search index.

---

## Support & Contribution

For support, feature requests, or bug reports:
- Create an issue in the plugin repository
- Contact the plugin developer
- Check the documentation files (README.md, INSTALLATION.md)

---

## Credits

- Developed with WordPress and WooCommerce best practices
- Icons: Custom SVG icons
- Inspired by Advanced Woo Search and other popular search plugins
- Built with love for the WordPress community

---

## License

This plugin is licensed under GPL v2 or later.
