/**
 * Shared Filter Scripts for All Result Pages
 * Handles view toggle, mobile filter toggle, and filter interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // View Toggle Functionality
    const viewButtons = document.querySelectorAll('.view-btn');
    const gridContainer = document.getElementById('programsGridContainer');
    const listContainer = document.getElementById('programsListContainer');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Update button states
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Toggle containers
            if (view === 'grid') {
                if (gridContainer) gridContainer.style.display = 'block';
                if (listContainer) listContainer.style.display = 'none';
            } else if (view === 'list') {
                if (gridContainer) gridContainer.style.display = 'none';
                if (listContainer) listContainer.style.display = 'block';
            }
            
            // Save preference
            localStorage.setItem('programView', view);
        });
    });
    
    // Restore saved view preference
    const savedView = localStorage.getItem('programView');
    if (savedView && savedView === 'list') {
        const listButton = document.querySelector('[data-view="list"]');
        if (listButton) {
            listButton.click();
        }
    }
    
    // Filter Button Interactions
    const filterButtons = document.querySelectorAll('.filter-button');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter');
            const filterValue = this.getAttribute('data-value');
            
            // Toggle active state
            this.classList.toggle('active');
            
            // Update URL parameters
            const url = new URL(window.location);
            if (this.classList.contains('active')) {
                url.searchParams.set(filterType === 'scholarship' ? 'isScholarShip' : filterType, filterValue);
            } else {
                url.searchParams.delete(filterType === 'scholarship' ? 'isScholarShip' : filterType);
            }
            
            // Navigate to new URL
            window.location.href = url.toString();
        });
    });
    
    // Checkbox Filter Interactions
    const checkboxes = document.querySelectorAll('.filter-checkbox-label input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('.filter-checkbox-label');
            const filterType = this.className.replace('-checkbox', '');
            const filterValue = this.value;
            
            // Update label state
            if (this.checked) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
            
            // Update URL parameters
            const url = new URL(window.location);
            const paramName = filterType === 'degree' ? 'level' : 
                             filterType === 'university' ? 'university' : 
                             filterType === 'language' ? 'language' : filterType;
            
            if (this.checked) {
                // Handle multiple selections for certain filters
                if (filterType === 'degree' || filterType === 'university') {
                    const currentValues = url.searchParams.getAll(paramName);
                    if (!currentValues.includes(filterValue)) {
                        url.searchParams.append(paramName, filterValue);
                    }
                } else {
                    url.searchParams.set(paramName, filterValue);
                }
            } else {
                // Remove the specific value
                if (filterType === 'degree' || filterType === 'university') {
                    const currentValues = url.searchParams.getAll(paramName);
                    url.searchParams.delete(paramName);
                    currentValues.forEach(value => {
                        if (value !== filterValue) {
                            url.searchParams.append(paramName, value);
                        }
                    });
                } else {
                    url.searchParams.delete(paramName);
                }
            }
            
            // Navigate to new URL
            window.location.href = url.toString();
        });
    });
    
    // Price Range Inputs
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    
    if (minPriceInput && maxPriceInput) {
        let priceTimeout;
        
        function updatePriceFilter() {
            clearTimeout(priceTimeout);
            priceTimeout = setTimeout(() => {
                const url = new URL(window.location);
                const minValue = minPriceInput.value;
                const maxValue = maxPriceInput.value;
                
                if (minValue) {
                    url.searchParams.set('min_fee', minValue);
                } else {
                    url.searchParams.delete('min_fee');
                }
                
                if (maxValue) {
                    url.searchParams.set('max_fee', maxValue);
                } else {
                    url.searchParams.delete('max_fee');
                }
                
                window.location.href = url.toString();
            }, 1000); // 1 second delay
        }
        
        minPriceInput.addEventListener('input', updatePriceFilter);
        maxPriceInput.addEventListener('input', updatePriceFilter);
    }
    
    // Clear All Filters
    const clearAllButton = document.querySelector('.clear-all-filters');
    if (clearAllButton) {
        clearAllButton.addEventListener('click', function() {
            const url = new URL(window.location);
            
            // Remove all filter parameters
            const filterParams = ['level', 'duration', 'language', 'min_fee', 'max_fee', 'university', 'isScholarShip'];
            filterParams.forEach(param => {
                url.searchParams.delete(param);
            });
            
            window.location.href = url.toString();
        });
    }
});

// Mobile filter toggle functionality
function toggleMobileFilters() {
    const sidebar = document.getElementById('filterSidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-hidden');
    }
}
