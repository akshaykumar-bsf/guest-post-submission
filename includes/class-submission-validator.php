<?php
class GPS_Submission_Validator {
    public function validate($data) {
        // Check required fields
        $required_fields = array(
            'post_title' => __('Post title is required', 'guest-post-submission'),
            'post_content' => __('Post content is required', 'guest-post-submission'),
            'author_name' => __('Author name is required', 'guest-post-submission'),
            'author_email' => __('Author email is required', 'guest-post-submission'),
            'author_bio' => __('Author bio is required', 'guest-post-submission')
        );
        
        foreach ($required_fields as $field => $message) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', $message);
            }
        }
        
        // Validate email
        if (!is_email($data['author_email'])) {
            return new WP_Error('invalid_email', __('Please enter a valid email address', 'guest-post-submission'));
        }
        
        // Validate content length
        if (strlen($data['post_content']) < 100) {
            return new WP_Error('content_too_short', __('Post content is too short. Please write at least 100 characters.', 'guest-post-submission'));
        }
        
        // Validate featured image if uploaded
        if (!empty($_FILES['featured_image']['name'])) {
            $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
            
            if (!in_array($_FILES['featured_image']['type'], $allowed_types)) {
                return new WP_Error('invalid_image_type', __('Please upload a valid image file (JPEG, PNG, or GIF)', 'guest-post-submission'));
            }
            
            // Check file size (2MB max)
            if ($_FILES['featured_image']['size'] > 2 * 1024 * 1024) {
                return new WP_Error('image_too_large', __('Featured image is too large. Maximum size is 2MB.', 'guest-post-submission'));
            }
        }
        
        return true;
    }
    
    public function can_submit() {
        // Get IP submission limit from settings
        $options = get_option('gps_settings', array());
        $limit = isset($options['ip_submission_limit']) ? (int) $options['ip_submission_limit'] : 3;
        
        // If limit is 0, unlimited submissions are allowed
        if ($limit === 0) {
            return true;
        }
        
        // Get current IP address
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Get submissions from this IP in the last 24 hours
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} 
            WHERE meta_key = 'gps_submission_ip' 
            AND meta_value = %s 
            AND post_id IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_date > %s
            )",
            $ip,
            date('Y-m-d H:i:s', strtotime('-24 hours'))
        ));
        
        return ($count < $limit);
    }
}
