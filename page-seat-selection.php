<?php
/*
Template Name: Seat Selection
*/

get_header();

// Get showtime data from URL parameters
$showtime_id = isset($_GET['showtime']) ? intval($_GET['showtime']) : 0;
$showtime = get_post($showtime_id);

if (!$showtime || $showtime->post_type !== 'showtimes') {
    wp_redirect(home_url('/movies'));
    exit;
}

$movie_id = get_post_meta($showtime_id, '_showtime_movie_id', true);
$movie = get_post($movie_id);
$screen_number = get_post_meta($showtime_id, '_showtime_screen', true);
$show_date = get_post_meta($showtime_id, '_showtime_date', true);
$show_time = get_post_meta($showtime_id, '_showtime_time', true);
$ticket_price = get_post_meta($showtime_id, '_showtime_ticket_price', true);
$duration = get_post_meta($movie_id, '_movie_duration', true);
$rating = get_post_meta($movie_id, '_movie_rating', true);

// Format date and time for display
$show_date_formatted = date('M jS', strtotime($show_date));
$show_time_formatted = format_showtime($show_time);
?>

<div class="seat-selection-container">
    <!-- Header with progress and cart info -->
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
                    <span>Your Cart is Empty</span>
                    <div class="promo-input-header">
                        <input type="text" placeholder="Add Gift Card, Voucher, Promo Code" class="promo-header-input">
                        <button class="apply-btn">APPLY</button>
                    </div>
                </div>
            </div>
            
            <div class="booking-progress">
                <div class="progress-step active">
                    <span class="step-number">1</span>
                    <span class="step-label">Seats</span>
                </div>
                <div class="progress-step">
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

    <div class="booking-content">
        <div class="cinema-container">
            <div class="booking-layout">
                <!-- Left side - Movie info -->
                <div class="booking-sidebar">
                    <div class="movie-booking-card">
                        <?php if (has_post_thumbnail($movie_id)) : ?>
                            <?php echo get_the_post_thumbnail($movie_id, 'medium', array('class' => 'booking-poster')); ?>
                        <?php endif; ?>
                        
                        <div class="booking-movie-info">
                            <h3>
                                <?php echo get_the_title($movie_id); ?>
                                <?php if ($rating) : ?>
                                    <?php echo get_movie_rating_badge($rating); ?>
                                <?php endif; ?>
                            </h3>
                            <p class="booking-movie-meta">
                                Horror | <?php echo $duration; ?> min
                                <span class="movie-icons">
                                    <svg width="16" height="16" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    <svg width="16" height="16" fill="currentColor"><path d="M9 11H7v6h2v-6zm4-4H11v10h2V7zm4-4h-2v14h2V3z"/></svg>
                                    <svg width="16" height="16" fill="currentColor"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>
                                </span>
                            </p>
                            
                            <div class="showtime-info">
                                <div class="showtime-details">
                                    <div class="showtime-date">
                                        <strong><?php echo $show_date_formatted; ?></strong>
                                        <span>at <?php echo $show_time_formatted; ?></span>
                                    </div>
                                    <div class="showtime-location">
                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                        SAN MATEO • SCREEN <?php echo $screen_number; ?>
                                    </div>
                                </div>
                                <button class="showtime-selected-btn"><?php echo $show_time_formatted; ?></button>
                            </div>
                        </div>
                    </div>

                    <!-- Seat Legend -->
                    <div class="seat-legend">
                        <div class="legend-row">
                            <div class="legend-item">
                                <div class="seat-icon recliner"></div>
                                <span>Recliner</span>
                            </div>
                            <div class="legend-item">
                                <div class="seat-icon wheelchair">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 4c2.21 0 4 1.79 4 4 0 1.1-.45 2.1-1.17 2.83l1.24 1.76C17.41 11.56 18 10.36 18 9c0-3.31-2.69-6-6-6S6 5.69 6 9c0 1.36.59 2.56 1.93 3.59l1.24-1.76C8.45 10.1 8 9.1 8 8c0-2.21 1.79-4 4-4zm-8 9h2.5l2.48-3.5.02-.01c.13-.19.39-.31.67-.31.28 0 .54.12.67.31l.02.01L12.5 13H15c.55 0 1 .45 1 1s-.45 1-1 1h-3c-.35 0-.68-.18-.86-.47L10 13l-1.14 1.53c-.18.29-.51.47-.86.47H5c-.55 0-1-.45-1-1s.45-1 1-1z"/>
                                    </svg>
                                </div>
                                <span>Wheelchair</span>
                            </div>
                            <div class="legend-item">
                                <div class="seat-icon companion">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zm4 18v-6h2.5l-2.54-7.63A1.999 1.999 0 0 0 18.06 7c-.22 0-.42.04-.64.1L15.3 8.2c-.4.16-.85-.05-1.01-.45s.05-.85.45-1.01L16.86 6.1c.42-.17.88-.26 1.34-.26 2.21 0 4 1.79 4 4h-2V22h-2zM12.5 11.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5S11 9.17 11 10s.67 1.5 1.5 1.5zM5.5 6c1.11 0 2-.89 2-2s-.89-2-2-2-2 .89-2 2 .89 2 2 2zm1.5 2h-3C2.67 8 2 8.67 2 9.5c0 .83.67 1.5 1.5 1.5H4v7h1.5v-4.5h1V18H8v-6c0-1.1-.9-2-2-2z"/>
                                    </svg>
                                </div>
                                <span>Companion</span>
                            </div>
                        </div>
                        <div class="legend-row">
                            <div class="legend-item">
                                <div class="seat-icon reserved"></div>
                                <span>Reserved</span>
                            </div>
                            <div class="legend-item">
                                <div class="seat-icon unavailable"></div>
                                <span>Unavailable</span>
                            </div>
                            <div class="legend-item">
                                <div class="seat-icon seat-legend-icon">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </div>
                                <span>SEAT LEGEND</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side - Seat map -->
                <div class="seat-selection-main">
                    <div class="screen-indicator">
                        <div class="screen">SCREEN <?php echo $screen_number; ?></div>
                    </div>

                    <div class="seat-map" data-showtime-id="<?php echo $showtime_id; ?>">

                    <!-- <svg data-v-e3c83d98="" viewBox="0 0 200 200"><use data-v-e3c83d98="" xlink:href="/seats.svg#recliner-seat"></use></svg> -->
                        <?php
                        // Generate seat map layout matching the image
                        $seat_layout = array(
                            'A' => array('seats' => range(1, 11), 'all_regular' => true),
                            'B' => array('seats' => range(1, 11), 'all_regular' => true),
                            'C' => array(
                                'seats' => array(
                                    2 => 'wheelchair', 3 => 'wheelchair', 4 => 'regular', 5 => 'regular', 
                                    6 => 'wheelchair', 7 => 'wheelchair', 8 => 'regular', 9 => 'regular'
                                )
                            ),
                            'D' => array('seats' => range(1, 10), 'all_regular' => true),
                            'E' => array('seats' => array(1, 2, 3, 4, 5, 6, 7, 8, 9), 'reserved' => array(8, 9)),
                            'F' => array('seats' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), 'reserved' => array(4, 5, 6)),
                            'G' => array('seats' => array(1, 2, 3, 4, 5, 6, 7, 8, 9), 'unavailable' => array(1, 2)),
                            'H' => array('seats' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), 'reserved' => array(1, 2, 7, 8, 9, 10))
                        );

                        foreach ($seat_layout as $row => $layout) :
                        ?>
                            <div class="seat-row" data-row="<?php echo $row; ?>">
                                <div class="row-label"><?php echo $row; ?></div>
                                
                                <div class="seats">
                                    <?php
                                    if (isset($layout['all_regular']) && $layout['all_regular']) {
                                        // All regular seats
                                        foreach ($layout['seats'] as $seat_num) {
                                            echo '<button class="seat available" data-seat="' . $row . $seat_num . '" data-price="' . $ticket_price . '" data-type="regular"></button>';
                                        }
                                    } else {
                                        // Mixed seat types
                                        foreach ($layout['seats'] as $seat_num => $seat_type) {
                                            $seat_class = 'seat';
                                            $disabled = '';
                                            
                                            if (is_numeric($seat_num)) {
                                                // Handle special cases
                                                if (isset($layout['reserved']) && in_array($seat_type, $layout['reserved'])) {
                                                    $seat_class .= ' reserved';
                                                    $disabled = ' disabled';
                                                } elseif (isset($layout['unavailable']) && in_array($seat_type, $layout['unavailable'])) {
                                                    $seat_class .= ' unavailable';
                                                    $disabled = ' disabled';
                                                } else {
                                                    $seat_class .= ' available';
                                                }
                                                $seat_identifier = $row . $seat_type;
                                            } else {
                                                // Specific seat types
                                                if ($seat_type === 'wheelchair') {
                                                    $seat_class .= ' wheelchair available';
                                                } elseif ($seat_type === 'companion') {
                                                    $seat_class .= ' companion available';
                                                } else {
                                                    $seat_class .= ' available';
                                                }
                                                $seat_identifier = $row . $seat_num;
                                            }
                                            
                                            echo '<button class="' . $seat_class . '" data-seat="' . $seat_identifier . '" data-price="' . $ticket_price . '" data-type="' . (is_string($seat_type) ? $seat_type : 'regular') . '"' . $disabled . '></button>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="seat-controls">
                        <button class="btn-zoom-out" title="Zoom Out">−</button>
                        <button class="btn-fullscreen" title="Fullscreen">⛶</button>
                        <button class="btn-zoom-in" title="Zoom In">+</button>
                    </div>
                    
                    <div class="seat-count-display">
                        <span>Seats: <span id="selected-seat-numbers">D5</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom fixed bar -->
    <div class="seat-selection-footer">
        <div class="cinema-container">
            <div class="footer-content">
                <div class="selected-seats-summary">
                    <span class="seat-count">Seats: <span id="seat-count">0</span></span>
                </div>
                
                <button class="btn btn-primary btn-continue" id="continue-to-cart" disabled>
                    SELECT A SEAT TO CONTINUE
                </button>
                
                <!-- Fallback form method -->
                <form id="cart-redirect-form" method="POST" action="<?php echo home_url('/cart/'); ?>" style="display: none;">
                    <input type="hidden" name="showtime_id" id="form-showtime-id" value="<?php echo $showtime_id; ?>">
                    <input type="hidden" name="selected_seats" id="form-selected-seats" value="">
                    <input type="hidden" name="cinema_nonce" value="<?php echo wp_create_nonce('cinema_nonce'); ?>">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Seat Selection Modal -->
<div id="seat-selection-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>SELECT YOUR TICKET</h3>
            <button class="modal-close">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="selected-seat-info">
                <p><strong>Seats Selected:</strong> <span id="modal-selected-seats">D5/Recliner Seat</span></p>
                <p class="booking-fee-notice">A $2.25 booking fee per ticket is included in the price of the ticket.</p>
            </div>

            <div class="ticket-options">
                <div class="ticket-type ora-member">
                    <div class="ticket-details">
                        <div class="member-badge">
                            <span>Ora</span>
                            <small>Cinepolis Rewards Member</small>
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
                    <button class="btn btn-dark" data-action="link">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H6.85C4.63 7 2.8 8.83 2.8 11.05s1.83 4.05 4.05 4.05H11v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm5-6h4.15c2.22 0 4.05 1.83 4.05 4.05s-1.83 4.05-4.05 4.05H13v1.9h4.15c2.22 0 4.05-1.83 4.05-4.05S19.37 7 17.15 7H13v1.9z"/>
                        </svg>
                        LINK
                    </button>
                    <button class="btn btn-dark" data-action="card">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                        </svg>
                        CARD
                    </button>
                    <button class="btn btn-dark" data-action="email">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                        EMAIL
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>