# Guest Post Submission Plugin

A WordPress plugin that allows website visitors to submit guest post requests through a front-end form.

## Features

- Frontend submission form with Bootstrap styling
- Post validation and sanitization
- Admin email notifications
- Quick approve/reject functionality
- Custom "Submissions" category
- IP-based submission limiting
- Admin settings page

## Installation

1. Upload the `guest-post-submission` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Guest Post Settings to configure the plugin
4. Add the shortcode `[guest_post_form]` to any page or post where you want the submission form to appear

## Testing

This plugin includes both PHPUnit tests and Cypress E2E tests.

### PHPUnit Tests

To run the PHPUnit tests:

1. Set up the test environment:
```bash
cd /path/to/plugin
bin/install-wp-tests.sh wordpress_test root root localhost latest
```

2. Run the tests:
```bash
composer install
vendor/bin/phpunit
```

Or if you have PHPUnit installed globally:
```bash
phpunit
```

### Cypress E2E Tests

To run the Cypress E2E tests:

1. Install dependencies:
```bash
npm install
```

2. Update the `cypress.config.js` file with your WordPress installation URL and admin credentials.

3. Create a test page with the shortcode:
```bash
npm test
```

4. In the Cypress UI, run the tests:
```bash
npm test
```

Or run headlessly:
```bash
npm run test:headless
```

## License

GPL v2 or later
