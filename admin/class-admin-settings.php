<?php
class GPS_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function add_settings_page() {
        add_options_page(
            __('Guest Post Submission Settings', 'guest-post-submission'),
            __('Guest Post Settings', 'guest-post-submission'),
            'manage_options',
            'guest-post-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Guest Post Submission Settings', 'guest-post-submission'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('gps_settings_group');
                do_settings_sections('guest-post-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    public function register_settings() {
        register_setting('gps_settings_group', 'gps_settings', array($this, 'sanitize_settings'));
        
        // General Settings
        add_settings_section(
            'gps_general_settings',
            __('General Settings', 'guest-post-submission'),
            array($this, 'general_settings_callback'),
            'guest-post-settings'
        );
        
        add_settings_field(
            'default_category',
            __('Default Category', 'guest-post-submission'),
            array($this, 'default_category_callback'),
            'guest-post-settings',
            'gps_general_settings'
        );
        
        add_settings_field(
            'ip_submission_limit',
            __('IP Submission Limit', 'guest-post-submission'),
            array($this, 'ip_limit_callback'),
            'guest-post-settings',
            'gps_general_settings'
        );
        
        // Email Settings
        add_settings_section(
            'gps_email_settings',
            __('Email Notification Settings', 'guest-post-submission'),
            array($this, 'email_settings_callback'),
            'guest-post-settings'
        );
        
        add_settings_field(
            'notification_email',
            __('Notification Email', 'guest-post-submission'),
            array($this, 'notification_email_callback'),
            'guest-post-settings',
            'gps_email_settings'
        );
        
        add_settings_field(
            'email_subject',
            __('Email Subject', 'guest-post-submission'),
            array($this, 'email_subject_callback'),
            'guest-post-settings',
            'gps_email_settings'
        );
        
        add_settings_field(
            'email_template',
            __('Email Template', 'guest-post-submission'),
            array($this, 'email_template_callback'),
            'guest-post-settings',
            'gps_email_settings'
        );
    }
    
    // Callback functions for settings fields
    public function general_settings_callback() {
        echo '<p>' . __('Configure general settings for guest post submissions.', 'guest-post-submission') . '</p>';
    }
    
    public function email_settings_callback() {
        echo '<p>' . __('Configure email notification settings.', 'guest-post-submission') . '</p>';
    }
    
    public function default_category_callback() {
        $options = get_option('gps_settings');
        $default_category = isset($options['default_category']) ? $options['default_category'] : get_option('default_category');
        
        wp_dropdown_categories(array(
            'name' => 'gps_settings[default_category]',
            'selected' => $default_category,
            'show_option_none' => __('Select a category', 'guest-post-submission'),
            'option_none_value' => '0'
        ));
    }
    
    public function ip_limit_callback() {
        $options = get_option('gps_settings');
        $limit = isset($options['ip_submission_limit']) ? $options['ip_submission_limit'] : 3;
        
        echo '<input type="number" name="gps_settings[ip_submission_limit]" value="' . esc_attr($limit) . '" min="0" />';
        echo '<p class="description">' . __('Maximum number of submissions allowed from a single IP address per day. Set to 0 for unlimited.', 'guest-post-submission') . '</p>';
    }
    
    public function notification_email_callback() {
        $options = get_option('gps_settings');
        $email = isset($options['notification_email']) ? $options['notification_email'] : get_option('admin_email');
        
        echo '<input type="email" name="gps_settings[notification_email]" value="' . esc_attr($email) . '" class="regular-text" />';
    }
    
    public function email_subject_callback() {
        $options = get_option('gps_settings');
        $subject = isset($options['email_subject']) ? $options['email_subject'] : __('New Guest Post Submission', 'guest-post-submission');
        
        echo '<input type="text" name="gps_settings[email_subject]" value="' . esc_attr($subject) . '" class="regular-text" />';
    }
    
    public function email_template_callback() {
        $options = get_option('gps_settings');
        
        // Get default template if not set
        if (!isset($options['email_template']) || empty($options['email_template'])) {
            $notifier = new GPS_Email_Notifications();
            $template = $notifier->get_default_template();
        } else {
            $template = $options['email_template'];
        }
        
        wp_editor($template, 'gps_settings_email_template', array(
            'textarea_name' => 'gps_settings[email_template]',
            'media_buttons' => false,
            'textarea_rows' => 15
        ));
        
        echo '<p class="description">' . __('Available placeholders: {post_title}, {author_name}, {author_email}, {submission_date}, {preview_link}, {approve_link}, {reject_link}', 'guest-post-submission') . '</p>';
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Sanitize each setting
        if (isset($input['default_category'])) {
            $sanitized['default_category'] = absint($input['default_category']);
        }
        
        if (isset($input['ip_submission_limit'])) {
            $sanitized['ip_submission_limit'] = absint($input['ip_submission_limit']);
        }
        
        if (isset($input['notification_email'])) {
            $sanitized['notification_email'] = sanitize_email($input['notification_email']);
        }
        
        if (isset($input['email_subject'])) {
            $sanitized['email_subject'] = sanitize_text_field($input['email_subject']);
        }
        
        if (isset($input['email_template'])) {
            $sanitized['email_template'] = wp_kses_post($input['email_template']);
        }
        
        return $sanitized;
    }
}
