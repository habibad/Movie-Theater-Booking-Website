<?php
/**
 * Recommended way to include parent theme styles.
 * (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
 *
 */  

add_action( 'wp_enqueue_scripts', 'astra_child_style' );
				function astra_child_style() {
					wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
					wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
				}

/**
 * Your code goes below.
 */


// Register Custom Post Types
function cinema_register_post_types() {
    // Movies Custom Post Type
    register_post_type('movies', array(
        'labels' => array(
            'name' => 'Movies',
            'singular_name' => 'Movie',
            'add_new' => 'Add New Movie',
            'add_new_item' => 'Add New Movie',
            'edit_item' => 'Edit Movie',
            'new_item' => 'New Movie',
            'view_item' => 'View Movie',
            'search_items' => 'Search Movies',
            'not_found' => 'No movies found',
            'not_found_in_trash' => 'No movies found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'menu_icon' => 'dashicons-video-alt',
        'rewrite' => array('slug' => 'movies')
    ));

    // Showtimes Custom Post Type
    register_post_type('showtimes', array(
        'labels' => array(
            'name' => 'Showtimes',
            'singular_name' => 'Showtime',
        ),
        'public' => true,
        'supports' => array('title', 'custom-fields'),
        'menu_icon' => 'dashicons-clock'
    ));
}
add_action('init', 'cinema_register_post_types');

// Add Custom Meta Boxes
function cinema_add_meta_boxes() {
    // Movie Meta Box
    add_meta_box(
        'movie_details',
        'Movie Details',
        'movie_details_callback',
        'movies',
        'normal',
        'high'
    );

    // Showtime Meta Box
    add_meta_box(
        'showtime_details',
        'Showtime Details',
        'showtime_details_callback',
        'showtimes',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cinema_add_meta_boxes');

// Movie Details Meta Box Callback
function movie_details_callback($post) {
    wp_nonce_field('movie_details_nonce', 'movie_details_nonce');
    
    $director = get_post_meta($post->ID, '_movie_director', true);
    $producer = get_post_meta($post->ID, '_movie_producer', true);
    $cast = get_post_meta($post->ID, '_movie_cast', true);
    $duration = get_post_meta($post->ID, '_movie_duration', true);
    $rating = get_post_meta($post->ID, '_movie_rating', true);
    $release_date = get_post_meta($post->ID, '_movie_release_date', true);
    $genre = get_post_meta($post->ID, '_movie_genre', true);
    $trailer_url = get_post_meta($post->ID, '_movie_trailer_url', true);
    
    echo '<table class="form-table">';
    echo '<tr><th><label for="movie_director">Director</label></th>';
    echo '<td><input type="text" id="movie_director" name="movie_director" value="' . esc_attr($director) . '" size="50" /></td></tr>';
    
    echo '<tr><th><label for="movie_producer">Producer</label></th>';
    echo '<td><input type="text" id="movie_producer" name="movie_producer" value="' . esc_attr($producer) . '" size="50" /></td></tr>';
    
    echo '<tr><th><label for="movie_cast">Cast</label></th>';
    echo '<td><textarea id="movie_cast" name="movie_cast" rows="3" cols="50">' . esc_textarea($cast) . '</textarea></td></tr>';
    
    echo '<tr><th><label for="movie_duration">Duration (minutes)</label></th>';
    echo '<td><input type="number" id="movie_duration" name="movie_duration" value="' . esc_attr($duration) . '" /></td></tr>';
    
    echo '<tr><th><label for="movie_rating">Rating</label></th>';
    echo '<td><select id="movie_rating" name="movie_rating">';
    echo '<option value="G"' . selected($rating, 'G', false) . '>G</option>';
    echo '<option value="PG"' . selected($rating, 'PG', false) . '>PG</option>';
    echo '<option value="PG-13"' . selected($rating, 'PG-13', false) . '>PG-13</option>';
    echo '<option value="R"' . selected($rating, 'R', false) . '>R</option>';
    echo '</select></td></tr>';
    
    echo '<tr><th><label for="movie_release_date">Release Date</label></th>';
    echo '<td><input type="date" id="movie_release_date" name="movie_release_date" value="' . esc_attr($release_date) . '" /></td></tr>';
    
    echo '<tr><th><label for="movie_genre">Genre</label></th>';
    echo '<td><input type="text" id="movie_genre" name="movie_genre" value="' . esc_attr($genre) . '" size="50" /></td></tr>';
    
    echo '<tr><th><label for="movie_trailer_url">Trailer URL</label></th>';
    echo '<td><input type="url" id="movie_trailer_url" name="movie_trailer_url" value="' . esc_attr($trailer_url) . '" size="50" /></td></tr>';
    
    echo '</table>';
}

// Showtime Details Meta Box Callback
function showtime_details_callback($post) {
    wp_nonce_field('showtime_details_nonce', 'showtime_details_nonce');
    
    $movie_id = get_post_meta($post->ID, '_showtime_movie_id', true);
    $screen_number = get_post_meta($post->ID, '_showtime_screen', true);
    $show_date = get_post_meta($post->ID, '_showtime_date', true);
    $show_time = get_post_meta($post->ID, '_showtime_time', true);
    $ticket_price = get_post_meta($post->ID, '_showtime_ticket_price', true);
    
    // Get all movies for dropdown
    $movies = get_posts(array('post_type' => 'movies', 'numberposts' => -1));
    
    echo '<table class="form-table">';
    echo '<tr><th><label for="showtime_movie_id">Movie</label></th>';
    echo '<td><select id="showtime_movie_id" name="showtime_movie_id">';
    echo '<option value="">Select Movie</option>';
    foreach($movies as $movie) {
        echo '<option value="' . $movie->ID . '"' . selected($movie_id, $movie->ID, false) . '>' . $movie->post_title . '</option>';
    }
    echo '</select></td></tr>';
    
    echo '<tr><th><label for="showtime_screen">Screen Number</label></th>';
    echo '<td><input type="number" id="showtime_screen" name="showtime_screen" value="' . esc_attr($screen_number) . '" min="1" max="20" /></td></tr>';
    
    echo '<tr><th><label for="showtime_date">Show Date</label></th>';
    echo '<td><input type="date" id="showtime_date" name="showtime_date" value="' . esc_attr($show_date) . '" /></td></tr>';
    
    echo '<tr><th><label for="showtime_time">Show Time</label></th>';
    echo '<td><input type="time" id="showtime_time" name="showtime_time" value="' . esc_attr($show_time) . '" /></td></tr>';
    
    echo '<tr><th><label for="showtime_ticket_price">Ticket Price</label></th>';
    echo '<td><input type="number" id="showtime_ticket_price" name="showtime_ticket_price" value="' . esc_attr($ticket_price) . '" step="0.01" /></td></tr>';
    
    echo '</table>';
}

// Save Meta Box Data
function cinema_save_meta_boxes($post_id) {
    // Movie meta
    if (isset($_POST['movie_details_nonce']) && wp_verify_nonce($_POST['movie_details_nonce'], 'movie_details_nonce')) {
        $fields = array('director', 'producer', 'cast', 'duration', 'rating', 'release_date', 'genre', 'trailer_url');
        foreach($fields as $field) {
            if (isset($_POST['movie_' . $field])) {
                update_post_meta($post_id, '_movie_' . $field, sanitize_text_field($_POST['movie_' . $field]));
            }
        }
    }
    
    // Showtime meta
    if (isset($_POST['showtime_details_nonce']) && wp_verify_nonce($_POST['showtime_details_nonce'], 'showtime_details_nonce')) {
        $fields = array('movie_id', 'screen', 'date', 'time', 'ticket_price');
        foreach($fields as $field) {
            if (isset($_POST['showtime_' . $field])) {
                update_post_meta($post_id, '_showtime_' . $field, sanitize_text_field($_POST['showtime_' . $field]));
            }
        }
    }
}
add_action('save_post', 'cinema_save_meta_boxes');

// Enqueue Scripts and Styles
function cinema_enqueue_scripts() {
    wp_enqueue_style('cinema-style', get_stylesheet_directory_uri() . '/cinema-style.css');
    wp_enqueue_script('cinema-booking', get_stylesheet_directory_uri() . '/cinema-booking.js', array('jquery'), '1.0', true);
    
    // Localize script for AJAX
    wp_localize_script('cinema-booking', 'cinema_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cinema_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'cinema_enqueue_scripts');

// AJAX Handler for Seat Selection
function cinema_handle_seat_selection() {
    check_ajax_referer('cinema_nonce', 'nonce');
    
    $showtime_id = intval($_POST['showtime_id']);
    $selected_seats = sanitize_text_field($_POST['seats']);
    $booking_data = sanitize_text_field($_POST['booking_data']);
    
    // Start session if not already started
    if (!session_id()) {
        session_start();
    }
    
    $_SESSION['cinema_booking'] = array(
        'showtime_id' => $showtime_id,
        'selected_seats' => $selected_seats,
        'booking_data' => $booking_data,
        'timestamp' => time()
    );
    
    wp_send_json_success(array(
        'message' => 'Seats selected successfully',
        'seats' => $selected_seats,
        'redirect_url' => home_url('/cart/')
    ));
}
add_action('wp_ajax_cinema_seat_selection', 'cinema_handle_seat_selection');
add_action('wp_ajax_nopriv_cinema_seat_selection', 'cinema_handle_seat_selection');

// AJAX Handler for Removing Seats
function cinema_handle_remove_seat() {
    check_ajax_referer('cinema_nonce', 'nonce');
    
    $seat_to_remove = sanitize_text_field($_POST['seat']);
    
    if (!session_id()) {
        session_start();
    }
    
    if (isset($_SESSION['cinema_booking'])) {
        $seats = explode(',', $_SESSION['cinema_booking']['selected_seats']);
        $seats = array_filter($seats, function($seat) use ($seat_to_remove) {
            return trim($seat) !== trim($seat_to_remove);
        });
        $_SESSION['cinema_booking']['selected_seats'] = implode(',', $seats);
        
        wp_send_json_success(array('message' => 'Seat removed successfully'));
    }
    
    wp_send_json_error(array('message' => 'Failed to remove seat'));
}
add_action('wp_ajax_cinema_remove_seat', 'cinema_handle_remove_seat');
add_action('wp_ajax_nopriv_cinema_remove_seat', 'cinema_handle_remove_seat');

// Get Movie Showtimes
function get_movie_showtimes($movie_id, $date = '') {
    $args = array(
        'post_type' => 'showtimes',
        'meta_query' => array(
            array(
                'key' => '_showtime_movie_id',
                'value' => $movie_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => -1
    );
    
    if ($date) {
        $args['meta_query'][] = array(
            'key' => '_showtime_date',
            'value' => $date,
            'compare' => '='
        );
    }
    
    return get_posts($args);
}

// Helper function to format time
function format_showtime($time) {
    return date('g:i A', strtotime($time));
}

// Helper function to get movie rating badge
function get_movie_rating_badge($rating) {
    $badge_class = 'rating-badge';
    switch($rating) {
        case 'G':
            $badge_class .= ' rating-g';
            break;
        case 'PG':
            $badge_class .= ' rating-pg';
            break;
        case 'PG-13':
            $badge_class .= ' rating-pg13';
            break;
        case 'R':
            $badge_class .= ' rating-r';
            break;
    }
    return '<span class="' . $badge_class . '">' . $rating . '</span>';
}

// Create database tables for bookings
function cinema_create_booking_tables() {
    global $wpdb;
    
    $bookings_table = $wpdb->prefix . 'cinema_bookings';
    $seats_table = $wpdb->prefix . 'cinema_seats';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql_bookings = "CREATE TABLE $bookings_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        showtime_id mediumint(9) NOT NULL,
        user_id mediumint(9) NOT NULL,
        booking_date datetime DEFAULT CURRENT_TIMESTAMP,
        total_amount decimal(10,2) NOT NULL,
        booking_status varchar(20) DEFAULT 'pending',
        payment_status varchar(20) DEFAULT 'pending',
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    $sql_seats = "CREATE TABLE $seats_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        booking_id mediumint(9) NOT NULL,
        seat_number varchar(10) NOT NULL,
        seat_type varchar(20) DEFAULT 'regular',
        price decimal(10,2) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_bookings);
    dbDelta($sql_seats);
}
register_activation_hook(__FILE__, 'cinema_create_booking_tables');