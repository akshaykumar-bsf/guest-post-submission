<?php
/**
 * Shortcode functionality for the Guest Post Submission plugin
 *
 * @package Guest_Post_Submission
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class GPS_Shortcode
 * 
 * Handles the shortcode functionality for the Guest Post Submission plugin
 */
class GPS_Shortcode {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_shortcode('guest_post_form', array($this, 'render_submission_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_gps_submit_post', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_gps_submit_post', array($this, 'handle_form_submission'));
    }

    /**
     * Enqueue scripts and styles for the frontend
     */
    public function enqueue_scripts() {
        // Only enqueue on pages where the shortcode is used
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'guest_post_form')) {
            // Enqueue WordPress's built-in TinyMCE
            wp_enqueue_editor();
            
            // Enqueue React bundle
            wp_enqueue_script(
                'gps-frontend-script',
                GPS_PLUGIN_URL . 'assets/js/bundle.js',
                array('jquery'),
                GPS_VERSION,
                true
            );
            
            // Pass data to JavaScript
            wp_localize_script(
                'gps-frontend-script',
                'gpsData',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('gps_submit_post_nonce'),
                    'tinymce_url' => includes_url('js/tinymce/')
                )
            );
        }
    }

    /**
     * Render the submission form shortcode
     *
     * @return string The HTML output for the form
     */
    public function render_submission_form() {
        // Return the container for React to render into
        return '<div id="gps-submission-form" class="gps-form-wrapper"></div>';
    }

    /**
     * Handle form submission via AJAX
     */
    public function handle_form_submission() {
        // Use the form handler class to process the submission
        $form_handler = new GPS_Form_Handler();
        $result = $form_handler->process_submission($_POST, 'gps_submit_post_nonce');

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message(),
                'errors' => array(
                    $result->get_error_code() => $result->get_error_message()
                )
            ));
        } else {
            wp_send_json_success(array(
                'message' => $result['message'],
                'post_id' => $result['post_id'],
                'image_error' => $result['image_error']
            ));
        }
    }
}
