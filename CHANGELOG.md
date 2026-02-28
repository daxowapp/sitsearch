[2026-02-20] - AI Search Module - Implemented `[sit_ai_search]` shortcode powered by OpenAI (gpt-4o-mini), including admin settings page, index caching, frontend sorting, and debounced REST endpoints.
[2026-02-20] - All Search Fields Optional - Removed Javascript validation blocking the search bar form if some fields are not provided. Users can now search with any combination of criteria.
[2026-02-04] - Fix Country Filtering - Refactored program search to filter by University's country instead of Program's country tag, resolving incorrect results for North Cyprus.
[2026-02-05] - Country Filter & Search Bar Fixes - Fixed `FilterSort.php` to correctly filter programs by university country (resolving North Cyprus issue) and corrected `selected` attribute logic in `search-bar.html.php`.
