jQuery(document).ready(function($) {
    if ($('#sit-ai-search-input').length === 0) return;

    const localSynonyms = {
        'cs': ['computer science', 'software engineering', 'information technology'],
        'db': ['database', 'data science'],
        'med': ['medicine', 'medical', 'healthcare', 'nursing', 'dentistry'],
        'business': ['management', 'administration', 'finance', 'marketing'],
        'ai': ['artificial intelligence', 'machine learning', 'deep learning'],
        'it': ['information technology', 'computer science']
    };

    let debounceTimer;
    const resultsUrl = sit_ai_search_vars.results_url;

    // Handle form submission
    $('#sit-ai-search-form').on('submit', function(e) {
        e.preventDefault();
        const query = $('#sit-ai-search-input').val().trim();
        if (query.length < 2) return;
        performAiSearch(query);
    });

    // Handle enter key
    $('#sit-ai-search-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const query = $(this).val().trim();
            if (query.length < 2) return;
            performAiSearch(query);
        }
    });

    // Live suggestions with debounce
    $('#sit-ai-search-input').on('input', function() {
        const query = $(this).val().trim().toLowerCase();
        clearTimeout(debounceTimer);
        
        if (query.length < 2) {
            $('#sit-ai-suggestions').hide();
            return;
        }

        debounceTimer = setTimeout(() => {
            showSuggestions(query);
        }, 600);
    });

    async function showSuggestions(query) {
        let expandedTerms = [];

        // Local cache bypass
        if (localSynonyms[query]) {
            expandedTerms = localSynonyms[query];
        } else {
            $('#sit-ai-loading').show();
            
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

        if (!expandedTerms.includes(query)) expandedTerms.push(query);

        // Show suggestion chips
        const container = $('#sit-ai-suggestions');
        container.empty();
        
        if (expandedTerms.length > 0) {
            let html = '<div style="font-size: 13px; color: #888; margin-bottom: 8px;">AI suggestions (click to search):</div>';
            expandedTerms.forEach(term => {
                html += `<a href="${resultsUrl}?search=${encodeURIComponent(term)}" class="ai-suggestion-chip">${term}</a>`;
            });
            // Add "Search all" button
            const allTerms = expandedTerms.join(',');
            html += `<a href="${resultsUrl}?search=${encodeURIComponent(allTerms)}" class="ai-suggestion-chip ai-suggestion-all">🔍 Search all related</a>`;
            container.html(html).show();
        }
    }

    async function performAiSearch(query) {
        let expandedTerms = [];
        const queryLower = query.toLowerCase();

        // Local cache bypass
        if (localSynonyms[queryLower]) {
            expandedTerms = localSynonyms[queryLower];
        } else {
            $('#sit-ai-loading').show();
            
            // Handle loading state for different button types
            const shortcodeBtn = $('#sit-ai-search-btn');
            if (shortcodeBtn.length) shortcodeBtn.prop('disabled', true).text('AI is thinking...');
            
            const resultsBtnText = $('.results-search-btn span');
            if (resultsBtnText.length) resultsBtnText.text('Thinking...');
            
            const archiveBtn = $('.ProgramArchivePage-search-button');
            if (archiveBtn.length) archiveBtn.prop('disabled', true).text('Thinking...');
            
            try {
                const response = await $.get(sit_ai_search_vars.api_url + 'ai-search', { q: query });
                if (response && response.terms) {
                    expandedTerms = response.terms;
                }
            } catch (e) {
                expandedTerms = [query];
            }
        }

        if (!expandedTerms.includes(queryLower)) expandedTerms.push(queryLower);

        // Redirect to results page with expanded search terms
        const searchParam = expandedTerms.join(',');
        window.location.href = resultsUrl + '?search=' + encodeURIComponent(searchParam) + '&ai_query=' + encodeURIComponent(queryLower);
    }

    // Expose globally so main.js can use it for archive page search inputs
    window.performGlobalAiSearch = performAiSearch;
});
