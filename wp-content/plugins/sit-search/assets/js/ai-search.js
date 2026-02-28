jQuery(document).ready(function($) {
    if ($('#sit-ai-search-input').length === 0) return;

    let allPrograms = [];
    let isDataLoaded = false;

    // Load data once
    $.get(sit_ai_search_vars.api_url + 'ai-search-index', function(data) {
        if(Array.isArray(data)) {
            allPrograms = data;
        }
        isDataLoaded = true;
    }).fail(function() {
        console.error("Failed to load programs for AI search.");
    });

    const localSynonyms = {
        'cs': ['computer science', 'software engineering', 'information technology'],
        'db': ['database', 'data science'],
        'med': ['medicine', 'medical', 'healthcare', 'nursing', 'dentistry'],
        'business': ['management', 'administration', 'finance', 'marketing'],
        'ai': ['artificial intelligence', 'machine learning', 'deep learning'],
        'it': ['information technology', 'computer science']
    };

    let debounceTimer;

    $('#sit-ai-search-input').on('input', function() {
        const query = $(this).val().trim().toLowerCase();
        
        clearTimeout(debounceTimer);
        
        if (query.length < 2) {
            $('#sit-ai-results').empty();
            $('#sit-ai-loading').hide();
            return;
        }

        debounceTimer = setTimeout(() => {
            performAiSearch(query);
        }, 600);
    });

    async function performAiSearch(query) {
        if (!isDataLoaded) return;

        let expandedTerms = [];

        // Local cache bypass
        if (localSynonyms[query]) {
            expandedTerms = localSynonyms[query];
        } else {
            $('#sit-ai-loading').show().html(`<span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; display: inline-block;"></span> AI is thinking... finding related fields for "${query}"`);
            
            try {
                const response = await $.get(sit_ai_search_vars.api_url + 'ai-search', { q: query });
                if (response && response.terms) {
                    expandedTerms = response.terms;
                }
            } catch (e) {
                expandedTerms = [query];
            }
            
            $('#sit-ai-loading').hide();
        }

        // Add original query to terms just to be safe
        if (!expandedTerms.includes(query)) expandedTerms.push(query);

        filterAndSortResults(query, expandedTerms);
    }

    function filterAndSortResults(originalQuery, terms) {
        const queryWords = originalQuery.split(' ').filter(w => w.length > 0);
        let scoredResults = [];

        allPrograms.forEach(program => {
            const name = (program.title || '').toLowerCase();
            const category = (program.category || '').toLowerCase();
            const uni = (program.uni || '').toLowerCase();
            
            let matched = false;
            let score = 0;

            // Check if ANY term matches
            for (let term of terms) {
                if (name.includes(term) || category.includes(term) || uni.includes(term)) {
                    matched = true;
                    break;
                }
            }

            if (matched) {
                // Apply scoring logic
                if (name === originalQuery) score += 100;
                else if (name.includes(originalQuery)) score += 80;
                else if (category.includes(originalQuery) || uni.includes(originalQuery)) score += 60;
                else score += 10; // Only matched via synonym

                // Bonus for direct word matches in title
                queryWords.forEach(word => {
                    if (name.includes(word)) score += 5;
                });

                scoredResults.push({ program, score });
            }
        });

        // Sort by score
        scoredResults.sort((a, b) => b.score - a.score);

        // Render results
        const container = $('#sit-ai-results');
        container.empty();
        
        if (scoredResults.length === 0) {
            container.append('<p style="text-align: center; color: #666; margin-top: 20px;">No programs found for this query. Try a different term or AI was not able to interpret it.</p>');
            return;
        }

        // Render top 20
        scoredResults.slice(0, 20).forEach(result => {
            const p = result.program;
            const title = p.title || 'Unknown Program';
            const uni = p.uni || '';
            const link = p.url || '#';
            const category = p.category ? `<p style="margin: 5px 0; font-size: 13px; color: #666;"><strong>Tags:</strong> ${p.category}</p>` : '';
            
            container.append(`
                <a href="${link}" class="ai-program-card">
                    <h4>${title}</h4>
                    <p><strong>University:</strong> ${uni}</p>
                    ${category}
                </a>
            `);
        });
    }
});
