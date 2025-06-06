<?php
class GPS_Email_Notifications {
    public function send_admin_notification($post_id) {
        $post = get_post($post_id);
        $options = get_option('gps_settings', array());
        
        $admin_email = isset($options['notification_email']) ? $options['notification_email'] : get_option('admin_email');
        $subject = isset($options['email_subject']) ? $options['email_subject'] : __('New Guest Post Submission', 'guest-post-submission');
        
        // Replace placeholders in email template
        $template = isset($options['email_template']) ? $options['email_template'] : $this->get_default_template();
        
        $author_name = get_post_meta($post_id, 'gps_author_name', true);
        $author_email = get_post_meta($post_id, 'gps_author_email', true);
        
        $preview_link = admin_url('post.php?post=' . $post_id . '&action=edit');
        $approve_link = admin_url('admin-ajax.php?action=gps_moderate_post&operation=approve&post_id=' . $post_id . '&nonce=' . wp_create_nonce('gps_moderate_' . $post_id));
        $reject_link = admin_url('admin-ajax.php?action=gps_moderate_post&operation=reject&post_id=' . $post_id . '&nonce=' . wp_create_nonce('gps_moderate_' . $post_id));
        
        $replacements = array(
            '{post_title}' => $post->post_title,
            '{author_name}' => $author_name,
            '{author_email}' => $author_email,
            '{submission_date}' => get_post_meta($post_id, 'gps_submission_date', true),
            '{preview_link}' => $preview_link,
            '{approve_link}' => $approve_link,
            '{reject_link}' => $reject_link
        );
        
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        // Send email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($admin_email, $subject, $message, $headers);
    }
    
    public function get_default_template() {
        ob_start();
        include GPS_PLUGIN_DIR . 'templates/email-templates/admin-notification.php';
        return ob_get_clean();
    }
}
