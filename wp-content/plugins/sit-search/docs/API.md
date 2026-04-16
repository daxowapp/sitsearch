# SIT Search API Documentation

> **Security & Performance Notice (v1.2.1):** All REST endpoints strictly sanitize input parameters. The `search` endpoint utilizes 15-minute WP Transient caching to bypass complex DB queries, and N+1 loads are eliminated via `_prime_post_caches`. The AI endpoints incorporate sliding-window rate limiting (15 req/min per IP) to prevent OpenAI credit exhaustion.

> **GEO (v1.2.0):** All program and university pages now output JSON-LD structured data (Course, EducationalOrganization, FAQPage, BreadcrumbList) in `<head>` for AI search engine visibility. See `src/Services/GeoSchema.php`. *Note: Embedding search defaults to cosine similarity over text-embedding-ada-002 vectors.*

> **Project Architecture & Rules (v1.4.1):** AI agents and developers should refer to `AGENTS.md` in the project root for comprehensive architectural details, the Supabase data strategy, and coding guidelines.

Base URL: `/wp-json/sit-search/v1/`

## Existing Endpoints

### Programs & Universities

| Endpoint      | Method | Description                    | Params                          |
| ------------- | ------ | ------------------------------ | ------------------------------- |
| `/programs`   | GET    | Programs by university         | `zoho_university_id` (required) |
| `/program`    | GET    | All programs (Turkey/N.Cyprus) | None                            |
| `/university` | GET    | All universities               | None                            |

### Taxonomies

| Endpoint      | Method | Description       |
| ------------- | ------ | ----------------- |
| `/degree`     | GET    | All degree levels |
| `/country`    | GET    | All countries     |
| `/city`       | GET    | All cities        |
| `/language`   | GET    | All languages     |
| `/faculty`    | GET    | All faculties     |
| `/speciality` | GET    | All specialities  |
| `/campus`     | GET    | Campus data       |

---

## Missing Endpoints (Need to Create)

### 1. Search/Filter Endpoint

**Route:** `/search`
**Purpose:** Main search with all filters (what FilterSort.php does)
**Params:**

- `speciality` - Speciality ID (Optional)
- `country` - Country ID
- `level` or `level[]` - Degree ID(s)
- `language` or `language[]` - Language ID(s)
- `city` or `city[]` - City ID(s)
- `university` or `university[]` - University names
- `feeFilter` - Price range (min-max)
- `duration` - Study years
- `isScholarShip` - Yes/No
- `search` - Keyword search
- `sort` - fee_low, fee_high, popular, newest
- `page` - Pagination

### 2. Single Program by Slug

**Route:** `/program/{slug}`
**Purpose:** Get single program data for SSG/ISR
**Returns:** Full program details + university info

### 3. Single University by Slug

**Route:** `/university/{slug}`
**Purpose:** Get single university + its programs
**Returns:** University data + programs list

### 4. Featured Universities

**Route:** `/featured-universities`
**Purpose:** Get featured/recommended universities
**Returns:** Featured universities list with priority

### 5. Filter Options

**Route:** `/filter-options`
**Purpose:** Get all filter dropdowns in one call
**Returns:** Combined degrees, countries, specialities, languages

---

## Implementation Priority

1. **Search Endpoint** - Critical for results page
2. **Single Program** - For program detail pages
3. **Single University** - For university pages
4. **Featured Universities** - For homepage/recommendations
5. **Filter Options** - For sidebar filters (optimization)

---

## Admin AJAX Endpoints (Search Queries)

These endpoints are available only to admins via `wp-admin/admin-ajax.php`. All require `nonce` (from `wp_create_nonce('sit_search_queries_nonce')`) and `manage_options` capability.

| Action                   | Method | Description                        | Extra Params                                                             |
| ------------------------ | ------ | ---------------------------------- | ------------------------------------------------------------------------ |
| `sit_get_search_queries` | POST   | Get paginated search query log     | `page`, `per_page`, `search`, `date_from`, `date_to`, `orderby`, `order` |
| `sit_get_search_stats`   | POST   | Get dashboard stats (total, today) | None                                                                     |
| `sit_clear_old_queries`  | POST   | Delete queries older than N days   | `days` (default: 30)                                                     |
| `sit_export_queries_csv` | GET    | Download CSV of search queries     | `date_from`, `date_to`                                                   |

### Database Table: `{prefix}_sit_search_queries`

| Column              | Type         | Description                     |
| ------------------- | ------------ | ------------------------------- |
| `id`                | bigint(20)   | Auto-increment primary key      |
| `query`             | varchar(500) | Raw search text from user       |
| `expanded_terms`    | text         | JSON array of AI-expanded terms |
| `filters_extracted` | text         | JSON object of detected filters |
| `results_count`     | int(11)      | Number of results found         |
| `ip_hash`           | varchar(64)  | SHA-256 hashed IP for privacy   |
| `user_agent`        | varchar(500) | Browser user agent string       |
| `source`            | varchar(50)  | `server` or `rest_api`          |
| `created_at`        | datetime     | Timestamp of the search         |

---

## GEO Endpoints (Generative Engine Optimization)

### FAQ Lazy Loading

| Endpoint | Method | Description | Params |
| --- | --- | --- | --- |
| `/faqs/{post_id}` | GET | Get paginated FAQs for a program | `page` (default: 1), `per_page` (default: 10), `category` (filter by category) |

**Response Example:**
```json
{
  "faqs": [
    {"question": "...", "answer": "...", "category": "tuition"}
  ],
  "total": 100,
  "page": 1,
  "per_page": 10,
  "total_pages": 10,
  "categories": ["tuition", "admission", "academic", ...],
  "has_more": true
}
```

### LLMs.txt

| Endpoint | Method | Description |
| --- | --- | --- |
| `/llms.txt` | GET | Machine-readable site summary for AI engines (text/plain) |

---

## GEO Admin AJAX Endpoints

These endpoints require `nonce` (from `wp_create_nonce('sit_geo_nonce')`) and `manage_options` capability.

| Action | Method | Description | Extra Params |
| --- | --- | --- | --- |
| `sit_geo_generate_faqs` | POST | Generate 100 FAQs for a program | `post_id` |
| `sit_geo_get_status` | POST | Get FAQ generation statistics | None |
| `sit_geo_regenerate_program` | POST | Delete & regenerate FAQs | `post_id` |

---

## Database Table: `{prefix}_program_faqs`

> Added in v1.3.0 for GEO FAQ storage.

| Column | Type | Description |
| --- | --- | --- |
| `id` | bigint unsigned | Auto-increment primary key |
| `post_id` | bigint unsigned | WordPress program post ID |
| `faq_order` | int unsigned | Position 1-100 |
| `question` | text | FAQ question |
| `answer` | text | FAQ answer (2-4 sentences, entity-rich) |
| `category` | varchar(50) | Category: tuition, admission, academic, campus_life, career, visa, scholarship, accommodation, comparison, general |
| `generated_at` | datetime | When AI generated this FAQ |
| `model` | varchar(100) | LLM model used for generation |

**Indexes:** `(post_id, faq_order)`, `(post_id, category)`

---

## Admin Pages & Interfaces

The plugin provides several WordPress Administrator dashboards accessible via **Study in Turkiye**:

- **Dashboard:** Core plugin metrics and links.
- **University Media:** Manage custom logo and banner overrides (does not sync from Supabase). Edits update all translated language duplicates simultaneously.
- **University Status:** View sync statuses, program counts, active flags, and deduplicate entities.
- **Mapping:** Map WordPress taxonomies to Zoho/Supabase dependencies.
- **AI Search Settings:** Manage OpenAI credentials and index controls.
- **Search Queries:** Track and analyze recent user searches.
- **GEO Settings:** Generative Engine Optimization parameters.
