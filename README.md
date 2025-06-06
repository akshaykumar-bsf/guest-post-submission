# Guest Post Submission

A WordPress plugin that allows visitors to submit guest posts through a front-end form.

## Features

- Clean, responsive front-end submission form
- TinyMCE editor for post content
- Form validation
- Accessibility compliant
- Automatic categorization of submissions
- Email notifications for new submissions
- Featured image upload support

## Installation

1. Upload the `guest-post-submission` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the shortcode `[guest_post_form]` on any page where you want the submission form to appear

## Usage

### Basic Usage

Simply add the shortcode `[guest_post_form]` to any page or post where you want the submission form to appear.

### Customization

The plugin creates a "Submissions" category automatically. All guest posts will be assigned to this category by default.

## Development

### Prerequisites

- Node.js (v14+)
- npm or yarn
- WordPress development environment
- Composer

### Setup

1. Clone the repository
2. Run `npm install` to install dependencies
3. Run `composer install` to install PHP dependencies
4. Run `npm run dev` for development mode with hot reloading
5. Run `npm run build` to build for production

### Testing

#### End-to-End Testing with Playwright

The plugin uses Playwright for end-to-end testing:

```bash
# Install Playwright browsers
npx playwright install

# Run tests
npm run test:e2e

# Run tests with UI
npm run test:e2e:ui

# Run tests in debug mode
npm run test:e2e:debug

# View test report
npm run test:e2e:report
```

#### Unit Testing with PHPUnit

The plugin uses PHPUnit with Brain Monkey for unit testing:

```bash
# Run unit tests
composer test

# Run unit tests with coverage report
composer test:coverage
```

## Accessibility

This plugin is built with accessibility in mind and follows WCAG 2.1 AA guidelines:

- Proper heading structure
- ARIA attributes
- Keyboard navigation support
- Screen reader announcements
- Sufficient color contrast
- Focus management

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- React for the front-end UI
- TinyMCE for the rich text editor
- Tailwind CSS for styling
- Playwright for E2E testing
- PHPUnit and Brain Monkey for unit testing
