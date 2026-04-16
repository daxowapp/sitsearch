# Agent Instructions & Project Architecture

## Project Overview
This project (`sitsearch`) contains a custom WordPress plugin ("Sit Search") for the Study in Türkiye platform, located in `wp-content/plugins/sit-search/`.
It powers program and university search, AI-driven embeddings search, and Generative Engine Optimization (GEO).

## Architecture & File Structure
- **Object-Oriented PHP**: The plugin uses OOP principles with autoloading via Composer (`vendor/autoload.php`).
- **Database / Data Source**: Uses Supabase via API as the main backend data source replacing Zoho CRM. Supabase webhooks trigger sync operations via endpoints.
- **`src/` Directory**: Contains the core logic:
  - `Actions/`: Admin panels, WordPress hooks, AJAX handlers.
  - `Endpoints/`: Custom REST API endpoints (e.g., `/wp-json/sit-search/v1/...`).
  - `Services/`: Core business logic, API clients (Supabase, OpenAI), utilities, schema generation, caching (`CachedData`).
  - `Shortcodes/`: UI rendering for search interfaces and grids.
  - `Handlers/`: Specific data handlers mapping to API.
- **`templates/` Directory**: Contains HTML/PHP templates for shortcodes and admin views.
- **`scripts/` Directory**: Contains NodeJS scripts (e.g., batch FAQ generators using OpenRouter).
- **`assets/` Directory**: Contains CSS and JS files for frontend display and UI interactions.
- **`docs/` Directory**: Contains API documentation (`API.md`) and project instructions.

## Data Models
Data is synchronized from Supabase databases into WordPress Custom Post Types and Taxonomies:
- `sit-university` (Universities)
- `sit-program` (Programs)
- `sit-campus` (Campuses)
- Taxonomies: `sit-country`, `sit-city`, `sit-degree`, `sit-faculty`, `sit-speciality`, `sit-language`.

## SEO & GEO (Generative Engine Optimization)
- Uses extensive AI-generated FAQs for programs.
- Specialized JSON-LD (Course, EducationalOrganization, FAQPage).
- Machine-readable `/llms.txt` integration.
- Custom meta descriptions and OG tags overriding default SEO plugins for programs/universities.

## Agent Guidelines & Global Rules
1. **Always Update the Changelog**: After completing **any** code implementation or bug fix, you MUST automatically update `CHANGELOG.md`. 
   - Consistent format: `[Date] - [Feature/Fix Name] - [Brief Description]`.
2. **Always Update Documentation**: Identify and update any relevant files in the `/docs` or `/documentation` folder to reflect the changes.
3. **No Permission Needed**: Do not ask for permission to update the changelog or documentation; consider them part of the "Definition of Done" for every task.
4. **Read Before Writing**: Study `sit-search.php` and `composer.json` for major dependencies and structures.
5. **Data Synchronization**: All core data (Universities, Programs, Taxonomies) is synced via Supabase Webhooks. Do not introduce alternative CRM connections.
6. **Performance Constraints**: Avoid `WP_Query` inside loops. Use `_prime_post_caches()` or raw SQL via the `$wpdb` class with transients (`CachedData.php`) wherever possible.
