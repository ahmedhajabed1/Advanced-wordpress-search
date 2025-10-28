# Unified Search - Installation & Quick Start Guide

## Quick Installation Steps

### Step 1: Upload Plugin

**Option A: Via WordPress Admin (Recommended)**
1. Compress the `unified-search` folder into a ZIP file
2. Go to your WordPress Admin Dashboard
3. Navigate to: **Plugins → Add New → Upload Plugin**
4. Click "Choose File" and select `unified-search.zip`
5. Click "Install Now"
6. Click "Activate Plugin"

**Option B: Via FTP**
1. Upload the `unified-search` folder to `/wp-content/plugins/`
2. Go to WordPress Admin → Plugins
3. Find "Unified Search - Products & Posts" and click "Activate"

### Step 2: Build Search Index (CRITICAL!)

This step is **required** for the plugin to work:

1. Go to **Settings → Unified Search**
2. Click on the **"Index Management"** tab
3. Click the **"Reindex All Content"** button
4. Wait for the success message (may take 30 seconds to a few minutes depending on content volume)

### Step 3: Add Search Form

**Method A: Using Shortcode**
Add this shortcode anywhere in your content:
```
[unified_search]
```

**Method B: Using Widget**
1. Go to **Appearance → Widgets**
2. Add a "Text" or "HTML" widget to your desired widget area
3. Paste the shortcode: `[unified_search]`
4. Save

**Method C: In Theme Files**
Add this code to your theme files (e.g., header.php):
```php
<?php echo do_shortcode('[unified_search]'); ?>
```

### Step 4: Test It Out!

1. Go to the page where you added the search form
2. Start typing (at least 3 characters)
3. Results should appear instantly!

## Configuration

### Recommended Settings for First-Time Setup

1. **General Settings**
   - Minimum Characters: 3
   - Search Delay: 300ms
   - Max Results: 10

2. **Search Settings**
   - Enable all search fields (Title, Content, Excerpt, etc.)
   - Enable both Products and Posts

3. **Display Settings**
   - Show Images: ✓
   - Show Price: ✓
   - Show Excerpt: ✓

## Troubleshooting

### No Search Results Appearing

**Problem**: Search box appears but no results show up

**Solution**:
1. Make sure you clicked "Reindex All Content" in Settings → Unified Search → Index Management
2. Check that you have published products/posts
3. Try typing more than 3 characters
4. Check that Products and/or Posts are enabled in Search Settings

### Search Box Not Appearing

**Problem**: Shortcode doesn't display the search form

**Solution**:
1. Verify the plugin is activated
2. Check for JavaScript errors in browser console (F12)
3. Try adding the shortcode to a test page
4. Clear your browser cache and WordPress cache

### Styling Issues

**Problem**: Search box looks broken or unstyled

**Solution**:
1. Clear browser cache (Ctrl+F5 / Cmd+Shift+R)
2. Check if your theme has conflicting CSS
3. Try adding `!important` to custom CSS rules
4. Deactivate other plugins temporarily to check for conflicts

### Slow Search Performance

**Problem**: Search takes too long to return results

**Solution**:
1. Reindex content: Settings → Unified Search → Index Management → Reindex
2. Reduce "Max Results" in General Settings
3. Check your hosting server performance
4. Consider upgrading hosting if you have thousands of products

## Advanced Configuration

### Customizing Search Behavior

**Search Fewer Items Per Type:**
- Go to General Settings
- Change "Results Per Type" to 3 or less
- This shows fewer products and posts, making results more focused

**Search Only Products (No Posts):**
1. Go to Search Settings → Post Types
2. Uncheck "Blog Posts"
3. Save Settings

**Search Only in Titles:**
1. Go to Search Settings → Search Fields
2. Uncheck everything except "Title"
3. Save Settings

### Styling Customization

Add custom CSS through **Appearance → Customize → Additional CSS**:

```css
/* Make search box wider */
.us-search-wrapper {
    max-width: 800px;
}

/* Change primary color */
.us-search-input-wrapper:focus-within {
    border-color: #your-color-here;
}

/* Change result hover color */
.us-result-item:hover {
    background: #your-color-here;
}
```

## Performance Optimization

### For Large Stores (1000+ Products)

1. **Reduce Search Fields:**
   - Search only in Title and SKU
   - This reduces database query complexity

2. **Lower Max Results:**
   - Set Max Results to 5-8
   - Set Results Per Type to 3

3. **Increase Search Delay:**
   - Set Search Delay to 500ms
   - This reduces the number of searches performed

### For Better User Experience

1. **Lower Minimum Characters:**
   - Set to 2 characters for broader searches
   - Users get results faster

2. **Show More Results:**
   - Set Max Results to 15-20
   - More comprehensive results

## Integration with Themes

### Popular Theme Integration

**Elementor:**
1. Add "Shortcode" widget
2. Paste: `[unified_search]`

**Divi:**
1. Add "Code" module
2. Paste: `[unified_search]`

**Gutenberg:**
1. Add "Shortcode" block
2. Paste: `[unified_search]`

**Custom Header:**
```php
// In your theme's header.php
<div class="site-header-search">
    <?php echo do_shortcode('[unified_search]'); ?>
</div>
```

## Updating the Plugin

1. Deactivate the plugin
2. Delete old plugin folder
3. Upload new version
4. Activate plugin
5. Go to Settings → Unified Search → Index Management
6. Click "Reindex All Content"

## Getting Help

If you encounter issues:

1. Check this guide first
2. Check WordPress debug log
3. Disable other plugins to check for conflicts
4. Try switching to a default WordPress theme temporarily
5. Contact support with:
   - WordPress version
   - WooCommerce version
   - Number of products/posts
   - Description of the issue
   - Screenshots if applicable

## Uninstallation

To completely remove the plugin:

1. Deactivate the plugin
2. Delete the plugin
3. The database table `wp_unified_search_index` will remain
4. To remove it manually, run this SQL query:
   ```sql
   DROP TABLE IF EXISTS wp_unified_search_index;
   ```

## Next Steps

Once installed and working:

1. ✅ Customize the appearance to match your brand
2. ✅ Test search with various keywords
3. ✅ Monitor user search behavior
4. ✅ Adjust settings based on user feedback
5. ✅ Consider adding to mobile menu for better accessibility

---

**Need More Help?**
Check the README.md file for additional documentation and advanced features.
