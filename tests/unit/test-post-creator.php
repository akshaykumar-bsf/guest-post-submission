<?php
/**
 * Class Test_Post_Creator
 *
 * @package Guest_Post_Submission
 */

use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Test the post creator class
 */
class Test_Post_Creator extends \PHPUnit\Framework\TestCase {
    use MockeryPHPUnitIntegration;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        Brain\Monkey\setUp();
        
        // Load the class being tested
        require_once GPS_PLUGIN_DIR . 'includes/class-post-creator.php';
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void {
        Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test create_post with default category from settings
     */
    public function test_create_post_with_default_category() {
        $post_creator = new GPS_Post_Creator();
        
        $form_data = [
            'post_title' => 'Test Post Title',
            'post_content' => 'Test post content',
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        // Mock get_option to return a default category
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn(['default_category' => 5]);
        
        // Mock sanitize functions
        Functions\expect('sanitize_text_field')
            ->times(2)
            ->andReturnArg(0);
        
        Functions\expect('wp_kses_post')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('sanitize_email')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('sanitize_textarea_field')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('current_time')
            ->once()
            ->with('mysql')
            ->andReturn('2023-01-01 12:00:00');
        
        // Mock wp_insert_post
        Functions\expect('wp_insert_post')
            ->once()
            ->andReturn(123);
        
        // Mock wp_set_post_categories
        Functions\expect('wp_set_post_categories')
            ->once()
            ->with(123, [5])
            ->andReturn(true);
        
        $post_id = $post_creator->create_post($form_data);
        
        $this->assertEquals(123, $post_id);
    }

    /**
     * Test create_post with no default category but existing Submissions category
     */
    public function test_create_post_with_existing_submissions_category() {
        $post_creator = new GPS_Post_Creator();
        
        $form_data = [
            'post_title' => 'Test Post Title',
            'post_content' => 'Test post content',
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        // Mock get_option to return no default category
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn([]);
        
        // Mock get_term_by to return a term
        Functions\expect('get_term_by')
            ->once()
            ->with('slug', 'submissions', 'category')
            ->andReturn((object) ['term_id' => 10]);
        
        // Mock sanitize functions
        Functions\expect('sanitize_text_field')
            ->times(2)
            ->andReturnArg(0);
        
        Functions\expect('wp_kses_post')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('sanitize_email')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('sanitize_textarea_field')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('current_time')
            ->once()
            ->with('mysql')
            ->andReturn('2023-01-01 12:00:00');
        
        // Mock wp_insert_post
        Functions\expect('wp_insert_post')
            ->once()
            ->andReturn(123);
        
        // Mock wp_set_post_categories
        Functions\expect('wp_set_post_categories')
            ->once()
            ->with(123, [10])
            ->andReturn(true);
        
        $post_id = $post_creator->create_post($form_data);
        
        $this->assertEquals(123, $post_id);
    }

    /**
     * Test create_post with no default category and no existing Submissions category
     */
    public function test_create_post_creating_submissions_category() {
        $post_creator = new GPS_Post_Creator();
        
        $form_data = [
            'post_title' => 'Test Post Title',
            'post_content' => 'Test post content',
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        // Mock get_option to return no default category
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn([]);
        
        // Mock get_term_by to return false (no existing category)
        Functions\expect('get_term_by')
            ->once()
            ->with('slug', 'submissions', 'category')
            ->andReturn(false);
        
        // Mock wp_insert_term to create the category
        Functions\expect('wp_insert_term')
            ->once()
            ->with('Submissions', 'category', [
                'description' => 'Category for guest post submissions',
                'slug' => 'submissions'
            ])
            ->andReturn([
                'term_id' => 15,
                'term_taxonomy_id' => 15
            ]);
        
        // Mock sanitize functions
        Functions\expect('sanitize_text_field')
            ->times(2)
            ->andReturnArg(0);
        
        Functions\expect('wp_kses_post')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('sanitize_email')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('sanitize_textarea_field')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('current_time')
            ->once()
            ->with('mysql')
            ->andReturn('2023-01-01 12:00:00');
        
        // Mock wp_insert_post
        Functions\expect('wp_insert_post')
            ->once()
            ->andReturn(123);
        
        // Mock wp_set_post_categories
        Functions\expect('wp_set_post_categories')
            ->once()
            ->with(123, [15])
            ->andReturn(true);
        
        $post_id = $post_creator->create_post($form_data);
        
        $this->assertEquals(123, $post_id);
    }

    /**
     * Test create_post with error from wp_insert_post
     */
    public function test_create_post_with_error() {
        $post_creator = new GPS_Post_Creator();
        
        $form_data = [
            'post_title' => 'Test Post Title',
            'post_content' => 'Test post content',
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_bio' => 'This is a test bio'
        ];
        
        // Mock get_option to return a default category
        Functions\expect('get_option')
            ->once()
            ->with('gps_settings', [])
            ->andReturn(['default_category' => 5]);
        
        // Mock sanitize functions
        Functions\expect('sanitize_text_field')
            ->times(2)
            ->andReturnArg(0);
        
        Functions\expect('wp_kses_post')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('sanitize_email')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('sanitize_textarea_field')
            ->once()
            ->andReturnArg(0);
        
        Functions\expect('current_time')
            ->once()
            ->with('mysql')
            ->andReturn('2023-01-01 12:00:00');
        
        // Mock wp_insert_post to return an error
        $error = \Mockery::mock('WP_Error');
        Functions\expect('wp_insert_post')
            ->once()
            ->andReturn($error);
        
        $post_id = $post_creator->create_post($form_data);
        
        $this->assertSame($error, $post_id);
    }

    /**
     * Test handle_featured_image with valid image
     */
    public function test_handle_featured_image_with_valid_image() {
        $post_creator = new GPS_Post_Creator();
        
        $post_id = 123;
        $file = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => 0,
            'size' => 1024
        ];
        
        // Mock required functions
        Functions\expect('function_exists')
            ->times(3)
            ->andReturn(false);
        
        Functions\expect('wp_handle_upload')
            ->once()
            ->with($file, ['test_form' => false])
            ->andReturn([
                'file' => '/tmp/uploads/test.jpg',
                'url' => 'http://example.org/wp-content/uploads/test.jpg',
                'type' => 'image/jpeg'
            ]);
        
        Functions\expect('wp_check_filetype')
            ->once()
            ->andReturn([
                'ext' => 'jpg',
                'type' => 'image/jpeg'
            ]);
        
        Functions\expect('sanitize_file_name')
            ->once()
            ->with('test.jpg')
            ->andReturn('test.jpg');
        
        Functions\expect('wp_insert_attachment')
            ->once()
            ->andReturn(456);
        
        Functions\expect('wp_generate_attachment_metadata')
            ->once()
            ->andReturn(['metadata']);
        
        Functions\expect('wp_update_attachment_metadata')
            ->once()
            ->with(456, ['metadata'])
            ->andReturn(true);
        
        Functions\expect('set_post_thumbnail')
            ->once()
            ->with(123, 456)
            ->andReturn(true);
        
        $attachment_id = $post_creator->handle_featured_image($post_id, $file);
        
        $this->assertEquals(456, $attachment_id);
    }

    /**
     * Test handle_featured_image with invalid file
     */
    public function test_handle_featured_image_with_invalid_file() {
        $post_creator = new GPS_Post_Creator();
        
        $post_id = 123;
        $file = []; // Empty file data
        
        Functions\expect('__')
            ->once()
            ->with('Invalid image file.', 'guest-post-submission')
            ->andReturn('Invalid image file.');
        
        $result = $post_creator->handle_featured_image($post_id, $file);
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('invalid_image', $result->get_error_code());
    }

    /**
     * Test handle_featured_image with upload error
     */
    public function test_handle_featured_image_with_upload_error() {
        $post_creator = new GPS_Post_Creator();
        
        $post_id = 123;
        $file = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => 0,
            'size' => 1024
        ];
        
        // Mock required functions
        Functions\expect('function_exists')
            ->times(3)
            ->andReturn(false);
        
        Functions\expect('wp_handle_upload')
            ->once()
            ->with($file, ['test_form' => false])
            ->andReturn(['error' => 'Upload error message']);
        
        $result = $post_creator->handle_featured_image($post_id, $file);
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('upload_error', $result->get_error_code());
    }

    /**
     * Test handle_featured_image with attachment error
     */
    public function test_handle_featured_image_with_attachment_error() {
        $post_creator = new GPS_Post_Creator();
        
        $post_id = 123;
        $file = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => 0,
            'size' => 1024
        ];
        
        // Mock required functions
        Functions\expect('function_exists')
            ->times(3)
            ->andReturn(false);
        
        Functions\expect('wp_handle_upload')
            ->once()
            ->with($file, ['test_form' => false])
            ->andReturn([
                'file' => '/tmp/uploads/test.jpg',
                'url' => 'http://example.org/wp-content/uploads/test.jpg',
                'type' => 'image/jpeg'
            ]);
        
        Functions\expect('wp_check_filetype')
            ->once()
            ->andReturn([
                'ext' => 'jpg',
                'type' => 'image/jpeg'
            ]);
        
        Functions\expect('sanitize_file_name')
            ->once()
            ->with('test.jpg')
            ->andReturn('test.jpg');
        
        // Mock wp_insert_attachment to return an error
        $error = \Mockery::mock('WP_Error');
        Functions\expect('wp_insert_attachment')
            ->once()
            ->andReturn($error);
        
        $result = $post_creator->handle_featured_image($post_id, $file);
        
        $this->assertSame($error, $result);
    }
}
