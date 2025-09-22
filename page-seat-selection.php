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
?>

<div class="seat-selection-container">
    <!-- Header with movie info -->
    <div class="booking-header">
        <div class="cinema-container">
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
                <!-- Left side - Movie poster and info -->
                <div class="booking-sidebar">
                    <div class="movie-booking-card">
                        <?php if (has_post_thumbnail($movie_id)) : ?>
                            <?php echo get_the_post_thumbnail($movie_id, 'medium', array('class' => 'booking-poster')); ?>
                        <?php endif; ?>
                        
                        <div class="booking-movie-info">
                            <h3><?php echo get_the_title($movie_id); ?> 
                                <?php if ($rating) : ?>
                                    <?php echo get_movie_rating_badge($rating); ?>
                                <?php endif; ?>
                            </h3>
                            <p class="booking-movie-meta">
                                <?php echo ucfirst(get_post_field('post_name', $movie)); ?> | 
                                <?php echo $duration; ?> min
                            </p>
                            
                            <div class="showtime-info">
                                <p><strong><?php echo date('D, M j', strtotime($show_date)); ?></strong> 
                                   at <?php echo format_showtime($show_time); ?></p>
                                <p class="screen-info">Screen <?php echo $screen_number; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Seat Legend -->
                    <div class="seat-legend">
                        <div class="legend-item">
                            <div class="seat-icon recliner"></div>
                            <span>Recliner</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat-icon wheelchair"></div>
                            <span>Wheelchair</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat-icon companion"></div>
                            <span>Companion</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat-icon reserved"></div>
                            <span>Reserved</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat-icon unavailable"></div>
                            <span>Unavailable</span>
                        </div>
                    </div>
                </div>

                <!-- Right side - Seat map -->
                <div class="seat-selection-main">
                    <div class="screen-indicator">
                        <div class="screen">SCREEN <?php echo $screen_number; ?></div>
                    </div>

                    <div class="seat-map" data-showtime-id="<?php echo $showtime_id; ?>">
                        <?php
                        // Generate seat map (8 rows, varying seats per row)
                        $seat_layout = array(
                            'A' => array('type' => 'regular', 'count' => 11, 'start' => 1),
                            'B' => array('type' => 'regular', 'count' => 11, 'start' => 1),
                            'C' => array('type' => 'mixed', 'seats' => array(
                                1 => 'unavailable', 2 => 'wheelchair', 3 => 'companion',
                                4 => 'regular', 5 => 'regular', 6 => 'companion', 7 => 'wheelchair',
                                8 => 'regular', 9 => 'regular'
                            )),
                            'D' => array('type' => 'regular', 'count' => 10, 'start' => 1),
                            'E' => array('type' => 'mixed', 'seats' => array(
                                1 => 'regular', 2 => 'regular', 3 => 'regular', 4 => 'regular',
                                5 => 'regular', 6 => 'regular', 7 => 'regular', 8 => 'reserved',
                                9 => 'reserved'
                            )),
                            'F' => array('type' => 'mixed', 'seats' => array(
                                1 => 'regular', 2 => 'regular', 3 => 'regular', 4 => 'reserved',
                                5 => 'reserved', 6 => 'regular', 7 => 'regular', 8 => 'regular',
                                9 => 'regular'
                            )),
                            'G' => array('type' => 'mixed', 'seats' => array(
                                1 => 'unavailable', 2 => 'unavailable', 3 => 'regular', 4 => 'regular',
                                5 => 'regular', 6 => 'regular', 7 => 'regular', 8 => 'regular',
                                9 => 'regular'
                            )),
                            'H' => array('type' => 'mixed', 'seats' => array(
                                1 => 'reserved', 2 => 'reserved', 3 => 'regular', 4 => 'regular',
                                5 => 'regular', 6 => 'regular', 7 => 'reserved', 8 => 'reserved',
                                9 => 'reserved', 10 => 'reserved'
                            ))
                        );

                        foreach ($seat_layout as $row => $layout) :
                        ?>
                            <div class="seat-row" data-row="<?php echo $row; ?>">
                                <div class="row-label"><?php echo $row; ?></div>
                                
                                <div class="seats">
                                    <?php
                                    if ($layout['type'] === 'regular') {
                                        for ($i = 1; $i <= $layout['count']; $i++) {
                                            echo '<button class="seat available" data-seat="' . $row . $i . '" data-price="' . $ticket_price . '"></button>';
                                        }
                                    } else {
                                        foreach ($layout['seats'] as $seat_num => $seat_type) {
                                            $seat_class = 'seat ' . $seat_type;
                                            if ($seat_type === 'regular') {
                                                $seat_class = 'seat available';
                                            }
                                            echo '<button class="' . $seat_class . '" data-seat="' . $row . $seat_num . '" data-price="' . $ticket_price . '"';
                                            if (in_array($seat_type, array('reserved', 'unavailable'))) {
                                                echo ' disabled';
                                            }
                                            echo '></button>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="seat-controls">
                        <button class="btn-zoom-out">−</button>
                        <button class="btn-fullscreen">⛶</button>
                        <button class="btn-zoom-in">+</button>
                    </div>
                </div>
            </div>

            <!-- Selected seats info -->
            <div class="selected-seats-info">
                <div class="seats-summary">
                    <span class="selected-count">Seats: <span id="seat-count">0</span></span>
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
                <p>Seats Selected: <span id="modal-selected-seats">D5/Recliner Seat</span></p>
                <p class="booking-fee-notice">A $2.25 booking fee per ticket is included in the price of the ticket.</p>
            </div>

            <div class="ticket-options">
                <div class="ticket-type">
                    <div class="ticket-details">
                        <div class="member-badge">Ora<br><small>Cinepolis Rewards Member</small></div>
                        <div class="ticket-info">
                            <select class="ticket-select">
                                <option>Adult</option>
                                <option>Child</option>
                                <option>Senior</option>
                            </select>
                            <small>2 ticket types available</small>
                        </div>
                    </div>
                    <div class="ticket-price">
                        <span class="price">$21.25</span>
                        <small>($19.00 + $2.25 Booking Fee)</small>
                        <button class="btn-add">ADD</button>
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
                        <button class="btn-add">ADD</button>
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
                        <button class="btn-add">ADD</button>
                    </div>
                </div>
            </div>

            <div class="member-options">
                <h4>WOULD YOU LIKE TO PURCHASE TICKETS FOR OTHER MEMBERS?</h4>
                <p>Link a member</p>
                
                <div class="link-buttons">
                    <button class="btn btn-dark">LINK</button>
                    <button class="btn btn-dark">CARD</button>
                    <button class="btn btn-dark">EMAIL</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>