<?php
/**
 * Class PluginLoadsTest
 *
 * @package Guest_Post_Submission
 */

class PluginLoadsTest extends WP_UnitTestCase {

    /**
     * Test if the plugin is loaded.
     */
    public function test_plugin_loaded() {
        $this->assertTrue( class_exists('GPS_Shortcode') );
    }
}
