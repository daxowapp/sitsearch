<?php

namespace SIT\Search\Services;

class ProgramEmbeddings
{
    private SIT_OpenAI_Service $openai;
    private string $cache_table = 'sit_program_embeddings';
    
    public function __construct(SIT_OpenAI_Service $openai)
    {
        $this->openai = $openai;
    }
    
    /**
     * Create database table for storing embeddings
     */
    private function createCacheTable(): void
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . $this->cache_table;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            program_id bigint(20) NOT NULL,
            program_title text NOT NULL,
            program_description text,
            university_name varchar(255),
            embedding longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY program_id (program_id),
            KEY program_title (program_title(100)),
            KEY university_name (university_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Generate and store embeddings for all programs
     */
    public function generateAllEmbeddings(callable $progress_callback): array
    {
        $this->createCacheTable();
        call_user_func($progress_callback, 'Database table checked/created.');

        if (!function_exists('get_field')) {
            call_user_func($progress_callback, 'Error: Advanced Custom Fields (ACF) plugin is not active or the get_field() function is missing.');
            throw new \Exception('ACF function get_field() not found.');
        }

        $paged = 1;
        $posts_per_page = 20;
        $results = [
            'processed' => 0,
            'errors' => 0,
            'skipped' => 0,
            'cached' => 0
        ];

        $args = [
            'post_type' => 'sit-program',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'zh_university',
                    'compare' => 'EXISTS'
                ]
            ]
        ];

        $query = new \WP_Query($args);
        $total_pages = $query->max_num_pages;

        if ($total_pages == 0) {
            call_user_func($progress_callback, 'No programs found to process.');
            return $results;
        }

        call_user_func($progress_callback, "Found {$query->found_posts} total programs. Starting processing in {$total_pages} batches...");

        for ($paged = 1; $paged <= $total_pages; $paged++) {
            $args['paged'] = $paged;
            $query = new \WP_Query($args);

            if ($query->have_posts()) {
                call_user_func($progress_callback, "Processing batch {$paged} of {$total_pages}...");
                foreach ($query->posts as $program_id) {
                    try {
                        $university_id = get_post_meta($program_id, 'zh_university', true);
                        if (!$university_id || get_field('Active_in_Search', $university_id) != '1') {
                            $results['skipped']++;
                            continue;
                        }

                        if ($this->generateProgramEmbedding($program_id)) {
                            $results['processed']++;
                        } else {
                            $results['cached']++;
                        }
                    } catch (\Throwable $e) {
                        $results['errors']++;
                        call_user_func($progress_callback, "Error on program {$program_id}: " . $e->getMessage());
                    }
                }
            }
        }

        wp_reset_postdata();
        call_user_func($progress_callback, "Processing complete. Processed: {$results['processed']}, Cached: {$results['cached']}, Skipped: {$results['skipped']}, Errors: {$results['errors']}");
        return $results;
    }
    
    /**
     * Generate embedding for a single program
     */
    public function generateProgramEmbedding(int $program_id): bool
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . $this->cache_table;
        
        // Check if embedding already exists and is recent
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE program_id = %d AND updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            $program_id
        ));
        
        if ($existing) {
            return false; // Already cached
        }
        
        $program = get_post($program_id);
        if (!$program) {
            return false;
        }
        
        // Get program details
        $university_id = get_post_meta($program_id, 'zh_university', true);
        $university_name = $university_id ? get_the_title($university_id) : '';
        $description = get_post_meta($program_id, 'Description', true);
        
        // Get taxonomies
        $degree_terms = get_the_terms($program_id, 'sit-degree');
        $language_terms = get_the_terms($program_id, 'sit-language');
        $speciality_terms = get_the_terms($program_id, 'sit-speciality');
        $country_terms = get_the_terms($program_id, 'sit-country');
        
        $degree = $degree_terms && !is_wp_error($degree_terms) ? $degree_terms[0]->name : '';
        $language = $language_terms && !is_wp_error($language_terms) ? $language_terms[0]->name : '';
        $speciality = $speciality_terms && !is_wp_error($speciality_terms) ? $speciality_terms[0]->name : '';
        $country = $country_terms && !is_wp_error($country_terms) ? $country_terms[0]->name : '';
        
        // Create comprehensive text for embedding
        $embedding_text = $this->createEmbeddingText(
            $program->post_title,
            $description,
            $university_name,
            $degree,
            $language,
            $speciality,
            $country
        );
        
        // Generate embedding
        $embedding = $this->openai->generateEmbedding($embedding_text);
        
        if (!$embedding) {
            return false;
        }
        
        // Store in database
        $result = $wpdb->replace(
            $table_name,
            [
                'program_id' => $program_id,
                'program_title' => $program->post_title,
                'program_description' => $description,
                'university_name' => $university_name,
                'embedding' => json_encode($embedding)
            ],
            ['%d', '%s', '%s', '%s', '%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Create comprehensive text for embedding generation
     */
    private function createEmbeddingText(string $title, string $description, string $university, string $degree, string $language, string $speciality, string $country): string
    {
        $parts = array_filter([
            $title,
            $speciality,
            $degree . ' degree',
            'at ' . $university,
            'in ' . $language . ' language',
            'in ' . $country,
            $description
        ]);
        
        return implode(' ', $parts);
    }
    
    /**
     * Search programs using AI embeddings
     */
    public function searchPrograms(string $query, int $limit = 50, float $similarity_threshold = 0.7): array
    {
        // Generate embedding for search query
        $query_embedding = $this->openai->generateEmbedding($query);
        
        if (!$query_embedding) {
            return [];
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . $this->cache_table;
        
        // Get all embeddings from database
        $embeddings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY updated_at DESC");
        
        $results = [];
        
        foreach ($embeddings as $row) {
            $stored_embedding = json_decode($row->embedding, true);
            
            if (!$stored_embedding) {
                continue;
            }
            
            // Calculate similarity
            $similarity = $this->openai->cosineSimilarity($query_embedding, $stored_embedding);
            
            if ($similarity >= $similarity_threshold) {
                $results[] = [
                    'program_id' => (int)$row->program_id,
                    'similarity' => $similarity,
                    'title' => $row->program_title,
                    'university' => $row->university_name,
                    'description' => $row->program_description
                ];
            }
        }
        
        // Sort by similarity (highest first)
        usort($results, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice($results, 0, $limit);
    }
    
    /**
     * Get embedding statistics
     */
    public function getStats(): array
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->cache_table;
        
        $total_programs = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'sit-program' AND post_status = 'publish'");
        $cached_embeddings = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $recent_embeddings = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
        
        return [
            'total_programs' => (int)$total_programs,
            'cached_embeddings' => (int)$cached_embeddings,
            'recent_embeddings' => (int)$recent_embeddings,
            'coverage_percentage' => $total_programs > 0 ? round(($cached_embeddings / $total_programs) * 100, 2) : 0
        ];
    }
    
    /**
     * Clear old embeddings
     */
    public function clearOldEmbeddings(int $days = 30): int
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->cache_table;
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE updated_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        return $deleted ?: 0;
    }
}