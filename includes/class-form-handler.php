<?php
/**
 * Form Handler class for Guest Post Submission plugin
 * 
 * @package Guest_Post_Submission
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class GPS_Form_Handler
 * 
 * Handles the form submission processing logic
 */
class GPS_Form_Handler {
    /**
     * Process the form submission data
     *
     * @param array $post_data The submitted form data
     * @param string $nonce_name The name of the nonce to verify
     * @return array|WP_Error Result of the submission process
     */
    public function process_submission($post_data, $nonce_name) {
        // Verify nonce
        if (!isset($post_data['nonce']) || !wp_verify_nonce($post_data['nonce'], $nonce_name)) {
            return new WP_Error('security_check_failed', __('Security check failed', 'guest-post-submission'));
        }
        
        // Validate submission
        $validator = new GPS_Submission_Validator();
        $validation = $validator->validate($post_data);
        
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Get the Submissions category ID
        $options = get_option('gps_settings', array());
        $category_id = isset($options['default_category']) ? $options['default_category'] : 0;
        
        // If no default category is set, try to find the Submissions category
        if (!$category_id) {
            $submissions_cat = get_term_by('slug', 'submissions', 'category');
            if ($submissions_cat) {
                $category_id = $submissions_cat->term_id;
            }
        }
        
        // Add the category ID to the post data
        $post_data['category'] = $category_id;
        
        // Create post
        $post_creator = new GPS_Post_Creator();
        $post_id = $post_creator->create_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Handle featured image if present
        $image_error = null;
        if (!empty($_FILES['featured_image'])) {
            $attachment_id = $post_creator->handle_featured_image($post_id, $_FILES['featured_image']);
            
            if (is_wp_error($attachment_id)) {
                $image_error = $attachment_id->get_error_message();
            }
        }
        
        // Send notification
        $notifier = new GPS_Email_Notifications();
        $notifier->send_admin_notification($post_id);
        
        // Return success
        return array(
            'success' => true,
            'post_id' => $post_id,
            'message' => __('Your post has been submitted successfully and is awaiting review.', 'guest-post-submission'),
            'image_error' => $image_error
        );
    }
}
