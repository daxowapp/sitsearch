# Agent Instructions & Project Architecture

## Project Overview
This project (`sitsearch`) is a custom WordPress plugin ("Sit Search") for the Study in Türkiye platform. It powers program and university search, AI-driven embeddings search, and Generative Engine Optimization (GEO).

**Main Plugin Directory**: `wp-content/plugins/sit-search/`
**Namespace**: `\SIT\Search\`

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

## KvKK Compliance (Data Protection)
- **KvKK** (Kişisel Verilerin Korunması Kanunu) is the Turkish Personal Data Protection Law No. 6698.
- **All student-facing forms** that collect personal data MUST include a mandatory KvKK consent checkbox.
- **Affected forms**: Apply Now (`apply-now.html.php`), Consultation (`consultation.html.php`), AI Recommender Chat (`frontend.js`).
- **Server-side validation**: Both `ApplyNow.php` and `Consultation.php` reject submissions without `kvkk_consent` POST field.
- **Frontend behavior**: Submit buttons are `disabled` until the KvKK checkbox is checked. JavaScript toggles the button state.
- **Bilingual text**: KvKK clarification text is provided in both English and Turkish.
- **KvKK page URL**: All consent links point to `/privacy-notice-data-processing-policy-kvkk-compliance/`.
- **Optional marketing consent**: A separate, non-required checkbox for promotional communications.
- **CSS classes**: `.kvkk-consent-section`, `.kvkk-consent-box`, `.kvkk-checkbox-label`, `.kvkk-checkmark`, `.kvkk-link`.
## Agent Guidelines & Global Rules
1. **Always Update the Changelog**: After completing **any** code implementation or bug fix, you MUST automatically update `CHANGELOG.md`. 
   - Consistent format: `[Date] - [Feature/Fix Name] - [Brief Description]`.
2. **Always Update Documentation**: Identify and update any relevant files in the `/docs` or `/documentation` folder to reflect the changes.
3. **No Permission Needed**: Do not ask for permission to update the changelog or documentation; consider them part of the "Definition of Done" for every task.
4. **Read Before Writing**: Study `site-search.php` and `composer.json` for major dependencies and structures.
5. **Data Synchronization**: All core data (Universities, Programs, Taxonomies) is synced via Supabase Webhooks. Do not introduce alternative CRM connections.
6. **Performance Constraints**: Avoid `WP_Query` inside loops. Use `_prime_post_caches()` or raw SQL via the `$wpdb` class with transients (`CachedData.php`) wherever possible.
7. **Polylang Language Filtering**: The site uses Polylang for multilingual support. All public-facing `WP_Query` calls for `sit-university` and `sit-program` MUST include `'lang' => 'en'` to prevent translation duplicates from appearing. Only internal sync queries (SupabaseSyncEndpoint) should use `'lang' => ''` to access all translations.

## Recent Changes
- [2026-05-11] Fixed duplicate universities on all-universities page caused by missing Polylang language filter in WP_Query.
- [2026-05-10] Removed all payment collection (Stripe, service fees, application fees) from the entire platform.
- [2026-05-10] Standardized privacy footer across all forms to link to KvKK compliance page.
- [2026-05-10] Added official legal disclaimer (YÖK non-affiliation) to all program and university pages.
- [2026-05-08] Added KvKK (Data Protection) consent to all student-facing forms.
