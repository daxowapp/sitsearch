# Changelog

All notable changes to the SIT Search plugin will be documented in this file.

## [1.4.5] - 2026-05-11

### Fixed

- **Duplicate Universities on All-Universities Page** — Universities like "Istanbul Gelisim University" appeared multiple times in the grid because Polylang creates separate WordPress posts for each language translation, and the `WP_Query` in `UniversityGrid` had no language filter — causing both the English post and its translation(s) to be returned.
  - **Root cause**: `get_universities()` lacked a `'lang' => 'en'` parameter, so WP_Query returned posts from all Polylang languages
  - **Fix**: Added `'lang' => 'en'` to the default query args in both `UniversityGrid::get_universities()` and `TopUniversities` shortcode to ensure only the primary English version of each university is displayed

### Files Modified

- `src/Shortcodes/UniversityGrid.php` — Added `'lang' => 'en'` to default WP_Query args
- `src/Shortcodes/TopUniversities.php` — Added `'lang' => 'en'` to WP_Query args

---

## [1.4.4] - 2026-05-10

### Removed

- **All Payment Collection** — Completely removed service fee, application fee, and Stripe payment processing
  - Removed fee display (Service Fee, Application Fee, Total Fee) from the Apply Now form
  - Removed Stripe card input fields (card number, expiry, CVC) for Public university applications
  - Removed Stripe.js CDN loading and PaymentIntent creation logic
  - Removed `create-payment-intent.php` endpoint file
  - Removed `STRIPE_PUBLIC_KEY` and `STRIPE_SECRET_KEY` constants from `sit-search.php`
  - Removed `Service_fee` and `Application_Fee` data fetching from both ApplyNow shortcode files
  - Removed Service Fee / Application Fee display from program cards (`program-box.html.php`) — now always shows Rankings + Students

### Changed

- **Privacy Footer Standardization** — All forms now display: "By submitting this form, you agree to our Privacy Policy and Terms of Service" with both links pointing to `/privacy-notice-data-processing-policy-kvkk-compliance/`
  - Updated Apply Now form footer
  - Added privacy footer to Consultation form

### Files Modified

- `templates/shortcodes/apply-now.html.php` — Fee display, Stripe fields, Stripe JS removed; privacy footer updated
- `templates/shortcodes/program-box.html.php` — Fee attributes replaced with Rankings/Students
- `templates/shortcodes/consultation.html.php` — Privacy footer added
- `templates/shortcodes/ApplyNow.php` — Fee data fetching removed
- `src/Shortcodes/ApplyNow.php` — Fee data fetching removed
- `sit-search.php` — Stripe constants removed
- `create-payment-intent.php` — **DELETED**

---

## [1.4.3] - 2026-05-10

### Added

- **Official Legal Disclaimer** — Added mandatory legal disclaimer at the bottom of every program page and university page
  - States that Studyinturkiye.com is operated by SIT Consultancy LLC and is not affiliated with YÖK or any government authority
  - Styled with a subtle gray left-border info box, responsive on mobile
  - CSS component: `.sit-legal-disclaimer`, `.sit-legal-disclaimer-inner`, `.sit-legal-disclaimer-icon`, `.sit-legal-disclaimer-text`

### Files Modified

- `templates/shortcodes/single-program.html.php` — Legal disclaimer section added before the floating share button
- `templates/shortcodes/single-university.html.php` — Legal disclaimer section added after the data correction disclaimer
- `assets/css/sit-search.css` — Legal disclaimer component styles (55+ lines)

---

## [1.4.2] - 2026-05-08

### Added

- **KvKK Data Protection Consent** — Added mandatory KvKK (Turkish Personal Data Protection Law No. 6698) consent checkboxes to all student-facing forms
  - **Apply Now Form** — Required KvKK consent checkbox with bilingual (EN/TR) clarification text, optional marketing consent, disabled submit button until consent is given
  - **Consultation Form** — Same KvKK consent pattern with server-side validation
  - **AI Recommender Chat** — KvKK consent checkbox on the welcome screen before students can start the AI assessment
  - **Server-side Validation** — Both `ApplyNow.php` and `Consultation.php` PHP handlers reject submissions without `kvkk_consent` field
  - **Custom CSS** — Styled checkbox with custom checkmark, red accent border, hover effects, shake animation on error, disabled button state
  - **Footer Updated** — Apply form footer now links to KvKK Clarification Text, Privacy Policy, and Terms of Service

### Files Modified

- `templates/shortcodes/apply-now.html.php` — KvKK consent section, disabled submit button, JS validation
- `templates/shortcodes/consultation.html.php` — KvKK consent section, disabled submit button, JS validation
- `src/Shortcodes/ApplyNow.php` — Server-side KvKK consent validation
- `src/Shortcodes/Consultation.php` — Server-side KvKK consent validation
- `assets/css/sit-search.css` — KvKK consent component styles (175+ lines)

### Files Modified (sit-program-recommender plugin)

- `assets/js/frontend.js` — KvKK consent on AI recommender welcome screen

---

## [1.4.1] - 2026-04-14

### Added

- **Project Metadata** — Added `AGENTS.md` file to document full project architecture and agent AI rules.
- **University Media Admin Dashboard** — Created a centralized WordPress admin dashboard page (`UniversityMediaAdmin`) under "Study in Turkiye" to easily upload, change, and monitor University custom logos and banners (`uni_logo`, `uni_image`).
  - Identifies universities missing a logo or banner.
  - Automatically updates all language translations globally via Polylang sync to keep media consistent.
  - Integrates native WordPress media uploader and saves directly via AJAX, instantly updating frontend shortcodes without code changes.

### Fixed

- **Global Admin Visibility Bug** — Applied strict `tax_query` filters to `UniversityMediaAdmin`, `UniversityStatusAdmin`, and `FeaturedUniversityAdmin` to exclusively retrieve universities located in **Turkey** and **Northern Cyprus**. This prevents irrelevant data (e.g., universities from China synced via CRM) from appearing in the administrative lists and search dropdowns.

---

## [1.4.0] - 2026-04-13

### Fixed

- **GEO Program Pages — Missing University Data** — Programs without a valid `zh_university` link (or with a deleted/inactive university) were rendering broken pages with blank university names, empty locations (`, Turkey` or `,`), and broken breadcrumb/schema links. Affected GEO structured data, meta descriptions, and visual layout.
  - **Root cause**: `Singleprogram.php` and `GeoSchema.php` called `get_post()` on empty/null `zh_university` meta values without null checks, then accessed `->post_title`, `->guid`, `->ID` on the null result
  - **Singleprogram.php**: Added null-safe university resolution with fallback chain: university post → program's `University` ACF text field → empty string. Country/city now falls back to the program's own taxonomy terms when the university has none assigned.
  - **GeoSchema.php**: Added null checks in `get_course_schema()`, `get_faq_schema()`, `get_program_meta_description()`, and `output_schemas()` breadcrumb generation. University name fallback uses program's `University` meta field.
  - **single-program.html.php**: Template now conditionally renders location (no more `, Turkey` or `,`), hides empty university rows in the facts table, uses `array_filter()` + `implode()` for location strings, and shows program title as h1 fallback when university title is empty.

### Changed

- **GEO FAQ Count Reduced: 100 → 20** — Reduced FAQ generation from 100 (10 categories × 10 each) to 20 (5 categories × 4 each). AI search engines cite 3-5 key facts; 100 FAQs diluted topical focus and looked spammy. Focused categories: Tuition, Admission, Academic, Campus Life, Visa.
- **University Name in Every FAQ** — Added explicit prompt instructions requiring the university name in every FAQ question and answer (e.g. "What is the tuition fee for X at **Sabanci University**?"). Improves entity recognition by AI search engines.
- **Single-Call FAQ Generation** — Eliminated the 5-round batch system. All 20 FAQs are now generated in a single API call per program, reducing OpenRouter API usage by 80% and simplifying the batch pipeline.
- **FAQ Admin Null Safety** — `GeoSettingsAdmin.php` now uses null-safe university lookups when building FAQ prompts, preventing empty university names in generated content.

### Files Modified

- `src/Shortcodes/Singleprogram.php` — Null-safe university resolution with taxonomy and meta field fallbacks
- `src/Services/GeoSchema.php` — Null checks across all methods using `zh_university`
- `templates/shortcodes/single-program.html.php` — Conditional rendering for empty university/location data
- `src/Actions/GeoSettingsAdmin.php` — Simplified FAQ generation (single call, 20 FAQs), null-safe university, university name in prompts
- `scripts/generate-faqs.js` — Reduced to 20 FAQs (5 categories × 4), mandatory university name in every Q&A

---

## [1.3.9] - 2026-04-11

### Fixed

- **GEO Admin Page** — WordPress footer no longer overlaps the "Generate All FAQs" button (hidden via CSS on this page)
- **Search Results Page** — Fixed search bar header overlapping filters/results on scroll. Killed sticky/fixed positioning from Elementor on the results page so the header scrolls naturally with the content.

### Improved

- **FAQ Batch Generation Speed** — ~3× faster with parallel workers
  - **3 parallel workers** — browser launches 3 concurrent AJAX requests, each processing a different program simultaneously
  - Transient-based locking prevents duplicate work — each worker claims a program ID before processing
  - Server processes **2 rounds per request** (40 FAQs) instead of 1 (20 FAQs)
  - ETA display now shows throughput in programs/min and active worker count

### Added

- **Site-Wide UTM Tracking** — Persistent attribution across all pages
  - `assets/js/utm-tracker.js` — Global script captures `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term` from any landing URL
  - Stores attribution in both cookie (`sit_utm`, 30-day expiry) and localStorage for redundancy
  - Auto-injects UTM hidden fields into every `POST` form on the site (Apply Now + Consultation)
  - Last-touch attribution: new UTM params overwrite previous, but persists across page navigation
  - Also captures `landing_page` and `referrer` for full attribution context
  - **Consultation form** now sends UTM data to Zoho CRM (`UTM_Source`, `UTM_Medium`, `UTM_Campaign`, `UTM_Content`, `UTM_Term`)
  - **Apply Now form** cleanup: removed debug `print_r` block, fixed duplicate hidden fields
  - MutationObserver watches for dynamically injected forms (modals, popups) and auto-fills them too

- **Comprehensive Mobile UX Overhaul** — 660+ lines of responsive CSS improvements across all pages
  - **Program Page**: Hero compact layout, full-width buttons, scrollable FAQ tabs, compact GEO summary/facts table, reduced padding
  - **Apply Page**: Single-column form fields, touch-friendly inputs (44px min), compact program summary, stacked CTA
  - **Search Results**: Full-width cards, stacked action buttons, scrollable filter chips
  - **University Page**: Compact hero, single-column info grid, responsive tab navigation
  - **Global**: Prevented horizontal overflow, 44px min tap targets, `-webkit-overflow-scrolling: touch` on scroll areas
  - Breakpoints: 768px (tablets/large phones) + 480px (small phones)

- **Application Submit Loading State** — Prevents double-submission
  - "Submit Application" button shows inline spinner + "Submitting..." text on click
  - Button becomes disabled and grayed out
  - Full-page overlay with blur appears: "Submitting your application... Please wait, uploading your documents"
  - Overlay blocks all interaction until server responds

- **Program Overview "Read More"** — Collapses long overview text
  - Content truncated to ~5 lines (120px) with gradient fade
  - "Read More" / "Read Less" pill button with animated arrow
  - Full content stays in DOM for SEO/AI crawlers

- **GEO FAQ System** — AI-Generated FAQ pipeline for Generative Engine Optimization
  - Generate 100 entity-rich FAQs per program using OpenRouter LLM API (10 categories × 10 questions)
  - **Round-Based Batch Generation** — 5 rounds of 20 FAQs each per program (2 categories per round) to stay under MAMP FastCGI 30s timeout
  - **Admin Batch Generator** — "Generate All FAQs" button processes all pending programs one round at a time from the admin panel with:
    - Real-time dark-theme log console showing per-round and per-program progress
    - Live ETA estimation and average time per program
    - Purple progress bar with live stats updates
    - Stop/resume capability (partially-completed programs resume from last round)
    - Automatic retry on network errors (up to 10 consecutive failures)
  - **Category Tab AJAX Loading** — Clicking a category tab fetches all FAQs for that category via REST API instead of filtering pre-loaded items
  - **Boxed FAQ Layout** — FAQ section wrapped in programPage-container (max-width: 1200px) to match page design
  - **Database Table Check** — GEO admin page detects if `program_faqs` table exists; shows warning banner with one-click "Create Table Now" button for production deployments
  - FAQ categories: Tuition, Admission, Academic, Campus Life, Career, Visa, Scholarship, Accommodation, Comparison, General
  - `program_faqs` database table with indexed `(post_id, faq_order)` and `(post_id, category)` columns
  - Node.js batch generator script (`scripts/generate-faqs.js`) with round-robin key rotation, concurrency control, and resume capability
  - GEO Admin Settings page under "Study in Turkiye" → "GEO (AI Visibility)" with status dashboard, progress bar, and manual FAQ generation/regeneration
  - `GET /sit-search/v1/faqs/{post_id}` REST endpoint for paginated, category-filtered FAQ lazy-loading
  - `GET /sit-search/v1/llms.txt` endpoint — machine-readable site summary for LLM discoverability (emerging standard)

- **GEO Enhancements** — 6 high-impact optimizations for AI search engine visibility:
  1. **`/llms.txt` Root URL** — Rewrite rule serves llms.txt at root URL (not just REST API), with enriched content: data coverage, key data points, API endpoints, structured data documentation
  2. **WebSite + Organization Schema** — Homepage now outputs `WebSite` and `Organization` JSON-LD with `knowsAbout`, `areaServed`, `SearchAction`, and brand entity signals for AI knowledge graph
  3. **Program TL;DR Summary** — Entity-rich, citable 2-sentence summary at top of every program page (program name, degree, university, location, language, tuition)
  4. **"Last Updated" Timestamp** — Visible `<time>` element with `datetime` attribute on every program page — AI engines weight freshness ~10%
  5. **"Program At a Glance" Facts Table** — Structured HTML `<table>` with all key metrics (tuition, duration, IELTS/TOEFL, QS ranking, students, founded year) — AI engines heavily favor tabular data for citation
  6. **Enhanced `llms.txt` Content** — Added key data points per program, structured data documentation, and API endpoint listing for AI crawler discoverability

- **Enhanced Course Schema (GEO)** — Added `speakable`, `audience`, and `about` properties to Course JSON-LD for voice assistants and AI categorization

- **Frontend FAQ Upgrade** — Program pages now feature:
  - Category tabs for filtering FAQs by topic
  - "Show More Questions" lazy-loading button (loads 10 FAQs per request via AJAX)
  - Category badges on each FAQ item
  - Semantic HTML (`<article>`, `<h4>` headings, `itemscope` microdata) for optimal AI extraction
  - Full FAQPage JSON-LD schema with all 100 Q&As injected in `<head>`

- **Expanded AI Crawler Access** — Added `robots.txt` rules for Amazonbot, Bytespider, GoogleOther, cohere-ai, Meta-ExternalAgent with polite `Crawl-delay: 1`

### Files Added

- `src/Actions/GeoSettingsAdmin.php` — Admin page controller with AJAX handlers
- `src/Endpoints/FaqEndpoint.php` — REST API for paginated FAQ delivery
- `templates/admin/geo-settings.html.php` — Admin template with status cards and checklist
- `scripts/generate-faqs.js` — Batch FAQ generator (Node.js + OpenRouter)
- `scripts/package.json` — Dependencies for generator script

### Files Modified

- `sit-search.php` — Added `program_faqs` table creation, enhanced robots.txt, added llms.txt endpoint
- `src/Services/Constants.php` — Added `get_openrouter_api_key()` method
- `src/Services/GeoSchema.php` — Added `get_ai_faq_data()`, `get_faq_count()`, `get_faq_categories()`; upgraded `get_faq_schema()` and `get_faq_data()` to prefer AI FAQs with template fallback; added speakable/audience/about schema
- `src/Shortcodes/Singleprogram.php` — Passes FAQ data, count, and categories to template
- `src/Actions/RegisterMenu.php` — Added GEO Settings submenu
- `src/Actions/AiSettingsAdmin.php` — Registered OpenRouter API key setting
- `src/App.php` — Initialized GeoSettingsAdmin, registered FaqEndpoint
- `templates/shortcodes/single-program.html.php` — Category tabs, load-more button, semantic markup
- `assets/css/sit-search.css` — FAQ tabs, badges, load-more button, spinner styles
- `docs/API.md` — Documented GEO endpoints and program_faqs table schema

---

## [1.3.8] - 2026-04-06

### Fixed

- **Exclude Images from Sync** — Removed `logo` and `profile_image` from the university field mapping so Supabase sync does not overwrite images managed in WordPress.
- **Top Universities Slider Not Updating** — Removing `Featured_Univesity` from a university in settings had no effect on the `[sit_top_universities]` slider because the 12-hour transient cache (`sit_top_universities_data`) was never invalidated.
  - Added `delete_transient('sit_top_universities_data')` to `CachedData::clear_university_cache()` and `CachedData::clear_all()`
  - Updated `App.php` save hooks (`save_post_sit-university`, `acf/save_post`) to call `CachedData::clear_university_cache()` instead of only clearing the object cache
  - Updated `SupabaseSyncEndpoint::clear_related_cache()` to use centralized `CachedData::clear_university_cache()`

### Files Modified

- `src/Services/CachedData.php` — Added top universities transient deletion to `clear_university_cache()` and `clear_all()`
- `src/App.php` — Save hooks now call `CachedData::clear_university_cache()`
- `src/Endpoints/SupabaseSyncEndpoint.php` — Uses `CachedData::clear_university_cache()` instead of manual `wp_cache_delete`

---

## [1.3.7] - 2026-04-06

### Fixed

- **Supabase Field Mapping Audit** — Queried all 9 Supabase tables to verify 100% column coverage. Found and fixed multiple gaps:
  - **University taxonomy missing** — `city` and `country` columns (without `_id` suffix) were silently skipped by `update_post_taxonomies()`. Universities were not getting city/country taxonomy terms assigned during sync.
  - **Program `university_sector` unmapped** — Added mapping for the `university_sector` Supabase column.
  - **Program `advanced_discount` unmapped** — Added mapping (column needs to be added to Supabase first).
  - **Campus `Faculty` array unmapped** — Campus records have a `Faculty` array of faculty IDs that was never linked to `sit-faculty` taxonomy terms.
  - **CamelCase meta key mismatch** — Supabase sync wrote lowercase keys (`qs_rank`, `year_founded`, `official_tuition`, etc.) but 30+ templates read old Zoho CamelCase keys (`QS_Rank`, `Year_Founded`, `Official_Tuition`, etc.). Added dual-write for all affected fields: `qs_rank`/`QS_Rank`, `year_founded`/`Year_Founded`, `acomodation`/`Accommodation`, `description`/`Description`, `official_tuition`/`Official_Tuition`, `discounted_tuition`/`Discounted_Tuition`, `tuition_currency`/`Tuition_Currency`, `study_years`/`Study_Years`, `advanced_discount`/`Advanced_Discount`.
  - **Missing term meta fields** — Added `country_code`, `active_on_nationalities`, `active_on_university`, `active_in_university`, `faculty_id` to taxonomy term meta storage.

### Identified (Requires Supabase Schema Change)

- **`featured_university`** — Column does NOT exist in Supabase `zoho_universities` table. Mapping is in place but needs `ALTER TABLE` to add the boolean column. SQL migration provided.
- **`number_of_students`** — Column does NOT exist in Supabase. Same treatment needed.
- **`advanced_discount`** — Column does NOT exist in Supabase `zoho_programs` table. SQL migration provided.

### Files Modified

- `src/Endpoints/SupabaseSyncEndpoint.php` — University taxonomy mapping, campus Faculty mapping, program university_sector + advanced_discount, dual-write CamelCase aliases, expanded term meta fields

---

## [1.3.6] - 2026-04-06

### Fixed

- **Column Name Mismatch Fix** — Supabase tables use inconsistent timestamp column names: `zoho_universities`, `zoho_degrees`, `zoho_faculty`, `zoho_speciality` use `update_at` (no 'd'), while `zoho_programs`, `zoho_campus`, `zoho_countries`, `zoho_cities`, `zoho_languages` use `updated_at`. The incremental sync filter now uses the correct column per table.
- **Field Mapping Audit** — Queried all 9 Supabase tables via API to verify actual column names. Fixed: programs use `active_applications` (not `active_in_apps`), campus uses `university` (not `university_id`). Removed non-existent columns (`active_in_search`, `students`, `number_of_students`, `website`).
- **Timezone Mismatch Fix** — `current_time('c')` returned WordPress timezone (e.g. `+03:00`) but Supabase stores UTC timestamps. Changed to `gmdate('c')` so incremental sync filter comparisons are consistent.
- **Reset Sync Button** — Added red "Reset Sync (Full Re-sync)" button that deletes all `sit_last_sync_*` options, allowing a fresh full sync.
- **Diagnostics** — Sync log now shows which filter and timestamp column is used per table, and displays stored last-sync timestamps in the table view.
- **Batch size = 3** — Each AJAX request processes 3 records with auto-retry (3 attempts) on failure.
- **`\Throwable` catch** — PHP fatal errors are now caught and returned as JSON instead of triggering silent 500 errors.

---

## [1.3.5] - 2026-04-06

### Changed

- **Zoho Sync Decoupling** — Completely isolated the plugin from processing incoming data from Zoho CRM.
  - Removed `Webhook` handler initialization in `App.php` that mapped Zoho webhooks for Universities, Programs, and Taxonomies to their respective CRM sync handlers.
  - Excluded Application and Consultation submissions from this migration; these form submissions natively retain their current direct data push to Zoho via `Zoho.php`.
- **Enforced Webhook Syncing** — The plugin now relies *strictly* on Supabase Database Webhooks hitting the `/wp-json/sit-search/v1/supabase-sync` endpoint for all data syncing (Universities, Programs, Taxonomies). Experimental WP-Cron background syncing has been removed in favor of instant webhook propagation per configuration preferences.

---

## [1.3.4] - 2026-04-06
### Fixed

- **Incremental Sync Broken** — Typo `update_at` → `updated_at` in ManualSyncAdmin caused all incremental syncs to return 0 records from Supabase
  - Also added `created_at` filter so newly created records are caught by incremental sync
- **Sync Pagination Missing** — Only first 500 records were synced per table; tables with >500 records silently dropped the rest
  - Added pagination loop to fetch all records in batches of 500
- **Boolean Type Mismatch** — `Active_in_Search` and `Active_in_Apps` stored as raw booleans from Supabase, but WordPress checks expected `'1'`/`''` strings
  - Added boolean normalization in `SupabaseSyncEndpoint::update_post_meta_from_record()`
  - Also normalized `post_status` determination to handle all boolean variants
- **Meta Key Mismatch** — Sync wrote `Active_in_Apps` but UniversityStatusAdmin UI read `Active_in_New_Apps`
  - Admin now checks both meta keys for backward compatibility
- **Missing Program Fields** — Programs were missing `active`, `active_in_search`, and `active_in_apps` field mappings in SupabaseSyncEndpoint
- **Missing University Fields** — Universities were missing `website`, `active_in_search`, `description`, and `students` field mappings
- **University-Program Link Fallback** — Added `zoho_account_id` fallback when looking up university posts if `supabase_id` doesn't match
- **Taxonomy Name Fallback** — Added name-based taxonomy assignment when ID-based lookup fails (uses `country_name`, `city_name`, etc.)
- **Debug Echo Statements** — Removed `echo 'in'` and `echo $this->base_url . $url` from Zoho.php that leaked to API responses
- **Stale Cache After Webhook Sync** — Added `CachedData::clear_all()` to Program and University webhook handlers

### Files Modified

- `src/Actions/ManualSyncAdmin.php` — Fixed typo, added pagination, added `created_at` filter
- `src/Endpoints/SupabaseSyncEndpoint.php` — Boolean normalization, expanded field mappings, name-based taxonomy fallback, zoho_account_id fallback
- `src/Services/Zoho.php` — Removed debug echo statements
- `src/Handlers/Program.php` — Added CachedData import and cache clearing
- `src/Handlers/University.php` — Added CachedData import and cache clearing
- `src/Actions/UniversityStatusAdmin.php` — Fixed Active_in_Apps/Active_in_New_Apps meta key check

---

## [1.3.3] - 2026-04-02

### Fixed

- **Results Count Badge Showing 0** — The "Programs Found" badge on the search results page always displayed 0 despite results being present
  - Root cause: Template used `$query->found_posts` directly, but the WP_Query was created with `no_found_rows => true` (performance optimization from v1.3.0) which skips `SQL_CALC_FOUND_ROWS`, leaving `found_posts` at 0
  - Fix: Template now uses the pre-calculated `$found_posts` variable passed from the controller, which correctly counts the mapped programs array
  - Also fixed the `$results_count` helper variable used elsewhere in the template

### Files Modified

- `templates/shortcodes/filter-sort.html.php` — Lines 50 and 75: replaced `$query->found_posts` with `$found_posts` (with fallback)

---

## [1.3.2] - 2026-04-02

### Fixed

- **PDF Export Empty on Results Page** — Fixed export showing 0 programs and empty table
  - Root cause: Variable name mismatch — `FilterSort.php` passed `pdf_programs` (plural) but template expected `$pdf_program` (singular)
  - Raw `WP_Post` objects were passed instead of mapped arrays — now uses fully mapped `$mapped_programs`
  - Added missing `$disstr` description text
- **PDF Export JS Error** — `exportToPDF()` was called but never defined; redirected to `downloadPDF()`

### Changed

- **PDF Engine Replaced** — Switched from `html2pdf.js` (html2canvas image-based) to `jsPDF` + `jsPDF-autoTable` (text-based vector PDF)
  - Eliminated blank space / content offset issues caused by html2canvas rendering
  - PDFs now have selectable/searchable text instead of rasterized images
  - Smaller file sizes and sharper rendering
- **Turkish Character Support** — Loads Roboto TTF fonts (Regular + Bold) from CDN at runtime
  - Fixes broken rendering of Turkish characters: ı, ş, ğ, ç, İ, Ş, Ğ, Ç, ö, ü
  - Falls back to Helvetica if font download fails
- **Discounted Tuition Display** — Custom cell rendering in PDF for discounted prices
  - Old price shown in small gray text with strikethrough line
  - New (discounted) price shown in bold green below
- **Logo Proportions** — Logo sized at 45mm wide with automatic aspect ratio calculation to match website appearance
- **PDF Redesign** — Professional PDF layout with branded header, red accent table headers, alternating row colors
  - Logo + title header on every page (full on page 1, compact on subsequent pages)
  - Red table header repeats on every page via `showHead: 'everyPage'`
  - Footer: "StudyinTurkiye.com — Powered by Sitconnect" + page numbers on every page
  - Contact info section with styled card at end of document
  - Loading spinner on download button during PDF generation

### Files Modified

- `src/Shortcodes/FilterSort.php` — Fixed `pdf_program` key name, added `disstr` in both cached/non-cached render paths
- `templates/shortcodes/filter-sort.html.php` — Replaced html2pdf CDN with jsPDF + autoTable + Roboto TTF font loading; complete PDF generator rewrite

---

## [1.3.1] - 2026-03-01

### Added

- **Search Loading Indicators** — Improved UX by adding visual feedback during search redirection
  - Added CSS-based spinners to Standard and AI search buttons on the homepage
  - Buttons now disable automatically on click/enter to prevent redundant requests
  - Integrated loading state with both `main.js` and standalone `ai-search.js`
  - Added `sit-ai-loading` visibility to AI search shortcode

### Fixed

- **Broken Search Redirection** — Fixed issue where homepage search filters were ignored and users were redirected to the home page instead of the results page
- **Consistent Loaders** — Added loading spinner support to search buttons on the results and archive pages

## [1.3.0] - 2026-03-01

### Performance

- **ProgramArchive Loop Optimization** - Fixed massive performance sink on the programs archive page where up to 500 items were mapped with `array_map` unconditionally via `$pdf_query` on every load. The PDF mapping loop is now correctly shielded by an `isset($_GET['download'])` check so it doesn't process unnecessarily.
- **N+1 Cache Priming** - Updated `ProgramArchive.php` to use `_prime_post_caches()` instead of standard meta cache for fetching university objects inside the loop, preventing hundreds of redundant `get_post()` triggers.
- **Systematic Query Deduplication** - Neutralized massive repeating database hits fetching individual university statistics inside `UniversityGrid`, `FilterSort`, and single-program sidebars loops. Replaced loops with bulk-prime caching or explicit object calls.
- **AJAX Rest Endpoint Optimization** - Fixed repeating `get_post_meta()` loops without prewarmed caches inside `SearchEndpoint.php` and `SearchProgramAjax.php`. Eliminated massive taxonomy query iterations generating index maps inside `AiSearchIndexEndpoint.php`.
- **`SQL_CALC_FOUND_ROWS` Elimination** - Added `'no_found_rows' => true` to all non-paginated `WP_Query` instances across 10 files (`FilterSort`, `ProgramArchive`, `ProgramCity`, `CampusFaculties`, `SingleUniversity`, `Singleprogram`, `UniversityPrograms`, `FeaturedUniversity`, `SearchEndpoint`, `AiSearchIndexEndpoint`). Every `-1` posts_per_page query was silently paying for a MySQL full-table count it never used.
- **`fields => ids` Memory Shift** - Swapped full `WP_Post` object loads to `'fields' => 'ids'` in all AJAX and filter extraction queries that only needed post IDs (`FilterSort` AJAX methods `get_uni_ids`, `get_uni__search_ids`; `SearchProgramAjax` traditional search). Cuts PHP memory usage by up to 80% per query.

### Added

- **Search Queries Admin Panel** — Track what people search for in the AI search
  - New admin page under "Study in Turkiye" → "Search Queries"
  - Stats dashboard: total searches, today's count, unique queries, weekly count
  - Top 10 most-searched queries display
  - Paginated, sortable, filterable search log table
  - Date range filtering and keyword search within queries
  - CSV export of search query data
  - "Clear old queries" button (deletes entries older than 30 days)
  - `src/Services/SearchQueryLogger.php` — Query logging service with hashed IPs for privacy
  - `src/Actions/SearchQueriesAjax.php` — AJAX handlers for admin panel
  - `templates/admin/search-queries.html.php` — Admin template with stats + table
  - Custom `sit_search_queries` database table created on activation/upgrade

### Changed

- `src/Services/AiSearchHelper.php` — Logs every AI search query (simple, cached, and AI-expanded)
- `src/Actions/RegisterMenu.php` — Added "Search Queries" submenu page
- `src/App.php` — Initialized `SearchQueriesAjax` handler
- `sit-search.php` — Added activation hook and `admin_init` version check for table creation

---

## [1.2.1] - 2026-03-01

### Fixed

- **Undefined variable `$university`** - Moved `$university` initialization before the `if (!empty($search))` block so it is always defined (line 281)
- **Undefined constant `SIT_SEARCH_DIR`** - Changed to `STI_SEARCH_DIR` to match the actual constant defined in `sit-search.php` (line 489)
- **Template not found** - Fixed `Template::render()` call using route `'filter-sort'` instead of `'shortcodes/filter-sort'`, causing the cached-result branch to fail loading the template
- **"Attempt to read property ID on string"** - Replaced `update_post_caches()` (expects WP_Post objects) with `_prime_post_caches()` (accepts IDs) for university cache priming; cast meta values to `int`
- **Missing template variables in cached branch** - Aligned cached-result render call with non-cached branch: added `$degreeid`, `$countryid`, `$specialityid`, `$query`, `$programs`, `$found_posts`, `$degree`, `$country`, `$speciality`, and all filter variables; removed broken double-include pattern
- **CSS not loading on single program/university pages** - Added `is_singular(['sit-program', 'sit-university', 'sit-campus'])` check to `maybe_enqueue_assets()` in `App.php`; these pages use Elementor templates so shortcode-in-content detection doesn't match them

---

## [1.2.0] - 2026-03-01

### Added

- **GEO (Generative Engine Optimization)** — Optimized for AI search engine visibility (Perplexity, ChatGPT, Claude, Gemini)
  - `src/Services/GeoSchema.php` — New JSON-LD structured data generator
  - **Course schema** for `sit-program` pages (name, provider, tuition, duration, language)
  - **EducationalOrganization schema** for `sit-university` pages (name, QS ranking, founding date, students)
  - **FAQPage schema** — auto-generated FAQ from program data (tuition, duration, language, requirements)
  - **BreadcrumbList schema** — structured breadcrumb trail for programs and universities
  - **Visible FAQ section** on program pages with expandable Q&A cards
  - **Open Graph tags** (`og:title`, `og:description`, `og:type`, `og:url`, `og:image`)
  - **Timestamp meta tags** (`date`, `last-modified`) for freshness signals
  - **AI crawler access** — `robots.txt` rules for GPTBot, ChatGPT-User, PerplexityBot, Claude-Web
  - **Enhanced meta descriptions** — entity-rich with city, country, degree, tuition, language
  - **University meta support** — `sit-university` CPT now has full meta + OG tags

### Removed

- Hidden keyword-stuffing div (`programPage-seo-keywords`) — AI engines penalize cloaked content

### Changed

- `sit-search.php` — Replaced `custom_add_meta_description_for_cpt()` with GEO-optimized handlers
- `templates/shortcodes/single-program.html.php` — Added FAQ section + sidebar nav link
- `assets/css/sit-search.css` — Added FAQ accordion styles
- Rank Math title filter now covers `sit-university` and uses em dash separator

---

## [1.1.7] - 2026-03-01

### Performance

- **Conditional Asset Loading** - CSS/JS bundles (OwlCarousel, Select2, AI Search JS) no longer load globally on every page
  - Assets are now registered via `wp_register_*` and only enqueued on pages containing SIT Search shortcodes
  - Non-search pages (blog posts, homepage, etc.) no longer carry the plugin's network payload
  - Added `maybe_enqueue_assets()` with shortcode detection, taxonomy archive checks, and URL fallbacks
- **UniversityGrid SQL Caching** - Cached `get_sectors_from_db()` (24h transient) and `get_cities_from_db()` (12h country-keyed transient) to eliminate repeated raw `$wpdb` queries on every page load

### Security

- **Input Sanitization** - Wrapped all raw `$_GET` reads in `sanitize_text_field()` across `FilterSort.php` (3 methods, 11 fixes) and `ProgramArchive.php` (6 fixes)
  - `$sort`, `$type`, `$feeFilter`, `$duration`, `$isScholarShip`, `$language`, `$city`, `$university`, `$degreeType`, `$modeOfStudy`

### Changed

- `src/App.php` - Refactored `setup_assets()` with register/enqueue split and new `maybe_enqueue_assets()` method
- `src/Shortcodes/UniversityGrid.php` - Added transient caching to `get_sectors_from_db()` and `get_cities_from_db()`
- `src/Shortcodes/FilterSort.php` - Sanitized all `$_GET` reads in `__invoke()`, `get_uni_ids()`, `get_uni__search_ids()`
- `src/Shortcodes/ProgramArchive.php` - Sanitized all `$_GET` reads in `__invoke()`

---

## [1.1.6] - 2026-01-20

### Added

- **Search API Endpoint** - New REST API for Next.js headless frontend
  - `GET /wp-json/sit-search/v1/search` - Full search with filters, sorting, pagination
  - `GET /wp-json/sit-search/v1/program/{slug}` - Single program by slug
  - `GET /wp-json/sit-search/v1/university/{slug}` - Single university with programs
  - `GET /wp-json/sit-search/v1/featured-universities` - Featured universities list
  - `GET /wp-json/sit-search/v1/filter-options` - All filter dropdowns in one call
- **API Documentation** - Created `docs/API.md` documenting all endpoints

### Changed

- `src/App.php` - Registered new SearchEndpoint
- `src/Endpoints/SearchEndpoint.php` - New comprehensive endpoint file

---

## [1.1.5] - 2026-01-20

### Added

- **City Filter** - New filter option to search programs by city
  - Filter dynamically shows only cities that have programs in current results
  - Supports multiple city selection via checkboxes
  - Integrates with existing filter system (URL parameters, applied filters display)
  - Works with "Clear All" and individual filter removal

### Fixed

- **Price Sorting** - Sorting by price (Low to High / High to Low) now uses effective price
  - Uses discounted_fee when available, otherwise Advanced_Discount, otherwise official fee
  - Programs with discounts are now sorted correctly by their actual price
- **Sort Dropdown** - Fixed sort dropdown not responding in new results header
  - Updated selector to target `#sort-dropdown` and `.results-sort-select`
- **Currency Display** - List view now uses actual Tuition_Currency instead of hardcoded USD

### Changed

- `src/Shortcodes/FilterSort.php` - Added city filter handling, fixed price sorting logic
- `templates/shortcodes/filter-sort.html.php` - Added City filter UI, fixed currency display
- `assets/js/main.js` - Added city checkbox handlers, fixed sort dropdown selector
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

| Supabase Table    | WordPress Entity          |
| ----------------- | ------------------------- |
| zoho_universities | sit-university (post)     |
| zoho_programs     | sit-program (post)        |
| zoho_campus       | sit-campus (post)         |
| zoho_countries    | sit-country (taxonomy)    |
| zoho_cities       | sit-city (taxonomy)       |
| zoho_degrees      | sit-degree (taxonomy)     |
| zoho_faculty      | sit-faculty (taxonomy)    |
| zoho_speciality   | sit-speciality (taxonomy) |
| zoho_languages    | sit-language (taxonomy)   |

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
