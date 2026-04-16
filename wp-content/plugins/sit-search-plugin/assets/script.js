(function($) {
    'use strict';

    // ─── Debounce utility ───
    function debounce(fn, delay) {
        var timer;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function() { fn.apply(context, args); }, delay);
        };
    }

    // ─── Shared AJAX helper for program actions ───
    function loadProgramsAjax(opts) {
        var postId = $('#post_id').val();
        if (!postId) return;
        $.ajax({
            url: upd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'upd_get_programs_new',
                post_id: postId,
                page: opts.page || 1,
                level: opts.level || '',
                search: opts.search || ''
            },
            beforeSend: function() {
                $('#university-programs-container').html('<div class="loading">Loading programs...</div>');
            },
            success: function(response) {
                $('#university-programs-container').html(response);
            }
        });
    }

    // ════════════════════════════════════════════════
    //  University Search (for [university_search])
    // ════════════════════════════════════════════════
    $(document).ready(function() {
        // Only run university search logic if the elements exist on the page
        if (!$('#university-cards').length) return;

        function loadUniversities(page) {
            page = page || 1;
            $.ajax({
                url: upd_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'load_universities',
                    keyword: $('#search-keyword').val() || '',
                    country: $('#filter-country').val() || '',
                    city:    $('#filter-city').val() || '',
                    type:    $('#filter-type').val() || '',
                    page:    page
                },
                beforeSend: function() {
                    $('.university-search-container .loading').css('display', 'flex');
                },
                success: function(res) {
                    $('.university-search-container .loading').css('display', 'none');
                    $('#university-cards').html(res.html);
                    $('#pagination').html(res.pagination);
                }
            });
        }

        loadUniversities();

        // Debounced search input (300ms delay)
        $('#search-keyword').on('input', debounce(function() {
            loadUniversities();
        }, 300));

        $('#filter-country, #filter-city, #filter-type').on('change', function() {
            loadUniversities();
        });

        $('.clear-btn').on('click', function() {
            $('#search-keyword').val('');
            loadUniversities();
        });

        $(document).on('click', '.pagination span', function() {
            var page = $(this).data('page');
            if (page) loadUniversities(page);
        });

        // Load filter dropdowns
        $.post(upd_ajax.ajax_url, { action: 'get_filters' }, function(res) {
            if (res.countries) $('#filter-country').append(res.countries);
            if (res.cities)    $('#filter-city').append(res.cities);
            if (res.type)      $('#filter-type').append(res.type);
        });
    });

    // ════════════════════════════════════════════════
    //  Program Tabs & Pagination (for [university_programs])
    // ════════════════════════════════════════════════
    $(document).ready(function() {
        // Only run if the programs container exists
        if (!$('#university-programs-container').length) return;

        // Initial load
        loadProgramsAjax({ page: 1 });

        // Pagination clicks
        $(document).on('click', '.programs-pagination a', function(e) {
            e.preventDefault();
            if ($(this).hasClass('current') || $(this).hasClass('dots')) return;
            var page   = $(this).data('page');
            var level  = $('.upd-tabs button.active').data('tab') || '';
            var search = $('#upd-search').val() || '';
            loadProgramsAjax({ page: page, level: level, search: search });
        });

        // Tab clicks
        $(document).on('click', '.upd-tabs button', function() {
            var level = $(this).data('tab');
            $('.upd-tabs button').removeClass('active');
            $(this).addClass('active');
            var search = $('#upd-search').val() || '';
            loadProgramsAjax({ page: 1, level: level, search: search });
        });

        // Search input with debounce
        $(document).on('keyup', '#upd-search', debounce(function() {
            var level = $('.upd-tabs button.active').data('tab') || '';
            loadProgramsAjax({ page: 1, level: level, search: $(this).val() });
        }, 300));
    });

})(jQuery);