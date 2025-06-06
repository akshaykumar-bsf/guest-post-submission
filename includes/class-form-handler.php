<?php
class GPS_Form_Handler {
    public function __construct() {
        add_action('wp_ajax_gps_submit_post', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_gps_submit_post', array($this, 'handle_submission'));
    }
    
    public function handle_submission() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gps_submission_nonce')) {
            wp_send_json_error(__('Security check failed', 'guest-post-submission'));
        }
        
        // Validate submission
        $validator = new GPS_Submission_Validator();
        $validation = $validator->validate($_POST);
        
        if (is_wp_error($validation)) {
            wp_send_json_error($validation->get_error_message());
        }
        
        // Create post
        $post_creator = new GPS_Post_Creator();
        $post_id = $post_creator->create_post($_POST);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error($post_id->get_error_message());
        }
        
        // Send notification
        $notifier = new GPS_Email_Notifications();
        $notifier->send_admin_notification($post_id);
        
        // Return success
        wp_send_json_success(array(
            'message' => __('Your post has been submitted successfully and is awaiting review.', 'guest-post-submission')
        ));
    }
}

// Initialize the form handler
new GPS_Form_Handler();
