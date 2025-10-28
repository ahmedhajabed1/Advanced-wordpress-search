/**
 * Frontend JavaScript for Unified Search
 */

(function($) {
    'use strict';
    
    var searchTimeout = null;
    var currentRequest = null;
    
    $(document).ready(function() {
        
        // Initialize search forms
        $('.us-search-input').each(function() {
            initSearchForm($(this));
        });
        
        // Close results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.us-search-wrapper').length) {
                $('.us-search-results').fadeOut(200);
            }
        });
        
    });
    
    /**
     * Initialize search form
     */
    function initSearchForm($input) {
        var $form = $input.closest('.us-search-form');
        var $results = $form.find('.us-search-results');
        var $resultsContainer = $form.find('.us-results-container');
        
        // Handle input
        $input.on('keyup', function(e) {
            var query = $(this).val().trim();
            
            // Clear timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Abort previous request
            if (currentRequest) {
                currentRequest.abort();
            }
            
            // Check minimum characters
            if (query.length < unifiedSearch.minChars) {
                $results.fadeOut(200);
                $form.removeClass('loading');
                return;
            }
            
            // Set timeout for search
            searchTimeout = setTimeout(function() {
                performSearch(query, $form, $results, $resultsContainer);
            }, unifiedSearch.delay);
        });
        
        // Handle form submit
        $form.on('submit', function(e) {
            e.preventDefault();
            var query = $input.val().trim();
            
            if (query.length >= unifiedSearch.minChars) {
                // Redirect to WordPress search results page
                window.location.href = '/?s=' + encodeURIComponent(query);
            }
        });
        
        // Handle ESC key
        $input.on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC
                $results.fadeOut(200);
                $form.removeClass('loading');
            }
        });
    }
    
    /**
     * Perform search
     */
    function performSearch(query, $form, $results, $resultsContainer) {
        // Show loading state
        $form.addClass('loading');
        
        // Show loading message
        $resultsContainer.html(getLoadingHTML());
        $results.fadeIn(200);
        
        // Perform AJAX request
        currentRequest = $.ajax({
            url: unifiedSearch.ajaxUrl,
            type: 'POST',
            data: {
                action: 'us_search',
                nonce: unifiedSearch.nonce,
                query: query
            },
            success: function(response) {
                $form.removeClass('loading');
                
                if (response.success) {
                    if (response.data.results && response.data.results.length > 0) {
                        renderResults(response.data.results, $resultsContainer, query);
                    } else {
                        $resultsContainer.html(getNoResultsHTML());
                    }
                } else {
                    $resultsContainer.html(getErrorHTML());
                }
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    $form.removeClass('loading');
                    $resultsContainer.html(getErrorHTML());
                }
            }
        });
    }
    
    /**
     * Render search results
     */
    function renderResults(results, $container, query) {
        var html = '';
        
        // Group results by type
        var products = results.filter(function(r) { return r.type === 'product'; });
        var posts = results.filter(function(r) { return r.type === 'post'; });
        
        // Render products in grid
        if (products.length > 0) {
            html += '<div class="us-results-group">';
            html += '<div class="us-results-group-title">🛍️ Products (' + products.length + ')</div>';
            html += '<div class="us-results-grid">';
            products.forEach(function(result) {
                html += renderResultItem(result);
            });
            html += '</div>'; // Close grid
            html += '</div>'; // Close group
        }
        
        // Render posts in grid
        if (posts.length > 0) {
            html += '<div class="us-results-group">';
            html += '<div class="us-results-group-title">📝 Blog Posts (' + posts.length + ')</div>';
            html += '<div class="us-results-grid">';
            posts.forEach(function(result) {
                html += renderResultItem(result);
            });
            html += '</div>'; // Close grid
            html += '</div>'; // Close group
        }
        
        // Add view all link
        html += '<a href="/?s=' + encodeURIComponent(query) + '" class="us-view-all">' + 
                unifiedSearch.strings.viewAll + 
                '</a>';
        
        $container.html(html);
    }
    
    /**
     * Render single result item
     */
    function renderResultItem(result) {
        var html = '<div class="us-result-item" data-type="' + escapeHtml(result.type) + '">';
        html += '<a href="' + escapeHtml(result.url) + '" class="us-result-link">';
        
        // Image Section (top)
        if (result.image) {
            html += '<div class="us-result-image">';
            html += '<img src="' + escapeHtml(result.image) + '" alt="' + escapeHtml(result.title) + '" />';
            html += '</div>';
        }
        
        // Content Section (middle)
        html += '<div class="us-result-content">';
        
        // Header with type badge
        html += '<div class="us-result-header">';
        html += '<span class="us-result-type ' + escapeHtml(result.type) + '">';
        html += result.type === 'product' ? 'Product' : 'Post';
        html += '</span>';
        html += '<h4 class="us-result-title">' + escapeHtml(result.title) + '</h4>';
        html += '</div>';
        
        // Excerpt (if available)
        if (result.excerpt) {
            html += '<p class="us-result-excerpt">' + escapeHtml(result.excerpt) + '</p>';
        }
        
        // Meta for posts
        if (result.type === 'post') {
            html += '<div class="us-result-meta">';
            if (result.date) {
                html += '<span class="us-result-date">' + escapeHtml(result.date) + '</span>';
            }
            if (result.author) {
                html += '<span class="us-result-author">' + escapeHtml(result.author) + '</span>';
            }
            html += '</div>';
        }
        
        // Price Section (separate for products)
        if (result.type === 'product' && result.price) {
            html += '<div class="us-result-price-section">';
            html += '<div class="us-result-price">' + result.price + '</div>';
            html += '</div>';
        }
        
        html += '</div>'; // Close content
        html += '</a>'; // Close link
        
        // Add to cart section (bottom, outside link)
        if (result.type === 'product' && result.add_to_cart) {
            html += '<div class="us-result-actions">';
            html += '<a href="' + escapeHtml(result.add_to_cart_url) + '" class="button us-add-to-cart" data-product-id="' + result.id + '" onclick="event.stopPropagation();">🛒 Add to Cart</a>';
            html += '</div>';
        }
        
        html += '</div>'; // Close item
        
        return html;
    }
    
    /**
     * Get loading HTML
     */
    function getLoadingHTML() {
        return '<div class="us-loading-results">' +
               '<div class="us-loading-spinner"></div>' +
               '<div>' + unifiedSearch.strings.searching + '</div>' +
               '</div>';
    }
    
    /**
     * Get no results HTML
     */
    function getNoResultsHTML() {
        return '<div class="us-no-results">' +
               '<div class="us-no-results-icon">🔍</div>' +
               '<div class="us-no-results-text">' + unifiedSearch.strings.noResults + '</div>' +
               '</div>';
    }
    
    /**
     * Get error HTML
     */
    function getErrorHTML() {
        return '<div class="us-no-results">' +
               '<div class="us-no-results-icon">⚠️</div>' +
               '<div class="us-no-results-text">An error occurred. Please try again.</div>' +
               '</div>';
    }
    
    /**
     * Get products label
     */
    function getProductsLabel(count) {
        return count === 1 ? '1 Product' : count + ' Products';
    }
    
    /**
     * Get posts label
     */
    function getPostsLabel(count) {
        return count === 1 ? '1 Post' : count + ' Posts';
    }
    
    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        if (typeof text !== 'string') {
            return text;
        }
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
})(jQuery);
