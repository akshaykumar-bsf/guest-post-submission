=== Guest Post Submission ===
Contributors: yourname
Tags: guest post, submission, form, posts
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow visitors to submit guest posts through a front-end form.

== Description ==

Guest Post Submission is a WordPress plugin that allows website visitors to submit guest post requests through a front-end form. The plugin automatically saves the submission as a draft post, assigns the appropriate metadata (title, category, author info), sets the status to 'pending review,' and sends an email notification to the admin with quick-approve or reject links.

= Features =

* Frontend Submission Form: Post Title, Post Content (rich text editor), Author Name, Author Email, Author Bio, and Featured Image upload.
* Draft Saving: Save the post as 'pending' status, assign metadata for author details, and attach the featured image.
* Admin Notifications: Send an email with the post title, preview link, and quick approve/reject links.
* Admin Settings Page: Email templates, default post category, IP submission limit, moderation toggle.
* Shortcode: For embedding the form anywhere.

= Usage =

Simply add the shortcode `[guest_post_form]` to any page or post where you want the submission form to appear.

= Configuration =

1. Go to Settings > Guest Post Settings to configure:
   * Default category for submissions
   * IP submission limit (set to 0 for unlimited)
   * Notification email address
   * Email subject and template

= Shortcode Options =

The basic shortcode usage is:

`[guest_post_form]`

== Installation ==

1. Upload the `guest-post-submission` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Guest Post Settings to configure the plugin
4. Add the shortcode `[guest_post_form]` to any page or post where you want the submission form to appear

== Frequently Asked Questions ==

= How do I display the submission form? =

Add the shortcode `[guest_post_form]` to any page or post where you want the form to appear.

= Can I customize the email notification? =

Yes, you can customize the email subject and template from the plugin settings page.

= How do I moderate submissions? =

All submissions are saved as pending posts. You can moderate them from the WordPress admin area or use the quick approve/reject links in the notification email.

== Screenshots ==

1. Frontend submission form
2. Admin settings page
3. Email notification

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release
