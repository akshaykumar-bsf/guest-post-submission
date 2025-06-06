<?php
/**
 * Class Test_Email_Notifications
 *
 * @package Guest_Post_Submission
 */

use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Test the email notifications class
 */
class Test_Email_Notifications extends \PHPUnit\Framework\TestCase {
    use MockeryPHPUnitIntegration;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        Brain\Monkey\setUp();
        
        // Load the class being tested
        require_once GPS_PLUGIN_DIR . 'includes/class-email-notifications.php';
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void {
        Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test send_admin_notification with custom settings
     */
    public function test_send_admin_notification_with_custom_settings() {
        $email_notifications = new GPS_Email_Notifications();
        
        $post_id = 123;
        
        // Mock get_post
        $post = new \stdClass();
        $post->post_title = 'Test Post Title';
        
        Functions\expect('get_post')
            ->once()
            ->with(123)
            ->andReturn($post);
        
        // Mock get_option with custom settings
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn([
                'notification_email' => 'admin@example.com',
                'email_subject' => 'Custom Subject',
                'email_template' => 'Custom template with {post_title}'
            ]);
        
        // Mock get_post_meta calls
        Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'gps_author_name', true)
            ->andReturn('John Doe');
        
        Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'gps_author_email', true)
            ->andReturn('john@example.com');
        
        Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'gps_submission_date', true)
            ->andReturn('2023-01-01 12:00:00');
        
        // Mock admin_url calls
        Functions\expect('admin_url')
            ->once()
            ->with('post.php?post=123&action=edit')
            ->andReturn('http://example.org/wp-admin/post.php?post=123&action=edit');
        
        Functions\expect('admin_url')
            ->once()
            ->with('admin-ajax.php?action=gps_moderate_post&operation=approve&post_id=123&nonce=test-nonce')
            ->andReturn('http://example.org/wp-admin/admin-ajax.php?action=gps_moderate_post&operation=approve&post_id=123&nonce=test-nonce');
        
        Functions\expect('admin_url')
            ->once()
            ->with('admin-ajax.php?action=gps_moderate_post&operation=reject&post_id=123&nonce=test-nonce')
            ->andReturn('http://example.org/wp-admin/admin-ajax.php?action=gps_moderate_post&operation=reject&post_id=123&nonce=test-nonce');
        
        // Mock wp_create_nonce
        Functions\expect('wp_create_nonce')
            ->times(2)
            ->with('gps_moderate_123')
            ->andReturn('test-nonce');
        
        // Mock wp_mail
        Functions\expect('wp_mail')
            ->once()
            ->with(
                'admin@example.com',
                'Custom Subject',
                'Custom template with Test Post Title',
                ['Content-Type: text/html; charset=UTF-8']
            )
            ->andReturn(true);
        
        $email_notifications->send_admin_notification($post_id);
    }

    /**
     * Test send_admin_notification with default settings
     */
    public function test_send_admin_notification_with_default_settings() {
        $email_notifications = \Mockery::mock('GPS_Email_Notifications')->makePartial();
        
        $post_id = 123;
        
        // Mock get_post
        $post = new \stdClass();
        $post->post_title = 'Test Post Title';
        
        Functions\expect('get_post')
            ->once()
            ->with(123)
            ->andReturn($post);
        
        // Mock get_option with empty settings
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn([]);
        
        // Mock get_option for admin_email
        Functions\expect('get_option')
            ->once()
            ->with('admin_email')
            ->andReturn('default@example.com');
        
        // Mock __
        Functions\expect('__')
            ->once()
            ->with('New Guest Post Submission', 'guest-post-submission')
            ->andReturn('New Guest Post Submission');
        
        // Mock get_default_template
        $email_notifications->shouldReceive('get_default_template')
            ->once()
            ->andReturn('<p>Mock email template for testing</p>');
        
        // Mock get_post_meta calls
        Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'gps_author_name', true)
            ->andReturn('John Doe');
        
        Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'gps_author_email', true)
            ->andReturn('john@example.com');
        
        Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'gps_submission_date', true)
            ->andReturn('2023-01-01 12:00:00');
        
        // Mock admin_url calls
        Functions\expect('admin_url')
            ->once()
            ->with('post.php?post=123&action=edit')
            ->andReturn('http://example.org/wp-admin/post.php?post=123&action=edit');
        
        Functions\expect('admin_url')
            ->once()
            ->with('admin-ajax.php?action=gps_moderate_post&operation=approve&post_id=123&nonce=test-nonce')
            ->andReturn('http://example.org/wp-admin/admin-ajax.php?action=gps_moderate_post&operation=approve&post_id=123&nonce=test-nonce');
        
        Functions\expect('admin_url')
            ->once()
            ->with('admin-ajax.php?action=gps_moderate_post&operation=reject&post_id=123&nonce=test-nonce')
            ->andReturn('http://example.org/wp-admin/admin-ajax.php?action=gps_moderate_post&operation=reject&post_id=123&nonce=test-nonce');
        
        // Mock wp_create_nonce
        Functions\expect('wp_create_nonce')
            ->times(2)
            ->with('gps_moderate_123')
            ->andReturn('test-nonce');
        
        // Mock wp_mail
        Functions\expect('wp_mail')
            ->once()
            ->with(
                'default@example.com',
                'New Guest Post Submission',
                \Mockery::any(),
                ['Content-Type: text/html; charset=UTF-8']
            )
            ->andReturn(true);
        
        $email_notifications->send_admin_notification($post_id);
    }
}
