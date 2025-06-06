<div class="gps-form-container">
    <form id="gps-submission-form" enctype="multipart/form-data">
        <div class="gps-form-group">
            <label for="post_title"><?php _e('Post Title', 'guest-post-submission'); ?> <span class="required">*</span></label>
            <input type="text" id="post_title" name="post_title" required>
        </div>
        
        <div class="gps-form-group">
            <label for="post_content"><?php _e('Post Content', 'guest-post-submission'); ?> <span class="required">*</span></label>
            <?php
            wp_editor('', 'post_content', array(
                'media_buttons' => false,
                'textarea_name' => 'post_content',
                'textarea_rows' => 10,
                'teeny' => true
            ));
            ?>
        </div>
        
        <div class="gps-form-group">
            <label for="author_name"><?php _e('Your Name', 'guest-post-submission'); ?> <span class="required">*</span></label>
            <input type="text" id="author_name" name="author_name" required>
        </div>
        
        <div class="gps-form-group">
            <label for="author_email"><?php _e('Your Email', 'guest-post-submission'); ?> <span class="required">*</span></label>
            <input type="email" id="author_email" name="author_email" required>
        </div>
        
        <div class="gps-form-group">
            <label for="author_bio"><?php _e('About You', 'guest-post-submission'); ?> <span class="required">*</span></label>
            <textarea id="author_bio" name="author_bio" rows="4" required></textarea>
        </div>
        
        <div class="gps-form-group">
            <label for="featured_image"><?php _e('Featured Image', 'guest-post-submission'); ?></label>
            <input type="file" id="featured_image" name="featured_image" accept="image/*">
            <p class="description"><?php _e('Maximum file size: 2MB. Recommended dimensions: 1200x628 pixels.', 'guest-post-submission'); ?></p>
        </div>
        
        <div class="gps-form-group">
            <input type="hidden" name="action" value="gps_submit_post">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('gps_submission_nonce'); ?>">
            <button type="submit" class="gps-submit-button"><?php _e('Submit Post', 'guest-post-submission'); ?></button>
        </div>
        
        <div class="gps-form-message"></div>
    </form>
</div>
