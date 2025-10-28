/**
 * Admin JavaScript for Unified Search
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Tab switching
        $('.us-tab-button').on('click', function() {
            var tabId = $(this).data('tab');
            
            // Update buttons
            $('.us-tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Update content
            $('.us-tab-content').removeClass('active');
            $('#' + tabId + '-tab').addClass('active');
        });
        
        // Reindex button
        $('#us-reindex-btn').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $spinner = $button.next('.spinner');
            var $status = $('#us-index-status');
            
            // Disable button and show spinner
            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $status.text('').removeClass('success error');
            
            $.ajax({
                url: usAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'us_reindex',
                    nonce: usAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status
                            .addClass('success')
                            .text(response.data.message);
                    } else {
                        $status
                            .addClass('error')
                            .text(response.data.message || usAdmin.strings.error);
                    }
                },
                error: function() {
                    $status
                        .addClass('error')
                        .text(usAdmin.strings.error);
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                    
                    // Hide status after 5 seconds
                    setTimeout(function() {
                        $status.fadeOut(function() {
                            $(this).text('').removeClass('success error').show();
                        });
                    }, 5000);
                }
            });
        });
        
    });
    
})(jQuery);
