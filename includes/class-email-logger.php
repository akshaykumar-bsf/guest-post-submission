<?php
/**
 * Email logging functionality for development environments.
 */
class GPS_Email_Logger {
    
    /**
     * Initialize the email logger.
     */
    public function __construct() {
        // Only hook in development environments
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_filter('wp_mail', array($this, 'log_email'));
        }
    }
    
    /**
     * Log emails instead of sending them in development environments.
     *
     * @param array $mail_args The wp_mail arguments.
     * @return array The original mail arguments (to allow actual sending).
     */
    public function log_email($mail_args) {
        $log_dir = GPS_PLUGIN_DIR . 'logs';
        
        // Create logs directory if it doesn't exist
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/email-' . date('Y-m-d-H-i-s') . '.log';
        
        $log_content = "Time: " . date('Y-m-d H:i:s') . "\n";
        $log_content .= "To: " . (is_array($mail_args['to']) ? implode(', ', $mail_args['to']) : $mail_args['to']) . "\n";
        $log_content .= "Subject: " . $mail_args['subject'] . "\n";
        $log_content .= "Headers: " . (is_array($mail_args['headers']) ? implode("\n", $mail_args['headers']) : $mail_args['headers']) . "\n";
        $log_content .= "Message: \n" . $mail_args['message'] . "\n";
        $log_content .= "------------------------------\n";
        
        file_put_contents($log_file, $log_content);
        
        return $mail_args;
    }
}
