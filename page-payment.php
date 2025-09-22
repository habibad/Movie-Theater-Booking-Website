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
                        <h3>PRICING</h3>
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
                                I have read and agree to the 
                                <a href="#" class="terms-link">Terms of Service</a> and 
                                <a href="#" class="privacy-link">Privacy Policy</a>
                            </label>
                        </div>
                    </div>

                    <div class="payment-methods">
                        <!-- Gift Card/Voucher Section -->
                        <div class="payment-section">
                            <div class="payment-header">
                                <span class="payment-icon">🎁</span>
                                <span>Add Gift Card, Voucher, Promo Code</span>
                                <button class="btn-expand">+</button>
                            </div>
                        </div>

                        <!-- Credit Card Section -->
                        <div class="payment-section active">
                            <div class="payment-header">
                                <span class="payment-icon">💳</span>
                                <span>Add Default Credit Card</span>
                                <button class="btn-expand">+</button>
                            </div>

                            <div class="payment-form">
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
                                        <input type="text" id="card_number" name="card_number" 
                                               placeholder="1234 1234 1234 1234" 
                                               class="form-control card-input" maxlength="19">
                                        <div class="card-icons">
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/visa.png" alt="Visa">
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/mastercard.png" alt="Mastercard">
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/amex.png" alt="American Express">
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
                                            <input type="text" id="security_code" name="security_code" 
                                                   placeholder="CVC" class="form-control" maxlength="4">
                                            <span class="cvc-icon">🛡</span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <select id="country" name="country" class="form-control">
                                            <option value="BD" selected>Bangladesh</option>
                                            <option value="US">United States</option>
                                            <option value="CA">Canada</option>
                                            <option value="UK">United Kingdom</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Bank Section -->
                        <div class="payment-section">
                            <div class="payment-header">
                                <span class="payment-icon">🏦</span>
                                <span>Bank</span>
                                <span class="badge success">US$ best</span>
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
                            <span>TICKETS</span>
                            <span><?php echo count($selected_seats); ?> ticket · $<?php echo number_format($total, 2); ?></span>
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
            <p>Please wait while we process your payment.</p>
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
            <div class="success-icon">✓</div>
            <p>Your booking has been confirmed.</p>
            <p>You will receive a confirmation email shortly.</p>
            <button class="btn btn-primary" id="view-tickets">View Tickets</button>
        </div>
    </div>
</div>

<?php get_footer(); ?>