// assets/js/main.js - Main JavaScript Functions

// Functions executed when document is ready
$(document).ready(function() {
    
    // Initialize tooltips
    initTooltips();
    
    // Image preview
    initImagePreview();
    
    // Form validation
    initFormValidation();
    
    // Search functionality
    initSearch();
    
    // Filter functionality
    initFilters();
    
    // Smooth scroll
    initSmoothScroll();
    
    // Auto hide alerts
    autoHideAlerts();
});


// Initialize tooltips
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}


// Image upload preview
function initImagePreview() {
    $('input[type="file"][name="image"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Remove old preview
                $('.image-preview').remove();
                
                // Add new preview
                const preview = $('<img>')
                    .attr('src', e.target.result)
                    .addClass('image-preview img-fluid mt-2');
                
                $('input[type="file"][name="image"]').after(preview);
            };
            reader.readAsDataURL(file);
        }
    });
}


// Form validation
function initFormValidation() {
    // Registration form validation
    $('#registerForm').on('submit', function(e) {
        const password = $('input[name="password"]').val();
        const confirmPassword = $('input[name="confirm_password"]').val();
        
        if (password !== confirmPassword) {
            e.preventDefault();
            showAlert('Passwords do not match!', 'danger');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            showAlert('Password must be at least 6 characters long!', 'danger');
            return false;
        }
    });
    
    // Advertisement form validation
    $('#adForm').on('submit', function(e) {
        const originalPrice = parseFloat($('input[name="original_price"]').val());
        const discountedPrice = parseFloat($('input[name="discounted_price"]').val());
        
        if (discountedPrice >= originalPrice) {
            e.preventDefault();
            showAlert('Discounted price must be less than the original price!', 'danger');
            return false;
        }
    });
}


// Search functionality
function initSearch() {
    let searchTimeout;
    
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (query.length >= 3) {
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 500);
        } else if (query.length === 0) {
            // When search is cleared, show all results
            $('.deal-card').show();
        }
    });
}


// Function to perform search
function performSearch(query) {
    const lowerQuery = query.toLowerCase();
    
    $('.deal-card').each(function() {
        const title = $(this).find('.card-title').text().toLowerCase();
        const description = $(this).find('.card-text').text().toLowerCase();
        
        if (title.includes(lowerQuery) || description.includes(lowerQuery)) {
            $(this).fadeIn();
        } else {
            $(this).fadeOut();
        }
    });
}


// Filter functionality
function initFilters() {
    // Category filter
    $('#categoryFilter').on('change', function() {
        applyFilters();
    });
    
    // Location filter
    $('#locationFilter').on('change', function() {
        applyFilters();
    });
    
    // Price range filter
    $('#priceRange').on('change', function() {
        applyFilters();
    });
}


// Function to apply filters
function applyFilters() {
    const category = $('#categoryFilter').val();
    const location = $('#locationFilter').val();
    const priceRange = $('#priceRange').val();
    
    $('.deal-card').each(function() {
        let show = true;
        
        // Category filter
        if (category && $(this).data('category') !== category) {
            show = false;
        }
        
        // Location filter
        if (location && !$(this).data('location').includes(location)) {
            show = false;
        }
        
        // Price filter
        if (priceRange) {
            const price = parseFloat($(this).data('price'));
            const [min, max] = priceRange.split('-').map(Number);
            
            if (max && (price < min || price > max)) {
                show = false;
            } else if (!max && price < min) {
                show = false;
            }
        }
        
        if (show) {
            $(this).fadeIn();
        } else {
            $(this).fadeOut();
        }
    });
}


// Smooth scrolling
function initSmoothScroll() {
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 70
            }, 800);
        }
    });
}


// Auto hide alerts
function autoHideAlerts() {
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}


// Function to show a custom alert
function showAlert(message, type = 'info') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Add alert to the top of the body
    $('body').prepend(alertHtml);
    
    // Auto hide
    setTimeout(function() {
        $('.alert').first().fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}


// Function to calculate discount percentage
function calculateDiscount(originalPrice, discountedPrice) {
    if (originalPrice <= 0) return 0;
    
    const discount = ((originalPrice - discountedPrice) / originalPrice) * 100;
    return Math.round(discount);
}


// Function to format price (with LKR)
function formatPrice(price) {
    return 'LKR ' + parseFloat(price).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}


// Function to show confirm dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}


// Delete advertisement
function deleteAd(adId) {
    confirmAction('Do you want to delete this advertisement?', function() {
        window.location.href = `delete-ad.php?id=${adId}`;
    });
}


// Common function to send AJAX requests
function sendAjaxRequest(url, data, successCallback, errorCallback) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (successCallback) {
                successCallback(response);
            }
        },
        error: function(xhr, status, error) {
            if (errorCallback) {
                errorCallback(error);
            } else {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        }
    });
}


// Function to increase view count
function incrementViewCount(adId) {
    sendAjaxRequest(
        '../api/increment-view.php',
        { ad_id: adId },
        function(response) {
            // View count updated successfully
        }
    );
}


// Function to submit contact form
function submitContactForm(formData, callback) {
    sendAjaxRequest(
        '../api/contact-handler.php',
        formData,
        function(response) {
            if (response.success) {
                showAlert('Your message has been sent!', 'success');
                if (callback) callback();
            } else {
                showAlert(response.message || 'An error occurred while sending the message.', 'danger');
            }
        }
    );
}


// Function to reset form
function resetForm(formId) {
    $(`#${formId}`)[0].reset();
    $('.image-preview').remove();
}


// Show loading spinner
function showLoader() {
    const loader = `
        <div id="loader" class="text-center my-5">
            <div class="spinner"></div>
            <p class="mt-2">Please wait...</p>
        </div>
    `;
    $('body').append(loader);
}


// Hide loading spinner
function hideLoader() {
    $('#loader').remove();
}


// Pagination
function initPagination() {
    const itemsPerPage = 12;
    let currentPage = 1;
    const items = $('.deal-card');
    const totalPages = Math.ceil(items.length / itemsPerPage);
    
    function showPage(page) {
        items.hide();
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        items.slice(start, end).fadeIn();
        
        // Update pagination buttons
        updatePaginationButtons(page, totalPages);
    }
    
    function updatePaginationButtons(current, total) {
        // Implementation for pagination buttons
    }
    
    // Initialize first page
    if (items.length > 0) {
        showPage(1);
    }
}


// Price range slider
function initPriceSlider() {
    const slider = document.getElementById('priceSlider');
    if (slider) {
        slider.addEventListener('input', function() {
            const value = this.value;
            $('#priceValue').text(`LKR ${value}`);
            applyFilters();
        });
    }
}
