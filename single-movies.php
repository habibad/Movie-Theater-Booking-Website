<?php
get_header();

while (have_posts()) : the_post();
    $movie_id = get_the_ID();
    $director = get_post_meta($movie_id, '_movie_director', true);
    $producer = get_post_meta($movie_id, '_movie_producer', true);
    $cast = get_post_meta($movie_id, '_movie_cast', true);
    $duration = get_post_meta($movie_id, '_movie_duration', true);
    $rating = get_post_meta($movie_id, '_movie_rating', true);
    $release_date = get_post_meta($movie_id, '_movie_release_date', true);
    $genre = get_post_meta($movie_id, '_movie_genre', true);
    $trailer_url = get_post_meta($movie_id, '_movie_trailer_url', true);
?>

<div class="movie-hero">
    <?php if ($trailer_url) : ?>
        <div class="trailer-background">
            <video autoplay muted loop>
                <source src="<?php echo esc_url($trailer_url); ?>" type="video/mp4">
            </video>
            <div class="trailer-overlay"></div>
        </div>
    <?php endif; ?>
    
    <div class="hero-content">
        <div class="cinema-container">
            <div class="movie-hero-info">
                <h1 class="movie-hero-title">
                    <?php the_title(); ?>
                    <?php if ($rating) : ?>
                        <?php echo get_movie_rating_badge($rating); ?>
                    <?php endif; ?>
                </h1>
                
                <div class="movie-hero-meta">
                    <span class="release-date">Release Date <?php echo date('l, F j, Y', strtotime($release_date)); ?></span>
                    <span class="divider">|</span>
                    <span class="genre"><?php echo $genre; ?></span>
                    <span class="divider">|</span>
                    <span class="duration"><?php echo $duration; ?> min</span>
                </div>

                <div class="movie-actions">
                    <button class="btn btn-primary add-to-watchlist">
                        <svg width="20" height="20" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Add to Watch List
                    </button>
                    
                    <button class="btn btn-secondary rate-movie">
                        <svg width="20" height="20" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Rate
                    </button>
                    
                    <button class="btn btn-secondary add-to-favorites">
                        <svg width="20" height="20" fill="currentColor">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        Add to Favorites
                    </button>
                </div>

                <?php if ($trailer_url) : ?>
                    <button class="play-trailer-btn">
                        <svg width="24" height="24" fill="white">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>

<div class="movie-details">
    <div class="cinema-container">
        <div class="movie-content">
            <div class="movie-poster-sidebar">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('medium', array('class' => 'movie-poster-large')); ?>
                <?php endif; ?>
            </div>
            
            <div class="movie-info-main">
                <div class="movie-overview">
                    <h2>Overview</h2>
                    <p><?php echo get_the_content(); ?></p>
                </div>

                <div class="movie-credits">
                    <?php if ($director) : ?>
                        <div class="credit-item">
                            <strong>Director:</strong>
                            <span><?php echo $director; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($producer) : ?>
                        <div class="credit-item">
                            <strong>Producer:</strong>
                            <span><?php echo $producer; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($cast) : ?>
                        <div class="credit-item">
                            <strong>Cast:</strong>
                            <span><?php echo $cast; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="showtimes-section">
    <div class="cinema-container">
        <h2>Showtimes</h2>
        
        <div class="showtime-filters">
            <button class="filter-btn active" data-date="all">ALL</button>
            <button class="filter-btn" data-date="today">TODAY</button>
            <button class="filter-btn" data-date="tomorrow">TOMORROW</button>
        </div>

        <div class="showtimes-container">
            <?php
            // Get showtimes for this movie
            $showtimes = get_movie_showtimes($movie_id);
            
            if ($showtimes) :
                // Group showtimes by date
                $grouped_showtimes = array();
                foreach ($showtimes as $showtime) {
                    $show_date = get_post_meta($showtime->ID, '_showtime_date', true);
                    $grouped_showtimes[$show_date][] = $showtime;
                }
                
                foreach ($grouped_showtimes as $date => $date_showtimes) :
                    $date_obj = new DateTime($date);
                    $today = new DateTime();
                    $tomorrow = new DateTime('+1 day');
                    
                    $date_class = 'showtime-date';
                    if ($date_obj->format('Y-m-d') == $today->format('Y-m-d')) {
                        $date_class .= ' today';
                    } elseif ($date_obj->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
                        $date_class .= ' tomorrow';
                    }
            ?>
            
            <div class="<?php echo $date_class; ?>">
                <h3 class="showtime-date-title">
                    <?php 
                    if ($date_obj->format('Y-m-d') == $today->format('Y-m-d')) {
                        echo 'TODAY ' . strtoupper($date_obj->format('D, M j, Y'));
                    } elseif ($date_obj->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
                        echo 'TOMORROW ' . strtoupper($date_obj->format('D, M j, Y'));
                    } else {
                        echo strtoupper($date_obj->format('D, M j, Y'));
                    }
                    ?>
                </h3>
                
                <div class="showtime-slots">
                    <?php foreach ($date_showtimes as $showtime) :
                        $show_time = get_post_meta($showtime->ID, '_showtime_time', true);
                        $screen_number = get_post_meta($showtime->ID, '_showtime_screen', true);
                        $ticket_price = get_post_meta($showtime->ID, '_showtime_ticket_price', true);
                    ?>
                    
                    <button class="showtime-slot" data-showtime-id="<?php echo $showtime->ID; ?>" 
                            data-screen="<?php echo $screen_number; ?>" 
                            data-price="<?php echo $ticket_price; ?>">
                        <span class="showtime-time"><?php echo format_showtime($show_time); ?></span>
                    </button>
                    
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php 
                endforeach;
            else :
            ?>
                <div class="no-showtimes">
                    <p>No showtimes available for this movie.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>