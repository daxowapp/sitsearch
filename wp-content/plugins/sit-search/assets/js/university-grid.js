jQuery(document).ready(function($) {
    'use strict';

    const UniversityGrid = {
        container: $('.university-grid-container'),
        grid: $('#university-grid'),
        pagination: $('#university-pagination'),
        loading: $('.university-grid-loading'),
        filters: {
            country: $('#country-filter'),
            sector: $('#sector-filter'),
            city: $('#city-filter'),
            search: $('#university-search')
        },
        currentPage: 1,
        postsPerPage: 12,
        currentLayout: 'grid',

        init: function() {
            this.bindEvents();
            this.getPostsPerPage();
            this.initializeFilters();
            this.initializeLayout();
        },

        initializeLayout: function() {
            const savedLayout = localStorage.getItem('sit_university_layout') || 'grid';
            this.setLayout(savedLayout);
        },

        setLayout: function(layout) {
            this.currentLayout = layout;
            localStorage.setItem('sit_university_layout', layout);

            // Update UI buttons
            $('.layout-btn').removeClass('active');
            $('.layout-btn[data-layout="' + layout + '"]').addClass('active');

            // Update Grid class
            if (layout === 'list') {
                this.grid.addClass('layout-list');
                this.grid.removeClass('columns-' + this.container.data('columns'));
            } else {
                this.grid.removeClass('layout-list');
                this.grid.addClass('columns-' + this.container.data('columns'));
            }
        },

        initializeFilters: function() {
            // Re-initialize filter elements in case they were added dynamically
            this.filters.search = $('#university-search');
            console.log('Search input found:', this.filters.search.length > 0);
        },

        bindEvents: function() {
            const self = this;

            // Use document delegation for all events to ensure they work with dynamic content
            
            // Layout toggle logic
            $(document).on('click', '.layout-btn', function(e) {
                e.preventDefault();
                const layout = $(this).data('layout');
                self.setLayout(layout);
            });

            // Country change - load cities for selected country
            $(document).on('change', '#country-filter', function() {
                const selectedCountry = $(this).val();
                console.log('Country changed to:', selectedCountry);
                self.loadCitiesForCountry(selectedCountry);
                // Auto-filter when country changes
                self.currentPage = 1;
                self.filterUniversities();
            });

            // Apply filters button
            $(document).on('click', '#apply-filters', function(e) {
                e.preventDefault();
                console.log('Apply filters clicked');
                self.currentPage = 1;
                self.filterUniversities();
            });

            // Reset filters button
            $(document).on('click', '#reset-filters, #reset-filters-inline', function(e) {
                e.preventDefault();
                console.log('Reset filters clicked');
                self.resetFilters();
            });

            // Sector change - auto filter
            $(document).on('change', '#sector-filter', function() {
                const selectedSector = $(this).val();
                console.log('Sector changed to:', selectedSector);
                self.currentPage = 1;
                self.filterUniversities();
            });

            // City change - auto filter
            $(document).on('change', '#city-filter', function() {
                const selectedCity = $(this).val();
                console.log('City changed to:', selectedCity);
                self.currentPage = 1;
                self.filterUniversities();
            });

            // Search button click - use document delegation
            $(document).on('click', '#search-universities', function(e) {
                e.preventDefault();
                console.log('Search button clicked');
                self.currentPage = 1;
                self.filterUniversities();
            });

            // Search on enter key - use document delegation
            $(document).on('keypress', '#university-search', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    console.log('Search enter key pressed');
                    self.currentPage = 1;
                    self.filterUniversities();
                }
            });

            // Filter on enter key
            Object.values(this.filters).forEach(function(filter) {
                filter.on('keypress', function(e) {
                    if (e.which === 13) { // Enter key
                        e.preventDefault();
                        self.currentPage = 1;
                        self.filterUniversities();
                    }
                });
            });

            // Pagination clicks
            $(document).on('click', '.university-grid-pagination .page-numbers', function(e) {
                e.preventDefault();
                
                if ($(this).hasClass('current') || $(this).hasClass('dots')) {
                    return;
                }

                const href = $(this).attr('href');
                if (href) {
                    const urlParams = new URLSearchParams(href.split('?')[1]);
                    const page = urlParams.get('paged') || 1;
                    self.currentPage = parseInt(page);
                    self.filterUniversities();
                }
            });
        },

        loadCitiesForCountry: function(country) {
            const self = this;
            const citySelect = this.filters.city;

            console.log('Loading cities for country:', country);

            // Show loading in city dropdown
            citySelect.prop('disabled', true);
            citySelect.html('<option value="">Loading cities...</option>');

            $.ajax({
                url: university_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_cities_by_country',
                    nonce: university_ajax.nonce,
                    country: country || '' // Send empty string if no country
                },
                success: function(response) {
                    console.log('Cities response:', response);
                    
                    if (response.success && response.data.cities) {
                        // Rebuild city dropdown
                        let cityOptions = '<option value="">All Cities</option>';
                        response.data.cities.forEach(function(city) {
                            cityOptions += '<option value="' + city + '">' + city + '</option>';
                        });
                        
                        citySelect.html(cityOptions);
                        citySelect.prop('disabled', false);
                        
                        console.log('Cities loaded successfully:', response.data.cities.length + ' cities');
                    } else {
                        console.error('Failed to load cities:', response.data);
                        citySelect.html('<option value="">All Cities</option>');
                        citySelect.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading cities:', error);
                    console.error('Response text:', xhr.responseText);
                    citySelect.html('<option value="">All Cities</option>');
                    citySelect.prop('disabled', false);
                }
            });
        },

        getPostsPerPage: function() {
            // Try to get posts per page from container data attribute or use default
            const containerData = this.container.data('posts-per-page');
            if (containerData) {
                this.postsPerPage = parseInt(containerData);
            }
        },

        getFilterValues: function() {
            const values = {
                country: $('#country-filter').val() || '',
                sector: $('#sector-filter').val() || '',
                city: $('#city-filter').val() || '',
                search: $('#university-search').val() || ''
            };
            console.log('Current filter values:', values);
            return values;
        },

        filterUniversities: function() {
            const self = this;
            const filterValues = this.getFilterValues();

            console.log('Filtering universities with:', filterValues);

            // Show loading
            this.showLoading();

            // Scroll to grid (optional)
            $('html, body').animate({
                scrollTop: this.container.offset().top - 100
            }, 300);

            $.ajax({
                url: university_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'filter_universities',
                    nonce: university_ajax.nonce,
                    country: filterValues.country,
                    sector: filterValues.sector,
                    city: filterValues.city,
                    search: filterValues.search,
                    paged: this.currentPage,
                    posts_per_page: this.postsPerPage
                },
                success: function(response) {
                    console.log('Filter response:', response);
                    
                    if (response.success) {
                        // Update grid content
                        self.grid.html(response.data.html);
                        
                        // Ensure layout is preserved upon AJAX refresh
                        self.setLayout(self.currentLayout);
                        
                        // Update pagination
                        self.pagination.html(response.data.pagination);
                        
                        // Show results count
                        self.updateResultsCount(response.data.found_posts);
                        
                        // Hide loading
                        self.hideLoading();
                        
                        // Trigger custom event
                        $(document).trigger('universityGridFiltered', {
                            filters: filterValues,
                            results: response.data
                        });
                        
                    } else {
                        console.error('Filter request failed:', response.data);
                        self.showError('Failed to load universities. Please try again.');
                        self.hideLoading();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    
                    // Specific handling for non-critical errors or cache issues
                    if (xhr.status === 403 && xhr.responseText === "-1") {
                        self.showError('Search timed out or session expired. Please refresh the page and try again.');
                    } else {
                        self.showError('Unable to load universities at the moment. Please check your connection or try again later.');
                    }
                    self.hideLoading();
                }
            });
        },

        resetFilters: function() {
            console.log('Resetting all filters');
            
            // Clear all filters using direct selectors
            $('#country-filter').val('');
            $('#sector-filter').val('');
            $('#city-filter').val('');
            $('#university-search').val('');

            // Reset current page
            this.currentPage = 1;

            // Load all cities (for Turkey and Northern Cyprus)
            this.loadCitiesForCountry('');

            // Reload universities
            this.filterUniversities();
        },

        showLoading: function() {
            this.loading.show();
            this.grid.css('opacity', '0.5');
        },

        hideLoading: function() {
            this.loading.hide();
            this.grid.css('opacity', '1');
        },

        updateResultsCount: function(count) {
            // Remove existing count
            $('.university-results-count').remove();
            
            // Add new count
            if (count > 0) {
                const countText = count === 1 ? 
                    count + ' university found' : 
                    count + ' universities found';
                
                const countHtml = '<div class="university-results-count">' + countText + '</div>';
                
                if ($('.university-results-count-container').length > 0) {
                    $('.university-results-count-container').html(countHtml);
                } else {
                    this.container.prepend(countHtml);
                }
            } else if ($('.university-results-count-container').length > 0) {
                $('.university-results-count-container').empty();
            }
        },

        showError: function(message) {
            // Remove existing error messages
            $('.university-error-message').remove();
            
            // Show error message
            this.grid.html(
                '<div class="university-error-message" style="text-align: center; padding: 2rem; color: #E10B17; background: rgba(225, 11, 23, 0.1); border-radius: 8px; margin: 1rem 0; border: 1px solid rgba(225, 11, 23, 0.3);">' +
                '<strong>Error:</strong> ' + message +
                '</div>'
            );
        }
    };

    // Initialize if container exists
    if (UniversityGrid.container.length > 0) {
        UniversityGrid.init();
    }
    
    // Also initialize after a short delay to ensure all elements are loaded
    setTimeout(function() {
        if (UniversityGrid.container.length > 0) {
            UniversityGrid.initializeFilters();
        }
    }, 500);

    // Expose to global scope for external access
    window.UniversityGrid = UniversityGrid;

    // Additional utility functions
    window.refreshUniversityGrid = function() {
        UniversityGrid.filterUniversities();
    };

    window.setUniversityFilters = function(filters) {
        if (filters.country) {
            UniversityGrid.filters.country.val(filters.country);
            UniversityGrid.loadCitiesForCountry(filters.country);
        }
        if (filters.sector) UniversityGrid.filters.sector.val(filters.sector);
        if (filters.city) UniversityGrid.filters.city.val(filters.city);
        UniversityGrid.currentPage = 1;
        UniversityGrid.filterUniversities();
    };
    
});