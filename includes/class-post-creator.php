<?php
/**
 * Post Creator class for the Guest Post Submission plugin
 *
 * @package Guest_Post_Submission
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class GPS_Post_Creator
 * 
 * Handles the creation of posts from form submissions
 */
class GPS_Post_Creator {

    /**
     * Create a new post from form data
     *
     * @param array $form_data The form data
     * @return int|WP_Error The post ID on success, WP_Error on failure
     */
    public function create_post($form_data) {
        // Get the default category from plugin settings
        $options = get_option('gps_settings', array());
        $default_category = isset($options['default_category']) ? $options['default_category'] : 0;
        
        // If no default category is set, try to find the Submissions category
        if (!$default_category) {
            $submissions_cat = get_term_by('slug', 'submissions', 'category');
            if ($submissions_cat) {
                $default_category = $submissions_cat->term_id;
            } else {
                // If Submissions category doesn't exist, create it
                $result = wp_insert_term('Submissions', 'category', array(
                    'description' => 'Category for guest post submissions',
                    'slug' => 'submissions'
                ));
                
                if (!is_wp_error($result)) {
                    $default_category = $result['term_id'];
                }
            }
        }
        
        // Prepare post data
        $post_data = array(
            'post_title'    => sanitize_text_field($form_data['post_title']),
            'post_content'  => wp_kses_post($form_data['post_content']),
            'post_status'   => 'pending',
            'post_author'   => 1, // Default to admin
            'post_category' => array($default_category),
            'meta_input'    => array(
                'gps_author_name'  => sanitize_text_field($form_data['author_name']),
                'gps_author_email' => sanitize_email($form_data['author_email']),
                'gps_author_bio'   => sanitize_textarea_field($form_data['author_bio']),
                'gps_submission_date' => current_time('mysql')
            )
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_data, true);
        
        if (!is_wp_error($post_id)) {
            // Set the category
            wp_set_post_categories($post_id, array($default_category));
        }
        
        return $post_id;
    }
    
    /**
     * Handle featured image upload
     *
     * @param int   $post_id The post ID
     * @param array $file    The uploaded file data
     * @return int|WP_Error The attachment ID on success, WP_Error on failure
     */
    public function handle_featured_image($post_id, $file) {
        // Check if the file is valid
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return new WP_Error('invalid_image', __('Invalid image file.', 'guest-post-submission'));
        }
        
        // Include necessary files for media handling
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        if (!function_exists('media_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
        }
        
        // Handle the upload
        $upload_overrides = array('test_form' => false);
        $uploaded_file = wp_handle_upload($file, $upload_overrides);
        
        if (isset($uploaded_file['error'])) {
            return new WP_Error('upload_error', $uploaded_file['error']);
        }
        
        // Create attachment
        $filename = $uploaded_file['file'];
        $filetype = wp_check_filetype(basename($filename), null);
        
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => sanitize_file_name(basename($filename)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $filename, $post_id);
        
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        // Generate metadata for the attachment
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $filename);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // Set as featured image
        set_post_thumbnail($post_id, $attachment_id);
        
        return $attachment_id;
    }
}
