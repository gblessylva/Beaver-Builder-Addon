# HealthyJoint Goals - Beaver Builder Add Goal Module

## Overview

The Add Goal Module is a custom Beaver Builder component that provides a frontend form for users to create new health and wellness goals. The module integrates with the HealthyJoint Goals WordPress plugin to save goal data via REST API.

## Features

- **Responsive Design**: Mobile-first design that works on all devices
- **Form Validation**: Real-time client-side validation with error messaging
- **REST API Integration**: Secure submission via WordPress REST API
- **Customizable Settings**: Configurable form title, subtitle, and redirect URL
- **Visual Feedback**: Loading states and success/error messages
- **Accessibility**: Proper labeling and keyboard navigation support

## Installation

1. Ensure the HealthyJoint Goals plugin is installed and activated
2. Install and activate Beaver Builder plugin
3. The Add Goal Module will automatically be available in the Beaver Builder module list

## Usage

### Adding the Module to a Page

1. Edit a page with Beaver Builder
2. Look for the "HealthyJoint" group in the module panel
3. Drag the "Add Goal" module to your desired location
4. Configure the module settings as needed

### Module Settings

#### General Tab

- **Form Title**: The main heading displayed above the form (default: "Change your goal")
- **Form Subtitle**: Descriptive text below the title (default: "Set a new goal by following the same process as when you started.")
- **Success Redirect URL**: URL to redirect users after successful goal creation (optional)

#### Style Tab

- **Primary Color**: Color for submit button and focus states (default: #007cba)
- **Secondary Color**: Color for secondary elements (default: #f0f0f0)

### Form Fields

The form includes the following fields:

1. **Purpose** (Required): Dropdown selection
   - Pain Management
   - Physical Therapy
   - Fitness Improvement
   - Injury Recovery
   - Preventive Care

2. **Focus Areas** (Required): Multi-select checkboxes
   - Knee
   - Hip
   - Shoulder
   - Back
   - Ankle
   - Wrist

3. **Goal Title** (Required): Text input for goal name

4. **Goal Description** (Optional): Textarea for detailed goal description

## Technical Details

### File Structure

```
beaver-builder/
└── modules/
    └── add-goal/
        ├── add-goal.php              # Main module class
        ├── icon.svg                  # Module icon
        ├── css/
        │   └── frontend.css          # Module styles
        ├── js/
        │   └── frontend.js           # Module JavaScript
        └── includes/
            ├── frontend.php          # Module template
            └── settings.php          # Module settings (deprecated)
```

### API Integration

The module communicates with the following REST API endpoint:

- **Endpoint**: `POST /wp-json/healthyjoint/v1/goals`
- **Authentication**: WordPress nonce-based authentication
- **Data Format**: JSON

### JavaScript Events

The module emits the following custom events:

- `hj:goal:creating` - Fired before goal creation starts
- `hj:goal:created` - Fired after successful goal creation
- `hj:goal:error` - Fired when goal creation fails

### CSS Classes

Key CSS classes for styling customization:

- `.hj-add-goal-form` - Main form container
- `.hj-form-container` - Inner form wrapper
- `.hj-form-header` - Form title and subtitle area
- `.hj-goal-form` - The actual form element
- `.hj-form-group` - Individual field containers
- `.hj-btn-primary` - Primary action button
- `.hj-btn-secondary` - Secondary action button

## Customization

### Custom Styling

Add custom CSS to override default styles:

```css
.hj-add-goal-form {
    max-width: 800px; /* Increase form width */
}

.hj-btn-primary {
    background-color: #your-brand-color;
    border-color: #your-brand-color;
}
```

### Custom JavaScript

Hook into form events for additional functionality:

```javascript
jQuery(document).on('hj:goal:created', function(event, response) {
    // Custom success handling
    console.log('Goal created:', response);
});
```

## Troubleshooting

### Common Issues

1. **Module not appearing in Beaver Builder**
   - Ensure HealthyJoint Goals plugin is activated
   - Check for JavaScript errors in browser console
   - Verify Beaver Builder is properly installed

2. **Form submission failing**
   - Check WordPress REST API is enabled
   - Verify user has proper permissions to create goals
   - Check browser console for API errors

3. **Styling issues**
   - Check for CSS conflicts with theme
   - Ensure module CSS is loading properly
   - Clear any caching plugins

### Debug Mode

To enable debug mode, add this to your wp-config.php:

```php
define('HJ_GOALS_DEBUG', true);
```

This will log additional information to the browser console.

## Hooks and Filters

### Actions

- `hj_before_goal_form_render` - Fired before form HTML is rendered
- `hj_after_goal_form_render` - Fired after form HTML is rendered

### Filters

- `hj_goal_form_fields` - Modify available form fields
- `hj_goal_form_validation_rules` - Customize validation rules
- `hj_goal_form_redirect_url` - Dynamically set redirect URL

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Accessibility

The module follows WCAG 2.1 AA guidelines:

- Proper semantic HTML structure
- Keyboard navigation support
- Screen reader compatibility
- High contrast color support
- Focus management

## Performance

- Minimal JavaScript footprint (~8KB minified)
- CSS optimized for fast rendering
- Efficient DOM manipulation
- Debounced validation for better UX

## Security

- CSRF protection via WordPress nonces
- Input sanitization and validation
- Secure REST API communication
- No direct database queries from frontend

## Updates

The module is automatically updated with the HealthyJoint Goals plugin. No separate updates required.