<?php
class GPS_Post_Management {
    public function __construct() {
        add_action('wp_ajax_gps_moderate_post', array($this, 'moderate_post'));
        add_action('add_meta_boxes', array($this, 'add_guest_post_meta_box'));
        add_action('save_post', array($this, 'save_guest_post_meta'));
    }
    
    public function moderate_post() {
        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to moderate posts.', 'guest-post-submission'));
        }
        
        // Verify nonce
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'gps_moderate_' . $post_id)) {
            wp_die(__('Security check failed', 'guest-post-submission'));
        }
        
        // Get operation
        $operation = isset($_GET['operation']) ? $_GET['operation'] : '';
        
        if ($operation === 'approve') {
            // Update post status to publish
            wp_update_post(array(
                'ID' => $post_id,
                'post_status' => 'publish'
            ));
            
            // Redirect to the published post
            wp_redirect(get_permalink($post_id));
            exit;
        } elseif ($operation === 'reject') {
            // Update post status to trash
            wp_trash_post($post_id);
            
            // Redirect to posts list
            wp_redirect(admin_url('edit.php?post_status=trash'));
            exit;
        }
        
        // If we get here, something went wrong
        wp_die(__('Invalid operation', 'guest-post-submission'));
    }
    
    public function add_guest_post_meta_box() {
        add_meta_box(
            'gps_guest_post_info',
            __('Guest Post Information', 'guest-post-submission'),
            array($this, 'render_guest_post_meta_box'),
            'post',
            'side',
            'high'
        );
    }
    
    public function render_guest_post_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('gps_guest_post_meta', 'gps_guest_post_nonce');
        
        // Get the saved meta values
        $author_name = get_post_meta($post->ID, 'gps_author_name', true);
        $author_email = get_post_meta($post->ID, 'gps_author_email', true);
        $author_bio = get_post_meta($post->ID, 'gps_author_bio', true);
        $submission_ip = get_post_meta($post->ID, 'gps_submission_ip', true);
        $submission_date = get_post_meta($post->ID, 'gps_submission_date', true);
        
        // Only show if this is a guest post
        if (!$author_name && !$author_email) {
            echo '<p>' . __('This is not a guest post submission.', 'guest-post-submission') . '</p>';
            return;
        }
        
        // Display the meta fields
        ?>
        <p>
            <label for="gps_author_name"><?php _e('Author Name:', 'guest-post-submission'); ?></label>
            <input type="text" id="gps_author_name" name="gps_author_name" value="<?php echo esc_attr($author_name); ?>" class="widefat" />
        </p>
        <p>
            <label for="gps_author_email"><?php _e('Author Email:', 'guest-post-submission'); ?></label>
            <input type="email" id="gps_author_email" name="gps_author_email" value="<?php echo esc_attr($author_email); ?>" class="widefat" />
        </p>
        <p>
            <label for="gps_author_bio"><?php _e('Author Bio:', 'guest-post-submission'); ?></label>
            <textarea id="gps_author_bio" name="gps_author_bio" class="widefat" rows="4"><?php echo esc_textarea($author_bio); ?></textarea>
        </p>
        <?php if ($submission_ip) : ?>
        <p>
            <strong><?php _e('Submission IP:', 'guest-post-submission'); ?></strong>
            <span><?php echo esc_html($submission_ip); ?></span>
        </p>
        <?php endif; ?>
        <?php if ($submission_date) : ?>
        <p>
            <strong><?php _e('Submitted on:', 'guest-post-submission'); ?></strong>
            <span><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission_date))); ?></span>
        </p>
        <?php endif; ?>
        <?php
    }
    
    public function save_guest_post_meta($post_id) {
        // Check if nonce is set
        if (!isset($_POST['gps_guest_post_nonce'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['gps_guest_post_nonce'], 'gps_guest_post_meta')) {
            return;
        }
        
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save the meta fields
        if (isset($_POST['gps_author_name'])) {
            update_post_meta($post_id, 'gps_author_name', sanitize_text_field($_POST['gps_author_name']));
        }
        
        if (isset($_POST['gps_author_email'])) {
            update_post_meta($post_id, 'gps_author_email', sanitize_email($_POST['gps_author_email']));
        }
        
        if (isset($_POST['gps_author_bio'])) {
            update_post_meta($post_id, 'gps_author_bio', wp_kses_post($_POST['gps_author_bio']));
        }
    }
}
