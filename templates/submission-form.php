<div class="container py-4">
  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      <h2 class="card-title mb-4"><?php _e('Submit a Guest Post', 'guest-post-submission'); ?></h2>
      
      <form id="gps-submission-form" enctype="multipart/form-data">
        <!-- Post Title -->
        <div class="mb-3">
          <label for="post_title" class="form-label"><?php _e('Post Title', 'guest-post-submission'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="post_title" name="post_title" required>
          <div class="invalid-feedback"><?php _e('Please enter a post title.', 'guest-post-submission'); ?></div>
        </div>
        
        <!-- Post Content -->
        <div class="mb-3">
          <label for="post_content" class="form-label"><?php _e('Post Content', 'guest-post-submission'); ?> <span class="text-danger">*</span></label>
          <div id="post_content_wrapper">
            <?php
            wp_editor('', 'post_content', array(
                'media_buttons' => false,
                'textarea_name' => 'post_content',
                'textarea_rows' => 10,
                'teeny' => true,
                'quicktags' => array('buttons' => 'strong,em,link,ul,ol,li,code'),
                'tinymce' => array(
                    'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,link,unlink,undo,redo',
                    'toolbar2' => '',
                ),
            ));
            ?>
          </div>
          <div class="invalid-feedback"><?php _e('Please enter post content (minimum 100 characters).', 'guest-post-submission'); ?></div>
        </div>
        
        <div class="row">
          <!-- Author Name -->
          <div class="col-md-6 mb-3">
            <label for="author_name" class="form-label"><?php _e('Your Name', 'guest-post-submission'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="author_name" name="author_name" required>
            <div class="invalid-feedback"><?php _e('Please enter your name.', 'guest-post-submission'); ?></div>
          </div>
          
          <!-- Author Email -->
          <div class="col-md-6 mb-3">
            <label for="author_email" class="form-label"><?php _e('Your Email', 'guest-post-submission'); ?> <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="author_email" name="author_email" required>
            <div class="invalid-feedback"><?php _e('Please enter a valid email address.', 'guest-post-submission'); ?></div>
          </div>
        </div>
        
        <!-- Author Bio -->
        <div class="mb-3">
          <label for="author_bio" class="form-label"><?php _e('About You', 'guest-post-submission'); ?> <span class="text-danger">*</span></label>
          <textarea class="form-control" id="author_bio" name="author_bio" rows="3" required></textarea>
          <div class="invalid-feedback"><?php _e('Please tell us a bit about yourself.', 'guest-post-submission'); ?></div>
        </div>
        
        <!-- Featured Image -->
        <div class="mb-4">
          <label for="featured_image" class="form-label"><?php _e('Featured Image', 'guest-post-submission'); ?></label>
          <input class="form-control" type="file" id="featured_image" name="featured_image" accept="image/*">
          <div class="form-text"><?php _e('Maximum file size: 2MB. Recommended dimensions: 1200x628 pixels.', 'guest-post-submission'); ?></div>
        </div>
        
        <!-- Hidden fields -->
        <input type="hidden" name="action" value="gps_submit_post">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('gps_submission_nonce'); ?>">
        
        <!-- Submit Button -->
        <div class="d-grid gap-2 col-md-6 mx-auto">
          <button type="submit" class="btn btn-primary py-2"><?php _e('Submit Post', 'guest-post-submission'); ?></button>
        </div>
      </form>
      
      <!-- Success/Error Messages -->
      <div class="mt-4">
        <div id="gps-form-success" class="alert alert-success" style="display: none;">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?php _e('Thanks for your submission! We\'ll review and get back soon.', 'guest-post-submission'); ?>
        </div>
        <div id="gps-form-error" class="alert alert-danger" style="display: none;">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <span id="gps-error-message"><?php _e('There was an error submitting your post. Please try again.', 'guest-post-submission'); ?></span>
        </div>
        <div id="gps-form-loading" class="text-center" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden"><?php _e('Loading...', 'guest-post-submission'); ?></span>
          </div>
          <p class="mt-2"><?php _e('Submitting your post...', 'guest-post-submission'); ?></p>
        </div>
      </div>
    </div>
  </div>
</div>
