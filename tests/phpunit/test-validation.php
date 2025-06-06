<?php
/**
 * Class ValidationTest
 *
 * @package Guest_Post_Submission
 */

class ValidationTest extends WP_UnitTestCase {

    /**
     * Test validation with empty fields.
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
    }

    /**
     * Test validation with invalid email.
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
     * Test validation with valid data.
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
}
