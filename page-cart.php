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
$rating = get_post_meta($movie_id, '_movie_rating', true);

// Calculate pricing
$subtotal = count($selected_seats) * $ticket_price;
$booking_fee = count($selected_seats) * 2.25; // $2.25 per ticket
$total = $subtotal + $booking_fee;
?>

<div class="cart-container">
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
                        <div class="rewards-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div class="rewards-content">
                            <h3>REDEMPTIONS AVAILABLE</h3>
                            <p>0 Points available to spend</p>
                        </div>
                        <button class="btn-expand">⌄</button>
                    </div>

                    <div class="cart-items">
                        <div class="cart-section-header">
                            <div class="tickets-icon">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22 10V6c0-1.11-.9-2-2-2H4c-1.11 0-2 .89-2 2v4c1.11 0 2 .89 2 2s-.89 2-2 2v4c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2v-4c-1.11 0-2-.89-2-2s.89-2 2-2z"/>
                                </svg>
                            </div>
                            <h3>TICKETS</h3>
                            <div class="cart-summary">
                                <span><?php echo count($selected_seats); ?> ticket</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                                <button class="btn-expand" id="toggle-cart-details">⌃</button>
                            </div>
                        </div>

                        <div class="cart-item-details" id="cart-details">
                            <div class="movie-booking-summary">
                                <div class="movie-poster-cart">
                                    <?php if (has_post_thumbnail($movie_id)) : ?>
                                        <?php echo get_the_post_thumbnail($movie_id, 'thumbnail', array('class' => 'cart-poster')); ?>
                                    <?php endif; ?>
                                    
                                    <div class="movie-overlay-cart">
                                        <span class="movie-title-overlay"><?php echo strtoupper(substr(get_the_title($movie_id), 0, 3)); ?></span>
                                    </div>
                                </div>
                                
                                <div class="booking-details">
                                    <h4><?php echo get_the_title($movie_id); ?></h4>
                                    <p><?php echo date('M jS', strtotime($show_date)); ?> at <?php echo format_showtime($show_time); ?></p>
                                    <p>
                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                        SAN MATEO · SCREEN <?php echo $screen_number; ?>
                                    </p>
                                </div>
                                <button class="btn-expand" id="toggle-ticket-details">⌃</button>
                            </div>

                            <div class="ticket-details" id="ticket-details">
                                <?php foreach ($selected_seats as $index => $seat) : 
                                    $seat = trim($seat);
                                ?>
                                <div class="ticket-item" data-seat="<?php echo $seat; ?>">
                                    <div class="seat-indicator">
                                        <span class="seat-letter"><?php echo substr($seat, 0, 1); ?></span>
                                    </div>
                                    <div class="ticket-info">
                                        <span class="ticket-type">Adult</span>
                                        <span class="seat-number">Seat <?php echo $seat; ?></span>
                                    </div>
                                    <div class="ticket-price">$<?php echo number_format($ticket_price + 2.25, 2); ?></div>
                                    <button class="btn-remove" data-seat="<?php echo $seat; ?>" title="Remove ticket">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                        </svg>
                                    </button>
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
                            <div class="pricing-icon">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                                </svg>
                            </div>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modify tickets modal -->
<div id="modify-tickets-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>MODIFY TICKETS</h3>
            <button class="modal-close">&times;</button>
        </div>
        
        <div class="modal-body">
            <p>Would you like to modify your ticket selection?</p>
            
            <div class="ticket-modification">
                <button class="btn btn-secondary btn-fullwidth" id="back-to-seats" style="margin-bottom: 15px;">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                    Back to Seat Selection
                </button>
                <button class="btn btn-danger btn-fullwidth" id="remove-all">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                    </svg>
                    Remove All Tickets
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Promo code success modal -->
<div id="promo-success-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Promo Code Applied</h3>
            <button class="modal-close">&times;</button>
        </div>
        
        <div class="modal-body text-center">
            <div class="success-icon">
                <svg width="40" height="40" fill="#28a745" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <p id="promo-success-message">Your discount has been applied successfully!</p>
            <button class="btn btn-primary" onclick="$('#promo-success-modal').hide()">Continue</button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle cart details
    $('#toggle-cart-details, #toggle-ticket-details').on('click', function() {
        const target = $(this).attr('id') === 'toggle-cart-details' ? '#cart-details' : '#ticket-details';
        const $details = $(target);
        
        if ($details.is(':visible')) {
            $details.slideUp();
            $(this).text('⌄');
        } else {
            $details.slideDown();
            $(this).text('⌃');
        }
    });

    // Initialize with details visible
    $('#cart-details, #ticket-details').show();

    // Proceed to payment
    $('#proceed-to-payment').on('click', function() {
        window.location.href = '<?php echo home_url('/payment/'); ?>';
    });

    // Back to seats
    $('#back-to-seats').on('click', function() {
        window.location.href = '<?php echo home_url('/seat-selection/?showtime=' . $showtime_id); ?>';
    });

    // Remove all tickets
    $('#remove-all').on('click', function() {
        if (confirm('Are you sure you want to remove all tickets?')) {
            // Clear session and redirect
            $.ajax({
                url: cinema_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'cinema_clear_booking',
                    nonce: cinema_ajax.nonce
                },
                success: function() {
                    window.location.href = '<?php echo home_url('/movies'); ?>';
                }
            });
        }
    });
});
</script>

<?php get_footer(); ?>