<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Guest_Post_Submission
 */

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require dirname( dirname( __DIR__ ) ) . '/guest-post-submission.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
