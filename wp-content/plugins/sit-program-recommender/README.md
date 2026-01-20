# SIT Program Recommender

An intelligent program recommendation system for Singapore Institute of Technology (SIT) that uses quiz-based assessment to match students with suitable academic programs.

## Features

### 🎯 Core Functionality
- **Interactive Quiz System** - Engaging assessment with multiple question types
- **AI-Powered Matching** - Vector-based scoring with machine learning algorithms
- **Smart Recommendations** - Personalized program suggestions with detailed explanations
- **OpenAI Integration** - Enhanced recommendations using GPT models (optional)

### 🔧 Admin Features
- **Comprehensive Settings Panel** - Easy configuration through WordPress admin
- **Question Bank Management** - CRUD operations for quiz questions
- **Department Mapping** - Configure vector weights and scoring rules
- **Export/Import Settings** - Backup and restore plugin configuration
- **Performance Monitoring** - Built-in caching and rate limiting

### 🎨 Frontend Experience
- **Responsive Design** - Works seamlessly on all devices
- **Multiple Themes** - Default, Modern, and Minimal styling options
- **Live Filters** - Real-time program filtering and search
- **Accessibility Ready** - WCAG 2.1 compliant interface
- **No-JS Fallback** - Graceful degradation for users without JavaScript

### 🧩 Developer Features
- **Gutenberg Block** - Native block editor integration
- **REST API** - Full API for custom integrations
- **Shortcode Support** - Easy embedding with `[sit_program_recommender]`
- **Hooks & Filters** - Extensive customization options
- **Internationalization** - Translation-ready with .pot file

## Installation

1. Upload the plugin files to `/wp-content/plugins/sit-program-recommender/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to 'SIT Recommender' in the admin menu to configure settings
4. Create program posts with appropriate meta fields
5. Add the shortcode `[sit_program_recommender]` to any page or post

## Configuration

### General Settings
- **Enable/Disable Plugin** - Toggle plugin functionality
- **Cache Duration** - Set caching time for better performance
- **Rate Limiting** - Configure API request limits

### OpenAI Integration (Optional)
- **API Key** - Your OpenAI API key for enhanced recommendations
- **Model Selection** - Choose between GPT-3.5 Turbo, GPT-4, etc.
- **Response Parameters** - Configure max tokens and temperature

### Question Bank
- **Question Management** - Add, edit, and remove quiz questions
- **Answer Options** - Configure multiple choice options with vector weights
- **Question Categories** - Organize questions by type (interests, skills, etc.)
- **Weighting System** - Set importance levels for different questions

### Department Mapping
- **Vector Weights** - Configure how departments score against user preferences
- **Meta Key Mapping** - Map WordPress meta fields to program attributes
- **Bonus Rules** - Set additional scoring criteria based on user background

### Display Options
- **Results Per Page** - Control pagination
- **Progress Bar** - Show/hide quiz progress
- **Theme Selection** - Choose visual styling
- **Filter Options** - Configure available filters

## Usage

### Shortcode
```php
[sit_program_recommender theme="modern" show_filters="true" show_search="true" max_results="10"]
```

**Shortcode Parameters:**
- `theme` - Visual theme (default, modern, minimal)
- `show_filters` - Enable live filtering (true/false)
- `show_search` - Enable program search (true/false)
- `max_results` - Maximum recommendations to show

### Gutenberg Block
1. Add a new block in the editor
2. Search for "SIT Program Recommender"
3. Configure options in the block settings panel
4. Publish or update your page

### PHP Integration
```php
// Get recommendations programmatically
$engine = new SIT_Engine();
$user_vector = $engine->convert_answers_to_vector($answers);
$recommendations = $engine->get_program_recommendations($department_scores);
```

## Program Setup

### Required Meta Fields
Programs should be created as custom posts with the following meta fields:

- `sit_program_school` - School/Department name
- `sit_program_level` - Program level (undergraduate, postgraduate)
- `sit_program_mode` - Study mode (full-time, part-time)
- `sit_program_duration` - Duration in years
- `sit_program_intake` - Intake periods
- `sit_program_fees` - Program fees
- `sit_program_requirements` - Entry requirements
- `sit_program_careers` - Career prospects

### Sample Program Creation
```php
$program_id = wp_insert_post([
    'post_title' => 'Bachelor of Engineering (Computer Engineering)',
    'post_content' => 'Program description...',
    'post_type' => 'sit_program',
    'post_status' => 'publish'
]);

update_post_meta($program_id, 'sit_program_school', 'School of Engineering');
update_post_meta($program_id, 'sit_program_level', 'undergraduate');
update_post_meta($program_id, 'sit_program_mode', 'full-time');
update_post_meta($program_id, 'sit_program_duration', '4');
```

## REST API

The plugin provides a comprehensive REST API under the `sit/v1` namespace:

### Endpoints
- `POST /sit/v1/quiz/start` - Start a new quiz session
- `POST /sit/v1/quiz/answer` - Submit quiz answers
- `POST /sit/v1/recommend` - Get program recommendations
- `GET /sit/v1/programs` - Retrieve programs with filters
- `GET /sit/v1/filters` - Get available filter options
- `GET /sit/v1/quiz/questions` - Get quiz questions

### Authentication
All API requests require a valid WordPress nonce in the `X-WP-Nonce` header.

### Example API Usage
```javascript
// Start quiz
fetch('/wp-json/sit/v1/quiz/start', {
    method: 'POST',
    headers: {
        'X-WP-Nonce': wpApiSettings.nonce,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        user_data: {
            math_score: 85,
            programming_experience: true
        }
    })
});
```

## Customization

### Hooks and Filters

**Actions:**
- `sit_recommender_before_quiz` - Before quiz starts
- `sit_recommender_after_recommendation` - After recommendations generated
- `sit_recommender_program_save` - When program is saved

**Filters:**
- `sit_recommender_user_vector` - Modify user preference vector
- `sit_recommender_department_scores` - Adjust department scoring
- `sit_recommender_recommendations` - Filter final recommendations
- `sit_recommender_question_options` - Modify quiz questions

### Custom Themes
Create custom CSS themes by targeting the theme class:

```css
.sit-theme-custom {
    --primary-color: #your-color;
}

.sit-theme-custom .sit-btn-primary {
    background: var(--primary-color);
}
```

### Extending Functionality
```php
// Add custom scoring logic
add_filter('sit_recommender_department_scores', function($scores, $user_vector) {
    // Your custom logic here
    return $scores;
}, 10, 2);

// Modify recommendations
add_filter('sit_recommender_recommendations', function($recommendations) {
    // Filter or sort recommendations
    return $recommendations;
});
```

## Performance

### Caching
- **Transient Caching** - Program queries cached for configurable duration
- **Object Caching** - Compatible with Redis, Memcached
- **Static Assets** - CSS/JS files minified and cached

### Optimization Tips
1. Set appropriate cache duration (default: 1 hour)
2. Use rate limiting to prevent abuse
3. Optimize program meta queries
4. Consider CDN for static assets

## Security

### Built-in Security Features
- **Nonce Verification** - All forms and AJAX requests protected
- **Input Sanitization** - All user inputs sanitized and validated
- **Rate Limiting** - Prevents API abuse
- **Capability Checks** - Admin functions restricted to authorized users
- **SQL Injection Prevention** - Prepared statements used throughout

### Security Best Practices
1. Keep plugin updated
2. Use strong OpenAI API keys
3. Monitor rate limiting logs
4. Regular security audits

## Troubleshooting

### Common Issues

**Quiz not loading:**
- Check JavaScript console for errors
- Verify nonce is valid
- Ensure plugin is enabled in settings

**No recommendations:**
- Verify programs exist with proper meta fields
- Check department mapping configuration
- Review question vector weights

**OpenAI integration failing:**
- Validate API key format (starts with 'sk-')
- Check API quota and billing
- Test connection in admin panel

**Performance issues:**
- Increase cache duration
- Optimize database queries
- Check server resources

### Debug Mode
Enable WordPress debug mode to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

### Documentation
- Plugin settings include contextual help
- Inline documentation in code
- REST API documentation available

### Getting Help
1. Check the troubleshooting section
2. Review plugin settings
3. Enable debug logging
4. Contact SIT development team

## Changelog

### Version 1.0.0
- Initial release
- Core recommendation engine
- Admin interface
- REST API
- Gutenberg block integration
- Multiple themes
- OpenAI integration
- Internationalization support

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by the SIT Development Team for Singapore Institute of Technology.

### Third-party Libraries
- WordPress REST API
- OpenAI API (optional)
- jQuery (included with WordPress)

## System Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Modern web browser with JavaScript enabled

## Contributing

This plugin is developed for internal use at SIT. For feature requests or bug reports, please contact the development team.

---

**Note:** This plugin is specifically designed for Singapore Institute of Technology's program recommendation needs. While it can be adapted for other institutions, some features may be SIT-specific.
