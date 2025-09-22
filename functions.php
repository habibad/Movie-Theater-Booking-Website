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


/**
 * Theme bootstrap for Movie Tickets site
 */

add_action('after_setup_theme', function () {
  add_theme_support('post-thumbnails');
  add_image_size('poster-md', 360, 540, true);
  add_image_size('poster-sm', 240, 360, true);
  register_nav_menus(['primary' => 'Primary Menu']);
});

/** -------------------------
 * Sessions (simple cart)
 * ------------------------- */
add_action('init', function () {
  if (!session_id()) { session_start(); }
});

/** -------------------------
 * Assets
 * ------------------------- */
add_action('wp_enqueue_scripts', function () {
  $ver = wp_get_theme()->get('Version') ?: time();
  wp_enqueue_style('tickets', get_stylesheet_directory_uri().'/assets/tickets.css', [], $ver);
  wp_enqueue_script('tickets', get_stylesheet_directory_uri().'/assets/tickets.js', ['jquery'], $ver, true);
  wp_localize_script('tickets', 'TIX', [
    'ajax' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('tix'),
  ]);
});

/** -------------------------
 * Custom Post Types
 * ------------------------- */
function tix_labels($sing, $plur){ return [
  'name' => $plur,'singular_name'=>$sing,'add_new_item'=>"Add New $sing",'edit_item'=>"Edit $sing",
  'new_item'=>"New $sing",'view_item'=>"View $sing",'search_items'=>"Search $plur",'menu_name'=>$plur
];}

add_action('init', function () {
  // Movies
  register_post_type('movie', [
    'labels' => tix_labels('Movie','Movies'),
    'public' => true,
    'has_archive' => true,
    'menu_icon' => 'dashicons-format-video',
    'supports' => ['title','editor','thumbnail','excerpt'],
    'rewrite' => ['slug'=>'movies'],
    'show_in_rest' => true
  ]);

  // Screens (auditoria)
  register_post_type('screen', [
    'labels' => tix_labels('Screen','Screens'),
    'public' => false,
    'show_ui' => true,
    'menu_icon' => 'dashicons-layout',
    'supports' => ['title','editor'],
    'show_in_rest' => true
  ]);

  // Showtimes
  register_post_type('showtime', [
    'labels' => tix_labels('Showtime','Showtimes'),
    'public' => false,
    'show_ui' => true,
    'menu_icon' => 'dashicons-clock',
    'supports' => ['title'],
    'show_in_rest' => true
  ]);

  // Taxonomies
  register_taxonomy('movie_status','movie',[
    'labels'=>['name'=>'Statuses','singular_name'=>'Status'],
    'public'=>true,'hierarchical'=>false,'show_in_rest'=>true
  ]); // terms: now-playing, coming-soon

  register_taxonomy('genre','movie',[
    'labels'=>['name'=>'Genres','singular_name'=>'Genre'],
    'public'=>true,'hierarchical'=>true,'show_in_rest'=>true
  ]);
});

/** -------------------------
 * Meta fields
 * ------------------------- */
// Movie fields
add_action('init', function () {
  $movie_fields = [
    'rating'       => ['type'=>'string','single'=>true,'show_in_rest'=>true, 'default'=>'PG-13'],
    'runtime'      => ['type'=>'number','single'=>true,'show_in_rest'=>true, 'default'=>90],
    'release_date' => ['type'=>'string','single'=>true,'show_in_rest'=>true],
    'trailer_url'  => ['type'=>'string','single'=>true,'show_in_rest'=>true],
    'overview'     => ['type'=>'string','single'=>true,'show_in_rest'=>true],
    'director'     => ['type'=>'string','single'=>true,'show_in_rest'=>true],
    'producer'     => ['type'=>'string','single'=>true,'show_in_rest'=>true],
    'cast'         => ['type'=>'array', 'single'=>true,'show_in_rest'=>['schema'=>['items'=>['type'=>'string']]]],
  ];
  foreach($movie_fields as $k=>$args){
    register_post_meta('movie', "_tix_$k", array_merge($args, ['auth_callback'=>'__return_true']));
  }

  // Screen fields: JSON seat map (rows, cols, labels, wheelchair, blocked)
  register_post_meta('screen','_tix_seatmap',[
    'type'=>'string','single'=>true,'show_in_rest'=>true,'auth_callback'=>'__return_true'
  ]);

  // Showtime fields
  $show_fields = [
    'movie_id'   => ['type'=>'integer','single'=>true,'show_in_rest'=>true],
    'screen_id'  => ['type'=>'integer','single'=>true,'show_in_rest'=>true],
    'start_time' => ['type'=>'string', 'single'=>true,'show_in_rest'=>true], // ISO8601
    'price_adult'=> ['type'=>'number','single'=>true,'show_in_rest'=>true,'default'=>19.00],
    'price_senior'=>['type'=>'number','single'=>true,'show_in_rest'=>true,'default'=>17.00],
    'booking_fee'=>['type'=>'number','single'=>true,'show_in_rest'=>true,'default'=>2.25],
    // seats taken as array of seat codes e.g. ["D6","F4"]
    'taken'      => ['type'=>'array','single'=>true,'show_in_rest'=>['schema'=>['items'=>['type'=>'string']]]],
  ];
  foreach($show_fields as $k=>$args){
    register_post_meta('showtime', "_tix_$k", array_merge($args, ['auth_callback'=>'__return_true']));
  }
});

/** -------------------------
 * Helpers
 * ------------------------- */
function tix_money($n){ return '$'.number_format((float)$n, 2); }
function tix_get_cart(){ return $_SESSION['tix_cart'] ?? []; }
function tix_set_cart($cart){ $_SESSION['tix_cart'] = $cart; }

/** -------------------------
 * AJAX: seat map + cart actions
 * ------------------------- */
add_action('wp_ajax_tix_seatmap','tix_ajax_seatmap');
add_action('wp_ajax_nopriv_tix_seatmap','tix_ajax_seatmap');
function tix_ajax_seatmap(){
  check_ajax_referer('tix','nonce');
  $show_id = absint($_POST['show_id'] ?? 0);
  $screen_id = (int) get_post_meta($show_id, '_tix_screen_id', true);
  $map = get_post_meta($screen_id, '_tix_seatmap', true);
  $taken = get_post_meta($show_id, '_tix_taken', true) ?: [];
  wp_send_json_success(['map'=>json_decode($map, true),'taken'=>$taken]);
}

add_action('wp_ajax_tix_add_to_cart','tix_ajax_add_to_cart');
add_action('wp_ajax_nopriv_tix_add_to_cart','tix_ajax_add_to_cart');
function tix_ajax_add_to_cart(){
  check_ajax_referer('tix','nonce');
  $show_id = absint($_POST['show_id']);
  $seats = array_map('sanitize_text_field', $_POST['seats'] ?? []);
  if(!$show_id || !$seats){ wp_send_json_error('Missing');}

  // merge to taken seats
  $taken = get_post_meta($show_id, '_tix_taken', true) ?: [];
  foreach($seats as $s){ if(in_array($s,$taken)) wp_send_json_error('Seat already taken.'); }
  // For MVP we don't persist yet; we "reserve" by putting in cart only.
  $cart = tix_get_cart();
  $cart[] = ['show_id'=>$show_id,'seats'=>$seats,'qty'=>count($seats),'type'=>'adult'];
  tix_set_cart($cart);
  wp_send_json_success(['cart'=>$cart, 'redirect'=>site_url('/cart/')]);
}

/** -------------------------
 * URL endpoints (pages)
 * ------------------------- */
add_action('init', function () {
  add_rewrite_rule('^cart/?$', 'index.php?pagename=cart', 'top');
  add_rewrite_rule('^checkout/?$', 'index.php?pagename=checkout', 'top');
});

add_filter('template_include', function($tpl){
  if(is_page('select-seats')) return locate_template('templates/page-select-seats.php') ?: $tpl;
  if(is_page('cart'))        return locate_template('templates/page-cart.php') ?: $tpl;
  if(is_page('checkout'))    return locate_template('templates/page-checkout.php') ?: $tpl;
  return $tpl;
});

/** -------------------------
 * Short utilities (showtime queries)
 * ------------------------- */
function tix_showtimes_for_movie($movie_id, $from='now', $days=7){
  $start = $from === 'now' ? current_time('timestamp') : strtotime($from);
  $end = strtotime("+$days days", $start);
  $q = new WP_Query([
    'post_type'=>'showtime',
    'posts_per_page'=>-1,
    'meta_query'=>[
      ['key'=>'_tix_movie_id','value'=>$movie_id,'compare'=>'='],
    ],
    'orderby'=>'meta_value',
    'meta_key'=>'_tix_start_time',
    'order'=>'ASC'
  ]);
  $out = [];
  while($q->have_posts()){ $q->the_post();
    $t = get_post_meta(get_the_ID(), '_tix_start_time', true);
    if(!$t) continue;
    $ts = strtotime($t);
    if($ts >= $start && $ts <= $end){ $out[] = get_post(); }
  } wp_reset_postdata();
  return $out;
}
