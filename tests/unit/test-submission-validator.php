<?php
/**
 * Class Test_Submission_Validator
 *
 * @package Guest_Post_Submission
 */

use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Test the submission validator class
 */
class Test_Submission_Validator extends \PHPUnit\Framework\TestCase {
    use MockeryPHPUnitIntegration;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        Brain\Monkey\setUp();
        
        // Load the class being tested
        require_once GPS_PLUGIN_DIR . 'includes/class-submission-validator.php';
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void {
        Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test validation with all required fields
     */
    public function test_validate_with_all_required_fields() {
        $validator = new GPS_Submission_Validator();
        
        $data = [
            'post_title' => 'Test Post Title',
            'post_content' => str_repeat('a', 100), // 100 characters
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        Functions\expect('is_email')
            ->once()
            ->with('john@example.com')
            ->andReturn(true);
        
        $result = $validator->validate($data);
        
        $this->assertTrue($result);
    }

    /**
     * Test validation with missing required fields
     */
    public function test_validate_with_missing_required_fields() {
        $validator = new GPS_Submission_Validator();
        
        $data = [
            'post_title' => '', // Empty title
            'post_content' => str_repeat('a', 100),
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        Functions\expect('__')
            ->once()
            ->with('Post title is required', 'guest-post-submission')
            ->andReturn('Post title is required');
        
        $result = $validator->validate($data);
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('missing_field', $result->get_error_code());
    }

    /**
     * Test validation with invalid email
     */
    public function test_validate_with_invalid_email() {
        $validator = new GPS_Submission_Validator();
        
        $data = [
            'post_title' => 'Test Post Title',
            'post_content' => str_repeat('a', 100),
            'author_name' => 'John Doe',
            'author_email' => 'invalid-email',
            'author_bio' => 'This is a test bio'
        ];
        
        Functions\expect('is_email')
            ->once()
            ->with('invalid-email')
            ->andReturn(false);
        
        Functions\expect('__')
            ->once()
            ->with('Please enter a valid email address', 'guest-post-submission')
            ->andReturn('Please enter a valid email address');
        
        $result = $validator->validate($data);
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('invalid_email', $result->get_error_code());
    }

    /**
     * Test validation with content that is too short
     */
    public function test_validate_with_short_content() {
        $validator = new GPS_Submission_Validator();
        
        $data = [
            'post_title' => 'Test Post Title',
            'post_content' => 'Too short', // Less than 100 characters
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        Functions\expect('is_email')
            ->once()
            ->with('john@example.com')
            ->andReturn(true);
        
        Functions\expect('__')
            ->once()
            ->with('Post content is too short. Please write at least 100 characters.', 'guest-post-submission')
            ->andReturn('Post content is too short. Please write at least 100 characters.');
        
        $result = $validator->validate($data);
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('content_too_short', $result->get_error_code());
    }

    /**
     * Test validation with invalid image type
     */
    public function test_validate_with_invalid_image_type() {
        $validator = new GPS_Submission_Validator();
        
        $data = [
            'post_title' => 'Test Post Title',
            'post_content' => str_repeat('a', 100),
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        // Mock $_FILES global
        $_FILES['featured_image'] = [
            'name' => 'test.pdf',
            'type' => 'application/pdf', // Not an image
            'size' => 1024,
            'tmp_name' => '/tmp/test.pdf',
            'error' => 0
        ];
        
        Functions\expect('is_email')
            ->once()
            ->with('john@example.com')
            ->andReturn(true);
        
        Functions\expect('__')
            ->once()
            ->with('Please upload a valid image file (JPEG, PNG, or GIF)', 'guest-post-submission')
            ->andReturn('Please upload a valid image file (JPEG, PNG, or GIF)');
        
        $result = $validator->validate($data);
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('invalid_image_type', $result->get_error_code());
    }

    /**
     * Test validation with image that is too large
     */
    public function test_validate_with_large_image() {
        $validator = new GPS_Submission_Validator();
        
        $data = [
            'post_title' => 'Test Post Title',
            'post_content' => str_repeat('a', 100),
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        // Mock $_FILES global
        $_FILES['featured_image'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'size' => 3 * 1024 * 1024, // 3MB (exceeds 2MB limit)
            'tmp_name' => '/tmp/test.jpg',
            'error' => 0
        ];
        
        Functions\expect('is_email')
            ->once()
            ->with('john@example.com')
            ->andReturn(true);
        
        Functions\expect('__')
            ->once()
            ->with('Featured image is too large. Maximum size is 2MB.', 'guest-post-submission')
            ->andReturn('Featured image is too large. Maximum size is 2MB.');
        
        $result = $validator->validate($data);
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('image_too_large', $result->get_error_code());
    }

    /**
     * Test can_submit method with IP limit not reached
     */
    public function test_can_submit_with_limit_not_reached() {
        $validator = new GPS_Submission_Validator();
        
        // Mock $_SERVER global
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        
        // Mock get_option
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn(['ip_submission_limit' => 3]);
        
        // Mock global $wpdb
        global $wpdb;
        $wpdb = \Mockery::mock('wpdb');
        $wpdb->postmeta = 'wp_postmeta';
        $wpdb->posts = 'wp_posts';
        
        $wpdb->shouldReceive('prepare')
            ->once()
            ->andReturn('SQL QUERY');
        
        $wpdb->shouldReceive('get_var')
            ->once()
            ->with('SQL QUERY')
            ->andReturn(2); // 2 submissions in last 24 hours (below limit of 3)
        
        Functions\expect('date')
            ->once()
            ->andReturn('2023-01-01 00:00:00');
        
        $result = $validator->can_submit();
        
        $this->assertTrue($result);
    }

    /**
     * Test can_submit method with IP limit reached
     */
    public function test_can_submit_with_limit_reached() {
        $validator = new GPS_Submission_Validator();
        
        // Mock $_SERVER global
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        
        // Mock get_option
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn(['ip_submission_limit' => 3]);
        
        // Mock global $wpdb
        global $wpdb;
        $wpdb = \Mockery::mock('wpdb');
        $wpdb->postmeta = 'wp_postmeta';
        $wpdb->posts = 'wp_posts';
        
        $wpdb->shouldReceive('prepare')
            ->once()
            ->andReturn('SQL QUERY');
        
        $wpdb->shouldReceive('get_var')
            ->once()
            ->with('SQL QUERY')
            ->andReturn(3); // 3 submissions in last 24 hours (equals limit)
        
        Functions\expect('date')
            ->once()
            ->andReturn('2023-01-01 00:00:00');
        
        $result = $validator->can_submit();
        
        $this->assertFalse($result);
    }

    /**
     * Test can_submit method with unlimited submissions allowed
     */
    public function test_can_submit_with_unlimited_submissions() {
        $validator = new GPS_Submission_Validator();
        
        // Mock get_option
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn(['ip_submission_limit' => 0]); // 0 means unlimited
        
        $result = $validator->can_submit();
        
        $this->assertTrue($result);
    }
}
