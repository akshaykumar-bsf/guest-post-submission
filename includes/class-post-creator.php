<?php
class GPS_Post_Creator {
    public function create_post($data) {
        // Sanitize inputs
        $title = sanitize_text_field($data['post_title']);
        $content = wp_kses_post($data['post_content']);
        $author_name = sanitize_text_field($data['author_name']);
        $author_email = sanitize_email($data['author_email']);
        $author_bio = wp_kses_post($data['author_bio']);
        
        // Get default category from settings
        $options = get_option('gps_settings', array());
        
        // Check if we have a default category set
        if (!isset($options['default_category'])) {
            // Try to get the Submissions category
            $submissions_cat = term_exists('Submissions', 'category');
            if ($submissions_cat) {
                $default_category = is_array($submissions_cat) ? $submissions_cat['term_id'] : $submissions_cat;
            } else {
                // Fall back to the site's default category
                $default_category = get_option('default_category');
            }
        } else {
            $default_category = $options['default_category'];
        }
        
        // Create post array
        $post_data = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_status'   => 'pending',
            'post_type'     => 'post',
            'post_category' => array($default_category)
        );
        
        // Insert post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Add author meta
        update_post_meta($post_id, 'gps_author_name', $author_name);
        update_post_meta($post_id, 'gps_author_email', $author_email);
        update_post_meta($post_id, 'gps_author_bio', $author_bio);
        update_post_meta($post_id, 'gps_submission_ip', $_SERVER['REMOTE_ADDR']);
        update_post_meta($post_id, 'gps_submission_date', current_time('mysql'));
        
        // Handle featured image upload if present
        if (!empty($_FILES['featured_image']['name'])) {
            $this->handle_featured_image($post_id, $_FILES['featured_image']);
        }
        
        return $post_id;
    }
    
    private function handle_featured_image($post_id, $file) {
        // WordPress media handling requires these files
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Upload and attach the image
        $attachment_id = media_handle_upload('featured_image', $post_id);
        
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }
}
