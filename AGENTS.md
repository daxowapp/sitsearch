# Agent Instructions & Project Architecture

## Project Overview
This project (`sitsearch`) contains custom WordPress plugins for the Study in Türkiye platform, specifically `sit-search` for program and university display/search and `sit-program-recommender` for the AI-driven conversational interface. 
It powers program and university search, AI-driven embeddings search, OpenRouter-driven recommender chat, and Generative Engine Optimization (GEO).

## Architecture & File Structure
- **Object-Oriented PHP**: The plugin uses OOP principles. For `sit-search`, autoloading is handled via Composer (`vendor/autoload.php`).
- **Database / Data Source**: Uses Supabase via API as the main backend data source replacing Zoho CRM. Supabase webhooks trigger sync operations via endpoints.
- **`sit-search/src/` Directory**: Contains the core logic:
  - `Actions/`: Admin panels, WordPress hooks, AJAX handlers.
  - `Endpoints/`: Custom REST API endpoints (e.g., `/wp-json/sit-search/v1/...`).
  - `Services/`: Core business logic, API clients, utilities, schema generation, caching (`CachedData`).
- **`sit-program-recommender/includes/` Directory**: Contains the AI chatbot logic:
  - `class-sit-engine.php`: Core AI logic. Modified to use OpenRouter API for generating questions and analyzing answers.
  - `class-sit-rest-api.php`: Handles frontend to backend REST endpoints for the chat UI.
- **`templates/` Directory**: Contains HTML/PHP templates for shortcodes and admin views.
- **`assets/` Directory**: Contains CSS and JS files for frontend display and UI interactions.

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
- **Affected forms**: Apply Now, Consultation, AI Recommender Chat.
- **Server-side validation**: PHP handlers reject submissions without `kvkk_consent` POST field.
- **KvKK page URL**: All consent links point to `/privacy-notice-data-processing-policy-kvkk-compliance/`.

## Agent Guidelines & Global Rules
1. **Always Update the Changelog**: After completing **any** code implementation or bug fix, you MUST automatically update `CHANGELOG.md`. 
   - Consistent format: `[Date] - [Feature/Fix Name] - [Brief Description]`.
2. **Always Update Documentation**: Identify and update any relevant files in the `/docs` or `/documentation` folder to reflect the changes.
3. **No Permission Needed**: Do not ask for permission to update the changelog or documentation; consider them part of the "Definition of Done" for every task.
4. **Read Before Writing**: Study `sit-search.php` and configurations for structures.
5. **Data Synchronization**: All core data (Universities, Programs, Taxonomies) is synced via Supabase Webhooks. Do not introduce alternative CRM connections.
6. **Performance Constraints**: Avoid `WP_Query` inside loops. Use `_prime_post_caches()` or raw SQL via the `$wpdb` class with transients (`CachedData.php`) wherever possible.

## Legal Disclaimer
- **Every program page and university page** MUST display the official legal disclaimer at the bottom.
- **Disclaimer text**: "Studyinturkiye.com is a private educational consultancy operated by SIT Consultancy LLC. It is not affiliated with, endorsed by, or part of the Turkish Council of Higher Education (YÖK) or any government authority."
- **CSS classes**: `.sit-legal-disclaimer`, `.sit-legal-disclaimer-inner`, `.sit-legal-disclaimer-icon`, `.sit-legal-disclaimer-text`.

## No Payment Collection
- **The platform does NOT collect any money from students.** All service fees, application fees, and Stripe payment processing have been permanently removed.
- **Do NOT re-introduce** any payment forms, Stripe integration, or fee collection of any kind.
- **All forms** must display: "By submitting this form, you agree to our Privacy Policy and Terms of Service" with both links pointing to `/privacy-notice-data-processing-policy-kvkk-compliance/`.

## Recent Changes
- [2026-05-10] Removed all payment collection (Stripe, service fees, application fees) from the entire platform.
- [2026-05-10] Standardized privacy footer across all forms to link to KvKK compliance page.
- [2026-05-10] Added official legal disclaimer (YÖK non-affiliation) to all program and university pages.
- [2026-05-08] Added KvKK (Data Protection) consent to all student-facing forms (Apply Now, Consultation, AI Recommender). Includes server-side validation and bilingual clarification text.
- [2026-04-16] Migrated `sit-program-recommender` backend from OpenAI directly to OpenRouter allowing for model flexibility. Update configuration in WP Options under `sit_recommender_openrouter`.
