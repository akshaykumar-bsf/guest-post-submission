jQuery(document).ready(function($) {
  // Form validation and submission
  $('#gps-submission-form').on('submit', function(e) {
    e.preventDefault();
    
    // Reset form state
    $(this).removeClass('was-validated');
    $('#gps-form-success').hide();
    $('#gps-form-error').hide();
    $('#gps-form-loading').hide();
    
    // Client-side validation
    let isValid = true;
    
    // Validate title
    if (!$('#post_title').val().trim()) {
      $('#post_title').addClass('is-invalid');
      isValid = false;
    } else {
      $('#post_title').removeClass('is-invalid');
    }
    
    // Validate content (if using TinyMCE)
    let content = '';
    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('post_content')) {
      content = tinyMCE.get('post_content').getContent();
    } else {
      content = $('#post_content').val();
    }
    
    if (!content || content.length < 100) {
      $('#post_content_wrapper').addClass('border border-danger rounded');
      isValid = false;
    } else {
      $('#post_content_wrapper').removeClass('border border-danger rounded');
    }
    
    // Validate author name
    if (!$('#author_name').val().trim()) {
      $('#author_name').addClass('is-invalid');
      isValid = false;
    } else {
      $('#author_name').removeClass('is-invalid');
    }
    
    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!$('#author_email').val().trim() || !emailRegex.test($('#author_email').val().trim())) {
      $('#author_email').addClass('is-invalid');
      isValid = false;
    } else {
      $('#author_email').removeClass('is-invalid');
    }
    
    // Validate bio
    if (!$('#author_bio').val().trim()) {
      $('#author_bio').addClass('is-invalid');
      isValid = false;
    } else {
      $('#author_bio').removeClass('is-invalid');
    }
    
    // Validate image if provided
    if ($('#featured_image').val()) {
      const fileInput = $('#featured_image')[0];
      const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
      const fileType = fileInput.files[0].type;
      
      if (fileSize > 2) {
        $('#featured_image').addClass('is-invalid');
        $('#featured_image').next('.form-text').html('<span class="text-danger">File size exceeds 2MB limit.</span>');
        isValid = false;
      } else if (!fileType.match('image.*')) {
        $('#featured_image').addClass('is-invalid');
        $('#featured_image').next('.form-text').html('<span class="text-danger">Please upload an image file.</span>');
        isValid = false;
      } else {
        $('#featured_image').removeClass('is-invalid');
        $('#featured_image').next('.form-text').html('Maximum file size: 2MB. Recommended dimensions: 1200x628 pixels.');
      }
    }
    
    if (!isValid) {
      return false;
    }
    
    // Show loading indicator
    $('#gps-form-loading').show();
    
    // Prepare form data for AJAX submission
    var formData = new FormData(this);
    
    // AJAX submission
    $.ajax({
      url: gps_ajax.ajax_url,
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(response) {
        $('#gps-form-loading').hide();
        
        if (response.success) {
          // Show success message
          $('#gps-form-success').fadeIn();
          
          // Reset form
          $('#gps-submission-form')[0].reset();
          
          // Reset TinyMCE if used
          if (typeof tinyMCE !== 'undefined' && tinyMCE.get('post_content')) {
            tinyMCE.get('post_content').setContent('');
          }
          
          // Scroll to success message
          $('html, body').animate({
            scrollTop: $('#gps-form-success').offset().top - 100
          }, 500);
        } else {
          // Show error message
          $('#gps-error-message').text(response.data);
          $('#gps-form-error').fadeIn();
          
          // Scroll to error message
          $('html, body').animate({
            scrollTop: $('#gps-form-error').offset().top - 100
          }, 500);
        }
      },
      error: function() {
        $('#gps-form-loading').hide();
        $('#gps-error-message').text('Server error. Please try again later.');
        $('#gps-form-error').fadeIn();
      }
    });
  });
  
  // Real-time validation feedback
  $('#post_title, #author_name, #author_email, #author_bio').on('blur', function() {
    if (!$(this).val().trim()) {
      $(this).addClass('is-invalid');
    } else {
      $(this).removeClass('is-invalid');
      
      // Additional validation for email
      if ($(this).attr('id') === 'author_email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test($(this).val().trim())) {
          $(this).addClass('is-invalid');
        }
      }
    }
  });
});
