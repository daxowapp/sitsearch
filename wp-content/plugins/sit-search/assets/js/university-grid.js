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

        init: function() {
            this.bindEvents();
            this.getPostsPerPage();
            this.initializeFilters();
        },

        initializeFilters: function() {
            // Re-initialize filter elements in case they were added dynamically
            this.filters.search = $('#university-search');
            console.log('Search input found:', this.filters.search.length > 0);
        },

        bindEvents: function() {
            const self = this;

            // Use document delegation for all events to ensure they work with dynamic content
            
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
                    self.showError('Network error. Please check your connection and try again.');
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
                
                this.container.prepend(
                    '<div class="university-results-count" style="margin-bottom: 1rem; font-weight: 600; color: #E10B17; font-size: 1.1rem; text-align: center; padding: 0.5rem; background: rgba(225, 11, 23, 0.05); border-radius: 4px; border-left: 4px solid #E10B17;">' + 
                    countText + '</div>'
                );
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
    
    // Force apply clean styles via JavaScript
function forceCleanStyles() {
    // Apply card styles
    $('.university-card').each(function() {
        $(this).css({
            'background': '#ffffff',
            'border': '1px solid #e5e7eb',
            'border-radius': '8px',
            'box-shadow': 'none',
            'transform': 'none'
        });
    });

    // Apply logo styles
    $('.university-logo').each(function() {
        $(this).css({
            'background': '#fafafa',
            'padding': '2rem 1.5rem 1rem',
            'border-bottom': '1px solid #f0f0f0'
        });
    });

    // Apply button styles
    $('.university-actions .btn').each(function() {
        $(this).css({
            'border-radius': '4px',
            'text-transform': 'none',
            'letter-spacing': 'normal',
            'font-weight': '500',
            'padding': '0.75rem 1rem'
        });
    });

    // Apply hover effects
    $('.university-card').off('mouseenter mouseleave').on('mouseenter', function() {
        $(this).css('border-color', '#E10B17');
    }).on('mouseleave', function() {
        $(this).css('border-color', '#e5e7eb');
    });
}

// Apply styles when page loads and after filtering
$(document).ready(function() {
    forceCleanStyles();
});

// Reapply styles after AJAX filtering
$(document).on('universityGridFiltered', function() {
    setTimeout(forceCleanStyles, 100);
});
// Apply clean styles via JavaScript - permanent solution
function applyCleanStyles() {
    $('.university-card').each(function() {
        this.style.setProperty('background', '#ffffff', 'important');
        this.style.setProperty('border', '1px solid #e5e7eb', 'important');
        this.style.setProperty('border-radius', '8px', 'important');
        this.style.setProperty('box-shadow', 'none', 'important');
        this.style.setProperty('transform', 'none', 'important');
        this.style.setProperty('overflow', 'hidden', 'important');
        this.style.setProperty('height', '100%', 'important');
    });
    
    $('.university-logo').each(function() {
        this.style.setProperty('background', '#fafafa', 'important');
        this.style.setProperty('padding', '2rem 1.5rem 1rem', 'important');
        this.style.setProperty('border-bottom', '1px solid #f0f0f0', 'important');
        this.style.setProperty('min-height', '100px', 'important');
    });
    
    $('.university-content').each(function() {
        this.style.setProperty('padding', '1.5rem', 'important');
    });
    
    $('.university-actions .btn').each(function() {
        this.style.setProperty('border-radius', '4px', 'important');
        this.style.setProperty('text-transform', 'none', 'important');
        this.style.setProperty('letter-spacing', 'normal', 'important');
        this.style.setProperty('font-weight', '500', 'important');
        this.style.setProperty('padding', '0.75rem 1rem', 'important');
        this.style.setProperty('font-size', '0.875rem', 'important');
    });
    
    // Add hover effects
    $('.university-card').off('mouseenter mouseleave').on('mouseenter', function() {
        this.style.setProperty('border-color', '#E10B17', 'important');
        this.style.setProperty('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.08)', 'important');
    }).on('mouseleave', function() {
        this.style.setProperty('border-color', '#e5e7eb', 'important');
        this.style.setProperty('box-shadow', 'none', 'important');
    });
}

// Apply styles when page loads and after filtering
$(document).ready(function() {
    setTimeout(applyCleanStyles, 500); // Small delay to ensure elements are ready
});

$(document).on('universityGridFiltered', function() {
    setTimeout(applyCleanStyles, 200); // Reapply after AJAX filtering
});
});