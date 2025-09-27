<?php
/*
Template Name: Payment
*/

get_header();

// Start session if not already started
if (!session_id()) {
    session_start();
}

// Get booking data from session
$booking_data = isset($_SESSION['cinema_booking']) ? $_SESSION['cinema_booking'] : null;

if (!$booking_data) {
    wp_redirect(home_url('/movies'));
    exit;
}

$showtime_id = $booking_data['showtime_id'];
$selected_seats = explode(',', $booking_data['selected_seats']);

// Get showtime and movie details
$showtime = get_post($showtime_id);
$movie_id = get_post_meta($showtime_id, '_showtime_movie_id', true);
$movie = get_post($movie_id);
$ticket_price = get_post_meta($showtime_id, '_showtime_ticket_price', true);

// Calculate pricing
$subtotal = count($selected_seats) * $ticket_price;
$booking_fee = count($selected_seats) * 2.25;
$total = $subtotal + $booking_fee;
?>

<div class="payment-container">
    <!-- Header with progress -->
    <div class="booking-header">
        <div class="cinema-container">
            <div class="header-top">
                <button class="back-btn" onclick="history.back()">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                    SAN MATEO
                </button>
                
                <div class="cinema-logo">
                    <h2>cinépolis</h2>
                </div>
                
                <button class="close-btn" onclick="window.location.href='<?php echo home_url('/movies'); ?>'">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
                
                <div class="cart-status">
                    <div class="tickets-info">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 10V6c0-1.11-.9-2-2-2H4c-1.11 0-2 .89-2 2v4c1.11 0 2 .89 2 2s-.89 2-2 2v4c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2v-4c-1.11 0-2-.89-2-2s.89-2 2-2z"/>
                        </svg>
                        <span>TICKETS</span>
                        <span><?php echo count($selected_seats); ?> ticket • $<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="promo-input-header">
                        <input type="text" placeholder="Add Gift Card, Voucher, Promo Code" class="promo-header-input">
                        <button class="apply-btn">APPLY</button>
                    </div>
                </div>
            </div>
            
            <div class="booking-progress">
                <div class="progress-step completed">
                    <span class="step-number">✓</span>
                    <span class="step-label">Seats</span>
                </div>
                <div class="progress-step completed">
                    <span class="step-number">✓</span>
                    <span class="step-label">Cart</span>
                </div>
                <div class="progress-step active">
                    <span class="step-number">3</span>
                    <span class="step-label">Payment</span>
                </div>
            </div>
        </div>
    </div>

    <div class="payment-content">
        <div class="cinema-container">
            <div class="payment-layout">
                <!-- Left side - Payment form -->
                <div class="payment-main">
                    <div class="pricing-summary">
                        <div class="pricing-summary-header">
                            <div class="pricing-icon">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                                </svg>
                            </div>
                            <h3>PRICING</h3>
                        </div>
                        
                        <div class="pricing-breakdown">
                            <div class="pricing-row">
                                <span>Booking Fee</span>
                                <span>$<?php echo number_format($booking_fee, 2); ?></span>
                            </div>
                            <div class="pricing-row total">
                                <span>Total</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="terms-agreement">
                            <label class="checkbox-container">
                                <input type="checkbox" id="terms-agreement" required>
                                <span class="checkmark">✓</span>
                                <span class="checkbox-text">
                                    I have read and agree to the 
                                    <a href="#" class="terms-link">Terms of Service</a> and 
                                    <a href="#" class="privacy-link">Privacy Policy</a>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="payment-methods">
                        <!-- Gift Card/Voucher Section -->
                        <div class="payment-section">
                            <div class="payment-header" data-section="gift-card">
                                <span class="payment-icon">🎁</span>
                                <span class="payment-title">Add Gift Card, Voucher, Promo Code</span>
                                <button class="btn-expand">+</button>
                            </div>
                            <div class="payment-form gift-card-form" style="display: none;">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Enter gift card or promo code">
                                    <button class="btn btn-apply">Apply</button>
                                </div>
                            </div>
                        </div>

                        <!-- Credit Card Section -->
                        <div class="payment-section active">
                            <div class="payment-header" data-section="credit-card">
                                <span class="payment-icon">💳</span>
                                <span class="payment-title">Add Default Credit Card</span>
                                <button class="btn-expand">+</button>
                            </div>

                            <div class="payment-form credit-card-form">
                                <form id="payment-form">
                                    <div class="payment-type-selector">
                                        <label class="payment-option">
                                            <input type="radio" name="payment_type" value="card" checked>
                                            <span class="radio-custom"></span>
                                            <span class="payment-label">Card</span>
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label for="card_number">Card number</label>
                                        <div class="card-input-container">
                                            <input type="text" id="card_number" name="card_number" 
                                                   placeholder="1234 1234 1234 1234" 
                                                   class="form-control card-input" maxlength="19">
                                            <div class="card-icons">
                                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/visa.png" alt="Visa" style="width: 24px; opacity: 0.3;">
                                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/mastercard.png" alt="Mastercard" style="width: 24px; opacity: 0.3;">
                                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/amex.png" alt="American Express" style="width: 24px; opacity: 0.3;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="expiry_date">Expiry date</label>
                                            <input type="text" id="expiry_date" name="expiry_date" 
                                                   placeholder="MM / YY" class="form-control" maxlength="7">
                                        </div>
                                        <div class="form-group half">
                                            <label for="security_code">Security code</label>
                                            <div class="cvc-input-container">
                                                <input type="text" id="security_code" name="security_code" 
                                                       placeholder="CVC" class="form-control" maxlength="4">
                                                <span class="cvc-icon">🛡</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <select id="country" name="country" class="form-control">
                                            <option value="BD" selected>Bangladesh</option>
                                            <option value="US">United States</option>
                                            <option value="CA">Canada</option>
                                            <option value="UK">United Kingdom</option>
                                            <option value="AU">Australia</option>
                                            <option value="DE">Germany</option>
                                            <option value="FR">France</option>
                                            <option value="JP">Japan</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Bank Section -->
                        <div class="payment-section">
                            <div class="payment-header" data-section="bank">
                                <span class="payment-icon">🏦</span>
                                <span class="payment-title">Bank</span>
                                <span class="badge success">US$ best</span>
                                <button class="btn-expand">+</button>
                            </div>
                            <div class="payment-form bank-form" style="display: none;">
                                <div class="bank-options">
                                    <label class="bank-option">
                                        <input type="radio" name="bank" value="paypal">
                                        <span class="bank-logo">PayPal</span>
                                    </label>
                                    <label class="bank-option">
                                        <input type="radio" name="bank" value="stripe">
                                        <span class="bank-logo">Stripe</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-fullwidth btn-pay" id="complete-payment" disabled>
                        PAY $<?php echo number_format($total, 2); ?> WITH CARD
                    </button>
                </div>

                <!-- Right side - Ticket summary -->
                <div class="payment-sidebar">
                    <div class="ticket-summary-widget">
                        <div class="ticket-widget-header">
                            <div class="tickets-icon-widget">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22 10V6c0-1.11-.9-2-2-2H4c-1.11 0-2 .89-2 2v4c1.11 0 2 .89 2 2s-.89 2-2 2v4c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2v-4c-1.11 0-2-.89-2-2s.89-2 2-2z"/>
                                </svg>
                                <span>TICKETS</span>
                            </div>
                            <span class="ticket-summary"><?php echo count($selected_seats); ?> ticket · $<?php echo number_format($total, 2); ?></span>
                            <button class="btn-dropdown">⌄</button>
                        </div>
                        
                        <div class="promo-widget">
                            <input type="text" placeholder="Add Gift Card, Voucher, Promo Code" class="promo-widget-input">
                            <button class="btn btn-apply-widget">APPLY</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div id="payment-processing-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-body text-center">
            <div class="loading-spinner"></div>
            <h3>Processing Payment...</h3>
            <p>Please wait while we securely process your payment.</p>
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

<!-- Payment Success Modal -->
<div id="payment-success-modal" class="modal" style="display: none;">
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
                <p><strong>Confirmation Number:</strong> <span id="confirmation-number">CIN-<?php echo strtoupper(uniqid()); ?></span></p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($total, 2); ?></p>
            </div>
            <p class="confirmation-note">You will receive a confirmation email shortly with your tickets.</p>
            <div class="success-actions">
                <button class="btn btn-primary" id="view-tickets">View My Tickets</button>
                <button class="btn btn-secondary" id="book-another">Book Another Movie</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Error Modal -->
<div id="payment-error-modal" class="modal" style="display: none;">
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
            <p id="error-message">There was an issue processing your payment. Please check your card details and try again.</p>
            <div class="error-actions">
                <button class="btn btn-primary" onclick="$('#payment-error-modal').hide()">Try Again</button>
                <button class="btn btn-secondary" onclick="window.location.href='<?php echo home_url('/cart/'); ?>'">Back to Cart</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    'use strict';

    let paymentProcessingSteps = ['step-1', 'step-2', 'step-3'];
    let currentStep = 0;

    // Payment section toggles
    $('.payment-header').on('click', function() {
        const $section = $(this).parent();
        const $form = $section.find('.payment-form');
        const $expandBtn = $(this).find('.btn-expand');
        
        // Toggle current section
        if ($section.hasClass('active')) {
            $section.removeClass('active');
            $form.slideUp();
            $expandBtn.text('+');
        } else {
            // Close other sections
            $('.payment-section').removeClass('active');
            $('.payment-form').slideUp();
            $('.btn-expand').text('+');
            
            // Open current section
            $section.addClass('active');
            $form.slideDown();
            $expandBtn.text('-');
        }
    });

    // Initialize credit card section as open
    $('.payment-section.active .payment-form').show();
    $('.payment-section.active .btn-expand').text('-');

    // Card number formatting
    $('#card_number').on('input', function() {
        let value = $(this).val().replace(/\s/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        $(this).val(formattedValue);
        
        // Detect card type and update icons
        updateCardIcons(value);
        validatePaymentForm();
    });

    // Expiry date formatting
    $('#expiry_date').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + ' / ' + value.substring(2, 4);
        }
        $(this).val(value);
        validatePaymentForm();
    });

    // Security code input
    $('#security_code').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value);
        validatePaymentForm();
    });

    // Terms agreement
    $('#terms-agreement').on('change', function() {
        validatePaymentForm();
    });

    // Country selection
    $('#country').on('change', function() {
        validatePaymentForm();
    });

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
        
        const $payBtn = $('#complete-payment');
        $payBtn.prop('disabled', !isValid);
        
        if (isValid) {
            $payBtn.removeClass('btn-disabled');
        } else {
            $payBtn.addClass('btn-disabled');
        }
        
        return isValid;
    }

    // Complete payment
    $('#complete-payment').on('click', function() {
        if (!validatePaymentForm()) {
            showNotification('Please fill in all required fields and accept the terms.', 'error');
            return;
        }
        
        processPayment();
    });

    function processPayment() {
        // Show processing modal
        $('#payment-processing-modal').show();
        
        // Simulate processing steps
        simulatePaymentProcessing();
        
        // Collect payment data
        const paymentData = {
            card_number: $('#card_number').val(),
            expiry_date: $('#expiry_date').val(),
            security_code: $('#security_code').val(),
            country: $('#country').val(),
            showtime_id: '<?php echo $showtime_id; ?>',
            selected_seats: '<?php echo implode(',', $selected_seats); ?>',
            total_amount: <?php echo $total; ?>,
            booking_data: <?php echo json_encode($booking_data); ?>
        };
        
        // Simulate API call
        setTimeout(() => {
            // Simulate payment success/failure (90% success rate)
            if (Math.random() > 0.1) {
                showPaymentSuccess();
            } else {
                showPaymentError('Card declined. Please check your card details and try again.');
            }
        }, 4000);
    }

    function simulatePaymentProcessing() {
        currentStep = 0;
        
        const stepInterval = setInterval(() => {
            if (currentStep < paymentProcessingSteps.length) {
                // Mark previous step as completed
                if (currentStep > 0) {
                    $(`#${paymentProcessingSteps[currentStep - 1]}`)
                        .removeClass('active')
                        .addClass('completed')
                        .find('.step-icon')
                        .text('✓');
                }
                
                // Activate current step
                $(`#${paymentProcessingSteps[currentStep]}`).addClass('active');
                currentStep++;
            } else {
                clearInterval(stepInterval);
            }
        }, 1200);
    }

    function showPaymentSuccess() {
        $('#payment-processing-modal').hide();
        $('#payment-success-modal').show();
        
        // Clear booking data from session
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'cinema_clear_booking',
                nonce: '<?php echo wp_create_nonce('cinema_nonce'); ?>'
            }
        });
    }

    function showPaymentError(message) {
        $('#payment-processing-modal').hide();
        $('#error-message').text(message);
        $('#payment-error-modal').show();
    }

    // Success modal actions
    $('#view-tickets').on('click', function() {
        window.location.href = '<?php echo home_url('/my-tickets/'); ?>';
    });

    $('#book-another').on('click', function() {
        window.location.href = '<?php echo home_url('/movies/'); ?>';
    });

    // Promo code application
    $('.btn-apply, .btn-apply-widget, .apply-btn').on('click', function() {
        const $input = $(this).siblings('input').first();
        if (!$input.length) {
            $input = $(this).parent().find('input').first();
        }
        const promoCode = $input.val().trim();
        
        if (promoCode) {
            applyPromoCode(promoCode, $input);
        } else {
            showNotification('Please enter a promo code.', 'error');
        }
    });

    function applyPromoCode(code, $input) {
        // Show loading state
        const $btn = $input.siblings('button').first();
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('Applying...');
        
        // Simulate API call
        setTimeout(() => {
            $btn.prop('disabled', false).text(originalText);
            
            // Simulate different promo code results
            const lowerCode = code.toLowerCase();
            if (lowerCode === 'discount10' || lowerCode === '10off') {
                showNotification('10% discount applied!', 'success');
                applyDiscount(0.1);
                $input.val('').prop('disabled', true);
                $btn.text('Applied').prop('disabled', true);
            } else if (lowerCode === 'freebooking' || lowerCode === 'nobooking') {
                showNotification('Booking fee waived!', 'success');
                removeBookingFee();
                $input.val('').prop('disabled', true);
                $btn.text('Applied').prop('disabled', true);
            } else if (lowerCode === 'student' || lowerCode === 'student15') {
                showNotification('Student discount applied!', 'success');
                applyDiscount(0.15);
                $input.val('').prop('disabled', true);
                $btn.text('Applied').prop('disabled', true);
            } else {
                showNotification('Invalid promo code. Please check and try again.', 'error');
                $input.focus().select();
            }
        }, 1500);
    }

    function applyDiscount(percentage) {
        // Update pricing display
        const currentSubtotal = parseFloat($('.pricing-row:not(.total)').first().find('span:last').text().replace(', ''));
        const discountAmount = currentSubtotal * percentage;
        const newSubtotal = currentSubtotal - discountAmount;
        const bookingFee = parseFloat($('.pricing-row:not(.total)').eq(1).find('span:last').text().replace(', ''));
        const newTotal = newSubtotal + bookingFee;
        
        // Add discount row
        const discountRow = `
            <div class="pricing-row discount">
                <span>Discount (${(percentage * 100).toFixed(0)}%)</span>
                <span>-${discountAmount.toFixed(2)}</span>
            </div>
        `;
        $('.pricing-row.total').before(discountRow);
        
        // Update total
        $('.pricing-row.total span:last').text(`${newTotal.toFixed(2)}`);
        $('.ticket-summary').text(`<?php echo count($selected_seats); ?> ticket · ${newTotal.toFixed(2)}`);
        $('#complete-payment').text(`PAY ${newTotal.toFixed(2)} WITH CARD`);
    }

    function removeBookingFee() {
        $('.pricing-row:not(.total)').eq(1).find('span:last').text('$0.00');
        
        // Recalculate total
        const subtotal = parseFloat($('.pricing-row:not(.total)').first().find('span:last').text().replace(', ''));
        const discountAmount = parseFloat($('.pricing-row.discount span:last').text().replace(/[^0-9.]/g, '')) || 0;
        const newTotal = subtotal - discountAmount;
        
        $('.pricing-row.total span:last').text(`${newTotal.toFixed(2)}`);
        $('.ticket-summary').text(`<?php echo count($selected_seats); ?> ticket · ${newTotal.toFixed(2)}`);
        $('#complete-payment').text(`PAY ${newTotal.toFixed(2)} WITH CARD`);
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

    // Initialize form validation
    validatePaymentForm();
});
</script>

<?php get_footer(); ?>