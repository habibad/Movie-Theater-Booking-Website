<?php
/*
Template Name: Cart
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
$screen_number = get_post_meta($showtime_id, '_showtime_screen', true);
$show_date = get_post_meta($showtime_id, '_showtime_date', true);
$show_time = get_post_meta($showtime_id, '_showtime_time', true);
$ticket_price = get_post_meta($showtime_id, '_showtime_ticket_price', true);
$duration = get_post_meta($movie_id, '_movie_duration', true);

// Calculate pricing
$subtotal = count($selected_seats) * $ticket_price;
$booking_fee = count($selected_seats) * 2.25; // $2.25 per ticket
$total = $subtotal + $booking_fee;
?>

<div class="cart-container">
    <!-- Header with progress -->
    <div class="booking-header">
        <div class="cinema-container">
            <div class="booking-progress">
                <div class="progress-step completed">
                    <span class="step-number">✓</span>
                    <span class="step-label">Seats</span>
                </div>
                <div class="progress-step active">
                    <span class="step-number">2</span>
                    <span class="step-label">Cart</span>
                </div>
                <div class="progress-step">
                    <span class="step-number">3</span>
                    <span class="step-label">Payment</span>
                </div>
            </div>
        </div>
    </div>

    <div class="cart-content">
        <div class="cinema-container">
            <div class="cart-layout">
                <!-- Left side - Cart items -->
                <div class="cart-main">
                    <div class="rewards-section">
                        <h3>REDEMPTIONS AVAILABLE</h3>
                        <p>0 Points available to spend</p>
                        <button class="btn-expand">⌄</button>
                    </div>

                    <div class="cart-items">
                        <h3>TICKETS</h3>
                        <div class="cart-summary">
                            <span><?php echo count($selected_seats); ?> ticket</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                            <button class="btn-expand">⌃</button>
                        </div>

                        <div class="cart-item-details">
                            <div class="movie-booking-summary">
                                <?php if (has_post_thumbnail($movie_id)) : ?>
                                    <?php echo get_the_post_thumbnail($movie_id, 'thumbnail', array('class' => 'cart-poster')); ?>
                                <?php endif; ?>
                                
                                <div class="booking-details">
                                    <h4><?php echo get_the_title($movie_id); ?></h4>
                                    <p><?php echo date('M jS', strtotime($show_date)); ?> at <?php echo format_showtime($show_time); ?></p>
                                    <p>SAN MATEO · SCREEN <?php echo $screen_number; ?></p>
                                </div>
                                <button class="btn-expand">⌃</button>
                            </div>

                            <div class="ticket-details">
                                <?php foreach ($selected_seats as $index => $seat) : ?>
                                <div class="ticket-item">
                                    <div class="ticket-info">
                                        <span class="ticket-type">Adult</span>
                                        <span class="seat-number">Seat <?php echo trim($seat); ?></span>
                                    </div>
                                    <div class="ticket-price">$<?php echo number_format($ticket_price + 2.25, 2); ?></div>
                                    <button class="btn-remove" data-seat="<?php echo trim($seat); ?>">🗑</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side - Pricing summary -->
                <div class="cart-sidebar">
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h3>PRICING</h3>
                        </div>

                        <div class="pricing-details">
                            <div class="pricing-row">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="pricing-row">
                                <span>Booking Fee</span>
                                <span>$<?php echo number_format($booking_fee, 2); ?></span>
                            </div>
                            <div class="pricing-row total">
                                <span>TOTAL</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>

                        <div class="promo-code">
                            <input type="text" placeholder="Add Gift Card, Voucher, Promo Code" class="promo-input">
                            <button class="btn btn-apply">APPLY</button>
                        </div>

                        <button class="btn btn-primary btn-fullwidth btn-continue" id="proceed-to-payment">
                            NEXT: PAYMENT
                        </button>
                    </div>

                    <!-- Ticket summary in top right -->
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

<!-- Add/Remove ticket modal (if needed) -->
<div id="modify-tickets-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>MODIFY TICKETS</h3>
            <button class="modal-close">&times;</button>
        </div>
        
        <div class="modal-body">
            <p>Would you like to add or remove tickets?</p>
            
            <div class="ticket-modification">
                <button class="btn btn-secondary" id="back-to-seats">Back to Seat Selection</button>
                <button class="btn btn-danger" id="remove-all">Remove All</button>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>