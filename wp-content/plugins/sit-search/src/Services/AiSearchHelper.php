<?php

namespace SIT\Search\Services;

class AiSearchHelper
{
    /**
     * Common stop-words to remove when splitting a raw query into search terms.
     */
    private static $stop_words = [
        'in', 'at', 'for', 'the', 'a', 'an', 'of', 'on', 'to', 'and', 'or',
        'with', 'from', 'by', 'is', 'are', 'about', 'near', 'around',
        'i', 'want', 'looking', 'find', 'search', 'study', 'need',
        'في', 'من', 'على', 'عن', 'إلى', 'و', 'أو', 'أريد', 'أبحث', 'دراسة',
    ];

    /**
     * Local keyword → filter map for offline filter extraction when AI is unavailable.
     */
    private static $keyword_filters = [
        // Cities
        'istanbul'   => ['city' => 'Istanbul'],
        'ankara'     => ['city' => 'Ankara'],
        'izmir'      => ['city' => 'Izmir'],
        'antalya'    => ['city' => 'Antalya'],
        'bursa'      => ['city' => 'Bursa'],
        'trabzon'    => ['city' => 'Trabzon'],
        'sakarya'    => ['city' => 'Sakarya'],
        'eskisehir'  => ['city' => 'Eskişehir'],
        'konya'      => ['city' => 'Konya'],
        'kayseri'    => ['city' => 'Kayseri'],
        'gaziantep'  => ['city' => 'Gaziantep'],
        'lefkosa'    => ['city' => 'Lefkoşa'],
        'nicosia'    => ['city' => 'Lefkoşa'],
        'famagusta'  => ['city' => 'Famagusta'],
        'kyrenia'    => ['city' => 'Kyrenia'],
        'girne'      => ['city' => 'Kyrenia'],
        'gazimagusa' => ['city' => 'Famagusta'],
        // Arabic city names
        'اسطنبول'    => ['city' => 'Istanbul'],
        'أنقرة'      => ['city' => 'Ankara'],
        'إزمير'      => ['city' => 'Izmir'],
        'أنطاليا'    => ['city' => 'Antalya'],
        // Countries
        'turkey'     => ['country' => 'Turkey'],
        'cyprus'     => ['country' => 'Northern Cyprus'],
        'north cyprus' => ['country' => 'Northern Cyprus'],
        'northern cyprus' => ['country' => 'Northern Cyprus'],
        'تركيا'      => ['country' => 'Turkey'],
        'قبرص'       => ['country' => 'Northern Cyprus'],
        // Degree levels
        'bachelor'   => ['degree' => 'Bachelor'],
        'bachelors'  => ['degree' => 'Bachelor'],
        'master'     => ['degree' => 'Master'],
        'masters'    => ['degree' => 'Master'],
        'phd'        => ['degree' => 'PhD'],
        'doctorate'  => ['degree' => 'PhD'],
        'associate'  => ['degree' => 'Associate'],
        'diploma'    => ['degree' => 'Associate'],
        'بكالوريوس'  => ['degree' => 'Bachelor'],
        'ماجستير'    => ['degree' => 'Master'],
        'ماستر'      => ['degree' => 'Master'],
        'دكتوراه'    => ['degree' => 'PhD'],
        'دبلوم'      => ['degree' => 'Associate'],
        // Languages
        'english'    => ['language' => 'English'],
        'turkish'    => ['language' => 'Turkish'],
        'arabic'     => ['language' => 'Arabic'],
        'بالانجليزي' => ['language' => 'English'],
        'بالتركي'    => ['language' => 'Turkish'],
        'بالعربي'    => ['language' => 'Arabic'],
    ];

    /**
     * Expand a search query using OpenAI.
     * Returns an array with 'terms' and 'filters'.
     * Uses WordPress transient cache (24h TTL for success, 5min for failures).
     * Falls back to smart local parsing on any error.
     *
     * @param string $query  Raw search input (any language)
     * @return array          ['terms' => [...], 'filters' => [...]]
     */
    public static function expand_search(string $query): array
    {
        $query = trim($query);
        if (empty($query)) {
            return ['terms' => [], 'filters' => []];
        }

        // If the query is already comma-separated (from a previous AI expansion), split and return
        if (strpos($query, ',') !== false) {
            return [
                'terms' => array_filter(array_map('trim', explode(',', $query))),
                'filters' => []
            ];
        }

        // Check transient cache first
        $cache_key = 'ai_search_v5_' . md5(mb_strtolower($query));
        $cached = get_transient($cache_key);
        if ($cached !== false && is_array($cached)) {
            SearchQueryLogger::log($query, $cached['terms'] ?? [], $cached['filters'] ?? [], 0, 'server');
            return $cached;
        }

        // Get the OpenAI API key
        $api_key = get_option('sit_openai_api_key');
        if (empty($api_key)) {
            // No API key — use smart local fallback
            $result = self::smart_local_fallback($query);
            set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);
            SearchQueryLogger::log($query, $result['terms'], $result['filters'] ?? [], 0, 'server');
            return $result;
        }

        // Call OpenAI
        $result = self::call_openai($query, $api_key);

        // Check if it was a real AI response or a fallback
        $is_real_ai_response = !empty($result['_ai_success']);
        unset($result['_ai_success']); // Don't pass this internal flag downstream

        // Always include original query in terms if not empty
        if (!in_array(strtolower($query), array_map('strtolower', $result['terms'])) && !empty($query)) {
            $result['terms'][] = strtolower($query);
        }

        if ($is_real_ai_response) {
            // Cache successful AI responses for 24 hours
            set_transient($cache_key, $result, DAY_IN_SECONDS);
        } else {
            // Cache fallback results for only 5 minutes so we retry AI soon
            set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);
        }

        // Log the search query
        SearchQueryLogger::log($query, $result['terms'], $result['filters'] ?? [], 0, 'server');

        return $result;
    }

    /**
     * Smart local fallback when AI is unavailable.
     * Splits the query into meaningful terms and extracts filters from known keywords.
     *
     * @param string $query  Raw user query
     * @return array          ['terms' => [...], 'filters' => [...]]
     */
    private static function smart_local_fallback(string $query): array
    {
        $lower = mb_strtolower($query);
        $filters = [];

        // 1. Extract filters from known keywords  
        foreach (self::$keyword_filters as $keyword => $filter_data) {
            if (mb_strpos($lower, mb_strtolower($keyword)) !== false) {
                $filters = array_merge($filters, $filter_data);
            }
        }

        // 2. Remove filter keywords and stop-words from the query to get the academic terms
        $academic_query = $lower;
        
        // Remove detected filter keywords
        foreach (self::$keyword_filters as $keyword => $filter_data) {
            $academic_query = str_ireplace($keyword, ' ', $academic_query);
        }
        
        // Remove stop-words
        foreach (self::$stop_words as $sw) {
            // Use word boundary matching for Latin words  
            if (preg_match('/^[a-zA-Z]+$/', $sw)) {
                $academic_query = preg_replace('/\b' . preg_quote($sw, '/') . '\b/iu', ' ', $academic_query);
            } else {
                $academic_query = str_replace($sw, ' ', $academic_query);
            }
        }

        // Clean up whitespace
        $academic_query = trim(preg_replace('/\s+/', ' ', $academic_query));

        // 3. Build terms array
        $terms = [];
        if (!empty($academic_query)) {
            // Add the cleaned multi-word phrase
            $terms[] = $academic_query;
            
            // Also add individual words if multi-word
            $words = explode(' ', $academic_query);
            if (count($words) > 1) {
                foreach ($words as $word) {
                    $word = trim($word);
                    if (strlen($word) >= 3 && !in_array($word, $terms)) {
                        $terms[] = $word;
                    }
                }
            }
        }

        // Always include original query as a fallback term
        if (!in_array($lower, $terms)) {
            $terms[] = $lower;
        }

        return ['terms' => $terms, 'filters' => $filters];
    }

    /**
     * Call OpenAI API to translate and expand a search query.
     *
     * @param string $query    The user's search query
     * @param string $api_key  OpenAI API key
     * @return array           Array of ['terms' => [], 'filters' => [], '_ai_success' => bool]
     */
    private static function call_openai(string $query, string $api_key): array
    {
        $system_prompt = <<<PROMPT
You are an AI search assistant for a university program database in Turkey and Northern Cyprus.
The database contains program names in English like "Computer Science", "Mechanical Engineering", "Medicine", "Business Administration", etc.

Your job is to analyze the user's free-text query (which may be in Arabic, Turkish, or English) and extract TWO things:
1. The core academic subject(s) being searched.
2. Any implicit filters the user mentioned (Degree Level, Language, City, Country).

Instructions:
- `terms`: Translate the academic subject into English. Fix typos. Return 2-4 highly precise academic strings that might match the database. BE EXACT. If they look for "طب" return ["medicine", "human medicine"] and DO NOT return broad adjectives like "medical". If they ask for "هندسة" return exact strings like ["engineering"].
- `filters.degree`: If they mention "Master", "ماجستير", "ماستر", return "Master". If "Bachelor", "بكالوريوس", return "Bachelor". If "PhD", "دكتوراه", return "PhD". If "دبلوم", "معهد", return "Associate". Otherwise null.
- `filters.language`: If they mention language of instruction like "بالتركي" or "English", return "Turkish", "English", or "Arabic". Otherwise null.
- `filters.city`: If they mention a specific city like "اسطنبول" (Istanbul) or "Izmir", return the English standardized city name. Handle synonyms wisely. Otherwise null.
- `filters.country`: If they mention "تركيا" or "Turkey", return "Turkey". "قبرص", return "Northern Cyprus". Otherwise null.
- `filters.university`: If they mention a specific university name like "جامعه بهشه شاهير" or "Medipol", return the standardized English name as an array, e.g., ["Bahcesehir University"]. Otherwise null.

Return ONLY a JSON object:
{
  "terms": ["translated_query", "related1", "related2"],
  "filters": {
    "degree": "Master" | "Bachelor" | "PhD" | null,
    "language": "English" | "Turkish" | "Arabic" | null,
    "city": "Istanbul" | "Ankara" | "Izmir" | null,
    "country": "Turkey" | "Northern Cyprus" | null,
    "university": ["Bahcesehir University", "Medipol University"] | null
  }
}
PROMPT;

        $body = [
            'model'           => 'gpt-4o-mini',
            'messages'        => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $query]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.1,
            'max_tokens'      => 200,
        ];

        error_log('AiSearchHelper: Starting OpenAI request for query: ' . $query);
        $start_time = microtime(true);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Force IPv4 because MAMP often hangs indefinitely trying to resolve IPv6 for APIs
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        // Ignore SSL errors on local MAMP
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // Increased timeout from 3s to 8s to give OpenAI enough time
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        
        $response_body = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $elapsed = round(microtime(true) - $start_time, 2);
        error_log('AiSearchHelper: Request completed in ' . $elapsed . 's (HTTP ' . $code . ')');

        // On cURL error or bad HTTP status → smart local fallback
        if ($err) {
            error_log('AiSearchHelper Error: cURL Error - ' . $err);
            $fallback = self::smart_local_fallback($query);
            $fallback['_ai_success'] = false;
            return $fallback;
        }

        if ($code !== 200) {
            error_log('AiSearchHelper API Error (' . $code . '): ' . $response_body);
            $fallback = self::smart_local_fallback($query);
            $fallback['_ai_success'] = false;
            return $fallback;
        }

        $data = json_decode($response_body, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $content = json_decode($data['choices'][0]['message']['content'], true);
            
            $terms = [];
            if (isset($content['terms']) && is_array($content['terms'])) {
                $terms = array_map('strtolower', $content['terms']);
            }
            
            $filters = [];
            if (isset($content['filters']) && is_array($content['filters'])) {
                // Remove null values completely
                $filters = array_filter($content['filters'], function($val) {
                    return $val !== null && $val !== '';
                });
            }
            
            if (!empty($terms)) {
                return ['terms' => $terms, 'filters' => $filters, '_ai_success' => true];
            }
        }

        // JSON parse failure — smart local fallback
        error_log('AiSearchHelper: Failed to parse OpenAI response, using local fallback');
        $fallback = self::smart_local_fallback($query);
        $fallback['_ai_success'] = false;
        return $fallback;
    }
}
