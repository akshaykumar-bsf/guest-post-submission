<?php
/**
 * Class Test_Form_Handler
 *
 * @package Guest_Post_Submission
 */

use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Test the form handler class
 */
class Test_Form_Handler extends \PHPUnit\Framework\TestCase {
    use MockeryPHPUnitIntegration;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        Brain\Monkey\setUp();
        
        // Load the classes being tested
        require_once GPS_PLUGIN_DIR . 'includes/class-form-handler.php';
        
        // Mock the validator class
        $this->validator_mock = \Mockery::mock('GPS_Submission_Validator');
        
        // Mock the post creator class
        $this->post_creator_mock = \Mockery::mock('GPS_Post_Creator');
        
        // Mock the email notifications class
        $this->email_notifications_mock = \Mockery::mock('GPS_Email_Notifications');
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void {
        Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test process_submission with valid data
     */
    public function test_process_submission_with_valid_data() {
        $form_handler = new GPS_Form_Handler();
        
        $post_data = [
            'nonce' => 'valid-nonce',
            'post_title' => 'Test Post Title',
            'post_content' => 'Test post content',
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        $nonce_name = 'test_nonce';
        
        // Mock wp_verify_nonce
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('valid-nonce', 'test_nonce')
            ->andReturn(true);
        
        // Mock validator
        Functions\expect('new_GPS_Submission_Validator')
            ->once()
            ->andReturn($this->validator_mock);
        
        $this->validator_mock->shouldReceive('validate')
            ->once()
            ->with($post_data)
            ->andReturn(true);
        
        // Mock get_option
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn(['default_category' => 5]);
        
        // Mock post creator
        Functions\expect('new_GPS_Post_Creator')
            ->once()
            ->andReturn($this->post_creator_mock);
        
        $this->post_creator_mock->shouldReceive('create_post')
            ->once()
            ->andReturn(123);
        
        // Mock email notifications
        Functions\expect('new_GPS_Email_Notifications')
            ->once()
            ->andReturn($this->email_notifications_mock);
        
        $this->email_notifications_mock->shouldReceive('send_admin_notification')
            ->once()
            ->with(123)
            ->andReturn(true);
        
        // Mock __
        Functions\expect('__')
            ->once()
            ->with('Your post has been submitted successfully and is awaiting review.', 'guest-post-submission')
            ->andReturn('Your post has been submitted successfully and is awaiting review.');
        
        $result = $form_handler->process_submission($post_data, $nonce_name);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['post_id']);
        $this->assertEquals('Your post has been submitted successfully and is awaiting review.', $result['message']);
    }

    /**
     * Test process_submission with invalid nonce
     */
    public function test_process_submission_with_invalid_nonce() {
        $form_handler = new GPS_Form_Handler();
        
        $post_data = [
            'nonce' => 'invalid-nonce',
            'post_title' => 'Test Post Title'
        ];
        
        $nonce_name = 'test_nonce';
        
        // Mock wp_verify_nonce
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('invalid-nonce', 'test_nonce')
            ->andReturn(false);
        
        // Mock __
        Functions\expect('__')
            ->once()
            ->with('Security check failed', 'guest-post-submission')
            ->andReturn('Security check failed');
        
        $result = $form_handler->process_submission($post_data, $nonce_name);
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('security_check_failed', $result->get_error_code());
        $this->assertEquals('Security check failed', $result->get_error_message());
    }

    /**
     * Test process_submission with validation error
     */
    public function test_process_submission_with_validation_error() {
        $form_handler = new GPS_Form_Handler();
        
        $post_data = [
            'nonce' => 'valid-nonce',
            'post_title' => '', // Empty title will cause validation error
            'post_content' => 'Test post content'
        ];
        
        $nonce_name = 'test_nonce';
        
        // Mock wp_verify_nonce
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('valid-nonce', 'test_nonce')
            ->andReturn(true);
        
        // Mock validator
        Functions\expect('new_GPS_Submission_Validator')
            ->once()
            ->andReturn($this->validator_mock);
        
        // Create a WP_Error object for validation error
        $validation_error = \Mockery::mock('WP_Error');
        
        $this->validator_mock->shouldReceive('validate')
            ->once()
            ->with($post_data)
            ->andReturn($validation_error);
        
        $result = $form_handler->process_submission($post_data, $nonce_name);
        
        $this->assertSame($validation_error, $result);
    }

    /**
     * Test process_submission with post creation error
     */
    public function test_process_submission_with_post_creation_error() {
        $form_handler = new GPS_Form_Handler();
        
        $post_data = [
            'nonce' => 'valid-nonce',
            'post_title' => 'Test Post Title',
            'post_content' => 'Test post content',
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        $nonce_name = 'test_nonce';
        
        // Mock wp_verify_nonce
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('valid-nonce', 'test_nonce')
            ->andReturn(true);
        
        // Mock validator
        Functions\expect('new_GPS_Submission_Validator')
            ->once()
            ->andReturn($this->validator_mock);
        
        $this->validator_mock->shouldReceive('validate')
            ->once()
            ->with($post_data)
            ->andReturn(true);
        
        // Mock get_option
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn(['default_category' => 5]);
        
        // Mock post creator
        Functions\expect('new_GPS_Post_Creator')
            ->once()
            ->andReturn($this->post_creator_mock);
        
        // Create a WP_Error object for post creation error
        $post_error = \Mockery::mock('WP_Error');
        
        $this->post_creator_mock->shouldReceive('create_post')
            ->once()
            ->andReturn($post_error);
        
        $result = $form_handler->process_submission($post_data, $nonce_name);
        
        $this->assertSame($post_error, $result);
    }

    /**
     * Test process_submission with featured image
     */
    public function test_process_submission_with_featured_image() {
        $form_handler = new GPS_Form_Handler();
        
        $post_data = [
            'nonce' => 'valid-nonce',
            'post_title' => 'Test Post Title',
            'post_content' => 'Test post content',
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        $nonce_name = 'test_nonce';
        
        // Mock $_FILES global
        $_FILES['featured_image'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => 0,
            'size' => 1024
        ];
        
        // Mock wp_verify_nonce
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('valid-nonce', 'test_nonce')
            ->andReturn(true);
        
        // Mock validator
        Functions\expect('new_GPS_Submission_Validator')
            ->once()
            ->andReturn($this->validator_mock);
        
        $this->validator_mock->shouldReceive('validate')
            ->once()
            ->with($post_data)
            ->andReturn(true);
        
        // Mock get_option
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn(['default_category' => 5]);
        
        // Mock post creator
        Functions\expect('new_GPS_Post_Creator')
            ->once()
            ->andReturn($this->post_creator_mock);
        
        $this->post_creator_mock->shouldReceive('create_post')
            ->once()
            ->andReturn(123);
        
        $this->post_creator_mock->shouldReceive('handle_featured_image')
            ->once()
            ->with(123, $_FILES['featured_image'])
            ->andReturn(456);
        
        // Mock email notifications
        Functions\expect('new_GPS_Email_Notifications')
            ->once()
            ->andReturn($this->email_notifications_mock);
        
        $this->email_notifications_mock->shouldReceive('send_admin_notification')
            ->once()
            ->with(123)
            ->andReturn(true);
        
        // Mock __
        Functions\expect('__')
            ->once()
            ->with('Your post has been submitted successfully and is awaiting review.', 'guest-post-submission')
            ->andReturn('Your post has been submitted successfully and is awaiting review.');
        
        $result = $form_handler->process_submission($post_data, $nonce_name);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['post_id']);
        $this->assertEquals('Your post has been submitted successfully and is awaiting review.', $result['message']);
        $this->assertNull($result['image_error']);
    }

    /**
     * Test process_submission with featured image error
     */
    public function test_process_submission_with_featured_image_error() {
        $form_handler = new GPS_Form_Handler();
        
        $post_data = [
            'nonce' => 'valid-nonce',
            'post_title' => 'Test Post Title',
            'post_content' => 'Test post content',
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        $nonce_name = 'test_nonce';
        
        // Mock $_FILES global
        $_FILES['featured_image'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => 0,
            'size' => 1024
        ];
        
        // Mock wp_verify_nonce
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('valid-nonce', 'test_nonce')
            ->andReturn(true);
        
        // Mock validator
        Functions\expect('new_GPS_Submission_Validator')
            ->once()
            ->andReturn($this->validator_mock);
        
        $this->validator_mock->shouldReceive('validate')
            ->once()
            ->with($post_data)
            ->andReturn(true);
        
        // Mock get_option
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn(['default_category' => 5]);
        
        // Mock post creator
        Functions\expect('new_GPS_Post_Creator')
            ->once()
            ->andReturn($this->post_creator_mock);
        
        $this->post_creator_mock->shouldReceive('create_post')
            ->once()
            ->andReturn(123);
        
        // Create a WP_Error object for image error
        $image_error = \Mockery::mock('WP_Error');
        $image_error->shouldReceive('get_error_message')
            ->once()
            ->andReturn('Image upload failed');
        
        $this->post_creator_mock->shouldReceive('handle_featured_image')
            ->once()
            ->with(123, $_FILES['featured_image'])
            ->andReturn($image_error);
        
        // Mock email notifications
        Functions\expect('new_GPS_Email_Notifications')
            ->once()
            ->andReturn($this->email_notifications_mock);
        
        $this->email_notifications_mock->shouldReceive('send_admin_notification')
            ->once()
            ->with(123)
            ->andReturn(true);
        
        // Mock __
        Functions\expect('__')
            ->once()
            ->with('Your post has been submitted successfully and is awaiting review.', 'guest-post-submission')
            ->andReturn('Your post has been submitted successfully and is awaiting review.');
        
        $result = $form_handler->process_submission($post_data, $nonce_name);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['post_id']);
        $this->assertEquals('Your post has been submitted successfully and is awaiting review.', $result['message']);
        $this->assertEquals('Image upload failed', $result['image_error']);
    }
}
