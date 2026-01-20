<div class="wrap">
    <h1>AI Search Management</h1>
    
    <div class="ai-search-admin">
        <div class="card">
            <h2>Program Embeddings Status</h2>
            <div id="embeddings-stats">
                <p>Loading statistics...</p>
            </div>
            
            <div class="actions">
                <button id="generate-embeddings" class="button button-primary">
                    Generate All Embeddings
                </button>
                <button id="refresh-stats" class="button">
                    Refresh Statistics
                </button>
                <button id="clear-old-embeddings" class="button button-secondary">
                    Clear Old Embeddings (30+ days)
                </button>
            </div>
            
            <div id="generation-progress" style="display: none;">
                <h3>Generation Progress</h3>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
                <p id="progress-text">Starting...</p>
            </div>
            
            <div id="generation-results" style="display: none;">
                <h3>Results</h3>
                <pre id="results-output"></pre>
            </div>
        </div>
        
        <div class="card">
            <h2>AI Search Testing</h2>
            <p>Test the AI search functionality with various queries:</p>
            
            <div class="test-search">
                <input type="text" id="test-query" placeholder="Enter search query (e.g., 'computer sience', 'buisness admin')" style="width: 300px;">
                <button id="test-search-btn" class="button">Test Search</button>
            </div>
            
            <div id="test-results" style="margin-top: 20px;">
                <!-- Test results will appear here -->
            </div>
        </div>
        
        <div class="card">
            <h2>Common Test Queries</h2>
            <p>Try these queries to test typo tolerance and semantic understanding:</p>
            <ul>
                <li><code>computer sience</code> (typo in "science")</li>
                <li><code>buisness admin</code> (typo in "business")</li>
                <li><code>IT</code> (should match Information Technology)</li>
                <li><code>AI</code> (should match Artificial Intelligence)</li>
                <li><code>mech eng</code> (abbreviation for Mechanical Engineering)</li>
                <li><code>psycology</code> (typo in "psychology")</li>
                <li><code>architechture</code> (typo in "architecture")</li>
                <li><code>managment</code> (typo in "management")</li>
            </ul>
        </div>
    </div>
    <div id="ai-search-results"></div>

<hr>

<h3>Generation Log</h3>
<pre id="generation-log" style="background-color: #f1f1f1; border: 1px solid #ccc; padding: 10px; height: 200px; overflow-y: scroll; white-space: pre-wrap; margin-top: 1em;">Log messages will appear here...</pre>

</div>

<style>
.ai-search-admin .card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.ai-search-admin .actions {
    margin: 20px 0;
}

.ai-search-admin .actions .button {
    margin-right: 10px;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-fill {
    height: 100%;
    background: #0073aa;
    transition: width 0.3s ease;
}

.test-search {
    margin: 15px 0;
}

.test-search input {
    padding: 8px;
    margin-right: 10px;
}

#test-results {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 15px;
    background: #f9f9f9;
}

.test-result-item {
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}

.test-result-item:last-child {
    border-bottom: none;
}

.similarity-score {
    background: #0073aa;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
    margin-left: 10px;
}

#embeddings-stats {
    background: #f0f6fc;
    padding: 15px;
    border-radius: 4px;
    margin: 15px 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.stat-item {
    text-align: center;
    padding: 10px;
    background: white;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    
    // Load initial stats
    loadEmbeddingsStats();
    
    // Generate embeddings
    $('#generate-embeddings').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('Starting...');
        
        var logContainer = $('#generation-log');
        var initialMessage = 'Starting embedding generation...';
        logContainer.html(initialMessage + '\n');
        console.log(initialMessage);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_embeddings',
                nonce: '<?php echo wp_create_nonce('generate_embeddings_nonce'); ?>'
            },
            xhrFields: {
                onprogress: function(e) {
                    var response = e.target.responseText;
                    var newLogs = response.replace(/---PROGRESS---/g, '\n');
                    logContainer.html(newLogs);
                    logContainer.scrollTop(logContainer[0].scrollHeight);
                    console.clear(); // Clear console to show only the latest updates
                    console.log(newLogs);
                }
            }
        }).done(function(response) {
            var finalMessage = '\n--- GENERATION COMPLETE ---';
            logContainer.append(finalMessage);
            logContainer.scrollTop(logContainer[0].scrollHeight);
            console.log(finalMessage);
            console.log('Final server response:', response);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            var errorMessage = '\n--- AJAX ERROR ---\n' + textStatus + ': ' + errorThrown;
            logContainer.append(errorMessage);
            console.error(errorMessage);
            console.error('XHR Object:', jqXHR);
            alert('An AJAX error occurred. Check the browser console for more details.');
        }).always(function() {
            button.prop('disabled', false).text('Generate All Embeddings');
        });
    });
    
    // Refresh stats
    $('#refresh-stats').on('click', function() {
        loadEmbeddingsStats();
    });
    
    // Test search
    $('#test-search-btn').on('click', function() {
        const query = $('#test-query').val().trim();
        if (!query) {
            alert('Please enter a search query');
            return;
        }
        
        testSearch(query);
    });
    
    // Test search on Enter key
    $('#test-query').on('keypress', function(e) {
        if (e.which === 13) {
            $('#test-search-btn').click();
        }
    });
    
    function loadEmbeddingsStats() {
        $('#embeddings-stats').html('<p>Loading statistics...</p>');
        
        // This would need to be implemented as a separate AJAX action
        // For now, showing placeholder
        setTimeout(function() {
            $('#embeddings-stats').html(`
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">-</div>
                        <div class="stat-label">Total Programs</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">-</div>
                        <div class="stat-label">Cached Embeddings</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">-</div>
                        <div class="stat-label">Coverage %</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">-</div>
                        <div class="stat-label">Recent Updates</div>
                    </div>
                </div>
                <p><em>Generate embeddings first to see statistics</em></p>
            `);
        }, 500);
    }
    
    function testSearch(query) {
        $('#test-results').html('<p>Searching...</p>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'program_search_ajax',
                nonce: '<?php echo wp_create_nonce('program_search_nonce'); ?>',
                keyword: query
            },
            success: function(response) {
                displayTestResults(query, response);
            },
            error: function() {
                $('#test-results').html('<p>Error occurred during search</p>');
            }
        });
    }
    
    function displayTestResults(query, results) {
        if (!results || results.length === 0) {
            $('#test-results').html(`
                <h4>Search Results for: "${query}"</h4>
                <p>No results found</p>
            `);
            return;
        }
        
        let html = `<h4>Search Results for: "${query}" (${results.length} results)</h4>`;
        
        results.forEach(function(result, index) {
            const similarity = result.ai_similarity ? 
                `<span class="similarity-score">${(result.ai_similarity * 100).toFixed(1)}%</span>` : '';
            
            html += `
                <div class="test-result-item">
                    <strong>${result.title}</strong> ${similarity}
                    <br>
                    <small>University: ${result.university} | Level: ${result.level} | Language: ${result.language}</small>
                </div>
            `;
        });
        
        $('#test-results').html(html);
    }
});
</script>
