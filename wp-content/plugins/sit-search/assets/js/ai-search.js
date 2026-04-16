/**
 * AI Search Shortcode Handler
 * Simple form submission — AI expansion happens server-side in PHP.
 */
jQuery(document).ready(function($) {
    // Only activate if the AI search shortcode is on the page
    if ($('#sit-ai-search-input').length === 0) return;

    var resultsUrl = sit_ai_search_vars.results_url;

    // Handle form submission
    $('#sit-ai-search-form').on('submit', function(e) {
        e.preventDefault();
        var query = $('#sit-ai-search-input').val().trim();
        if (query.length < 2) return;

        // Show loading state
        var $btn = $('#sit-ai-search-btn');
        $btn.prop('disabled', true);
        $btn.find('.btn-text').text('Searching');
        $('#sit-ai-loading').show();

        // Simple redirect — server-side PHP handles AI expansion
        window.location.href = resultsUrl + '?search=' + encodeURIComponent(query);
    });

    // Handle enter key
    $('#sit-ai-search-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            var query = $(this).val().trim();
            if (query.length < 2) return;

            // Show loading state
            var $btn = $('#sit-ai-search-btn');
            $btn.prop('disabled', true);
            $btn.find('.btn-text').text('Searching');
            $('#sit-ai-loading').show();

            window.location.href = resultsUrl + '?search=' + encodeURIComponent(query);
        }
    });
});
