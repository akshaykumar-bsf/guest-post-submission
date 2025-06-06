<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Guest_Post_Submission
 */

// Define that we're in a testing environment
define('DOING_PHPUNIT', true);

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load Brain Monkey
require_once dirname( __DIR__ ) . '/vendor/antecedent/patchwork/Patchwork.php';
require_once dirname( __DIR__ ) . '/vendor/brain/monkey/inc/patchwork-loader.php';

// Define plugin constants
define( 'GPS_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'GPS_PLUGIN_URL', 'http://example.org/wp-content/plugins/guest-post-submission/' );
define( 'GPS_VERSION', '1.0.0' );

/**
 * Set up the WordPress testing environment.
 */
function _gps_bootstrap_wordpress_test_environment() {
    // Load Brain Monkey
    Brain\Monkey\setUp();
    
    // Define WordPress functions and classes that we'll use in tests
    Brain\Monkey\Functions\stubs([
        'plugin_dir_path'      => function( $file ) { return dirname( $file ) . '/'; },
        'plugin_dir_url'       => function( $file ) { return 'http://example.org/wp-content/plugins/' . basename( dirname( $file ) ) . '/'; },
        'plugin_basename'      => function( $file ) { return basename( dirname( $file ) ) . '/' . basename( $file ); },
        'wp_create_nonce'      => function() { return 'test-nonce'; },
        'wp_verify_nonce'      => function() { return true; },
        'sanitize_text_field'  => function( $text ) { return $text; },
        'sanitize_email'       => function( $email ) { return $email; },
        'wp_kses_post'         => function( $content ) { return $content; },
        'sanitize_textarea_field' => function( $text ) { return $text; },
        'is_email'             => function( $email ) { return strpos( $email, '@' ) !== false; },
        'wp_mail'              => function() { return true; },
        'get_option'           => function() { return []; },
        'update_option'        => function() { return true; },
        'get_post_meta'        => function() { return ''; },
        'add_post_meta'        => function() { return true; },
        'update_post_meta'     => function() { return true; },
        'get_bloginfo'         => function() { return 'Test Site'; },
        'admin_url'            => function() { return 'http://example.org/wp-admin/'; },
        'wp_insert_post'       => function() { return 123; },
        'wp_set_post_categories' => function() { return true; },
        'get_post'             => function() { 
            $post = new stdClass();
            $post->post_title = 'Test Post';
            $post->post_content = 'Test Content';
            return $post;
        },
        'get_term_by'          => function() { 
            $term = new stdClass();
            $term->term_id = 1;
            return $term;
        },
        'wp_insert_term'       => function() { 
            return [
                'term_id' => 1,
                'term_taxonomy_id' => 1
            ];
        },
        'current_time'         => function() { return '2023-01-01 12:00:00'; },
        '__'                   => function( $text ) { return $text; },
        'esc_html__'           => function( $text ) { return $text; },
        'esc_attr__'           => function( $text ) { return $text; },
        'wp_handle_upload'     => function() { 
            return [
                'file' => '/tmp/test-image.jpg',
                'url' => 'http://example.org/wp-content/uploads/test-image.jpg',
                'type' => 'image/jpeg'
            ];
        },
        'wp_check_filetype'    => function() { 
            return [
                'ext' => 'jpg',
                'type' => 'image/jpeg'
            ];
        },
        'wp_insert_attachment' => function() { return 456; },
        'wp_generate_attachment_metadata' => function() { return []; },
        'wp_update_attachment_metadata' => function() { return true; },
        'set_post_thumbnail'   => function() { return true; },
        'ob_start'             => function() { return true; },
        'ob_get_clean'         => function() { return '<p>Mock email template</p>'; }
    ]);
}

// Set up the test environment
_gps_bootstrap_wordpress_test_environment();
