<?php

namespace SIT\Search\Services;

/**
 * GeoSchema - Generative Engine Optimization via JSON-LD Structured Data
 *
 * Generates schema.org JSON-LD markup for programs and universities
 * to maximize visibility in AI-powered search engines (Perplexity, ChatGPT, Claude, Gemini).
 *
 * @since 1.2.0
 */
class GeoSchema
{
    /**
     * Generate Course schema for a program post
     *
     * @param int $post_id The program post ID
     * @return array Schema.org Course object
     */
    public static function get_course_schema($post_id)
    {
        $uni_id = get_post_meta($post_id, 'zh_university', true);
        $university = !empty($uni_id) ? get_post($uni_id) : null;
        $program_title = get_the_title($post_id);

        $country_terms = get_the_terms($post_id, 'sit-country');
        $city_terms = $university ? get_the_terms($university->ID, 'sit-city') : false;
        $degree_terms = get_the_terms($post_id, 'sit-degree');
        $language_terms = get_the_terms($post_id, 'sit-language');

        $country = (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '';
        $city = (!empty($city_terms) && !is_wp_error($city_terms)) ? $city_terms[0]->name : '';
        $degree = (!empty($degree_terms) && !is_wp_error($degree_terms)) ? $degree_terms[0]->name : '';
        $language = (!empty($language_terms) && !is_wp_error($language_terms)) ? $language_terms[0]->name : '';

        $fee = get_post_meta($post_id, 'Official_Tuition', true);
        $discounted_fee = get_post_meta($post_id, 'Discounted_Tuition', true);
        $currency = get_post_meta($post_id, 'Tuition_Currency', true) ?: 'USD';
        $duration = get_post_meta($post_id, 'Study_Years', true);
        $description = get_post_meta($post_id, 'Description', true);

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $program_title,
            'description' => wp_strip_all_tags($description),
            'url' => get_permalink($post_id),
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'inLanguage' => $language,
            'provider' => $university ? self::get_organization_schema($university->ID, true) : [],
        ];

        // Add degree/credential
        if ($degree) {
            $schema['educationalCredentialAwarded'] = $degree;
        }

        // Add duration in ISO 8601 format
        if ($duration) {
            $years = intval($duration);
            $schema['timeRequired'] = 'P' . $years . 'Y';
            $schema['hasCourseInstance'] = [
                '@type' => 'CourseInstance',
                'courseMode' => 'full-time',
                'courseWorkload' => 'P' . $years . 'Y',
            ];
        }

        // Add tuition as offer
        if ($fee) {
            $effective_fee = !empty($discounted_fee) ? $discounted_fee : $fee;
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => preg_replace('/[^0-9.]/', '', $effective_fee),
                'priceCurrency' => self::normalize_currency($currency),
                'category' => 'Annual Tuition Fee',
                'availability' => 'https://schema.org/InStock',
            ];
        }

        // Add location
        if ($city || $country) {
            $schema['locationCreated'] = [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $city,
                    'addressCountry' => $country,
                ],
            ];
        }

        // GEO: Add speakable property for voice assistants
        $schema['speakable'] = [
            '@type' => 'SpeakableSpecification',
            'cssSelector' => ['.programPage-overview-content', '.programPage-faq-answer'],
        ];

        // GEO: Add audience for better AI categorization
        $schema['audience'] = [
            '@type' => 'EducationalAudience',
            'educationalRole' => 'student',
            'audienceType' => 'International Students',
        ];

        // GEO: Add about for entity linking
        $faculty_terms = get_the_terms($post_id, 'sit-faculty');
        $faculty = (!empty($faculty_terms) && !is_wp_error($faculty_terms)) ? $faculty_terms[0]->name : '';
        if ($faculty) {
            $schema['about'] = [
                '@type' => 'Thing',
                'name' => $faculty,
            ];
        }

        return $schema;
    }

    /**
     * Generate EducationalOrganization schema for a university post
     *
     * @param int $post_id The university post ID
     * @param bool $embedded Whether this is embedded in another schema (skip @context)
     * @return array Schema.org EducationalOrganization object
     */
    public static function get_organization_schema($post_id, $embedded = false)
    {
        $university = get_post($post_id);
        if (!$university) {
            return [];
        }

        $country_terms = get_the_terms($post_id, 'sit-country');
        $city_terms = get_the_terms($post_id, 'sit-city');

        $country = (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '';
        $city = (!empty($city_terms) && !is_wp_error($city_terms)) ? $city_terms[0]->name : '';

        $description = get_post_meta($post_id, 'Description', true);
        $ranking = get_post_meta($post_id, 'QS_Rank', true);
        $year_founded = get_post_meta($post_id, 'Year_Founded', true);
        $students = get_post_meta($post_id, 'Number_Of_Students', true);
        $image = get_post_meta($post_id, 'uni_image', true);
        $logo = get_post_meta($post_id, 'uni_logo', true);

        $schema = [
            '@type' => 'EducationalOrganization',
            'name' => $university->post_title,
            'description' => wp_strip_all_tags($description),
            'url' => get_permalink($post_id),
        ];

        if (!$embedded) {
            $schema['@context'] = 'https://schema.org';
            $schema['dateModified'] = get_the_modified_date('c', $post_id);
        }

        if ($image) {
            $schema['image'] = esc_url($image);
        }

        if ($logo) {
            $schema['logo'] = esc_url($logo);
        }

        if ($year_founded) {
            $year = date('Y', strtotime($year_founded));
            $schema['foundingDate'] = $year;
        }

        if ($students) {
            $schema['numberOfEmployees'] = [
                '@type' => 'QuantitativeValue',
                'value' => intval(preg_replace('/[^0-9]/', '', $students)),
                'unitText' => 'students',
            ];
        }

        if ($ranking) {
            $schema['award'] = 'QS World University Rankings: ' . $ranking;
        }

        if ($city || $country) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $city,
                'addressCountry' => $country,
            ];
        }

        return $schema;
    }

    /**
     * Generate FAQPage schema for a program
     *
     * Prefers AI-generated FAQs from program_faqs table.
     * Falls back to auto-generated template FAQs if no AI FAQs exist.
     *
     * @param int $post_id The program post ID
     * @return array Schema.org FAQPage object
     */
    public static function get_faq_schema($post_id)
    {
        // Try AI-generated FAQs first
        $ai_faqs = self::get_ai_faq_data($post_id);
        if (!empty($ai_faqs)) {
            return [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'dateModified' => get_the_modified_date('c', $post_id),
                'mainEntity' => array_map(function ($faq) {
                    return [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $faq['answer'],
                        ],
                    ];
                }, $ai_faqs),
            ];
        }

        // Fallback: auto-generate from program data
        $uni_id = get_post_meta($post_id, 'zh_university', true);
        $university = !empty($uni_id) ? get_post($uni_id) : null;
        $program_title = get_the_title($post_id);
        $uni_name = $university ? $university->post_title : (get_post_meta($post_id, 'University', true) ?: '');

        $fee = get_post_meta($post_id, 'Official_Tuition', true);
        $discounted_fee = get_post_meta($post_id, 'Discounted_Tuition', true);
        $currency = get_post_meta($post_id, 'Tuition_Currency', true) ?: 'USD';
        $duration = get_post_meta($post_id, 'Study_Years', true);
        $ielts = get_post_meta($post_id, 'IELTS', true);
        $toefl = get_post_meta($post_id, 'TOEFL', true);

        $country_terms = get_the_terms($post_id, 'sit-country');
        $language_terms = get_the_terms($post_id, 'sit-language');
        $degree_terms = get_the_terms($post_id, 'sit-degree');
        $city_terms = $university ? get_the_terms($university->ID, 'sit-city') : false;

        $country = (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '';
        $city = (!empty($city_terms) && !is_wp_error($city_terms)) ? $city_terms[0]->name : '';
        $language = (!empty($language_terms) && !is_wp_error($language_terms)) ? $language_terms[0]->name : '';
        $degree = (!empty($degree_terms) && !is_wp_error($degree_terms)) ? $degree_terms[0]->name : '';

        $faqs = [];

        // Q1: Tuition
        if ($fee) {
            $effective_fee = !empty($discounted_fee) ? $discounted_fee : $fee;
            $fee_answer = "The annual tuition fee for {$program_title} at {$uni_name} is {$currency} {$fee}.";
            if (!empty($discounted_fee)) {
                $fee_answer .= " A discounted rate of {$currency} {$discounted_fee} is available.";
            }
            $faqs[] = [
                'q' => "How much does {$program_title} cost at {$uni_name}?",
                'a' => $fee_answer,
            ];
        }

        // Q2: Duration
        if ($duration) {
            $faqs[] = [
                'q' => "How long is the {$program_title} program at {$uni_name}?",
                'a' => "The {$program_title} program at {$uni_name} has a duration of {$duration} years of full-time study.",
            ];
        }

        // Q3: Language
        if ($language) {
            $faqs[] = [
                'q' => "What is the language of instruction for {$program_title} at {$uni_name}?",
                'a' => "The {$program_title} program at {$uni_name} is taught in {$language}.",
            ];
        }

        // Q4: Location
        if ($city && $country) {
            $faqs[] = [
                'q' => "Where is {$uni_name} located?",
                'a' => "{$uni_name} is located in {$city}, {$country}.",
            ];
        }

        // Q5: English requirements
        if ($ielts || $toefl) {
            $req_parts = [];
            if ($ielts) $req_parts[] = "IELTS {$ielts}";
            if ($toefl) $req_parts[] = "TOEFL {$toefl}";
            $faqs[] = [
                'q' => "What are the English language requirements for {$program_title} at {$uni_name}?",
                'a' => "The English language requirements for {$program_title} at {$uni_name} are: " . implode(' or ', $req_parts) . ".",
            ];
        }

        // Q6: Degree
        if ($degree) {
            $faqs[] = [
                'q' => "What degree do you get from {$program_title} at {$uni_name}?",
                'a' => "Upon completion of the {$program_title} program at {$uni_name}, you will receive a {$degree} degree.",
            ];
        }

        if (empty($faqs)) {
            return [];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'dateModified' => get_the_modified_date('c', $post_id),
            'mainEntity' => array_map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['a'],
                    ],
                ];
            }, $faqs),
        ];

        return $schema;
    }

    /**
     * Get AI-generated FAQ data from program_faqs table
     *
     * @param int $post_id The program post ID
     * @param int $limit Max FAQs to return (0 = all)
     * @param int $offset Offset for pagination
     * @param string $category Filter by category (empty = all)
     * @return array Array of ['question' => ..., 'answer' => ..., 'category' => ...]
     */
    public static function get_ai_faq_data($post_id, $limit = 0, $offset = 0, $category = '')
    {
        global $wpdb;
        $table = $wpdb->prefix . 'program_faqs';

        // Check table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return [];
        }

        $where = $wpdb->prepare("WHERE post_id = %d", $post_id);
        if (!empty($category)) {
            $where .= $wpdb->prepare(" AND category = %s", $category);
        }

        $sql = "SELECT question, answer, category FROM {$table} {$where} ORDER BY faq_order ASC";

        if ($limit > 0) {
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
        }

        $results = $wpdb->get_results($sql, ARRAY_A);
        return $results ?: [];
    }

    /**
     * Get FAQ count for a program
     *
     * @param int $post_id
     * @return int
     */
    public static function get_faq_count($post_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'program_faqs';

        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return 0;
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE post_id = %d", $post_id)
        );
    }

    /**
     * Get distinct FAQ categories for a program
     *
     * @param int $post_id
     * @return array
     */
    public static function get_faq_categories($post_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'program_faqs';

        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return [];
        }

        return $wpdb->get_col(
            $wpdb->prepare("SELECT DISTINCT category FROM {$table} WHERE post_id = %d ORDER BY category ASC", $post_id)
        ) ?: [];
    }

    /**
     * Generate FAQ data array (for template rendering)
     *
     * Prefers AI-generated FAQs. Falls back to template FAQs.
     *
     * @param int $post_id The program post ID
     * @param int $limit Max FAQs (0 = all)
     * @param int $offset Offset for pagination
     * @param string $category Category filter
     * @return array Array of ['question' => ..., 'answer' => ..., 'category' => ...]
     */
    public static function get_faq_data($post_id, $limit = 0, $offset = 0, $category = '')
    {
        // Try AI-generated FAQs first
        $ai_faqs = self::get_ai_faq_data($post_id, $limit, $offset, $category);
        if (!empty($ai_faqs)) {
            return $ai_faqs;
        }

        // Fallback to template FAQs
        $schema = self::get_faq_schema($post_id);
        if (empty($schema) || empty($schema['mainEntity'])) {
            return [];
        }

        return array_map(function ($entity) {
            return [
                'question' => $entity['name'],
                'answer' => $entity['acceptedAnswer']['text'],
                'category' => 'general',
            ];
        }, $schema['mainEntity']);
    }

    /**
     * Generate BreadcrumbList schema
     *
     * @param array $items Array of ['name' => ..., 'url' => ...]
     * @return array Schema.org BreadcrumbList object
     */
    public static function get_breadcrumb_schema($items)
    {
        $list_items = [];
        foreach ($items as $i => $item) {
            $list_items[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list_items,
        ];
    }

    /**
     * Output JSON-LD script tag
     *
     * @param array $schema The schema data
     */
    public static function render_jsonld($schema)
    {
        if (empty($schema)) {
            return;
        }
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }

    /**
     * Generate and output all schemas for the current page
     */
    public static function output_schemas()
    {
        if (is_singular('sit-program')) {
            $post_id = get_the_ID();

            // Course schema
            $course = self::get_course_schema($post_id);
            self::render_jsonld($course);

            // FAQ schema
            $faq = self::get_faq_schema($post_id);
            self::render_jsonld($faq);

            // Breadcrumb schema
            $uni_id = get_post_meta($post_id, 'zh_university', true);
            $university = !empty($uni_id) ? get_post($uni_id) : null;
            if ($university) {
                $breadcrumbs = self::get_breadcrumb_schema([
                    ['name' => 'Home', 'url' => home_url('/')],
                    ['name' => $university->post_title, 'url' => get_permalink($uni_id)],
                    ['name' => get_the_title($post_id), 'url' => get_permalink($post_id)],
                ]);
                self::render_jsonld($breadcrumbs);
            }
        }

        if (is_singular('sit-university')) {
            $post_id = get_the_ID();

            // Organization schema
            $org = self::get_organization_schema($post_id, false);
            self::render_jsonld($org);

            // Breadcrumb schema
            $country_terms = get_the_terms($post_id, 'sit-country');
            $country = (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : 'Universities';
            $breadcrumbs = self::get_breadcrumb_schema([
                ['name' => 'Home', 'url' => home_url('/')],
                ['name' => $country, 'url' => home_url('/universities/')],
                ['name' => get_the_title($post_id), 'url' => get_permalink($post_id)],
            ]);
            self::render_jsonld($breadcrumbs);
        }
    }

    /**
     * Generate enhanced meta description for programs
     *
     * Entity-rich descriptions that AI engines can extract and cite
     *
     * @param int $post_id
     * @return string
     */
    public static function get_program_meta_description($post_id)
    {
        $uni_id = get_post_meta($post_id, 'zh_university', true);
        $university = !empty($uni_id) ? get_post($uni_id) : null;
        $program_title = get_the_title($post_id);
        $uni_name = $university ? $university->post_title : (get_post_meta($post_id, 'University', true) ?: '');

        $fee = get_post_meta($post_id, 'Official_Tuition', true);
        $currency = get_post_meta($post_id, 'Tuition_Currency', true) ?: 'USD';
        $duration = get_post_meta($post_id, 'Study_Years', true);

        $country_terms = get_the_terms($post_id, 'sit-country');
        $city_terms = $university ? get_the_terms($university->ID, 'sit-city') : false;
        $degree_terms = get_the_terms($post_id, 'sit-degree');
        $language_terms = get_the_terms($post_id, 'sit-language');

        $country = (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '';
        $city = (!empty($city_terms) && !is_wp_error($city_terms)) ? $city_terms[0]->name : '';
        $degree = (!empty($degree_terms) && !is_wp_error($degree_terms)) ? $degree_terms[0]->name : '';
        $language = (!empty($language_terms) && !is_wp_error($language_terms)) ? $language_terms[0]->name : '';

        // Build entity-rich description
        $parts = [];
        $parts[] = "Study {$program_title}";
        if ($degree) $parts[0] .= " ({$degree})";
        $parts[0] .= " at {$uni_name}";
        if ($city && $country) {
            $parts[] = "Located in {$city}, {$country}";
        }
        if ($duration) {
            $parts[] = "{$duration}-year program";
        }
        if ($fee) {
            $parts[] = "Tuition: {$currency} {$fee}/year";
        }
        if ($language) {
            $parts[] = "Taught in {$language}";
        }

        $description = implode('. ', $parts) . '.';

        // Trim to 155-160 chars
        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }

        return $description;
    }

    /**
     * Generate enhanced meta description for universities
     *
     * @param int $post_id
     * @return string
     */
    public static function get_university_meta_description($post_id)
    {
        $uni_name = get_the_title($post_id);
        $description_text = get_post_meta($post_id, 'Description', true);
        $ranking = get_post_meta($post_id, 'QS_Rank', true);
        $students = get_post_meta($post_id, 'Number_Of_Students', true);
        $year_founded = get_post_meta($post_id, 'Year_Founded', true);

        $country_terms = get_the_terms($post_id, 'sit-country');
        $city_terms = get_the_terms($post_id, 'sit-city');

        $country = (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '';
        $city = (!empty($city_terms) && !is_wp_error($city_terms)) ? $city_terms[0]->name : '';

        $parts = [];
        $parts[] = $uni_name;
        if ($city && $country) {
            $parts[0] .= " in {$city}, {$country}";
        }
        if ($ranking) {
            $parts[] = "QS Ranked #{$ranking}";
        }
        if ($year_founded) {
            $year = date('Y', strtotime($year_founded));
            $parts[] = "Founded {$year}";
        }
        if ($students) {
            $parts[] = "{$students}+ students";
        }
        $parts[] = "Explore programs, tuition fees, and admissions";

        $description = implode('. ', $parts) . '.';

        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }

        return $description;
    }

    /**
     * Normalize currency codes
     *
     * @param string $currency
     * @return string ISO 4217 currency code
     */
    private static function normalize_currency($currency)
    {
        $map = [
            '$' => 'USD',
            '€' => 'EUR',
            '£' => 'GBP',
            '₺' => 'TRY',
            'TL' => 'TRY',
            'USD' => 'USD',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
            'TRY' => 'TRY',
        ];

        $trimmed = trim($currency);
        return isset($map[$trimmed]) ? $map[$trimmed] : $trimmed;
    }
}
