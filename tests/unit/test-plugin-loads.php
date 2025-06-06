<?php
/**
 * Class Test_Plugin_Loads
 *
 * @package Guest_Post_Submission
 */

use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Test that the plugin loads correctly
 */
class Test_Plugin_Loads extends \PHPUnit\Framework\TestCase {
    use MockeryPHPUnitIntegration;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        Brain\Monkey\setUp();
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void {
        Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test that the plugin activates correctly
     */
    public function test_plugin_activation() {
        // Mock term_exists
        Functions\expect('term_exists')
            ->once()
            ->with('Submissions', 'category')
            ->andReturn(false);
        
        // Mock wp_insert_term
        Functions\expect('wp_insert_term')
            ->once()
            ->with('Submissions', 'category', [
                'description' => 'Category for guest post submissions',
                'slug' => 'submissions'
            ])
            ->andReturn([
                'term_id' => 5,
                'term_taxonomy_id' => 5
            ]);
        
        // Mock get_option
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn([]);
        
        // Mock update_option
        Functions\expect('update_option')
            ->once()
            ->with('gps_settings', ['default_category' => 5])
            ->andReturn(true);
        
        // Include the main plugin file
        require_once GPS_PLUGIN_DIR . 'guest-post-submission.php';
        
        // Call the activation function
        gps_activate();
        
        // No assertions needed as we're just checking that the function runs without errors
        $this->assertTrue(true);
    }

    /**
     * Test that the plugin loads text domain
     */
    public function test_plugin_loads_textdomain() {
        // Mock load_plugin_textdomain
        Functions\expect('load_plugin_textdomain')
            ->once()
            ->with('guest-post-submission', false, \Mockery::type('string'))
            ->andReturn(true);
        
        // Include the main plugin file
        require_once GPS_PLUGIN_DIR . 'guest-post-submission.php';
        
        // Call the text domain function
        gps_load_textdomain();
        
        // No assertions needed as we're just checking that the function runs without errors
        $this->assertTrue(true);
    }

    /**
     * Test that the plugin defines constants
     */
    public function test_plugin_defines_constants() {
        // Mock plugin_dir_path
        Functions\expect('plugin_dir_path')
            ->once()
            ->andReturn('/path/to/plugin/');
        
        // Mock plugin_dir_url
        Functions\expect('plugin_dir_url')
            ->once()
            ->andReturn('http://example.org/wp-content/plugins/guest-post-submission/');
        
        // Include the main plugin file
        require_once GPS_PLUGIN_DIR . 'guest-post-submission.php';
        
        // Check that constants are defined
        $this->assertTrue(defined('GPS_PLUGIN_DIR'));
        $this->assertTrue(defined('GPS_PLUGIN_URL'));
        $this->assertTrue(defined('GPS_VERSION'));
    }

    /**
     * Test that the plugin loads required files
     */
    public function test_plugin_loads_required_files() {
        // Mock plugin_dir_path
        Functions\expect('plugin_dir_path')
            ->once()
            ->andReturn('/path/to/plugin/');
        
        // Mock plugin_dir_url
        Functions\expect('plugin_dir_url')
            ->once()
            ->andReturn('http://example.org/wp-content/plugins/guest-post-submission/');
        
        // Mock require_once for each file
        Functions\expect('require_once')
            ->times(7)
            ->andReturn(true);
        
        // Mock is_admin
        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);
        
        // Mock new_GPS_Admin_Settings
        Functions\expect('new_GPS_Admin_Settings')
            ->once()
            ->andReturn(true);
        
        // Mock new_GPS_Post_Management
        Functions\expect('new_GPS_Post_Management')
            ->once()
            ->andReturn(true);
        
        // Mock new_GPS_Shortcode
        Functions\expect('new_GPS_Shortcode')
            ->once()
            ->andReturn(true);
        
        // Include the main plugin file
        require_once GPS_PLUGIN_DIR . 'guest-post-submission.php';
        
        // No assertions needed as we're just checking that the function runs without errors
        $this->assertTrue(true);
    }
}
