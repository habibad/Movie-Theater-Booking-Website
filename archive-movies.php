<?php
get_header(); ?>

<div class="cinema-container">
    <div class="movies-header">
        <h1 class="movies-title">MOVIES</h1>
        <p class="movies-subtitle">Browse the latest movies out now, advanced tickets, and movies coming soon to Cinépolis.</p>
    </div>

    <div class="movies-filter">
        <nav class="filter-tabs">
            <button class="filter-tab active" data-filter="now-playing">Now Playing</button>
            <button class="filter-tab" data-filter="coming-soon">Coming Soon</button>
            <button class="filter-tab" data-filter="all">All</button>
            <button class="filter-tab" data-filter="my-movies">My Movies</button>
        </nav>
    </div>

    <div class="movies-grid">
        <?php 
        if (have_posts()) :
            while (have_posts()) : the_post();
                $movie_id = get_the_ID();
                $director = get_post_meta($movie_id, '_movie_director', true);
                $duration = get_post_meta($movie_id, '_movie_duration', true);
                $rating = get_post_meta($movie_id, '_movie_rating', true);
                $release_date = get_post_meta($movie_id, '_movie_release_date', true);
                $genre = get_post_meta($movie_id, '_movie_genre', true);
                
                // Check if movie is now playing or coming soon
                $current_date = date('Y-m-d');
                $is_now_playing = ($release_date <= $current_date);
                $status_class = $is_now_playing ? 'now-playing' : 'coming-soon';
        ?>
        
        <div class="movie-card <?php echo $status_class; ?>">
            <div class="movie-poster">
                <a href="<?php echo get_permalink(); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('medium', array('class' => 'poster-image')); ?>
                    <?php else : ?>
                        <div class="poster-placeholder">
                            <span>No Image</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="movie-overlay">
                        <div class="play-button">
                            <svg width="24" height="24" fill="white">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                        <div class="movie-actions">
                            <button class="action-btn" title="Add to Watch List">
                                <svg width="20" height="20" fill="white">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                            <button class="action-btn" title="Share">
                                <svg width="20" height="20" fill="white">
                                    <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="movie-info">
                <h3 class="movie-title">
                    <a href="<?php echo get_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h3>
                
                <div class="movie-meta">
                    <?php if ($rating) : ?>
                        <?php echo get_movie_rating_badge($rating); ?>
                    <?php endif; ?>
                    
                    <?php if ($duration) : ?>
                        <span class="duration"><?php echo $duration; ?> min</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($genre) : ?>
                    <p class="movie-genre"><?php echo $genre; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php 
            endwhile;
        else :
        ?>
            <div class="no-movies">
                <p>No movies found.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (have_posts()) : ?>
        <div class="movies-pagination">
            <?php
            echo paginate_links(array(
                'prev_text' => '&laquo; Previous',
                'next_text' => 'Next &raquo;',
                'mid_size' => 2,
                'end_size' => 1,
            ));
            ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>