/**
 * Complete Cinema Booking System JavaScript
 * Cinépolis-style movie theater booking functionality
 */

(function($) {
    'use strict';

    // Global variables
    let selectedSeats = [];
    let selectedShowtime = null;
    let bookingData = {};
    let currentZoom = 1;
    let seatSelectionModal = null;

    // Initialize when document is ready
    $(document).ready(function() {
        initializeCinemaBookingSystem();
    });

    function initializeCinemaBookingSystem() {
        // Initialize all modules
        initMoviesPage();
        initSeatSelection();
        initCartFunctionality();
        initPaymentSystem();
        initModalSystem();
        initUserInteractions();
        initUtilities();
        
        console.log('Cinema booking system initialized successfully');
    }

    // ===== MOVIES PAGE FUNCTIONALITY =====
    function initMoviesPage() {
        // Movie filter tabs
        $('.filter-tab').on('click', function() {
            const filter = $(this).data('filter');
            
            // Update active tab
            $('.filter-tab').removeClass('active');
            $(this).addClass('active');
            
            filterMovies(filter);
        });

        // Showtime filters
        $('.filter-btn').on('click', function() {
            const dateFilter = $(this).data('date');
            
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            filterShowtimes(dateFilter);
        });

        // Movie search
        initMovieSearch();
        
        // Showtime selection
        initShowtimeSelection();
    }

    function filterMovies(filter) {
        const $movies = $('.movie-card');
        
        $movies.hide().removeClass('filtered-visible');
        
        switch(filter) {
            case 'now-playing':
                $movies.filter('.now-playing').show().addClass('filtered-visible');
                break;
            case 'coming-soon':
                $movies.filter('.coming-soon').show().addClass('filtered-visible');
                break;
            case 'my-movies':
                $movies.filter('[data-favorite="true"], [data-watchlist="true"]').show().addClass('filtered-visible');
                break;
            case 'all':
            default:
                $movies.show().addClass('filtered-visible');
                break;
        }

        // Animate filtered movies
        $('.filtered-visible').each(function(index) {
            $(this).css({
                opacity: 0,
                transform: 'translateY(20px)'
            }).delay(index * 50).animate({
                opacity: 1
            }, 300, function() {
                $(this).css('transform', 'translateY(0)');
            });
        });
    }

    function filterShowtimes(filter) {
        const $showtimes = $('.showtime-date');
        
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

    function initMovieSearch() {
        // Add search box if it doesn't exist
        if (!$('#movie-search').length && $('.movies-filter').length) {
            const searchHTML = `
                <div class="movie-search-container">
                    <input type="text" id="movie-search" placeholder="Search movies..." class="movie-search-input">
                    <svg class="search-icon" width="20" height="20" fill="#666" viewBox="0 0 24 24">
                        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </div>
            `;
            $('.movies-filter').append(searchHTML);
        }

        // Search functionality
        $(document).on('input', '#movie-search, .movie-search-input', function() {
            const searchTerm = $(this).val().toLowerCase();
            const $movieCards = $('.movie-card');
            
            if (searchTerm.length === 0) {
                $movieCards.show();
                return;
            }
            
            $movieCards.each(function() {
                const movieTitle = $(this).find('.movie-title a').text().toLowerCase();
                const movieGenre = $(this).find('.movie-genre').text().toLowerCase();
                
                if (movieTitle.includes(searchTerm) || movieGenre.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }

    function initShowtimeSelection() {
        $(document).on('click', '.showtime-slot', function() {
            const $slot = $(this);
            const showtimeId = $slot.data('showtime-id');
            const screen = $slot.data('screen');
            const price = $slot.data('price');
            const date = $slot.data('date');
            const time = $slot.data('time');
            
            // Store showtime data
            selectedShowtime = {
                id: showtimeId,
                screen: screen,
                price: price,
                date: date,
                time: time
            };
            
            // Add loading state
            $slot.addClass('loading').prop('disabled', true);
            const originalText = $slot.find('.showtime-time').text();
            $slot.find('.showtime-time').text('Loading...');
            
            // Redirect to seat selection
            setTimeout(() => {
                window.location.href = `/seat-selection/?showtime=${showtimeId}`;
            }, 800);
        });
    }

    // ===== SEAT SELECTION FUNCTIONALITY =====
    function initSeatSelection() {
        if (!$('.seat-map').length) return;

        const $seatMap = $('.seat-map');
        const showtimeId = $seatMap.data('showtime-id');
        
        // Load any saved seat selection
        loadSavedSeatSelection();
        
        // Handle seat clicks
        $(document).on('click', '.seat.available', function() {
            const $seat = $(this);
            const seatNumber = $seat.data('seat');
            const seatPrice = parseFloat($seat.data('price')) || 19.00;
            const seatType = $seat.data('type') || 'regular';
            
            if ($seat.hasClass('selected')) {
                // Deselect seat
                deselectSeat($seat, seatNumber);
            } else {
                // Select seat (with limit check)
                if (selectedSeats.length >= 8) {
                    showNotification('Maximum 8 seats can be selected at once.', 'warning');
                    return;
                }
                selectSeat($seat, seatNumber, seatPrice, seatType);
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
            toggleFullscreen();
        });

        // Continue to cart button
        $(document).on('click', '#continue-to-cart', function(e) {
            e.preventDefault();
            console.log('Proceeding to cart with seats:', selectedSeats);
            
            if (selectedSeats.length === 0) {
                showNotification('Please select at least one seat to continue.', 'error');
                return;
            }
            
            showSeatSelectionModal();
        });

        // Auto-save selection every 30 seconds
        setInterval(autoSaveSeatSelection, 30000);
    }

    function selectSeat($seat, seatNumber, seatPrice, seatType) {
        $seat.addClass('selected seat-animation');
        selectedSeats.push({
            number: seatNumber,
            price: seatPrice,
            type: seatType
        });
        
        // Remove animation class after animation completes
        setTimeout(() => {
            $seat.removeClass('seat-animation');
        }, 300);
    }

    function deselectSeat($seat, seatNumber) {
        $seat.removeClass('selected');
        selectedSeats = selectedSeats.filter(seat => seat.number !== seatNumber);
    }

    function updateSeatSummary() {
        const seatCount = selectedSeats.length;
        const seatNumbers = selectedSeats.map(seat => seat.number).join(', ');
        
        // Update seat count display
        $('#seat-count').text(seatCount);
        $('#selected-seat-numbers').text(seatNumbers || 'None selected');
        
        // Update continue button
        const $continueBtn = $('#continue-to-cart');
        if (seatCount > 0) {
            $continueBtn.prop('disabled', false)
                       .removeClass('btn-disabled')
                       .html(`NEXT: CART <small>(${seatCount} seat${seatCount !== 1 ? 's' : ''})</small>`);
        } else {
            $continueBtn.prop('disabled', true)
                       .addClass('btn-disabled')
                       .text('SELECT A SEAT TO CONTINUE');
        }

        // Update form fields for fallback
        $('#form-selected-seats').val(seatNumbers);
    }

    function zoomSeatMap(factor) {
        const $seatMap = $('.seat-map');
        currentZoom = Math.max(0.5, Math.min(2.5, currentZoom * factor));
        
        $seatMap.css({
            'transform': `scale(${currentZoom})`,
            'transform-origin': 'center center'
        });
        
        // Update button states
        $('.btn-zoom-out').prop('disabled', currentZoom <= 0.5);
        $('.btn-zoom-in').prop('disabled', currentZoom >= 2.5);
    }

    function toggleFullscreen() {
        const element = $('.seat-selection-main')[0];
        
        if (!document.fullscreenElement) {
            if (element.requestFullscreen) {
                element.requestFullscreen().catch(() => {
                    showNotification('Fullscreen not supported on this device.', 'info');
                });
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    function autoSaveSeatSelection() {
        if (selectedSeats.length > 0 && $('.seat-map').length) {
            const tempData = {
                showtime_id: $('.seat-map').data('showtime-id'),
                seats: selectedSeats,
                timestamp: Date.now()
            };
            localStorage.setItem('cinema_temp_booking', JSON.stringify(tempData));
        }
    }

    function loadSavedSeatSelection() {
        const savedBooking = localStorage.getItem('cinema_temp_booking');
        if (savedBooking && $('.seat-map').length) {
            try {
                const bookingData = JSON.parse(savedBooking);
                const showtimeId = $('.seat-map').data('showtime-id');
                
                // Check if it's for the same showtime and within 30 minutes
                if (bookingData.showtime_id == showtimeId && 
                    (Date.now() - bookingData.timestamp) < 1800000) {
                    
                    // Restore seat selection
                    selectedSeats = bookingData.seats || [];
                    selectedSeats.forEach(seat => {
                        $(`.seat[data-seat="${seat.number}"]`).addClass('selected');
                    });
                    updateSeatSummary();
                    
                    if (selectedSeats.length > 0) {
                        showNotification(`${selectedSeats.length} seat(s) restored from previous session.`, 'info');
                    }
                }
            } catch (e) {
                console.error('Error loading saved booking:', e);
            }
        }
    }

    // ===== SEAT SELECTION MODAL =====
    function showSeatSelectionModal() {
        if (selectedSeats.length === 0) return;
        
        const selectedSeatNumbers = selectedSeats.map(seat => seat.number).join(', ');
        $('#modal-selected-seats').text(selectedSeatNumbers + '/Recliner Seat');
        
        const modal = $('#seat-selection-modal');
        if (modal.length) {
            modal.show();
        } else {
            createSeatSelectionModal();
        }
    }

    function createSeatSelectionModal() {
        const modalHTML = `
            <div id="seat-selection-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>SELECT YOUR TICKET</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="selected-seat-info">
                            <p><strong>Seats Selected:</strong> <span id="modal-selected-seats"></span></p>
                            <p class="booking-fee-notice">A $2.25 booking fee per ticket is included in the price of the ticket.</p>
                        </div>
                        <div class="ticket-options">
                            <div class="ticket-type ora-member">
                                <div class="ticket-details">
                                    <div class="member-badge">
                                        <span>Ora</span>
                                        <small>Cinépolis Rewards Member</small>
                                    </div>
                                    <div class="ticket-info">
                                        <select class="ticket-select">
                                            <option value="adult">Adult</option>
                                            <option value="child">Child</option>
                                            <option value="senior">Senior</option>
                                        </select>
                                        <small>2 ticket types available</small>
                                    </div>
                                </div>
                                <div class="ticket-price">
                                    <span class="price">$21.25</span>
                                    <small>($19.00 + $2.25 Booking Fee)</small>
                                    <button class="btn-add" data-type="ora-adult" data-price="21.25">ADD</button>
                                </div>
                            </div>
                            <div class="ticket-type">
                                <div class="ticket-details">
                                    <div class="ticket-info">
                                        <span class="ticket-label">Adult</span>
                                    </div>
                                </div>
                                <div class="ticket-price">
                                    <span class="price">$21.25</span>
                                    <small>($19.00 + $2.25 Booking Fee)</small>
                                    <button class="btn-add" data-type="adult" data-price="21.25">ADD</button>
                                </div>
                            </div>
                            <div class="ticket-type">
                                <div class="ticket-details">
                                    <div class="ticket-info">
                                        <span class="ticket-label">Senior (60+)</span>
                                    </div>
                                </div>
                                <div class="ticket-price">
                                    <span class="price">$19.25</span>
                                    <small>($17.00 + $2.25 Booking Fee)</small>
                                    <button class="btn-add" data-type="senior" data-price="19.25">ADD</button>
                                </div>
                            </div>
                        </div>
                        <div class="member-options">
                            <h4>WOULD YOU LIKE TO PURCHASE TICKETS FOR OTHER MEMBERS?</h4>
                            <p>Link a member</p>
                            <div class="link-buttons">
                                <button class="btn btn-dark" data-action="link">LINK</button>
                                <button class="btn btn-dark" data-action="card">CARD</button>
                                <button class="btn btn-dark" data-action="email">EMAIL</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHTML);
        showSeatSelectionModal(); // Show the newly created modal
    }

    // Handle ticket addition
    $(document).on('click', '.btn-add', function() {
        const ticketType = $(this).data('type') || 'adult';
        const ticketPrice = parseFloat($(this).data('price')) || 21.25;
        
        // Create booking data
        bookingData = {
            showtime_id: $('.seat-map').data('showtime-id'),
            seats: selectedSeats,
            ticket_type: ticketType,
            ticket_price: ticketPrice,
            total: selectedSeats.length * ticketPrice
        };
        
        // Close modal and proceed to cart
        $('#seat-selection-modal').hide();
        proceedToCart();
    });

    function proceedToCart() {
        const $continueBtn = $('#continue-to-cart');
        const originalText = $continueBtn.html();
        
        $continueBtn.prop('disabled', true).html('<div class="btn-spinner"></div> Processing...');
        
        // Prepare data for saving
        const saveData = {
            showtime_id: $('.seat-map').data('showtime-id'),
            seats: selectedSeats.map(seat => seat.number).join(','),
            booking_data: JSON.stringify(bookingData)
        };
        
        // Try to save booking data
        saveBookingData(saveData)
            .then(() => {
                localStorage.removeItem('cinema_temp_booking');
                redirectToCart();
            })
            .catch((error) => {
                console.error('Save failed:', error);
                // Try localStorage backup
                localStorage.setItem('cinema_booking_backup', JSON.stringify(saveData));
                redirectToCart();
            })
            .finally(() => {
                $continueBtn.prop('disabled', false).html(originalText);
            });
    }

    function redirectToCart() {
        window.location.href = '/cart/';
    }

    // ===== CART FUNCTIONALITY =====
    function initCartFunctionality() {
        if (!$('.cart-container').length) return;

        // Remove ticket functionality
        $(document).on('click', '.btn-remove', function() {
            const seatToRemove = $(this).data('seat');
            const $ticketItem = $(this).closest('.ticket-item');
            
            if (confirm(`Remove seat ${seatToRemove} from your cart?`)) {
                $ticketItem.addClass('removing');
                
                setTimeout(() => {
                    $ticketItem.remove();
                    updateCartTotals();
                    removeTicketFromBooking(seatToRemove);
                    
                    if ($('.ticket-item').length === 0) {
                        showEmptyCartMessage();
                    }
                }, 300);
            }
        });

        // Proceed to payment
        $(document).on('click', '#proceed-to-payment', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).html('<div class="btn-spinner"></div> Loading...');
            
            setTimeout(() => {
                window.location.href = '/payment/';
            }, 500);
        });

        // Toggle sections
        $(document).on('click', '.btn-expand', function() {
            const $section = $(this).closest('.rewards-section, .cart-items');
            const $details = $section.find('.cart-item-details, .ticket-details');
            
            if ($details.length === 0) return;
            
            if ($details.is(':visible')) {
                $details.slideUp(300);
                $(this).text('⌄');
            } else {
                $details.slideDown(300);
                $(this).text('⌃');
            }
        });

        // Initialize promo code handling
        initPromoCodeHandling();
    }

    function updateCartTotals() {
        const $ticketItems = $('.ticket-item');
        let subtotal = 0;
        
        $ticketItems.each(function() {
            const priceText = $(this).find('.ticket-price').text().replace('$', '');
            const price = parseFloat(priceText) || 0;
            subtotal += price;
        });
        
        const ticketCount = $ticketItems.length;
        const bookingFee = ticketCount * 2.25;
        const total = subtotal;
        
        // Update displays
        $('.pricing-row:contains("Subtotal") span:last').text(`$${(subtotal - bookingFee).toFixed(2)}`);
        $('.pricing-row:contains("Booking Fee") span:last').text(`$${bookingFee.toFixed(2)}`);
        $('.pricing-row.total span:last').text(`$${total.toFixed(2)}`);
        $('.ticket-summary, .tickets-info span:last').text(`${ticketCount} ticket${ticketCount !== 1 ? 's' : ''} • $${total.toFixed(2)}`);
    }

    function showEmptyCartMessage() {
        const emptyHTML = `
            <div class="empty-cart-message">
                <div class="empty-cart-icon">
                    <svg width="80" height="80" fill="#ccc" viewBox="0 0 24 24">
                        <path d="M22 10V6c0-1.11-.9-2-2-2H4c-1.11 0-2 .89-2 2v4c1.11 0 2 .89 2 2s-.89 2-2 2v4c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2v-4c-1.11 0-2-.89-2-2s.89-2 2-2z"/>
                    </svg>
                </div>
                <h3>Your cart is empty</h3>
                <p>You haven't selected any tickets yet.</p>
                <button class="btn btn-primary" onclick="history.back()">Select Seats</button>
            </div>
        `;
        $('.cart-main').html(emptyHTML);
        $('.cart-sidebar').hide();
    }

    // ===== PROMO CODE HANDLING =====
    function initPromoCodeHandling() {
        $(document).on('click', '.btn-apply, .btn-apply-widget, .apply-btn', function() {
            const $btn = $(this);
            const $input = $btn.siblings('input').first();
            const promoCode = $input.val().trim();
            
            if (!promoCode) {
                showNotification('Please enter a promo code.', 'error');
                $input.focus();
                return;
            }
            
            applyPromoCode(promoCode, $input, $btn);
        });
    }

    function applyPromoCode(code, $input, $btn) {
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('Applying...');
        
        setTimeout(() => {
            const lowerCode = code.toLowerCase();
            let success = false;
            let message = '';
            
            if (lowerCode === 'discount10' || lowerCode === '10off') {
                applyDiscount(0.1);
                message = '10% discount applied!';
                success = true;
            } else if (lowerCode === 'freebooking') {
                removeBookingFee();
                message = 'Booking fee waived!';
                success = true;
            } else if (lowerCode === 'student15') {
                applyDiscount(0.15);
                message = 'Student discount applied!';
                success = true;
            } else {
                message = 'Invalid promo code.';
            }
            
            if (success) {
                $input.val('').prop('disabled', true);
                $btn.text('Applied').prop('disabled', true);
                showNotification(message, 'success');
            } else {
                $btn.prop('disabled', false).text(originalText);
                showNotification(message, 'error');
                $input.focus().select();
            }
        }, 1500);
    }

    function applyDiscount(percentage) {
        const $subtotalRow = $('.pricing-row:contains("Subtotal")');
        const currentSubtotal = parseFloat($subtotalRow.find('span:last').text().replace('$', ''));
        const discountAmount = currentSubtotal * percentage;
        
        if ($('.pricing-row.discount').length === 0) {
            const discountRow = `
                <div class="pricing-row discount">
                    <span>Discount (${(percentage * 100).toFixed(0)}%)</span>
                    <span>-$${discountAmount.toFixed(2)}</span>
                </div>
            `;
            $('.pricing-row.total').before(discountRow);
        }
        
        updateCartTotals();
    }

    function removeBookingFee() {
        $('.pricing-row:contains("Booking Fee") span:last').text('$0.00');
        updateCartTotals();
    }

    // ===== PAYMENT SYSTEM =====
    function initPaymentSystem() {
        if (!$('.payment-container').length) return;

        // Payment section toggles
        $(document).on('click', '.payment-header', function() {
            const $section = $(this).parent();
            const $form = $section.find('.payment-form');
            const $expandBtn = $(this).find('.btn-expand');
            
            if ($section.hasClass('active')) {
                $section.removeClass('active');
                $form.slideUp();
                $expandBtn.text('+');
            } else {
                $('.payment-section').removeClass('active');
                $('.payment-form').slideUp();
                $('.btn-expand').text('+');
                
                $section.addClass('active');
                $form.slideDown();
                $expandBtn.text('-');
            }
        });

        // Form validation
        initPaymentFormValidation();
        
        // Payment processing
        $(document).on('click', '#complete-payment', function() {
            if (validatePaymentForm()) {
                processPayment();
            }
        });
    }

    function initPaymentFormValidation() {
        // Card number formatting
        $(document).on('input', '#card_number', function() {
            let value = $(this).val().replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
            $(this).val(formatted);
            updateCardIcons(value);
            validatePaymentForm();
        });

        // Expiry date formatting
        $(document).on('input', '#expiry_date', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + ' / ' + value.substring(2, 4);
            }
            $(this).val(value);
            validatePaymentForm();
        });

        // CVC formatting
        $(document).on('input', '#security_code', function() {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
            validatePaymentForm();
        });

        // Terms checkbox
        $(document).on('change', '#terms-agreement', validatePaymentForm);
        $(document).on('change', '#country', validatePaymentForm);
    }

    function updateCardIcons(cardNumber) {
        const $icons = $('.card-icons img');
        $icons.css('opacity', '0.3');
        
        if (cardNumber.startsWith('4')) {
            $icons.filter('[alt*="Visa"]').css('opacity', '1');
        } else if (cardNumber.startsWith('5') || cardNumber.startsWith('2')) {
            $icons.filter('[alt*="Master"]').css('opacity', '1');
        } else if (cardNumber.startsWith('3')) {
            $icons.filter('[alt*="American"], [alt*="Amex"]').css('opacity', '1');
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
        showPaymentProcessingModal();
        
        // Simulate payment processing
        setTimeout(() => {
            hidePaymentProcessingModal();
            
            if (Math.random() > 0.1) { // 90% success rate
                showPaymentSuccessModal();
            } else {
                showPaymentErrorModal('Payment declined. Please check your card details.');
            }
        }, 3500);
    }

    function showPaymentProcessingModal() {
        const modalHTML = `
            <div id="payment-processing-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <div class="loading-spinner"></div>
                        <h3>Processing Payment...</h3>
                        <p>Please wait while we process your payment.</p>
                        <div class="processing-steps">
                            <div class="step active" id="step-1">
                                <span class="step-icon">1</span>
                                <span>Validating card details</span>
                            </div>
                            <div class="step" id="step-2">
                                <span class="step-icon">2</span>
                                <span>Authorizing payment</span>
                            </div>
                            <div class="step" id="step-3">
                                <span class="step-icon">3</span>
                                <span>Confirming booking</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHTML);
        $('#payment-processing-modal').show();
        
        // Animate processing steps
        let currentStep = 0;
        const stepInterval = setInterval(() => {
            if (currentStep < 3) {
                if (currentStep > 0) {
                    $(`#step-${currentStep}`).removeClass('active').addClass('completed')
                        .find('.step-icon').text('✓');
                }
                currentStep++;
                if (currentStep <= 3) {
                    $(`#step-${currentStep}`).addClass('active');
                }
            } else {
                clearInterval(stepInterval);
            }
        }, 1000);
    }

    function hidePaymentProcessingModal() {
        $('#payment-processing-modal').hide().remove();
    }

    function showPaymentSuccessModal() {
        const modalHTML = `
            <div id="payment-success-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Payment Successful!</h3>
                    </div>
                    <div class="modal-body text-center">
                        <div class="success-icon">
                            <svg width="60" height="60" fill="#28a745" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                        </div>
                        <h4>Booking Confirmed!</h4>
                        <p>Your tickets have been booked successfully.</p>
                        <div class="booking-confirmation">
                            <p><strong>Confirmation Number:</strong> CIN-${Math.random().toString(36).substr(2, 9).toUpperCase()}</p>
                        </div>
                        <p class="confirmation-note">You will receive a confirmation email shortly.</p>
                        <div class="success-actions">
                            <button class="btn btn-primary" onclick="window.location.href='/movies/'">Book Another Movie</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHTML);
        $('#payment-success-modal').show();
        
        // Clear booking data
        clearBookingData();
    }

    function showPaymentErrorModal(message) {
        const modalHTML = `
            <div id="payment-error-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Payment Failed</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="error-icon">
                            <svg width="60" height="60" fill="#dc3545" viewBox="0 0 24 24">
                                <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                            </svg>
                        </div>
                        <h4>Payment could not be processed</h4>
                        <p>${message}</p>
                        <div class="error-actions">
                            <button class="btn btn-primary" onclick="$('#payment-error-modal').hide().remove()">Try Again</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHTML);
        $('#payment-error-modal').show();
    }

    // ===== USER INTERACTIONS =====
    function initUserInteractions() {
        // Add to watchlist
        $(document).on('click', '.add-to-watchlist', function() {
            const movieId = $(this).data('movie-id');
            const $btn = $(this);
            
            if ($btn.hasClass('added')) return;
            
            addToWatchlist(movieId, $btn);
        });

        // Add to favorites
        $(document).on('click', '.add-to-favorites', function() {
            const movieId = $(this).data('movie-id');
            const $btn = $(this);
            
            if ($btn.hasClass('added')) return;
            
            addToFavorites(movieId, $btn);
        });

        // Rate movie
        $(document).on('click', '.rate-movie', function() {
            const movieId = $(this).data('movie-id');
            showRatingModal(movieId);
        });

        // Share movie
        $(document).on('click', '.share-movie', function() {
            shareMovie();
        });

        // Play trailer
        $(document).on('click', '.play-trailer-btn, .play-button', function() {
            const trailerUrl = $(this).data('trailer-url') || $('[data-trailer-url]').data('trailer-url');
            if (trailerUrl) {
                showTrailerModal(trailerUrl);
            } else {
                showNotification('Trailer not available for this movie.', 'info');
            }
        });
    }

    function addToWatchlist(movieId, $btn) {
        $btn.prop('disabled', true).html('<div class="btn-spinner"></div> Adding...');
        
        // Simulate API call
        setTimeout(() => {
            $btn.removeClass('btn-loading').addClass('added').html(`
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Added to Watchlist
            `);
            showNotification('Movie added to your watchlist!', 'success');
        }, 1000);
    }

    function addToFavorites(movieId, $btn) {
        $btn.prop('disabled', true).html('<div class="btn-spinner"></div> Adding...');
        
        setTimeout(() => {
            $btn.removeClass('btn-loading').addClass('added').html(`
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                Added to Favorites
            `);
            showNotification('Movie added to your favorites!', 'success');
        }, 1000);
    }

    function shareMovie() {
        const movieTitle = $('.movie-hero-title, .movie-title a').first().text() || 'This Movie';
        const movieUrl = window.location.href;
        
        if (navigator.share) {
            navigator.share({
                title: movieTitle,
                text: `Check out "${movieTitle}" - now playing at Cinépolis!`,
                url: movieUrl
            }).catch(err => console.log('Share cancelled'));
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(movieUrl).then(() => {
                showNotification('Movie link copied to clipboard!', 'success');
            });
        } else {
            showNotification('Sharing not supported on this device.', 'info');
        }
    }

    function showRatingModal(movieId) {
        const modalHTML = `
            <div id="rating-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Rate This Movie</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="star-rating">
                            <span class="star" data-rating="1">★</span>
                            <span class="star" data-rating="2">★</span>
                            <span class="star" data-rating="3">★</span>
                            <span class="star" data-rating="4">★</span>
                            <span class="star" data-rating="5">★</span>
                        </div>
                        <textarea class="form-control" placeholder="Write a review (optional)" style="margin: 20px 0; width: 100%; height: 100px; resize: vertical;"></textarea>
                        <button class="btn btn-primary" id="submit-rating" style="width: 100%;">Submit Rating</button>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHTML);
        $('#rating-modal').show();

        let selectedRating = 0;
        
        // Star rating interaction
        $('.star').on('mouseenter', function() {
            const rating = $(this).data('rating');
            $('.star').removeClass('hover');
            for (let i = 0; i < rating; i++) {
                $('.star').eq(i).addClass('hover');
            }
        }).on('mouseleave', function() {
            $('.star').removeClass('hover');
            for (let i = 0; i < selectedRating; i++) {
                $('.star').eq(i).addClass('selected');
            }
        }).on('click', function() {
            selectedRating = $(this).data('rating');
            $('.star').removeClass('selected');
            for (let i = 0; i < selectedRating; i++) {
                $('.star').eq(i).addClass('selected');
            }
        });

        $('#submit-rating').on('click', function() {
            if (selectedRating === 0) {
                showNotification('Please select a rating.', 'error');
                return;
            }
            
            $(this).prop('disabled', true).html('<div class="btn-spinner"></div> Submitting...');
            
            setTimeout(() => {
                $('#rating-modal').hide().remove();
                $('.rate-movie').addClass('rated').html(`
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Rated ${selectedRating}/5
                `);
                showNotification('Thank you for your rating!', 'success');
            }, 1500);
        });
    }

    function showTrailerModal(url) {
        const modalHTML = `
            <div id="trailer-modal" class="modal">
                <div class="modal-content trailer-modal-content">
                    <button class="modal-close trailer-close">&times;</button>
                    <div class="trailer-container">
                        <video controls style="width: 100%; height: auto; max-height: 70vh;">
                            <source src="${url}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHTML);
        $('#trailer-modal').show();
    }

    // ===== MODAL SYSTEM =====
    function initModalSystem() {
        // Close modals
        $(document).on('click', '.modal-close', function() {
            $(this).closest('.modal').hide();
        });

        // Close on overlay click
        $(document).on('click', '.modal', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });

        // Close on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.modal:visible').hide();
            }
        });

        // Remove modals when hidden (except persistent ones)
        $(document).on('hide', '.modal', function() {
            const modalId = $(this).attr('id');
            if (modalId !== 'seat-selection-modal') {
                setTimeout(() => {
                    $(this).remove();
                }, 300);
            }
        });

        // Handle member linking
        $(document).on('click', '.link-buttons .btn-dark', function() {
            const action = $(this).data('action');
            showNotification(`${action.toUpperCase()} member linking is not available in demo mode.`, 'info');
        });
    }

    // ===== UTILITY FUNCTIONS =====
    function initUtilities() {
        // Accessibility improvements
        $(document).on('keydown', '.seat.available, .showtime-slot', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).click();
            }
        });

        // Prevent accidental page refresh when seats are selected
        $(window).on('beforeunload', function(e) {
            if (selectedSeats.length > 0) {
                const message = 'You have selected seats. Are you sure you want to leave?';
                e.returnValue = message;
                return message;
            }
        });

        // Handle browser back/forward
        window.addEventListener('popstate', function() {
            if (selectedSeats.length > 0) {
                const shouldLeave = confirm('You have selected seats. Are you sure you want to leave this page?');
                if (!shouldLeave) {
                    history.pushState(null, null, location.href);
                }
            }
        });

        // Clean up on successful booking
        if (window.location.search.includes('success')) {
            clearBookingData();
        }

        // Mobile responsive adjustments
        handleMobileOptimizations();
        
        // Initialize tooltips
        initTooltips();
    }

    function handleMobileOptimizations() {
        let resizeTimeout;
        $(window).on('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if ($(window).width() < 768 && $('.seat-map').length) {
                    currentZoom = Math.min(currentZoom, 0.8);
                    $('.seat-map').css('transform', `scale(${currentZoom})`);
                }
            }, 250);
        });

        // Touch support
        if ('ontouchstart' in window) {
            $('.seat.available').on('touchstart', function() {
                $(this).addClass('touch-active');
            }).on('touchend', function() {
                $(this).removeClass('touch-active');
            });
        }
    }

    function initTooltips() {
        $(document).on('mouseenter', '[title]', function() {
            const $elem = $(this);
            const title = $elem.attr('title');
            
            if (!title) return;
            
            $elem.removeAttr('title');
            
            const tooltip = $(`<div class="tooltip">${title}</div>`);
            $('body').append(tooltip);
            
            const offset = $elem.offset();
            tooltip.css({
                top: offset.top - tooltip.outerHeight() - 8,
                left: offset.left + ($elem.outerWidth() / 2) - (tooltip.outerWidth() / 2)
            }).fadeIn(200);
            
            $elem.data('tooltip', tooltip).data('original-title', title);
        }).on('mouseleave', function() {
            const $elem = $(this);
            const tooltip = $elem.data('tooltip');
            const originalTitle = $elem.data('original-title');
            
            if (tooltip) {
                tooltip.fadeOut(200, function() {
                    $(this).remove();
                });
            }
            
            if (originalTitle) {
                $elem.attr('title', originalTitle);
            }
        });
    }

    function showNotification(message, type = 'info') {
        const icons = {
            success: '<svg width="20" height="20" fill="#28a745" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
            error: '<svg width="20" height="20" fill="#dc3545" viewBox="0 0 24 24"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/></svg>',
            warning: '<svg width="20" height="20" fill="#ffc107" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
            info: '<svg width="20" height="20" fill="#17a2b8" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>'
        };
        
        const notification = `
            <div class="notification ${type}">
                <div class="notification-icon">${icons[type] || icons.info}</div>
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        if (!$('.notifications-container').length) {
            $('body').append('<div class="notifications-container"></div>');
        }
        
        const $notification = $(notification);
        $('.notifications-container').append($notification);
        
        setTimeout(() => $notification.addClass('notification-show'), 10);
        
        // Auto remove
        setTimeout(() => {
            $notification.removeClass('notification-show').addClass('notification-hide');
            setTimeout(() => $notification.remove(), 300);
        }, 5000);
        
        // Manual close
        $notification.find('.notification-close').on('click', function() {
            $notification.removeClass('notification-show').addClass('notification-hide');
            setTimeout(() => $notification.remove(), 300);
        });
    }

    // ===== AJAX FUNCTIONS =====
    function saveBookingData(data) {
        return new Promise((resolve, reject) => {
            // Check if cinema_ajax is available
            if (typeof cinema_ajax === 'undefined') {
                console.warn('cinema_ajax not available, using localStorage backup');
                localStorage.setItem('cinema_booking_data', JSON.stringify(data));
                resolve({ success: true });
                return;
            }

            $.ajax({
                url: cinema_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'cinema_seat_selection',
                    nonce: cinema_ajax.nonce,
                    ...data
                },
                timeout: 10000,
                success: function(response) {
                    if (response && response.success) {
                        resolve(response.data);
                    } else {
                        reject(response ? response.data : 'Unknown error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    reject(error);
                }
            });
        });
    }

    function removeTicketFromBooking(seatNumber) {
        if (typeof cinema_ajax === 'undefined') {
            console.warn('cinema_ajax not available for seat removal');
            return;
        }

        $.ajax({
            url: cinema_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'cinema_remove_seat',
                nonce: cinema_ajax.nonce,
                seat: seatNumber
            },
            success: function(response) {
                if (!response || !response.success) {
                    showNotification('Failed to remove seat. Please refresh the page.', 'error');
                }
            },
            error: function() {
                showNotification('Connection error. Please refresh the page.', 'error');
            }
        });
    }

    function clearBookingData() {
        localStorage.removeItem('cinema_temp_booking');
        localStorage.removeItem('cinema_booking_backup');
        localStorage.removeItem('cinema_booking_data');
        
        if (typeof cinema_ajax !== 'undefined') {
            $.ajax({
                url: cinema_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'cinema_clear_booking',
                    nonce: cinema_ajax.nonce
                }
            });
        }
    }

    // ===== GLOBAL ERROR HANDLING =====
    $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
        if (jqXHR.status === 0) return; // Ignore aborted requests
        console.error('Global AJAX Error:', thrownError);
    });

    // ===== INITIALIZATION COMPLETE =====
    
    // Show welcome message for first-time visitors
    setTimeout(() => {
        if (!localStorage.getItem('cinema_visited')) {
            showNotification('Welcome to Cinépolis! Select your movie and enjoy the show.', 'info');
            localStorage.setItem('cinema_visited', 'true');
        }
    }, 2000);

    // Log successful initialization
    console.log('🎬 Cinema booking system fully loaded and ready!');

})(jQuery);