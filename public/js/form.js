jQuery(document).ready(function($) {
    $('#gps-submission-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var messageContainer = $('.gps-form-message');
        
        // Clear previous messages
        messageContainer.html('').removeClass('error success');
        
        // Show loading indicator
        messageContainer.html('<p>Submitting your post...</p>').addClass('loading');
        
        $.ajax({
            url: gps_ajax.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                messageContainer.removeClass('loading');
                
                if (response.success) {
                    messageContainer.html('<p>' + response.data.message + '</p>').addClass('success');
                    $('#gps-submission-form')[0].reset();
                    
                    // Reset TinyMCE if used
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('post_content')) {
                        tinyMCE.get('post_content').setContent('');
                    }
                } else {
                    messageContainer.html('<p>' + response.data + '</p>').addClass('error');
                }
            },
            error: function() {
                messageContainer.removeClass('loading');
                messageContainer.html('<p>An error occurred. Please try again later.</p>').addClass('error');
            }
        });
    });
});
