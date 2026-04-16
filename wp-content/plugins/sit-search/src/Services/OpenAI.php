<?php

namespace SIT\Search\Services;

class OpenAI
{
    private string $api_key;
    private string $base_url = 'https://api.openai.com/v1';
    
    public function __construct()
    {
        $this->api_key = Constants::get_openai_api_key();
    }
    
    /**
     * Generate embeddings for text using OpenAI's text-embedding-3-small model
     */
    public function generateEmbedding(string $text): ?array
    {
        $url = $this->base_url . '/embeddings';
        
        $data = [
            'model' => 'text-embedding-3-small',
            'input' => $text,
            'encoding_format' => 'float'
        ];
        
        $response = $this->makeRequest($url, $data);
        
        if ($response && isset($response['data'][0]['embedding'])) {
            return $response['data'][0]['embedding'];
        }
        
        return null;
    }
    
    /**
     * Generate embeddings for multiple texts in batch
     */
    public function generateBatchEmbeddings(array $texts): array
    {
        $url = $this->base_url . '/embeddings';
        
        $data = [
            'model' => 'text-embedding-3-small',
            'input' => $texts,
            'encoding_format' => 'float'
        ];
        
        $response = $this->makeRequest($url, $data);
        $embeddings = [];
        
        if ($response && isset($response['data'])) {
            foreach ($response['data'] as $item) {
                $embeddings[] = $item['embedding'];
            }
        }
        
        return $embeddings;
    }
    
    /**
     * Calculate cosine similarity between two vectors
     */
    public function cosineSimilarity(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            return 0.0;
        }
        
        $dotProduct = 0.0;
        $magnitude1 = 0.0;
        $magnitude2 = 0.0;
        
        $count = count($vector1);
        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $vector1[$i] * $vector2[$i];
            $magnitude1 += $vector1[$i] * $vector1[$i];
            $magnitude2 += $vector2[$i] * $vector2[$i];
        }
        
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        
        if ($magnitude1 == 0.0 || $magnitude2 == 0.0) {
            return 0.0;
        }
        
        return $dotProduct / ($magnitude1 * $magnitude2);
    }
    
    /**
     * Make HTTP request to OpenAI API using WordPress HTTP API
     */
    private function makeRequest(string $url, array $data): ?array
    {
        if (empty($this->api_key)) {
            error_log('OpenAI API key not configured. Set it under SIT Search > AI Settings.');
            return null;
        }

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => json_encode($data),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            error_log('OpenAI API error: ' . $response->get_error_message());
            return null;
        }
        
        $httpCode = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($httpCode !== 200) {
            error_log('OpenAI API HTTP error: ' . $httpCode . ' - ' . $body);
            return null;
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('OpenAI API JSON decode error: ' . json_last_error_msg());
            return null;
        }
        
        return $decoded;
    }
    
    /**
     * Enhance search query with common synonyms and variations
     */
    public function enhanceSearchQuery(string $query): array
    {
        $synonyms = [
            // Computer Science variations
            'computer science' => ['cs', 'computing', 'informatics', 'it', 'information technology'],
            'software engineering' => ['software development', 'programming', 'coding'],
            'artificial intelligence' => ['ai', 'machine learning', 'ml', 'deep learning'],
            
            // Business variations
            'business administration' => ['business admin', 'mba', 'management', 'business management'],
            'economics' => ['economy', 'economic sciences', 'finance'],
            'marketing' => ['digital marketing', 'advertising', 'promotion'],
            
            // Engineering variations
            'mechanical engineering' => ['mechanical', 'mech eng', 'mechanics'],
            'electrical engineering' => ['electrical', 'electronics', 'ee'],
            'civil engineering' => ['civil', 'construction engineering'],
            'industrial engineering' => ['industrial', 'ie', 'systems engineering'],
            
            // Medical variations
            'medicine' => ['medical', 'doctor', 'physician', 'md'],
            'nursing' => ['nurse', 'healthcare', 'medical care'],
            'dentistry' => ['dental', 'dentist', 'oral health'],
            'pharmacy' => ['pharmaceutical', 'pharmacist', 'drug'],
            
            // Language variations
            'english' => ['english language', 'english literature', 'linguistics'],
            'turkish' => ['turkish language', 'türkçe', 'turkish studies'],
            
            // Common typos and variations
            'enginnering' => ['engineering'],
            'buisness' => ['business'],
            'managment' => ['management'],
            'psycology' => ['psychology'],
            'architechture' => ['architecture'],
        ];
        
        $query_lower = strtolower(trim($query));
        $enhanced_queries = [$query]; // Always include original
        
        // Add direct synonyms
        foreach ($synonyms as $term => $variations) {
            if (strpos($query_lower, $term) !== false) {
                foreach ($variations as $variation) {
                    $enhanced_queries[] = str_ireplace($term, $variation, $query);
                }
            }
            
            // Check if query matches any variation
            foreach ($variations as $variation) {
                if (strpos($query_lower, $variation) !== false) {
                    $enhanced_queries[] = str_ireplace($variation, $term, $query);
                }
            }
        }
        
        return array_unique($enhanced_queries);
    }
}
