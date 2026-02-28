jQuery(document).ready(function ($) {

    // Global variables
    var courseNameValue = "";
    var priceRange = "";

    // Helper function for export popup
    function closeExportPopup() {
        $("#exportModal").hide();
    }

    // Centralized function to update applied filters display
    function updateAppliedFiltersDisplay() {
        // Target only the main filter container, not sidebar duplicates
        const filtersContainer = $('.selected-filters-display .filtersApplied').first();
        const selectedFiltersDisplay = $('.selected-filters-display').first();

        // Clear existing filters to prevent duplicates
        filtersContainer.empty();

        let hasFilters = false;

        // Check for checked degree filters (handle duplicates)
        const uniqueDegrees = new Map();
        $('.degree-checkbox:checked').each(function () {
            const degreeId = $(this).val();
            const degreeName = $(this).siblings('.filter-checkbox-text').text();
            if (!uniqueDegrees.has(degreeId)) {
                uniqueDegrees.set(degreeId, degreeName);
            }
        });
        uniqueDegrees.forEach((degreeName, degreeId) => {
            const filterBut = `<a href='javascript:void(0)' class='filter-btn active degree-filter' data-degree-id='${degreeId}'>${degreeName} <span class='remove-filter' data-filter-type='degree' data-filter-value='${degreeId}'>×</span></a>`;
            filtersContainer.append(filterBut);
            hasFilters = true;
        });

        // Check for checked language filters (handle duplicates)
        const uniqueLanguages = new Set();
        $('.language-checkbox:checked').each(function () {
            const language = $(this).val();
            uniqueLanguages.add(language);
        });
        uniqueLanguages.forEach(language => {
            const filterBut = `<a href='javascript:void(0)' class='filter-btn active language-filter' data-language='${language}'>${language} <span class='remove-filter' data-filter-type='language' data-filter-value='${language}'>×</span></a>`;
            filtersContainer.append(filterBut);
            hasFilters = true;
        });

        // Check for checked university filters (handle duplicates)
        const uniqueUniversities = new Set();
        $('.university-checkbox:checked').each(function () {
            const university = $(this).val();
            uniqueUniversities.add(university);
        });
        uniqueUniversities.forEach(university => {
            const filterBut = `<a href='javascript:void(0)' class='filter-btn active university-filter' data-university='${university}'>${university} <span class='remove-filter' data-filter-type='university' data-filter-value='${university}'>×</span></a>`;
            filtersContainer.append(filterBut);
            hasFilters = true;
        });

        // Check for checked city filters (handle duplicates)
        const uniqueCities = new Set();
        $('.city-checkbox:checked').each(function () {
            const cityId = $(this).val();
            const cityName = $(this).siblings('.filter-checkbox-text').text();
            uniqueCities.add(JSON.stringify({ id: cityId, name: cityName }));
        });
        uniqueCities.forEach(cityJson => {
            const city = JSON.parse(cityJson);
            const filterBut = `<a href='javascript:void(0)' class='filter-btn active city-filter' data-city='${city.id}'>${city.name} <span class='remove-filter' data-filter-type='city' data-filter-value='${city.id}'>×</span></a>`;
            filtersContainer.append(filterBut);
            hasFilters = true;
        });

        // Check for search parameter
        const urlParams = new URLSearchParams(window.location.search);
        const searchTerm = urlParams.get('search');
        if (searchTerm) {
            const filterBut = `<a href='javascript:void(0)' class='filter-btn active search-filter' data-search='${searchTerm}'>Search: "${searchTerm}" <span class='remove-filter' data-filter-type='search' data-filter-value='${searchTerm}'>×</span></a>`;
            filtersContainer.append(filterBut);
            hasFilters = true;
        }

        // Check for duration filter from URL (since it's not a checkbox)
        const duration = urlParams.get('duration');
        if (duration) {
            const filterBut = `<a href='javascript:void(0)' class='filter-btn active duration-filter' data-duration='${duration}'>Duration: ${duration} <span class='remove-filter' data-filter-type='duration' data-filter-value='${duration}'>×</span></a>`;
            filtersContainer.append(filterBut);
            hasFilters = true;
        }

        // Check for scholarship filter from URL (since it's not a checkbox)
        const scholarship = urlParams.get('isScholarShip');
        if (scholarship) {
            const filterBut = `<a href='javascript:void(0)' class='filter-btn active scholarship-filter' data-scholarship='${scholarship}'>Scholarship: ${scholarship} <span class='remove-filter' data-filter-type='scholarship' data-filter-value='${scholarship}'>×</span></a>`;
            filtersContainer.append(filterBut);
            hasFilters = true;
        }

        // Check for price range filter from inputs
        const minPrice = $('.min-range').val();
        const maxPrice = $('.max-range').val();
        if (minPrice || maxPrice) {
            const priceText = `Price: ${minPrice || '0'} - ${maxPrice || '∞'} USD`;
            const filterBut = `<a href='javascript:void(0)' class='filter-btn active price-filter' data-min-price='${minPrice}' data-max-price='${maxPrice}'>${priceText} <span class='remove-filter' data-filter-type='price' data-filter-value='price'>×</span></a>`;
            filtersContainer.append(filterBut);
            hasFilters = true;
        }

        // Show/hide the selected filters display based on whether there are filters
        if (hasFilters) {
            selectedFiltersDisplay.removeClass('empty');
        } else {
            selectedFiltersDisplay.addClass('empty');
        }
    }

    // Initialize checkboxes based on URL parameters on page load
    function initializeFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);

        // Initialize degree checkboxes
        const degrees = urlParams.getAll('level[]').concat(urlParams.getAll('level'));
        degrees.forEach(degreeId => {
            if (degreeId) {
                $(`.degree-checkbox[value="${degreeId}"]`).prop('checked', true).closest('.filter-checkbox-label').addClass('active');
            }
        });

        // Initialize language checkboxes
        const languages = urlParams.getAll('language[]').concat(urlParams.getAll('language'));
        languages.forEach(language => {
            if (language) {
                $(`.language-checkbox[value="${language}"]`).prop('checked', true).closest('.filter-checkbox-label').addClass('active');
            }
        });

        // Initialize university checkboxes
        const universities = urlParams.getAll('university[]').concat(urlParams.getAll('university'));
        universities.forEach(university => {
            if (university) {
                $(`.university-checkbox[value="${university}"]`).prop('checked', true).closest('.filter-checkbox-label').addClass('active');
            }
        });

        // Initialize city checkboxes
        const cities = urlParams.getAll('city[]').concat(urlParams.getAll('city'));
        cities.forEach(cityId => {
            if (cityId) {
                $(`.city-checkbox[value="${cityId}"]`).prop('checked', true).closest('.filter-checkbox-label').addClass('active');
            }
        });
    }

    // Initialize on page load
    initializeFiltersFromURL();
    updateAppliedFiltersDisplay();


    function createCard(p) {
        return `
            <div class="program-card">
            <a href="${p.url}"><h4>${p.title}</h4></a>
            <p><strong>University</strong> ${p.university}</p>
            <p><strong>Language</strong> ${p.language}</p>
            <p><strong>Tuition fee</strong> <mark>${p.tuition}</mark></p>
            <p><strong>Discounted Fees</strong> <mark>${p.discounted}</mark></p>
            <p><strong>Period</strong> ${p.period}</p>
            </div>`;
    }
    function loadPrograms2(keyword = '') {
        $.ajax({
            url: upd_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'program_search_ajax',
                nonce: upd_ajax.nonce,
                keyword: keyword
            },
            success: function (response) {
                const container = $('#program-results');
                const total = $('.program-results-count');
                container.html('');

                let slides = '';
                for (let i = 0; i < response.length; i += 4) {
                    let group = response.slice(i, i + 4).map(createCard).join('');
                    slides += `<div class="swiper-slide"><div class="program-grid">${group}</div></div>`;
                }
                $('.program-results-main .jet-ajax-search__spinner-holder').hide();
                $('.program-results-main .program-results-header').css('display', 'flex');
                container.html(slides);
                total.text(`${response.length} Results`);
                $('.swiper-buttons').show();
                if (window.programSwiper) window.programSwiper.destroy(true, true);
                window.programSwiper = new Swiper(".program-slider", {
                    slidesPerView: 1,
                    spaceBetween: 30,
                    pagination: {
                        el: ".swiper-pagination-1",
                        clickable: true
                    },
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev"
                    }
                });
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    $('#program-search-input').on('input', function () {
        const keyword = $(this).val().trim();
        $('#program-results').html('');
        $('.program-results-count').html('');
        $('.swiper-buttons').hide();
        if (!keyword) {
            $('.program-results-main').hide();
            $('.program-results-main .jet-ajax-search__spinner-holder').hide();
        }
        else {
            $('.program-results-main').show();
            $('.program-results-main .jet-ajax-search__spinner-holder').show();
            loadPrograms2(keyword);
        }

    });

    $('#program-search-btn').on('click', function () {
        const keyword = $('#program-search-input').val().trim();
        $('#program-results').html('');
        $('.program-results-count').html('');
        $('.swiper-buttons').hide();
        if (!keyword) {
            $('.program-results-main').hide();
            $('.program-results-main .jet-ajax-search__spinner-holder').hide();
        }
        else {
            $('.program-results-main').show();
            $('.program-results-main .jet-ajax-search__spinner-holder').show();
            loadPrograms2(keyword);
        }
        loadPrograms2(keyword);
    });

    // Handle university checkbox changes
    $(document).on('change', '.university-checkbox', function () {
        const $label = $(this).closest('.filter-checkbox-label');
        const universityName = $(this).val();

        if ($(this).is(':checked')) {
            $label.addClass('active');
        } else {
            $label.removeClass('active');
        }

        // Update applied filters display
        updateAppliedFiltersDisplay();

        // Auto-apply university filter when checkbox changes
        applyUniversityFilter();
    });

    // Handle degree checkbox changes
    $(document).on('change', '.degree-checkbox', function () {
        const $label = $(this).closest('.filter-checkbox-label');
        const degreeId = $(this).val();
        const degreeName = $(this).siblings('.filter-checkbox-text').text();

        if ($(this).is(':checked')) {
            $label.addClass('active');
        } else {
            $label.removeClass('active');
        }

        // Update applied filters display
        updateAppliedFiltersDisplay();

        applyAllFilters();
    });

    // Handle language checkbox changes
    $(document).on('change', '.language-checkbox', function () {
        const $label = $(this).closest('.filter-checkbox-label');
        const languageName = $(this).val();

        if ($(this).is(':checked')) {
            $label.addClass('active');
        } else {
            $label.removeClass('active');
        }

        // Update applied filters display
        updateAppliedFiltersDisplay();

        // Auto-apply language filter when checkbox changes
        applyLanguageFilter();
    });

    // Handle city checkbox changes
    $(document).on('change', '.city-checkbox', function () {
        const $label = $(this).closest('.filter-checkbox-label');
        const cityId = $(this).val();

        if ($(this).is(':checked')) {
            $label.addClass('active');
        } else {
            $label.removeClass('active');
        }

        // Update applied filters display
        updateAppliedFiltersDisplay();

        // Auto-apply city filter when checkbox changes
        applyAllFilters();
    });

    // Function to apply all filters together
    function applyAllFilters() {
        const searchParams = new URLSearchParams();

        // Preserve existing URL parameters except the ones we're updating
        let urlParams = new URLSearchParams(window.location.search);

        // Add existing parameters except university, language, city arrays
        for (let [key, value] of urlParams.entries()) {
            if (key !== 'university' && key !== 'university[]' && key !== 'language' && key !== 'language[]' && key !== 'level' && key !== 'level[]' && key !== 'city' && key !== 'city[]') {
                searchParams.append(key, value);
            }
        }

        // Add selected universities
        $(".university-checkbox:checked").each(function () {
            searchParams.append('university[]', $(this).val());
        });

        // Add selected languages
        $(".language-checkbox:checked").each(function () {
            searchParams.append('language[]', $(this).val());
        });

        // Add selected degrees
        $(".degree-checkbox:checked").each(function () {
            searchParams.append('level[]', $(this).val());
        });

        // Add selected cities
        $(".city-checkbox:checked").each(function () {
            searchParams.append('city[]', $(this).val());
        });

        // Redirect with new parameters
        window.location.href = (location.protocol + '//' + location.host + location.pathname) + "?" + searchParams.toString();
    }

    // Function to apply university filter
    function applyUniversityFilter() {
        applyAllFilters();
    }

    // Function to apply language filter
    function applyLanguageFilter() {
        applyAllFilters();
    }

    // Handle search functionality
    function handleSearch() {
        const searchValue = $('#search-university').val().trim();
        if (searchValue.length > 1) {
            // Trigger the global AI search that we will expose from ai-search.js
            if (typeof window.performGlobalAiSearch === 'function') {
                window.performGlobalAiSearch(searchValue);
                return;
            }
        }
        
        const currentUrl = new URL(window.location.href);

        if (searchValue) {
            currentUrl.searchParams.set('search', searchValue);
        } else {
            currentUrl.searchParams.delete('search');
        }

        // Navigate to new URL with search parameter if AI search fails
        window.location.href = currentUrl.toString();
    }

    // Search button click handler
    $(document).on('click', '.ProgramArchivePage-search-button, .results-search-btn', function (e) {
        e.preventDefault();
        handleSearch();
    });

    // Search on Enter key press
    $(document).on('keypress', '#search-university', function (e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            handleSearch();
        }
    });

    // Also handle other search button classes that might exist
    $(document).on('click', '.search-by-name button, .search-by-name-campus button', function (e) {
        e.preventDefault();
        handleSearch();
    });

    // Use select2:select for better reliability with the library
    $("#sit-search select").on("select2:select", function (e) {
        console.log("Select2 selection made on:", $(this).attr("name"));
        let selects = $("#sit-search select");
        let selectedCount = 0;
        let emptyName = "";

        selects.each(function () {
            let val = $(this).val();
            let name = $(this).attr("name");

            if (val && val !== "0") {
                selectedCount++;
            } else if (!emptyName) {
                emptyName = name;
            }
        });

        console.log("Status:", { count: selectedCount, next: emptyName });

        if (selectedCount === 4) {
            // All filled
        } else if (selectedCount === 0) {
            // None filled
        } else if (emptyName) {
            console.log("Attempting to open:", emptyName);
            setTimeout(function () {
                let target = $('select[name="' + emptyName + '"]');
                console.log("Target found:", target.length);
                target.select2('open');
            }, 200);
        }
    });

    $('#trigger').guides({
        guides: [{
            element: $('#sit-search'),
            html: 'Welcome to Guides.js'
        }]
    });
    $('.top-universities').owlCarousel({
        loop: false,
        nav: true,
        dots: false,
        autoplay: true,
        autoplayTimeout: 3000,
        items: 3,
        responsive: {
            0: {         // from 0px up
                items: 1 // show 1 item on mobile
            },
            600: {       // from 600px up
                items: 1 // show 2 items on tablets
            },
            1000: {      // from 1000px up
                items: 3 // show 3 items on desktops
            }
        }
    });
    $('.other-universities').owlCarousel({
        items: 2,
        nav: true, // Enable arrows
        navText: ['<span class="owl-prev"><img src="https://search.studyinturkiye.com/wp-content/uploads/2025/03/reshot-icon-arrow-chevron-left-975UQXVKZF.svg" alt=""></span>', '<span class="owl-next"><img src="https://search.studyinturkiye.com/wp-content/uploads/2025/03/reshot-icon-arrow-chevron-right-WDGHUKQ634.svg" alt=""></span>'],
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 2
            }
        }
    });

    $('.select2').select2({
        placeholder: 'Select an option',
    });
    $('#search-program').on('keyup', function () {
        var searchText = $(this).val().toLowerCase();

        $('.university-programs .university-box-wrapper').each(function () {
            var universityText = $(this)
                .find('.university-content p:not(.country), .university-content a h3')
                .text()
                .toLowerCase();

            if (universityText.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    // Handle sort dropdown change - supports both .sort-dropdown class and #sort-dropdown ID
    $(document).on('change', '.sort-dropdown, #sort-dropdown, .results-sort-select', function () {
        var selectedOption = $(this).val();
        var currentUrl = window.location.href;
        var sortParam = '';
        sortParam = 'sort=' + selectedOption;
        if (currentUrl.indexOf('?') !== -1) {
            if (currentUrl.indexOf('sort=') !== -1) {
                currentUrl = currentUrl.replace(/([?&])sort=[^&]*/, '$1' + sortParam);
            } else {
                currentUrl += '&' + sortParam;
            }
        } else {
            currentUrl += '?' + sortParam;
        }
        window.location.href = currentUrl;
    });
    $('button.accordion-button').click(function (e) {
        if ($(e.target).hasClass("collapsed")) {
            e.stopPropagation();
            $(this).attr("aria-expanded", true);
            $(this)
                .parents(".accordion-item")
                .find(".accordion-collapse")
                .addClass("show");
            $(this).removeClass('collapsed');
            return false;
        }
        else {
            e.stopPropagation();
            $(this).attr("aria-expanded", false);
            $(this)
                .parents(".accordion-item")
                .find(".accordion-collapse")
                .removeClass("show");
            $(this).addClass('collapsed');
            return false;
        }
    });
    $('button.btn.filter').click(function (e) {
        $(".new-sr-filter").toggleClass("d-none");
    });
    // Removed old conflicting filter apply code - using new system instead

    $("#course-filter-btn").click(function () {
        $("#applyFee").trigger("click");
    });

    $(document).on("click", ".mobileShowResult", function () {
        $(".sr-filter").fadeOut();
    });

    // Handle individual filter removal via X button
    $(document).on("click", ".remove-filter", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const filterType = $(this).data('filter-type');
        const filterValue = $(this).data('filter-value');

        if (filterType === 'university') {
            // Uncheck the university checkbox
            $(`.university-checkbox[value="${filterValue}"]`).prop('checked', false);
            $(`.university-checkbox[value="${filterValue}"]`).closest('.filter-checkbox-label').removeClass('active');
        } else if (filterType === 'language') {
            // Uncheck the language checkbox
            $(`.language-checkbox[value="${filterValue}"]`).prop('checked', false);
            $(`.language-checkbox[value="${filterValue}"]`).closest('.filter-checkbox-label').removeClass('active');
        } else if (filterType === 'degree') {
            // Uncheck the degree checkbox
            $(`.degree-checkbox[value="${filterValue}"]`).prop('checked', false);
            $(`.degree-checkbox[value="${filterValue}"]`).closest('.filter-checkbox-label').removeClass('active');
        } else if (filterType === 'duration') {
            // Handle duration filter removal by redirecting without duration parameter
            let url = new URL(window.location);
            url.searchParams.delete('duration');
            window.location.href = url.toString();
            return;
        } else if (filterType === 'scholarship') {
            // Handle scholarship filter removal
            let url = new URL(window.location);
            url.searchParams.delete('isScholarShip');
            window.location.href = url.toString();
            return;
        } else if (filterType === 'price') {
            // Clear price range inputs
            $(".min-range, .max-range").val('');
        } else if (filterType === 'search') {
            // Handle search filter removal
            let url = new URL(window.location);
            url.searchParams.delete('search');
            // Also clear the search input
            $('#search-university').val('');
            window.location.href = url.toString();
            return;
        } else if (filterType === 'city') {
            // Uncheck the city checkbox
            $(`.city-checkbox[value="${filterValue}"]`).prop('checked', false);
            $(`.city-checkbox[value="${filterValue}"]`).closest('.filter-checkbox-label').removeClass('active');
        }

        // Update applied filters display
        updateAppliedFiltersDisplay();

        // Apply filters after removal
        applyAllFilters();
    });

    // Handle "Clear All" filters button
    $(document).on("click", ".clear-all-filters", function (e) {
        e.preventDefault();

        // Uncheck all checkboxes
        $(".university-checkbox, .language-checkbox, .degree-checkbox, .city-checkbox").prop('checked', false);
        $(".filter-checkbox-label").removeClass('active');

        // Clear price range inputs
        $(".min-range, .max-range").val('');

        // Clear search input
        $('#search-university').val('');

        // Remove active class from filter buttons
        $(".filter-btn").removeClass('active');
        $(".accordion-button").removeClass('active');

        // Update applied filters display
        updateAppliedFiltersDisplay();

        // Redirect to base URL without filters
        let urlParams = new URLSearchParams(window.location.search);
        const searchParams = new URLSearchParams();

        // Preserve only essential parameters
        if (urlParams.get('speciality')) searchParams.append('speciality', urlParams.get('speciality'));
        if (urlParams.get('country')) searchParams.append('country', urlParams.get('country'));
        if (urlParams.get('level')) searchParams.append('level', urlParams.get('level'));
        if (urlParams.get('uni-id')) searchParams.append('uni-id', urlParams.get('uni-id'));

        window.location.href = (location.protocol + '//' + location.host + location.pathname) + "?" + searchParams.toString();
    });

    // Clear filters functionality
    $(document).on("click", ".mob-clear-filter", function () {
        // Uncheck all checkboxes
        $(".university-checkbox, .language-checkbox, .degree-checkbox, .city-checkbox").prop('checked', false);
        $(".filter-checkbox-label").removeClass('active');

        // Clear price range inputs
        $(".min-range, .max-range").val('');

        // Remove active class from filter buttons
        $(".filter-btn").removeClass('active');
        $(".accordion-button").removeClass('active');

        // Update applied filters display
        updateAppliedFiltersDisplay();

        // Redirect to base URL without filters
        let urlParams = new URLSearchParams(window.location.search);
        const searchParams = new URLSearchParams();

        // Preserve only essential parameters
        if (urlParams.get('speciality')) searchParams.append('speciality', urlParams.get('speciality'));
        if (urlParams.get('country')) searchParams.append('country', urlParams.get('country'));
        if (urlParams.get('level')) searchParams.append('level', urlParams.get('level'));
        if (urlParams.get('uni-id')) searchParams.append('uni-id', urlParams.get('uni-id'));

        window.location.href = (location.protocol + '//' + location.host + location.pathname) + "?" + searchParams.toString();
    });

    $(document).on('change', ".sortbyWrap input[name*='sortBy']", function (e) {
        var sortByDropValue = $(this).val();
        var currentUrl = new URL(window.location.href);
        if (location.href.indexOf('sort-by=') > -1) {
            currentUrl.searchParams.set('sort-by', sortByDropValue);
        } else {
            currentUrl.searchParams.append('sort-by', sortByDropValue);
        }
        window.location.href = currentUrl.toString();
    });

    $(".selected-filters-display .filtersApplied").on("click", "a", function () {
        var id = $(this).attr("id");
        var idTag = id.replace('label-', '').trim();

        const currentSelectedElem = $(
            ".filter-list .accordion .filter-btn#" + idTag
        );

        currentSelectedElem.removeClass("active");
        const filteredLength = currentSelectedElem
            .parents(".accordion-body")
            .find(".filter-btn.active").length;
        if (filteredLength == 0) {
            currentSelectedElem
                .parents(".accordion-item")
                .find(".accordion-button")
                .removeClass("active");
        }
        if ($(this).hasClass('priceRangeFilter')) {
            // Reset price range inputs
            $(".min-range, .max-range").val('');
            priceRange = "";
            $("#panelsStayOpen-headingThree .accordion-button").removeClass('active');
        }
        if ($(this).hasClass('courseNameFilter')) {
            $(".course-filter input").val('');
            courseNameValue = "";
            $(".course-filter .btn").addClass('disabled');
            $("#panelsStayOpen-headingEight .accordion-button").removeClass('active');
        }
        $(this).remove();
        //   clearFilterButton();
    });
    // var isFilterApplied = false;
    // DISABLED OLD FILTER SYSTEM - Using new checkbox-based system instead
    /*
    $(".filter-list .accordion .filter-btn").click(async function(e) {
        e.preventDefault();
        var filterText = $(this).text();
        var id = filterText
            .toLowerCase()
            .replace(/[\*\^\'\!\&\(\)\.\£\-]/g, "")
            .split(" ")
            .join("");
        const filterHeading = $(this).parents('.accordion-item').find('.accordion-header').attr('id');
        const filterName = filterHeading.split('-')[1];
        const uniqueID = `${filterName}-${id}`
        const pageNumber = 1;
        const mode = $(window).width() > 1024 ? 'Desktop' : 'Mobile';
        var isItSelected = $(this).attr("class");
        if (isItSelected.indexOf("active") == -1) {

            $(this).addClass("active").siblings().removeClass("active");
            $(this)
                .parents(".accordion-item")
                .find(".accordion-button")
                .addClass("active");
            if(uniqueID == "headingSix-yes"){
                var newFilterBut = `<a href='javascript:void(0)' class='filter-btn active' id='label-${uniqueID.trim()}'>Scholarships available <span class='remove-filter' data-filter-id='${uniqueID.trim()}'>×</span></a>`;
            } else {
                var newFilterBut = `<a href='javascript:void(0)' class='filter-btn active' id='label-${uniqueID.trim()}'>${filterText} <span class='remove-filter' data-filter-id='${uniqueID.trim()}'>×</span></a>`;
            }
            $(".selected-filters-display .filtersApplied").find(`[id*="label-${filterName}"]`).remove();

            $(newFilterBut).appendTo(".selected-filters-display .filtersApplied");
            $(this).attr("id", uniqueID.trim());
        } else {
            $(this).removeClass("active");
            const filteredLength = $(this)
                .parents(".accordion-body")
                .find(".filter-btn.active").length;
            if (filteredLength == 0) {
                $(this)
                    .parents(".accordion-item")
                    .find(".accordion-button")
                    .removeClass("active");
            }
            $(".selected-filters-display .filtersApplied #label-" + uniqueID.trim()).remove();
        }
        const searchParams = new URLSearchParams();
        e.preventDefault();
        var mainFilterValue = $(this).parents('.accordion-item').find('.accordion-header').attr('value');
        var mainFilterText = $(this).attr('data-value');
        var sortBy = $('.sort-dropdown').val();
        let speciality = $('select[name="speciality"]').val();
        let country = $('select[name="country"]').val();
        let type = $('select[name="univerity-type"]').val();
        if (sortBy) {
            sortBy = sortBy;
        } else {
            sortBy = 'featured';
        }
        $(".filter-btn.active").each(function() {

            const filterValue = $(this).parents('.accordion-item').find('.accordion-header').attr('value');
            var filterText = $(this).attr('data-value');

            if (filterValue != undefined && filterValue != mainFilterValue && filterText != mainFilterText && filterValue != 'sort-by') {

                searchParams.append(filterValue, filterText);

            }

        })

        if ($(".min-range").val().length && $(".max-range").val().length) {
            priceRange = $(".min-range").val() + " - " + $(".max-range").val();
            searchParams.append('feeFiter', priceRange);
        }
        
        // Handle multiple university selections
        $(".university-checkbox:checked").each(function() {
            searchParams.append('university[]', $(this).val());
        });
        
        // Handle multiple language selections
        $(".language-checkbox:checked").each(function() {
            searchParams.append('language[]', $(this).val());
        });
        
        // Handle multiple degree selections
        $(".degree-checkbox:checked").each(function() {
            searchParams.append('level[]', $(this).val());
        });

        // Apply the current filter being clicked
        if (mainFilterValue != undefined && mainFilterValue != 'sort-by') {
            searchParams.append(mainFilterValue, mainFilterText);
        }
        if (location.href.indexOf('sort=') > -1) {
            searchParams.set('sort', sortBy);
        } else {
            searchParams.append('sort', sortBy);
        }
        if (location.href.indexOf('speciality=') > -1) {
            searchParams.set('speciality', speciality);
        } else {
            searchParams.append('speciality', speciality);
        }
        if (location.href.indexOf('country=') > -1) {
            searchParams.set('country', country);
        } else {
            searchParams.append('country', country);
        }

         if (location.href.indexOf('univerity-type=') > -1) {
            searchParams.set('univerity-type', type);
        } else {
            searchParams.append('univerity-type', type);
        }
    });
    */ // END DISABLED OLD FILTER SYSTEM

    // Removed old price range filter code - using new system instead
    $(".range-input input").on("keydown change paste keyup", function (e) {
        var minRange = $(".min-range").val();
        var maxRange = $(".max-range").val();

        // Update applied filters display when price range changes
        updateAppliedFiltersDisplay();

        if (minRange.length && maxRange.length) {
            var mathValue = Math.max(minRange, maxRange)
            if (mathValue > minRange) {
                $(".range-error").slideUp(500);
            } else {
                $(".range-error").slideDown(500);
            }
        }
    });
    $('.single-sit-program .page-content ul li,.single-sit-university .page-content ul li').on('click', function (e) {
        e.preventDefault();

        const targetSection = $(this).data('id');

        $('html, body').animate({
            scrollTop: $('#' + targetSection).offset().top
        }, 800);

        $('.page-content ul li').removeClass('active');
        $(this).addClass('active');
    });
    $('.other-universities').show();
    $('.top-universities').show();
    // Removed conflicting search button handler - using new filter system instead
    $('.trigger-modal').on('click', function () {
        $('#fresconsultpoup a').click();
    });
    // Removed conflicting campus filter apply code
    // Removed conflicting campus search button handler
    $(document).on("click", function (event) {
        const $popup = $("#exportModal");
        const $content = $(".export-popup");
        const $button = $("#openExportPopup");

        if (
            $popup.is(":visible") &&
            !$content.is(event.target) &&
            $content.has(event.target).length === 0 &&
            !$button.is(event.target) &&
            $button.has(event.target).length === 0
        ) {
            closeExportPopup();
        }
    });

    // Handle Duration and Scholarship filter button clicks
    $(document).on('click', '.filter-button', function (e) {
        e.preventDefault();
        const $button = $(this);
        const filterType = $button.data('filter');
        const filterValue = $button.data('value');

        // Toggle active state visually
        if ($button.hasClass('active')) {
            // If already active, remove the filter
            $button.removeClass('active');

            // Remove from URL and navigate
            let url = new URL(window.location);
            if (filterType === 'duration') {
                url.searchParams.delete('duration');
            } else if (filterType === 'scholarship') {
                url.searchParams.delete('isScholarShip');
            }
            window.location.href = url.toString();
        } else {
            // Add the filter
            $button.addClass('active');
            $button.siblings('.filter-button').removeClass('active'); // Only one duration at a time

            // Add to URL and navigate
            let url = new URL(window.location);
            if (filterType === 'duration') {
                url.searchParams.set('duration', filterValue);
            } else if (filterType === 'scholarship') {
                url.searchParams.set('isScholarShip', filterValue);
            }
            window.location.href = url.toString();
        }
    });

    // Handle Price Range Apply - apply price filter when user presses Enter or blur
    $(document).on('keypress', '#minPrice, #maxPrice', function (e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            applyPriceFilter();
        }
    });

    $(document).on('blur', '#minPrice, #maxPrice', function () {
        // Debounce: only apply if both fields have values or if clearing
        const minPrice = $('#minPrice').val();
        const maxPrice = $('#maxPrice').val();

        if (minPrice || maxPrice) {
            // Don't auto-apply on blur, wait for explicit action
        }
    });

    // Function to apply price filter
    function applyPriceFilter() {
        const minPrice = $('#minPrice').val();
        const maxPrice = $('#maxPrice').val();
        let url = new URL(window.location);

        if (minPrice) {
            url.searchParams.set('min_fee', minPrice);
        } else {
            url.searchParams.delete('min_fee');
        }

        if (maxPrice) {
            url.searchParams.set('max_fee', maxPrice);
        } else {
            url.searchParams.delete('max_fee');
        }

        window.location.href = url.toString();
    }
});