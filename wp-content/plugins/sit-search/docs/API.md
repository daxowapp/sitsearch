# SIT Search API Documentation

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
