<?php
class GPS_Shortcode {
    public function __construct() {
        add_shortcode('guest_post_form', array($this, 'render_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function enqueue_scripts() {
        // Enqueue only when shortcode is used
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'guest_post_form')) {
            // Enqueue Bootstrap CSS
            wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0');
            
            // Enqueue Bootstrap Icons
            wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css', array(), '1.10.0');
            
            // Enqueue custom CSS
            wp_enqueue_style('gps-form-style', GPS_PLUGIN_URL . 'public/css/form.css', array('bootstrap'), GPS_VERSION);
            
            // Enqueue Bootstrap JS
            wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
            
            // Enqueue custom JS
            wp_enqueue_script('gps-form-script', GPS_PLUGIN_URL . 'public/js/form.js', array('jquery', 'bootstrap'), GPS_VERSION, true);
            
            // Add wp_editor scripts if using rich text editor
            wp_enqueue_editor();
            
            // Add AJAX support
            wp_localize_script('gps-form-script', 'gps_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gps_submission_nonce')
            ));
        }
    }
    
    public function render_form() {
        // Check if user is allowed to submit (IP limit check)
        $validator = new GPS_Submission_Validator();
        if (!$validator->can_submit()) {
            return '<div class="alert alert-warning">' . __('You have reached the submission limit. Please try again later.', 'guest-post-submission') . '</div>';
        }
        
        ob_start();
        include GPS_PLUGIN_DIR . 'templates/submission-form.php';
        return ob_get_clean();
    }
}
