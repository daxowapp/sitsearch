# Changelog

All notable changes to the SIT Search plugin will be documented in this file.

## [1.1.5] - 2026-01-20

### Added
- **City Filter** - New filter option to search programs by city
  - Filter dynamically shows only cities that have programs in current results
  - Supports multiple city selection via checkboxes
  - Integrates with existing filter system (URL parameters, applied filters display)
  - Works with "Clear All" and individual filter removal

### Changed
- `src/Shortcodes/FilterSort.php` - Added city filter handling, tax_query, and available_cities extraction
- `templates/shortcodes/filter-sort.html.php` - Added City filter UI section in sidebar
- `assets/js/main.js` - Added city checkbox handlers, URL initialization, and filter display
- `src/App.php` - Bumped plugin version to `3.0.9` for cache busting

---

## [1.1.4] - 2026-01-17

### Security
- **API Keys Secured** - Moved all sensitive keys from plugin to `wp-config.php`
  - Stripe API keys now loaded from `SIT_STRIPE_*` constants
  - Supabase API keys now loaded from `SIT_SUPABASE_*` constants
  - Plugin no longer contains hardcoded credentials

### Fixed
- **CSS Compatibility** - Added standard `line-clamp` property alongside `-webkit-line-clamp` (11 instances)
- **Empty Rulesets** - Removed 2 empty CSS rulesets that caused lint warnings
- **Filename Typo** - Renamed `Breadcrump.php` to `Breadcrumb.php`
- **Export Button** - Ensured text color is white with `!important`
- **Apply Now Page** - Redesigned "Thank You" confirmation with modern UI and SVG icon (Fixed broken HTML)

### Removed
- **Duplicate Files** - Removed `university_grid_shortcode.php` (duplicate of `UniversityGrid.php`)
- **Empty Files** - Removed unused `Logger.php` and `Post.php` service stubs

### Changed
- `wp-config.php` - Added SIT Search configuration section
- `sit-search.php` - Now loads API keys from wp-config constants  
- `src/App.php` - Fixed `Breadcrumb` class reference, bumped version to `3.0.7`
- `assets/css/sit-search.css` - Fixed all lint warnings

---

## [1.1.3] - 2026-01-17

### Added
- **Dynamic Filters** - Filters now only show options from current search results
  - Language filter only shows languages that exist in programs from current results
  - University filter only shows universities that have programs in current results
  - Duration filter only shows duration values that exist in current results
  - Degree Level filter only shows degrees that match programs in current results

### Changed
- `src/Shortcodes/FilterSort.php` - Extracts unique filter values from mapped programs
- `templates/shortcodes/filter-sort.html.php` - Uses dynamic filter options
- `src/App.php` - Bumped plugin version to `3.0.6` for cache busting

---

## [1.1.2] - 2026-01-17

### Fixed
- **Degree Level Filter** - Fixed missing filter options
  - Added `$all_degrees` variable to `FilterSort.php` to fetch all degree terms
  - Degree dropdown now correctly renders and filters programs
  
- **Duration Filter Buttons** - Fixed non-responsive filter buttons
  - Added JavaScript click handlers for `.filter-button` elements in `main.js`
  - Buttons now update URL parameters (duration, isScholarShip) and reload page
  
- **Price (Annual Fee) Filter** - Added Enter key support for price inputs
  - Created `applyPriceFilter()` function in `main.js`
  - Min/max price inputs now apply on Enter key press

### Changed
- `src/Shortcodes/FilterSort.php` - Added degree terms to template context
- `assets/js/main.js` - Added filter button and price input event handlers
- `src/App.php` - Bumped plugin version to `3.0.5` for JS cache busting

---

## [1.1.1] - 2026-01-17

### Fixed
- **Results Header Redesign** - Modernized results header with glassmorphic design
  - Redesigned search bar layout with prominent styling
  - Refactored action buttons (Filters, Sort, View Toggle, Export)
  - New CSS classes: `results-header-v2`, `results-search-container`, `results-action-btn`
  
- **View Toggle Fix** - Fixed list/grid view toggle not working
  - Fixed PHP fatal errors from null `$pdf_program` variable preventing JS from rendering
  - Added `is_array()` guards around `foreach` loops and `count()` calls in export popup
  - Refactored JS with cleaner `switchView()` function and improved event handling

### Changed
- `templates/shortcodes/filter-sort.html.php` - Header layout and null checks for `$pdf_program`
- `src/App.php` - Bumped CSS version to `3.0.4` for cache busting

### Enhanced
- **Recommended Card Styling** - Premium design for recommended programs
  - Animated pulsing glow effect with golden/orange gradient border
  - Elevated card appearance with subtle scale transform
  - White text "RECOMMENDED" badge on gradient background
  - Consistent styling in both grid and list views
- **Export Button** - Changed color from blue to red to match site theme

---

## [1.1.0] - 2026-01-17

### Added
- **Supabase Integration** - New data source replacing Zoho CRM
  - `Services/Supabase.php` - Complete REST API client with caching
  - `Endpoints/SupabaseSyncEndpoint.php` - Webhook endpoint for real-time sync
  - Configuration constants in `sit-search.php`

- **Webhook Sync System** - Real-time data synchronization from Supabase
  - Supports INSERT, UPDATE, DELETE events
  - Maps Supabase tables to WordPress post types and taxonomies
  - Backwards compatible with existing Zoho meta keys

- **Performance Optimizations**
  - `Services/CachedData.php` - Cached access to active universities and taxonomy terms
  - Optimized SQL query for active university IDs (replaces slow loop)
  - Transient + object cache layer for frequently accessed data
  - Cache duration: 5min (short), 1hr (medium), 24hr (long)

### Changed
- **SearchProgramAjax.php** - Refactored with 3-tier search strategy:
  1. Supabase full-text search (fastest, uses PostgreSQL FTS)
  2. OpenAI embeddings semantic search
  3. Traditional WordPress search (fallback)
- Uses `CachedData::get_active_university_ids()` instead of slow loop

### Configuration
Add these constants to `sit-search.php` (already added):
```php
define('SUPABASE_URL', 'https://your-project.supabase.co');
define('SUPABASE_ANON_KEY', 'your-anon-key');
define('SUPABASE_SERVICE_ROLE_KEY', 'your-service-role-key');
define('SUPABASE_BUCKET', 'uploads');
```

### Webhook Setup (Required)
Configure Supabase Database Webhooks to POST to:
`https://your-site.com/wp-json/sit-search/v1/supabase-sync`

### Tables Synced
| Supabase Table | WordPress Entity |
|----------------|------------------|
| zoho_universities | sit-university (post) |
| zoho_programs | sit-program (post) |
| zoho_campus | sit-campus (post) |
| zoho_countries | sit-country (taxonomy) |
| zoho_cities | sit-city (taxonomy) |
| zoho_degrees | sit-degree (taxonomy) |
| zoho_faculty | sit-faculty (taxonomy) |
| zoho_speciality | sit-speciality (taxonomy) |
| zoho_languages | sit-language (taxonomy) |

### New Files
- `src/Services/Supabase.php`
- `src/Services/CachedData.php`
- `src/Endpoints/SupabaseSyncEndpoint.php`
- `CHANGELOG.md`

### Modified Files
- `sit-search.php` - Added Supabase config constants
- `src/App.php` - Registered SupabaseSyncEndpoint
- `src/Actions/SearchProgramAjax.php` - Refactored with Supabase search

---

## [1.0.0] - Initial Release

### Features
- Zoho CRM integration for data sync
- Program and university search
- OpenAI embeddings for semantic search
- Stripe payment integration
- Multiple shortcodes for frontend display
