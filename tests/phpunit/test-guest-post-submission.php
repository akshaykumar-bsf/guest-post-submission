<?php
/**
 * Class GuestPostSubmissionTest
 *
 * @package Guest_Post_Submission
 */

class GuestPostSubmissionTest extends WP_UnitTestCase {

    /**
     * Test instance of plugin main class.
     */
    public function test_plugin_initialization() {
        $this->assertTrue(class_exists('GPS_Shortcode'));
        $this->assertTrue(class_exists('GPS_Form_Handler'));
        $this->assertTrue(class_exists('GPS_Email_Notifications'));
        $this->assertTrue(class_exists('GPS_Submission_Validator'));
        $this->assertTrue(class_exists('GPS_Post_Creator'));
    }

    /**
     * Test category creation on activation.
     */
    public function test_submissions_category_creation() {
        // Simulate plugin activation
        gps_activate();
        
        // Check if Submissions category exists
        $submissions_cat = term_exists('Submissions', 'category');
        $this->assertNotEmpty($submissions_cat);
        
        // Check if it's set as default in options
        $options = get_option('gps_settings', array());
        $this->assertArrayHasKey('default_category', $options);
        
        // Check if the ID matches
        $cat_id = is_array($submissions_cat) ? $submissions_cat['term_id'] : $submissions_cat;
        $this->assertEquals($cat_id, $options['default_category']);
    }

    /**
     * Test form validation with empty fields.
     */
    public function test_validation_empty_fields() {
        $validator = new GPS_Submission_Validator();
        
        // Test with empty data
        $data = array(
            'post_title' => '',
            'post_content' => '',
            'author_name' => '',
            'author_email' => '',
            'author_bio' => ''
        );
        
        $result = $validator->validate($data);
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertTrue($result->has_errors());
    }

    /**
     * Test form validation with invalid email.
     */
    public function test_validation_invalid_email() {
        $validator = new GPS_Submission_Validator();
        
        // Test with invalid email
        $data = array(
            'post_title' => 'Test Title',
            'post_content' => 'This is test content that is long enough to pass validation. It needs to be at least 100 characters long to satisfy the content length requirement.',
            'author_name' => 'Test Author',
            'author_email' => 'invalid-email',
            'author_bio' => 'Test Bio'
        );
        
        $result = $validator->validate($data);
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('invalid_email', $result->get_error_code());
    }

    /**
     * Test form validation with short content.
     */
    public function test_validation_short_content() {
        $validator = new GPS_Submission_Validator();
        
        // Test with short content
        $data = array(
            'post_title' => 'Test Title',
            'post_content' => 'Too short',
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'author_bio' => 'Test Bio'
        );
        
        $result = $validator->validate($data);
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('content_too_short', $result->get_error_code());
    }

    /**
     * Test form validation with valid data.
     */
    public function test_validation_valid_data() {
        $validator = new GPS_Submission_Validator();
        
        // Test with valid data
        $data = array(
            'post_title' => 'Test Title',
            'post_content' => 'This is test content that is long enough to pass validation. It needs to be at least 100 characters long to satisfy the content length requirement.',
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'author_bio' => 'Test Bio'
        );
        
        $result = $validator->validate($data);
        $this->assertTrue($result);
    }

    /**
     * Test post creation.
     */
    public function test_post_creation() {
        $creator = new GPS_Post_Creator();
        
        // Test data
        $data = array(
            'post_title' => 'Test Post Title',
            'post_content' => 'This is test content that is long enough to pass validation. It needs to be at least 100 characters long to satisfy the content length requirement.',
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'author_bio' => 'Test Author Bio'
        );
        
        // Create post
        $post_id = $creator->create_post($data);
        
        // Check if post was created
        $this->assertIsInt($post_id);
        $this->assertGreaterThan(0, $post_id);
        
        // Check post status
        $post = get_post($post_id);
        $this->assertEquals('pending', $post->post_status);
        
        // Check post meta
        $this->assertEquals('Test Author', get_post_meta($post_id, 'gps_author_name', true));
        $this->assertEquals('test@example.com', get_post_meta($post_id, 'gps_author_email', true));
        $this->assertEquals('Test Author Bio', get_post_meta($post_id, 'gps_author_bio', true));
        
        // Check category
        $categories = wp_get_post_categories($post_id);
        $this->assertNotEmpty($categories);
        
        // Get the Submissions category ID
        $submissions_cat = term_exists('Submissions', 'category');
        $cat_id = is_array($submissions_cat) ? $submissions_cat['term_id'] : $submissions_cat;
        
        // Check if post is in Submissions category
        $this->assertContains($cat_id, $categories);
    }

    /**
     * Test IP submission limit.
     */
    public function test_ip_submission_limit() {
        // Set up test
        $validator = new GPS_Submission_Validator();
        $creator = new GPS_Post_Creator();
        
        // Set IP limit to 2
        $options = get_option('gps_settings', array());
        $options['ip_submission_limit'] = 2;
        update_option('gps_settings', $options);
        
        // Test data
        $data = array(
            'post_title' => 'Test Post Title',
            'post_content' => 'This is test content that is long enough to pass validation. It needs to be at least 100 characters long to satisfy the content length requirement.',
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'author_bio' => 'Test Author Bio'
        );
        
        // Create first post
        $post_id1 = $creator->create_post($data);
        $this->assertIsInt($post_id1);
        
        // Create second post
        $post_id2 = $creator->create_post($data);
        $this->assertIsInt($post_id2);
        
        // Check if we can submit a third post
        $can_submit = $validator->can_submit();
        $this->assertFalse($can_submit);
        
        // Reset limit to unlimited
        $options['ip_submission_limit'] = 0;
        update_option('gps_settings', $options);
        
        // Now we should be able to submit
        $can_submit = $validator->can_submit();
        $this->assertTrue($can_submit);
    }

    /**
     * Test email notification.
     */
    public function test_email_notification() {
        // Capture emails instead of sending
        add_filter('wp_mail', array($this, 'capture_email'));
        $this->captured_email = null;
        
        // Create a test post
        $creator = new GPS_Post_Creator();
        $data = array(
            'post_title' => 'Email Test Post',
            'post_content' => 'This is test content that is long enough to pass validation. It needs to be at least 100 characters long to satisfy the content length requirement.',
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'author_bio' => 'Test Author Bio'
        );
        $post_id = $creator->create_post($data);
        
        // Send notification
        $notifier = new GPS_Email_Notifications();
        $notifier->send_admin_notification($post_id);
        
        // Check if email was sent
        $this->assertNotNull($this->captured_email);
        $this->assertEquals(get_option('admin_email'), $this->captured_email['to']);
        $this->assertStringContainsString('Email Test Post', $this->captured_email['message']);
        $this->assertStringContainsString('Test Author', $this->captured_email['message']);
    }
    
    // Helper function to capture emails
    public function capture_email($args) {
        $this->captured_email = $args;
        return false; // Prevent actual sending
    }

    /**
     * Test post moderation.
     */
    public function test_post_moderation() {
        // Create a test post
        $creator = new GPS_Post_Creator();
        $data = array(
            'post_title' => 'Moderation Test Post',
            'post_content' => 'This is test content that is long enough to pass validation. It needs to be at least 100 characters long to satisfy the content length requirement.',
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'author_bio' => 'Test Author Bio'
        );
        $post_id = $creator->create_post($data);
        
        // Check initial status
        $post = get_post($post_id);
        $this->assertEquals('pending', $post->post_status);
        
        // Set up post manager
        $post_manager = new GPS_Post_Management();
        
        // Simulate approval
        $_GET['post_id'] = $post_id;
        $_GET['operation'] = 'approve';
        $_GET['nonce'] = wp_create_nonce('gps_moderate_' . $post_id);
        
        // We need to capture the redirect
        try {
            $post_manager->moderate_post();
        } catch (Exception $e) {
            // Ignore redirect exception
        }
        
        // Check if post was published
        $post = get_post($post_id);
        $this->assertEquals('publish', $post->post_status);
    }
}
