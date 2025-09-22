jQuery(document).ready(function($) {
    'use strict';

    // Global variables
    let selectedSeats = [];
    let selectedShowtime = null;
    let bookingData = {};

    // Initialize cinema booking system
    init();

    function init() {
        initMoviesFilter();
        initShowtimeSelection();
        initSeatSelection();
        initSeatModal();
        initCartFunctionality();
        initPaymentForm();
        initModals();
    }

    // Movies filter functionality
    function initMoviesFilter() {
        $('.filter-tab').on('click', function() {
            const filter = $(this).data('filter');
            
            // Update active tab
            $('.filter-tab').removeClass('active');
            $(this).addClass('active');
            
            // Filter movies
            filterMovies(filter);
        });

        // Showtime date filters
        $('.filter-btn').on('click', function() {
            const dateFilter = $(this).data('date');
            
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            filterShowtimes(dateFilter);
        });
    }

    function filterMovies(filter) {
        const $movies = $('.movie-card');
        
        $movies.hide();
        
        switch(filter) {
            case 'now-playing':
                $movies.filter('.now-playing').show();
                break;
            case 'coming-soon':
                $movies.filter('.coming-soon').show();
                break;
            case 'my-movies':
                // Show user's favorite/watchlist movies
                $movies.filter('[data-favorite="true"]').show();
                break;
            case 'all':
            default:
                $movies.show();
                break;
        }
    }

    function filterShowtimes(filter) {
        const $showtimes = $('.showtime-date');
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        $showtimes.hide();
        
        switch(filter) {
            case 'today':
                $showtimes.filter('.today').show();
                break;
            case 'tomorrow':
                $showtimes.filter('.tomorrow').show();
                break;
            case 'all':
            default:
                $showtimes.show();
                break;
        }
    }

    // Showtime selection
    function initShowtimeSelection() {
        $('.showtime-slot').on('click', function() {
            const showtimeId = $(this).data('showtime-id');
            const screen = $(this).data('screen');
            const price = $(this).data('price');
            
            selectedShowtime = {
                id: showtimeId,
                screen: screen,
                price: price,
                time: $(this).find('.showtime-time').text()
            };
            
            // Redirect to seat selection with showtime data
            const seatSelectionUrl = '/seat-selection/?showtime=' + showtimeId;
            window.location.href = seatSelectionUrl;
        });
    }

    // Seat selection functionality
    function initSeatSelection() {
        if (!$('.seat-map').length) return;

        const $seatMap = $('.seat-map');
        const showtimeId = $seatMap.data('showtime-id');
        
        // Handle seat clicks
        $('.seat.available').on('click', function() {
            const seatNumber = $(this).data('seat');
            const seatPrice = $(this).data('price');
            
            if ($(this).hasClass('selected')) {
                // Deselect seat
                $(this).removeClass('selected');
                selectedSeats = selectedSeats.filter(seat => seat.number !== seatNumber);
            } else {
                // Select seat
                $(this).addClass('selected');
                selectedSeats.push({
                    number: seatNumber,
                    price: parseFloat(seatPrice),
                    type: 'regular' // Could be determined by seat class
                });
            }
            
            updateSeatSummary();
        });

        // Seat control buttons
        $('.btn-zoom-in').on('click', function() {
            zoomSeatMap(1.2);
        });

        $('.btn-zoom-out').on('click', function() {
            zoomSeatMap(0.8);
        });

        $('.btn-fullscreen').on('click', function() {
            toggleFullscreenSeatMap();
        });

        // Continue to cart with multiple fallback methods
        $('#continue-to-cart').on('click', function(e) {
            e.preventDefault();
            
            if (selectedSeats.length === 0) {
                showErrorMessage('Please select at least one seat.');
                return;
            }
            
            console.log('Continue to cart clicked', selectedSeats);
            
            // Method 1: Try AJAX save first
            const bookingData = {
                showtime_id: $('.seat-map').data('showtime-id'),
                seats: selectedSeats,
                timestamp: Date.now()
            };
            
            // Show loading
            $(this).prop('disabled', true).text('Saving...');
            
            saveBookingData(bookingData).then(() => {
                console.log('Booking data saved, redirecting...');
                redirectToCart();
            }).catch((error) => {
                console.error('AJAX save failed, trying alternative methods:', error);
                
                // Method 2: Use localStorage as backup
                localStorage.setItem('cinema_booking_backup', JSON.stringify(bookingData));
                
                // Method 3: Use form submission as final fallback
                $('#form-selected-seats').val(selectedSeats.map(seat => seat.number).join(','));
                
                setTimeout(() => {
                    redirectToCart();
                }, 500);
            });
        });
        
        // Alternative click handler for double-safety
        $(document).on('click', '#continue-to-cart', function(e) {
            if ($(this).prop('disabled') || selectedSeats.length === 0) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Alternative handler triggered');
            redirectToCart();
        });
    }

    function updateSeatSummary() {
        const seatCount = selectedSeats.length;
        const totalPrice = selectedSeats.reduce((total, seat) => total + seat.price, 0);
        
        $('#seat-count').text(seatCount);
        
        // Update hidden form fields for fallback
        $('#form-selected-seats').val(selectedSeats.map(seat => seat.number).join(','));
        
        const $continueBtn = $('#continue-to-cart');
        if (seatCount > 0) {
            $continueBtn.prop('disabled', false)
                       .text(`NEXT: CART (${seatCount} seat${seatCount > 1 ? 's' : ''} - ${totalPrice.toFixed(2)})`);
        } else {
            $continueBtn.prop('disabled', true)
                       .text('SELECT A SEAT TO CONTINUE');
        }
    }

    function zoomSeatMap(factor) {
        const $seatMap = $('.seat-map');
        const currentScale = $seatMap.data('scale') || 1;
        const newScale = Math.max(0.5, Math.min(2, currentScale * factor));
        
        $seatMap.css('transform', `scale(${newScale})`)
                .data('scale', newScale);
    }

    function toggleFullscreenSeatMap() {
        const $container = $('.seat-selection-main');
        
        if (!document.fullscreenElement) {
            $container[0].requestFullscreen().catch(err => {
                console.log('Fullscreen error:', err);
            });
        } else {
            document.exitFullscreen();
        }
    }

    // Seat selection modal
    function initSeatModal() {
        // Show modal when seats are selected
        function showSeatSelectionModal() {
            const selectedSeatNumbers = selectedSeats.map(seat => seat.number).join(', ');
            $('#modal-selected-seats').text(selectedSeatNumbers + '/Recliner Seat');
            $('#seat-selection-modal').show();
        }

        // Modal close
        $('.modal-close').on('click', function() {
            $(this).closest('.modal').hide();
        });

        // Add ticket buttons
        $('.btn-add').on('click', function() {
            const $ticketType = $(this).closest('.ticket-type');
            const ticketPrice = parseFloat($ticketType.find('.price').text().replace('$', ''));
            
            // Add to booking data
            addTicketToBooking(ticketPrice, 'Adult'); // Could get type from selection
            
            // Close modal and continue to cart
            $('#seat-selection-modal').hide();
            proceedToCart();
        });

        // Member linking buttons
        $('.link-buttons .btn-dark').on('click', function() {
            const linkType = $(this).text().toLowerCase();
            handleMemberLinking(linkType);
        });
    }

    function addTicketToBooking(price, type) {
        bookingData = {
            showtime_id: selectedShowtime.id,
            seats: selectedSeats,
            tickets: selectedSeats.map(seat => ({
                seat: seat.number,
                price: price,
                type: type
            })),
            total: selectedSeats.length * price
        };
    }

    function redirectToCart() {
        console.log('Attempting to redirect to cart...');
        
        const cartUrl = window.location.origin + '/cart/';
        const cartUrlSlash = window.location.origin + '/cart';
        
        // Method 1: Standard location.href
        try {
            window.location.href = cartUrl;
        } catch (error) {
            console.error('Method 1 failed:', error);
        }
        
        // Method 2: Backup with location.assign
        setTimeout(() => {
            try {
                window.location.assign(cartUrl);
            } catch (error) {
                console.error('Method 2 failed:', error);
            }
        }, 100);
        
        // Method 3: Form submission fallback
        setTimeout(() => {
            try {
                $('#cart-redirect-form').attr('action', cartUrl).submit();
            } catch (error) {
                console.error('Method 3 failed:', error);
                
                // Method 4: Manual redirect attempt
                setTimeout(() => {
                    window.open(cartUrl, '_self');
                }, 100);
            }
        }, 200);
        
        // Method 5: Final fallback with different URL format
        setTimeout(() => {
            try {
                window.location = cartUrlSlash;
            } catch (error) {
                console.error('All redirect methods failed:', error);
                showErrorMessage('Unable to redirect. Please navigate to the cart page manually.');
            }
        }, 500);
    }

    // Cart functionality
    function initCartFunctionality() {
        if (!$('.cart-container').length) return;

        // Remove ticket item
        $('.btn-remove').on('click', function() {
            const seatToRemove = $(this).data('seat');
            const $ticketItem = $(this).closest('.ticket-item');
            
            // Remove from UI
            $ticketItem.remove();
            
            // Update totals
            updateCartTotals();
            
            // Update server-side data
            removeTicketFromBooking(seatToRemove);
        });

        // Promo code application
        $('.btn-apply, .btn-apply-widget').on('click', function() {
            const $input = $(this).siblings('input');
            const promoCode = $input.val().trim();
            
            if (promoCode) {
                applyPromoCode(promoCode);
            }
        });

        // Proceed to payment
        $('#proceed-to-payment').on('click', function() {
            window.location.href = '/payment/';
        });

        // Expand/collapse sections
        $('.btn-expand').on('click', function() {
            const $section = $(this).closest('.rewards-section, .cart-summary, .movie-booking-summary');
            const $details = $section.find('.cart-item-details, .ticket-details');
            
            if ($details.is(':visible')) {
                $details.slideUp();
                $(this).text('⌄');
            } else {
                $details.slideDown();
                $(this).text('⌃');
            }
        });
    }

    function updateCartTotals() {
        const $ticketItems = $('.ticket-item');
        let subtotal = 0;
        
        $ticketItems.each(function() {
            const price = parseFloat($(this).find('.ticket-price').text().replace('$', ''));
            subtotal += price;
        });
        
        const bookingFee = $ticketItems.length * 2.25;
        const total = subtotal + bookingFee;
        
        // Update pricing display
        $('.pricing-details .pricing-row:not(.total)').eq(0).find('span:last').text(`$${subtotal.toFixed(2)}`);
        $('.pricing-details .pricing-row:not(.total)').eq(1).find('span:last').text(`$${bookingFee.toFixed(2)}`);
        $('.pricing-details .pricing-row.total span:last').text(`$${total.toFixed(2)}`);
        $('.ticket-widget-header span:last').text(`${$ticketItems.length} ticket · $${total.toFixed(2)}`);
    }

    function applyPromoCode(code) {
        // Show loading state
        showLoadingState('Applying promo code...');
        
        // Simulate API call
        setTimeout(() => {
            hideLoadingState();
            
            // Simulate different promo code results
            if (code.toLowerCase() === 'discount10') {
                showSuccessMessage('10% discount applied!');
                // Apply discount logic
                applyDiscount(0.1);
            } else if (code.toLowerCase() === 'freebooking') {
                showSuccessMessage('Booking fee waived!');
                // Remove booking fee
                removeBookingFee();
            } else {
                showErrorMessage('Invalid promo code.');
            }
        }, 1500);
    }

    // Payment form functionality
    function initPaymentForm() {
        if (!$('.payment-container').length) return;

        // Payment method selection
        $('.payment-section .payment-header').on('click', function() {
            const $section = $(this).parent();
            
            // Toggle active state
            $('.payment-section').removeClass('active');
            $section.addClass('active');
            
            // Show/hide payment forms
            $('.payment-form').hide();
            $section.find('.payment-form').show();
        });

        // Card number formatting
        $('#card_number').on('input', function() {
            let value = $(this).val().replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            $(this).val(formattedValue);
            
            // Detect card type and update icons
            updateCardIcons(value);
        });

        // Expiry date formatting
        $('#expiry_date').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + ' / ' + value.substring(2, 4);
            }
            $(this).val(value);
        });

        // Form validation
        $('#payment-form input, #payment-form select').on('input change', function() {
            validatePaymentForm();
        });

        // Terms agreement
        $('#terms-agreement').on('change', function() {
            validatePaymentForm();
        });

        // Complete payment
        $('#complete-payment').on('click', function() {
            if (validatePaymentForm()) {
                processPayment();
            }
        });
    }

    function updateCardIcons(cardNumber) {
        const $icons = $('.card-icons img');
        
        // Reset all icons
        $icons.css('opacity', '0.3');
        
        // Detect card type
        if (cardNumber.startsWith('4')) {
            $icons.filter('[alt="Visa"]').css('opacity', '1');
        } else if (cardNumber.startsWith('5') || cardNumber.startsWith('2')) {
            $icons.filter('[alt="Mastercard"]').css('opacity', '1');
        } else if (cardNumber.startsWith('3')) {
            $icons.filter('[alt="American Express"]').css('opacity', '1');
        }
    }

    function validatePaymentForm() {
        const cardNumber = $('#card_number').val().replace(/\s/g, '');
        const expiryDate = $('#expiry_date').val();
        const securityCode = $('#security_code').val();
        const country = $('#country').val();
        const termsAccepted = $('#terms-agreement').is(':checked');
        
        const isValid = cardNumber.length >= 13 && 
                       expiryDate.length >= 7 && 
                       securityCode.length >= 3 && 
                       country && 
                       termsAccepted;
        
        $('#complete-payment').prop('disabled', !isValid);
        
        return isValid;
    }

    function processPayment() {
        // Show processing modal
        $('#payment-processing-modal').show();
        
        // Collect payment data
        const paymentData = {
            card_number: $('#card_number').val(),
            expiry_date: $('#expiry_date').val(),
            security_code: $('#security_code').val(),
            country: $('#country').val(),
            booking_data: bookingData
        };
        
        // Simulate payment processing
        setTimeout(() => {
            $('#payment-processing-modal').hide();
            
            // Simulate payment success/failure
            if (Math.random() > 0.1) { // 90% success rate
                showPaymentSuccess();
            } else {
                showPaymentError();
            }
        }, 3000);
    }

    function showPaymentSuccess() {
        $('#payment-success-modal').show();
        
        // Clear booking data from session
        clearBookingData();
    }

    function showPaymentError() {
        showErrorMessage('Payment failed. Please try again.');
    }

    // Modal functionality
    function initModals() {
        // Close modal on overlay click
        $('.modal').on('click', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });

        // Close modal on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.modal:visible').hide();
            }
        });

        // View tickets button
        $('#view-tickets').on('click', function() {
            window.location.href = '/my-tickets/';
        });
    }

    // Utility functions
    function saveBookingData(data) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: cinema_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'cinema_seat_selection',
                    nonce: cinema_ajax.nonce,
                    showtime_id: data.showtime_id,
                    seats: data.seats.map(seat => seat.number).join(','),
                    booking_data: JSON.stringify(data)
                },
                success: function(response) {
                    if (response.success) {
                        resolve(response.data);
                    } else {
                        reject(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    reject(error);
                }
            });
        });
    }

    function removeTicketFromBooking(seatNumber) {
        $.ajax({
            url: cinema_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'cinema_remove_seat',
                nonce: cinema_ajax.nonce,
                seat: seatNumber
            },
            success: function(response) {
                if (!response.success) {
                    showErrorMessage('Failed to remove seat. Please try again.');
                }
            }
        });
    }

    function clearBookingData() {
        $.ajax({
            url: cinema_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'cinema_clear_booking',
                nonce: cinema_ajax.nonce
            }
        });
    }

    function applyDiscount(percentage) {
        const $totalElements = $('.pricing-row.total span:last, .ticket-widget-header span:last');
        
        $totalElements.each(function() {
            const currentTotal = parseFloat($(this).text().replace(/[^0-9.]/g, ''));
            const discountedTotal = currentTotal * (1 - percentage);
            $(this).text(`${discountedTotal.toFixed(2)}`);
        });
        
        // Add discount row to pricing breakdown
        const discountAmount = parseFloat($('.pricing-row.total span:last').text().replace(/[^0-9.]/g, '')) * percentage;
        const discountRow = `
            <div class="pricing-row discount">
                <span>Discount (${(percentage * 100).toFixed(0)}%)</span>
                <span>-${discountAmount.toFixed(2)}</span>
            </div>
        `;
        $('.pricing-row.total').before(discountRow);
    }

    function removeBookingFee() {
        const $bookingFeeRow = $('.pricing-row').filter(function() {
            return $(this).find('span:first').text().toLowerCase().includes('booking');
        });
        
        $bookingFeeRow.find('span:last').text('$0.00');
        
        // Recalculate total
        updateCartTotals();
    }

    function showLoadingState(message = 'Loading...') {
        const loadingModal = `
            <div id="loading-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <div class="loading-spinner"></div>
                        <p>${message}</p>
                    </div>
                </div>
            </div>
        `;
        $('body').append(loadingModal);
        $('#loading-modal').show();
    }

    function hideLoadingState() {
        $('#loading-modal').remove();
    }

    function showSuccessMessage(message) {
        showNotification(message, 'success');
    }

    function showErrorMessage(message) {
        showNotification(message, 'error');
    }

    function showNotification(message, type = 'info') {
        const notification = `
            <div class="notification ${type}">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        if (!$('.notifications-container').length) {
            $('body').append('<div class="notifications-container"></div>');
        }
        
        const $notification = $(notification);
        $('.notifications-container').append($notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            $notification.fadeOut(() => $notification.remove());
        }, 5000);
        
        // Manual close
        $notification.find('.notification-close').on('click', function() {
            $notification.fadeOut(() => $notification.remove());
        });
    }

    function handleMemberLinking(linkType) {
        switch(linkType) {
            case 'link':
                showMemberLinkModal();
                break;
            case 'card':
                showMemberCardModal();
                break;
            case 'email':
                showMemberEmailModal();
                break;
        }
    }

    function showMemberLinkModal() {
        const modal = `
            <div id="member-link-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Link Member Account</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="member-link-form">
                            <div class="form-group">
                                <label>Member ID</label>
                                <input type="text" class="form-control" placeholder="Enter member ID">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" placeholder="Member email">
                            </div>
                            <button type="submit" class="btn btn-primary btn-fullwidth">Link Account</button>
                        </form>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modal);
        $('#member-link-modal').show();
    }

    function showMemberCardModal() {
        const modal = `
            <div id="member-card-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Scan Member Card</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="card-scanner">
                            <div class="scanner-icon">📱</div>
                            <p>Please scan your member card or enter the card number below:</p>
                            <input type="text" class="form-control" placeholder="Card number">
                            <button class="btn btn-primary" style="margin-top: 15px;">Scan Card</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modal);
        $('#member-card-modal').show();
    }

    function showMemberEmailModal() {
        const modal = `
            <div id="member-email-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Email Member Info</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Enter the email address of the member you'd like to purchase tickets for:</p>
                        <div class="form-group">
                            <input type="email" class="form-control" placeholder="Member email address">
                        </div>
                        <button class="btn btn-primary btn-fullwidth">Send Invitation</button>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modal);
        $('#member-email-modal').show();
    }

    // Movie action handlers
    $('.add-to-watchlist').on('click', function() {
        const movieId = getMovieIdFromPage();
        addToWatchlist(movieId);
    });

    $('.rate-movie').on('click', function() {
        const movieId = getMovieIdFromPage();
        showRatingModal(movieId);
    });

    $('.add-to-favorites').on('click', function() {
        const movieId = getMovieIdFromPage();
        addToFavorites(movieId);
    });

    function getMovieIdFromPage() {
        // Get movie ID from page data or URL
        return $('body').data('movie-id') || window.location.pathname.match(/\/movies\/(\d+)/)?.[1];
    }

    function addToWatchlist(movieId) {
        $.ajax({
            url: cinema_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'cinema_add_to_watchlist',
                nonce: cinema_ajax.nonce,
                movie_id: movieId
            },
            success: function(response) {
                if (response.success) {
                    showSuccessMessage('Movie added to watchlist!');
                    $('.add-to-watchlist').addClass('added').text('✓ Added to Watchlist');
                } else {
                    showErrorMessage('Failed to add to watchlist.');
                }
            }
        });
    }

    function addToFavorites(movieId) {
        $.ajax({
            url: cinema_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'cinema_add_to_favorites',
                nonce: cinema_ajax.nonce,
                movie_id: movieId
            },
            success: function(response) {
                if (response.success) {
                    showSuccessMessage('Movie added to favorites!');
                    $('.add-to-favorites').addClass('added').text('♥ Added to Favorites');
                } else {
                    showErrorMessage('Failed to add to favorites.');
                }
            }
        });
    }

    function showRatingModal(movieId) {
        const modal = `
            <div id="rating-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Rate This Movie</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="star-rating">
                            ${[1,2,3,4,5].map(i => `<span class="star" data-rating="${i}">★</span>`).join('')}
                        </div>
                        <textarea class="form-control" placeholder="Write a review (optional)" style="margin-top: 15px; height: 80px;"></textarea>
                        <button class="btn btn-primary" id="submit-rating" style="margin-top: 15px;">Submit Rating</button>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modal);
        $('#rating-modal').show();

        // Star rating interaction
        let selectedRating = 0;
        $('.star').on('mouseenter', function() {
            const rating = $(this).data('rating');
            $('.star').removeClass('hover');
            $('.star:lt(' + rating + ')').addClass('hover');
        }).on('mouseleave', function() {
            $('.star').removeClass('hover');
            if (selectedRating > 0) {
                $('.star:lt(' + selectedRating + ')').addClass('selected');
            }
        }).on('click', function() {
            selectedRating = $(this).data('rating');
            $('.star').removeClass('selected');
            $('.star:lt(' + selectedRating + ')').addClass('selected');
        });

        $('#submit-rating').on('click', function() {
            if (selectedRating === 0) {
                showErrorMessage('Please select a rating.');
                return;
            }
            
            const review = $(this).siblings('textarea').val();
            submitMovieRating(movieId, selectedRating, review);
        });
    }

    function submitMovieRating(movieId, rating, review) {
        $.ajax({
            url: cinema_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'cinema_rate_movie',
                nonce: cinema_ajax.nonce,
                movie_id: movieId,
                rating: rating,
                review: review
            },
            success: function(response) {
                if (response.success) {
                    showSuccessMessage('Thank you for your rating!');
                    $('#rating-modal').hide().remove();
                    $('.rate-movie').addClass('rated').text('★ Rated');
                } else {
                    showErrorMessage('Failed to submit rating.');
                }
            }
        });
    }

    // Trailer functionality
    $('.play-trailer-btn, .play-button').on('click', function() {
        const trailerUrl = $(this).data('trailer-url') || $('body').data('trailer-url');
        if (trailerUrl) {
            showTrailerModal(trailerUrl);
        }
    });

    function showTrailerModal(url) {
        const modal = `
            <div id="trailer-modal" class="modal">
                <div class="modal-content trailer-modal-content">
                    <button class="modal-close trailer-close">&times;</button>
                    <div class="trailer-container">
                        <iframe src="${url}" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modal);
        $('#trailer-modal').show();
    }

    // Auto-save seat selection periodically
    if ($('.seat-map').length) {
        setInterval(function() {
            if (selectedSeats.length > 0) {
                const tempData = {
                    showtime_id: $('.seat-map').data('showtime-id'),
                    seats: selectedSeats,
                    timestamp: Date.now()
                };
                localStorage.setItem('cinema_temp_booking', JSON.stringify(tempData));
            }
        }, 30000); // Save every 30 seconds
    }

    // Load saved seat selection
    if ($('.seat-map').length) {
        const savedBooking = localStorage.getItem('cinema_temp_booking');
        if (savedBooking) {
            const bookingData = JSON.parse(savedBooking);
            const showtimeId = $('.seat-map').data('showtime-id');
            
            // Check if it's for the same showtime and within 30 minutes
            if (bookingData.showtime_id === showtimeId && 
                (Date.now() - bookingData.timestamp) < 1800000) {
                
                // Restore seat selection
                selectedSeats = bookingData.seats;
                bookingData.seats.forEach(seat => {
                    $(`.seat[data-seat="${seat.number}"]`).addClass('selected');
                });
                updateSeatSummary();
                
                showNotification('Previous seat selection restored.', 'info');
            }
        }
    }

    // Clean up temp data when booking is completed
    if (window.location.pathname.includes('/payment-success/')) {
        localStorage.removeItem('cinema_temp_booking');
    }

    // Search functionality (if search box exists)
    $('#movie-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const $movieCards = $('.movie-card');
        
        if (searchTerm.length === 0) {
            $movieCards.show();
            return;
        }
        
        $movieCards.each(function() {
            const movieTitle = $(this).find('.movie-title').text().toLowerCase();
            const movieGenre = $(this).find('.movie-genre').text().toLowerCase();
            
            if (movieTitle.includes(searchTerm) || movieGenre.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Accessibility improvements
    $(document).on('keydown', '.seat.available', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
        }
    });

    // Add loading states to buttons
    $(document).on('click', '.btn-loading', function() {
        const $btn = $(this);
        const originalText = $btn.text();
        
        $btn.prop('disabled', true)
            .html('<div class="btn-spinner"></div> Loading...')
            .addClass('loading');
        
        // Restore button after operation (handled by specific functions)
        $btn.data('original-text', originalText);
    });

    console.log('Cinema booking system initialized successfully!');
});