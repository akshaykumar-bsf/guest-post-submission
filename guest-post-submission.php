<?php
/**
 * Plugin Name: Guest Post Submission
 * Plugin URI: https://yourwebsite.com/guest-post-submission
 * Description: Allows visitors to submit guest posts through a front-end form.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: guest-post-submission
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('GPS_VERSION', '1.0.0');
define('GPS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GPS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Activation hook
register_activation_hook(__FILE__, 'gps_activate');
function gps_activate() {
    // Create custom capabilities
    // Set default options
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'gps_deactivate');
function gps_deactivate() {
    // Clean up if necessary
}

// Load plugin classes
require_once GPS_PLUGIN_DIR . 'includes/class-form-handler.php';
require_once GPS_PLUGIN_DIR . 'includes/class-email-notifications.php';
require_once GPS_PLUGIN_DIR . 'includes/class-submission-validator.php';
require_once GPS_PLUGIN_DIR . 'includes/class-post-creator.php';
require_once GPS_PLUGIN_DIR . 'public/class-shortcode.php';

// Load admin classes if in admin area
if (is_admin()) {
    require_once GPS_PLUGIN_DIR . 'admin/class-admin-settings.php';
    require_once GPS_PLUGIN_DIR . 'admin/class-post-management.php';
    new GPS_Admin_Settings();
    new GPS_Post_Management();
}

// Initialize public-facing functionality
new GPS_Shortcode();
