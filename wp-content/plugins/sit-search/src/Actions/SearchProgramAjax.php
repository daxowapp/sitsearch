<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Hook;
use SIT\Search\Services\CachedData;
use SIT\Search\Services\Supabase;

/**
 * Program Search AJAX Handler
 * 
 * Supports three search strategies:
 * 1. Supabase full-text search (fastest)
 * 2. AI-powered semantic search with OpenAI embeddings
 * 3. Traditional WordPress search (fallback)
 */
class SearchProgramAjax extends Hook
{
    public static array $hooks = ['wp_ajax_program_search_ajax','wp_ajax_nopriv_program_search_ajax'];

    public static int $priority = 10;

    public function __invoke()
    {
        check_ajax_referer('program_search_nonce', 'nonce');

        $keyword = sanitize_text_field($_POST['keyword']);
        
        // Strategy 1: Try Supabase full-text search first (fastest)
        $data = $this->supabaseSearch($keyword);
        
        // Strategy 2: If Supabase fails, try AI-powered search
        if (empty($data)) {
            $data = $this->aiSearch($keyword);
        }
        
        // Strategy 3: Fall back to traditional WordPress search
        if (empty($data)) {
            $data = $this->traditionalSearch($keyword);
        }

        wp_send_json($data);
    }
    
    /**
     * Supabase full-text search (fastest option)
     * Uses PostgreSQL's built-in FTS with the pre-indexed 'fts' column
     */
    private function supabaseSearch(string $keyword): array
    {
        try {
            $supabase = new Supabase();
            
            if (!$supabase->is_configured()) {
                return [];
            }
            
            // Build search query for Supabase FTS
            // Replace spaces with & for AND search
            $search_terms = preg_split('/\s+/', trim($keyword));
            $fts_query = implode(' & ', array_filter($search_terms));
            
            // Query Supabase with full-text search
            $programs = $supabase->get('zoho_programs', [
                'fts' => 'fts.' . $fts_query,
                'active' => 'eq.true',
                'limit' => 50,
                'order' => 'tuition_fee_usd.asc.nullslast'
            ], false); // Don't cache search results
            
            if (empty($programs)) {
                return [];
            }
            
            // Format results
            $data = [];
            foreach ($programs as $program) {
                $data[] = $this->formatSupabaseProgram($program);
            }
            
            return $data;
            
        } catch (\Exception $e) {
            error_log('Supabase Search Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Format Supabase program data for frontend
     */
    private function formatSupabaseProgram(array $program): array
    {
        // Try to find local WordPress post for URL
        $local_url = '';
        $posts = get_posts([
            'post_type' => 'sit-program',
            'meta_key' => 'supabase_id',
            'meta_value' => $program['id'],
            'posts_per_page' => 1
        ]);
        
        if (!empty($posts)) {
            $local_url = get_permalink($posts[0]->ID);
        } else {
            // Fallback: try zoho_product_id
            $posts = get_posts([
                'post_type' => 'sit-program',
                'meta_key' => 'zoho_product_id',
                'meta_value' => $program['id'],
                'posts_per_page' => 1
            ]);
            if (!empty($posts)) {
                $local_url = get_permalink($posts[0]->ID);
            }
        }
        
        return [
            'url'        => $local_url ?: '#',
            'title'      => $program['name'] ?? '',
            'university' => $program['university_name'] ?? '',
            'language'   => $program['language_name'] ?? '',
            'tuition'    => $program['official_tuition'] ?? '',
            'discounted' => $program['discounted_tuition'] ?? '',
            'period'     => $program['study_years'] ?? '',
            'level'      => $program['degree_name'] ?? '',
            'source'     => 'supabase'
        ];
    }
    
    /**
     * AI-powered semantic search using OpenAI embeddings
     */
    private function aiSearch(string $keyword): array
    {
        try {
            $openai = new \SIT\Search\Services\OpenAI();
            $embeddings = new \SIT\Search\Services\ProgramEmbeddings($openai);
            
            // Enhance query with synonyms and variations
            $enhanced_queries = $openai->enhanceSearchQuery($keyword);
            $all_results = [];
            
            // Search with each enhanced query
            foreach ($enhanced_queries as $query) {
                $results = $embeddings->searchPrograms($query, 20, 0.65);
                $all_results = array_merge($all_results, $results);
            }
            
            // Remove duplicates and sort by similarity
            $unique_results = [];
            foreach ($all_results as $result) {
                $program_id = $result['program_id'];
                if (!isset($unique_results[$program_id]) || $unique_results[$program_id]['similarity'] < $result['similarity']) {
                    $unique_results[$program_id] = $result;
                }
            }
            
            // Sort by similarity
            uasort($unique_results, function($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            $top_results = array_slice($unique_results, 0, 50);
            $this->primeProgramCaches(array_column($top_results, 'program_id'));
            
            // Convert to program data format
            $data = [];
            foreach ($top_results as $result) {
                $program_data = $this->getProgramData($result['program_id']);
                if ($program_data) {
                    $program_data['ai_similarity'] = round($result['similarity'], 3);
                    $program_data['source'] = 'ai';
                    $data[] = $program_data;
                }
            }
            
            return $data;
            
        } catch (\Exception $e) {
            error_log('AI Search Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Traditional WordPress search (fallback)
     */
    private function traditionalSearch(string $keyword): array
    {
        $parts = preg_split('/\s+/', $keyword);
        $title_parts = [];
        $fee = '';
        $level = '';
        $language = '';

        $known_levels = ['bachelor', 'master', 'phd', 'diploma', 'associate'];
        $known_languages = ['english', 'turkish', 'french', 'german', 'arabic'];

        foreach ($parts as $part) {
            $cleaned_part = str_replace(',', '', $part);
            $lower_part = strtolower($part);

            if (is_numeric($cleaned_part)) {
                $fee = $cleaned_part;
            } elseif (in_array($lower_part, $known_levels)) {
                $level = $lower_part;
            } elseif (in_array($lower_part, $known_languages)) {
                $language = $lower_part;
            } else {
                $title_parts[] = $part;
            }
        }

        $program_name = implode(' ', $title_parts);

        $meta_query = ['relation' => 'AND'];
        $tax_query = [];
        
        // Use cached active university IDs (OPTIMIZED)
        $active_university_ids = CachedData::get_active_university_ids();
        if (!empty($active_university_ids)) {
            $meta_query[] = [
                'key'     => 'zh_university',
                'value'   => $active_university_ids,
                'compare' => 'IN',
            ];
        } else {
            return [];
        }

        if (!empty($fee)) {
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key'     => 'Official_Tuition',
                    'value'   => $fee,
                    'compare' => '=',
                    'type'    => 'NUMERIC'
                ],
                [
                    'key'     => 'Advanced_Discount',
                    'value'   => $fee,
                    'compare' => '=',
                    'type'    => 'NUMERIC'
                ]
            ];
        }

        if (!empty($level)) {
            $tax_query[] = [
                'taxonomy' => 'sit-degree',
                'field'    => 'slug',
                'terms'    => $level
            ];
        }

        if (!empty($language)) {
            $tax_query[] = [
                'taxonomy' => 'sit-language',
                'field'    => 'slug',
                'terms'    => $language
            ];
        }

        $args = [
            'post_type'      => 'sit-program',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            's'              => $program_name,
            'meta_query'     => $meta_query,
        ];

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        $query = new \WP_Query($args);

        $data = [];

        if ($query->have_posts()) {
            $program_ids = wp_list_pluck($query->posts, 'ID');
            $this->primeProgramCaches($program_ids);
            
            foreach ($program_ids as $program_id) {
                $program_data = $this->getProgramData($program_id);
                if ($program_data) {
                    $program_data['source'] = 'wordpress';
                    $data[] = $program_data;
                }
            }
        }

        return $data;
    }
    
    /**
     * Get formatted program data from WordPress
     */
    private function getProgramData(int $program_id): ?array
    {
        $program = get_post($program_id);
        if (!$program) {
            return null;
        }
        
        $uniid = get_post_meta($program_id, 'zh_university', true);
        if (!$uniid) {
            return null;
        }
        
        // Use cached check for active universities
        $active_ids = CachedData::get_active_university_ids();
        if (!in_array((int)$uniid, $active_ids)) {
            return null;
        }
        
        $university = get_post($uniid);
        if (!$university) {
            return null;
        }
        
        return [
            'url'        => get_permalink($program_id),
            'title'      => $program->post_title,
            'university' => $university->post_title,
            'language'   => strip_tags(get_the_term_list($program_id, 'sit-language', '', ', ')),
            'tuition'    => get_post_meta($program_id, 'Official_Tuition', true),
            'discounted' => get_post_meta($program_id, 'Advanced_Discount', true),
            'period'     => get_post_meta($program_id, 'Study_Years', true),
            'level'      => strip_tags(get_the_term_list($program_id, 'sit-degree', '', ', ')),
        ];
    }

    /**
     * Prime caches to prevent N+1 queries during mapping
     */
    private function primeProgramCaches(array $program_ids): void
    {
        if (empty($program_ids)) return;
        
        update_meta_cache('post', $program_ids);
        update_object_term_cache($program_ids, ['sit-language', 'sit-degree']);
        
        $uni_ids = [];
        foreach ($program_ids as $pid) {
            $uid = get_post_meta($pid, 'zh_university', true);
            if ($uid) {
                $uni_ids[] = (int)$uid;
            }
        }
        
        if (!empty($uni_ids)) {
            _prime_post_caches(array_unique($uni_ids), false, false);
        }
    }
}