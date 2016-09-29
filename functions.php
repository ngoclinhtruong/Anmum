<?php
/**
 * These are all the widgets file
 * please place all your future widgets within the 'widgets' folder
 */

// update_option( 'siteurl', 'http://www.anmum.dev:81' );
// update_option( 'home', 'http://www.anmum.dev:81' );

include_once 'widgets/product_widget.php';
include_once 'widgets/most_read_widget.php';
include_once 'widgets/related_article_widget.php';
include_once 'widgets/static_banner_widget.php';
include_once 'widgets/careline_banner_widget.php';

/**
 * FB Library. for counting comments and likes
 */
require_once 'lib/fb/facebook.php';

add_action('init', 'set_fb_config');


function set_fb_config()
{
	$config = array();
	$config['appId'] = '589257707771176';
	$config['secret'] = '846e7869f8247efc4ef6538cc065d497';

	global $facebook;

	$facebook = new Facebook(array(
		'appId' => $config['appId'],
		'secret' => $config['secret'],
	));
}

/**
 * Recaptcha Library.
 */
require_once 'lib/recaptcha/recaptchalib.php';

add_action('init', 'set_captcha_config');

function set_captcha_config()
{
	global $recaptcha;

	if (defined('ENVIRONMENT')) {
		switch (ENVIRONMENT) {
			case 'development':
				$recaptcha = array(
					'publickey' => '6LeebOMSAAAAAMH91k7-wsr5LsI6YhiWA0oWhDNH',
					'privatekey' => '6LeebOMSAAAAAJruO2OygkdewJl4hiIIGk3kW3pc'
				);
				break;

			case 'staging':
				$recaptcha = array(
					'publickey' => '6LcDd-MSAAAAAOKa62HLyGQmcn7a1B4Ri96dS5EN',
					'privatekey' => '6LcDd-MSAAAAAHpMuQYzXNaNMxgPT2WlpFEIEz9I'
				);
				break;

			case 'production':
				$recaptcha = array(
					'publickey' => '6LcJSeQSAAAAAAwSV-kiDg61aYa7JdUcc2OMJGr5',
					'privatekey' => '6LcJSeQSAAAAAJ9didGh80RLXAsjIjWxmKmUWaxZ'
				);
				break;
		}
	}
}

add_action('init', 'my_add_excerpts_to_pages');
function my_add_excerpts_to_pages()
{
	add_post_type_support('page', 'excerpt');
}

add_action('after_setup_theme', 'anmum_setup');
function anmum_setup()
{
	load_theme_textdomain('anmum', get_stylesheet_directory() . '/languages');

	$loaded = load_theme_textdomain('anmum', get_stylesheet_directory() . '/languages');

	//require only in admin!
	if (is_admin()) {
		require_once(get_stylesheet_directory() . '/theme_options.php');
	}

	if (!isset($_COOKIE['anmum_visitor'])) {
		setcookie("anmum_visitor", true, time() + 3600, "/", str_replace(array('http://', '/staging'), array(' ', ' '), get_bloginfo('url')));
	}

	// print_r( str_replace( array('http://', 'staging'), array('', ''), get_bloginfo('url') ) );

}

// if ( ! function_exists( 'add_first_and_last' ) ) {
// 	add_filter('wp_nav_menu', 'add_first_and_last');

// 	function add_first_and_last($output) {
// 	  $output = preg_replace('/class="menu-item/', 'class="alpha menu-item', $output, 1);
// 	  $output = substr_replace($output, 'class="omega menu-item', strripos($output, 'class="menu-item'), strlen('class="menu-item'));
// 	  return $output;
// 	}
// }

if (!function_exists('custom_excerpt_more')) {
	function custom_excerpt_more($output)
	{
		if (has_excerpt() && !is_attachment()) {
			return $output;
		}
	}
}

add_filter('get_the_excerpt', 'custom_excerpt_more');

if (!function_exists('special_nav_class')) {
	add_filter('nav_menu_css_class', 'special_nav_class', 10, 2);


	function special_nav_class($classes, $item)
	{
		$menu_locations = get_nav_menu_locations();
		// global $menu_size;

		if (has_term($menu_locations['primary'], 'nav_menu', $item)) {
			if ('0' === $item->menu_item_parent) {
				array_push($classes, count_menu_items() . ' columns rounded-corner');
			}
		}

		return $classes;
	}
}

function menu_set_current($sorted_menu_items, $args)
{
	$last_top = 0;
	foreach ($sorted_menu_items as $key => $obj) {
		// it is a top lv item?

		if (0 == $obj->menu_item_parent) {
			// set the key of the parent
			$last_top = $key;
		} else {
			if (1 == $obj->current_item_parent) {
				$sorted_menu_items[$last_top]->classes['current-menu-item'] = 'current-menu-item';
			}
		}

		if (true == in_array('current-category-ancestor', $obj->classes)) {
			$sorted_menu_items[$last_top]->classes['current-menu-item'] = 'current-menu-item';
		}
	}

	// print_r( $sorted_menu_items[$last_top] );


	return $sorted_menu_items;
}

add_filter('wp_nav_menu_objects', 'menu_set_current', 10, 2);

function count_menu_items()
{
	$menu = 'primary';
	$menu_location = get_nav_menu_locations();

	$menu_obj = get_term($menu_location[$menu], 'nav_menu');

	$args = array(
		'parent' => 0,
		'hide_empty' => 0,
		'hierarchical' => 0,
		'exclude' => '1',
	);

	$product_size = new WP_Query('post_type=anmum-product');
	$product_size = $product_size->post_count;

	$cat_arr = get_categories($args);

	$menu_size = intval($menu_obj->count) - (sizeof($cat_arr) + $product_size);
	$nav_class = convert_number_to_words(floor(16 / $menu_size));
	return $nav_class;
}

if (!function_exists('wpb_set_post_views')) {
	function wpb_set_post_views($postID)
	{
		$count_key = 'wpb_post_views_count';
		$count = get_post_meta($postID, $count_key, true);
		if ($count == '') {
			$count = 0;
			delete_post_meta($postID, $count_key);
			add_post_meta($postID, $count_key, '0');
		} else {
			$count++;
			update_post_meta($postID, $count_key, $count);
		}
	}

	//To keep the count accurate, lets get rid of prefetching
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
}

if (!function_exists('wpb_track_post_views')) {
	function wpb_track_post_views($post_id)
	{
		if (!is_single()) return;
		if (empty ($post_id)) {
			global $post;
			$post_id = $post->ID;
		}
		wpb_set_post_views($post_id);
	}

	add_action('wp_head', 'wpb_track_post_views');
}

if (!function_exists('wpb_get_post_views')) {
	function wpb_get_post_views($postID)
	{
		$count_key = 'wpb_post_views_count';
		$count = get_post_meta($postID, $count_key, true);
		if ($count == '') {
			delete_post_meta($postID, $count_key);
			add_post_meta($postID, $count_key, '0');
			return "0 View";
		}
		return $count . ' Views';
	}
}

if (!function_exists('wpb_get_most_read')) {
	function wpb_get_most_read()
	{
		$most_read_arr = array();

		$q_args = array(
			'orderby' => 'meta_value_num',
			'meta_key' => 'wpb_post_views_count',
			'posts_per_page' => 5
		);

		$query = new WP_Query($q_args);

		if ($query->have_posts()) {
			$most_read_arr = $query->posts;
		}

		return $most_read_arr;
	}
}

if (!function_exists('wpb_get_related_articles_by_product')) {
	function wpb_get_related_articles_by_product($article_slug = '')
	{
		$related_article_arr = array();

		// echo $article_slug;

		switch ($article_slug) {
			case 'pregnaplan':
				$qcat = 'getting-pregnant';
				break;
			case 'materna':
				$qcat = 'pregnant-mums';
				break;
			case 'lacta':
				$qcat = 'new-mums';
				break;
			case 'essential':
				$qcat = 'your-child';
				break;
		}

		$q_args = array(
			'orderby' => 'date',
			'order' => 'DESC',
			'category_name' => $qcat,
			'posts_per_page' => 3
		);

		$query = new WP_Query($q_args);

		// print_r($query);

		if ($query->have_posts()) {
			$related_article_arr = $query->posts;
		}

		return $related_article_arr;
	}
}

// add_action( 'init', 'custom_taxonomy' );

function create_product_type()
{
	register_post_type('anmum-product',
		array(
			'labels' => array(
				'name' => __('Products', 'anmum'),
				'singular_name' => __('Product', 'anmum'),
				'not_found' => __('No products found', 'anmum'),
				'add_new_item' => __('Add New Product', 'anmum'),
				'edit_new_item' => __('Edit Product', 'anmum'),
				'new_item' => __('New Product', 'anmum'),
				'view_item' => __('View Product', 'anmum')
			),
			'labels' => array(
				'name' => __('Products'),
				'singular_name' => __('Product'),
				'not_found' => 'No products found'
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'products'),
			'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats'),
		)
	);
}

add_action('init', 'create_product_type');

function create_happening_type()
{
	register_post_type('anmum-happening',
		array(
			'labels' => array(
				'name' => __('Happenings', 'anmum'),
				'singular_name' => __('Happening', 'anmum'),
				'not_found' => __('No happenings found', 'anmum'),
				'add_new_item' => __('Add New Happening', 'anmum'),
				'edit_new_item' => __('Edit Happening', 'anmum'),
				'new_item' => __('New Happening', 'anmum'),
				'view_item' => __('View Happening', 'anmum')
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'happenings'),
			'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats'),
		)
	);
}

add_action('widgets_init', function () {
	register_sidebar(array(
		'name' => __('Article Sidebar'),
		'id' => 'article-sidebar',
		'description' => __('Widgets in this area will be shown on Articles section.'),
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>'
	));

	register_sidebar(array(
		'name' => __('Product Sidebar'),
		'id' => 'product-sidebar',
		'description' => __('Widgets in this area will be shown on Products section.'),
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>'
	));
});

add_action('init', 'create_happening_type');


// Custom walker for menu
class gadata_walker extends Walker_Nav_Menu
{
	function start_el(&$output, $item, $depth, $args)
	{
		global $wp_query;
		$indent = ($depth) ? str_repeat("\t", $depth) : '';

		$class_names = $value = '';

		$classes = empty($item->classes) ? array() : (array)$item->classes;

		$class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));
		$class_names = ' class="' . esc_attr($class_names) . '"';

		$output .= $indent . '<li id="menu-item-' . $item->ID . '"' . $value . $class_names . '>';

		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
		$attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
		$datatitle = !empty($item->attr_title) ? ' data-label="' . esc_attr($item->attr_title) . '"' : '';
		$description = !empty($item->description) ? '<span>' . esc_attr($item->description) . '</span>' : '';

		if ($depth != 0) {
			$description = $append = $prepend = "";
		}

		$item_output = $args->before;
		$item_output .= '<a' . $attributes . ' data-category="Header" data-action="Navi Click" data-label="' . apply_filters('the_title', $item->title, $item->ID) . '">';
		$item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID);
		$item_output .= $description . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
	}
}

if (!function_exists('fb_get_like_comment')) {
	function fb_get_like_comment($post_name = '')
	{
		$social_comment_arr = array();
		global $facebook;

		try {
			$args = array(
				'method' => 'fql.query',
				'query' => "SELECT like_count FROM link_stat WHERE url='" . home_url($post_name) . "'"
			);

			$social_comment_arr = $facebook->api($args);
		} catch (FacebookApiException $e) {
			return array();
		}

		return $social_comment_arr;
	}
}

add_action('wp_enqueue_scripts', 'anmum_scripts');

function anmum_scripts()
{
	wp_enqueue_script(
		'plugin',
		get_stylesheet_directory_uri() . '/js/plugins.js',
		array('jquery'),
		false,
		true
	);

	$dependencies = array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-accordion', 'jquery-effects-core', 'plugin');

	wp_enqueue_script(
		'main',
		get_stylesheet_directory_uri() . '/js/main.js',
		$dependencies,
		false,
		true
	);

	global $recaptcha;

	wp_localize_script('main', 'global_var', array(
			'current_page' => esc_url($_SERVER['REQUEST_URI']),
			'base_url' => esc_url(home_url()),
			'theme_url' => esc_url(get_stylesheet_directory_uri()),
			'ajax_url' => admin_url('admin-ajax.php')
			// ,
			// 'sso_secret'			=> '05ad1bb16a7e61fa217a60f7f42ccb4d25a83f80'
		)
	);
}

add_action('wp_ajax_js_curl', 'js_curl'); // ajax for logged in users
add_action('wp_ajax_nopriv_js_curl', 'js_curl'); // ajax for not logged in users

function js_curl()
{
	// header("Content-type: application/json");
	// print_r($_POST);
	$error_valid = true;
	$error_msg = array();
	$base_url = 'http://www.connectedmums.com.my/';

	$sso_secret = '05ad1bb16a7e61fa217a60f7f42ccb4d25a83f80';
	$timestamp = time();
	// echo $timestamp;

	$passphrase = sha1($sso_secret . $timestamp);
	// echo $passphrase;

	$domain = $_POST['domain'];
	// Initializing curl
	$ch = curl_init();
	switch ($domain) {
		case 'product':
			if (isset($_POST['product_id'])) {
				$inputs['product_id'] = $_POST['product_id'];
			}

			$json_url = 'http://sky-forge.com/anmum_tcp_api/api/product';

			// print_r( $inputs );
			break;
		case 'sample':
			// validate input first
			// print_r( $_POST );
			$req_arr = array();
			$req_arr['e_request_sample_channel_mail'] = 0;
			$req_arr['e_request_sample_channel_email'] = 0;
			$req_arr['e_request_sample_channel_sms'] = 0;
			$req_arr['e_request_sample_channel_call'] = 0;

			$req_arr['user_pregnant_week'] = 0;
			$req_arr['sampleflavour'] = '';
			$req_arr['samplevariant'] = '';

			$check_final_q = true;
			$chosen_channel = null;

			// shared server side validation for both
			if (isset($_POST['request-submitted'])) {
				if ('' === sanitize_text_field($_POST['e_request_sample_sender'])) {
					$error_valid = false;
					array_push($error_msg, '1');
				}

				if ('' === sanitize_text_field($_POST['e_request_sample_contact'])) {
					$error_valid = false;
					array_push($error_msg, '2');
				}

				if ('' === trim($_POST['e_request_sample_contact_prefix'])) {
					$error_valid = false;
					array_push($error_msg, '3');
				}

				if ('' === sanitize_email($_POST['e_request_sample_email'])) {
					$error_valid = false;
					array_push($error_msg, '4');
				}

				if ('' === trim($_POST['product_id'])) {
					$error_valid = false;
					array_push($error_msg, '5');
				}

				if ('' === trim($_POST['e_request_sample_choice'])) {
					$error_valid = false;
					array_push($error_msg, '6');
				} else {
					if ('yes' === $_POST['e_request_sample_choice']) {
						if ('' === trim($_POST['e_request_sample_brand'])) {
							$error_valid = false;
							array_push($error_msg, '7');
						}
					}
				}

				// print_r($_POST);

				if ('juno' != trim($_POST['e_request_sample_source']) && 'anmum.com.my' != $_POST['e_request_sample_source']) {
					$error_valid = false;
					array_push($error_msg, 'source');
				}

				if ('anmum.com.my' === trim($_POST['e_request_sample_source'])) {
					if ('desktop' !== $_POST['e_request_sample_phone_os']) {
						$error_valid = false;
						array_push($error_msg, 'site');
					}
				} else {
					if ('ios' !== trim($_POST['e_request_sample_phone_os']) && 'android' !== trim($_POST['e_request_sample_phone_os'])) {
						$error_valid = false;
						array_push($error_msg, 'smartphone');
					}
				}
			}

			if (true === isset($_POST['e_request_sample_action']) && 'Get a sample' === $_POST['e_request_sample_action']) {
				if (isset($_POST['request-submitted'])) {
					if (false === isset($_POST['e_request_sample_stage'])) {
						$error_valid = false;
						array_push($error_msg, '8');
					} else {
						switch ($_POST['e_request_sample_stage']) {
							case 'Pregnant':
								// if( '' === trim( $_POST['user_pregnant_week']  ) ) {
								// 	$error_valid  = false;
								// 	array_push($error_msg, '9');
								// }

								// unset( $_POST['user_breastfeeding_month'] );
								break;
							case 'Lactating':
								// if( '' === trim( $_POST['user_breastfeeding_month']  ) ) {
								// 	$error_valid  = false;
								// 	array_push($error_msg, '10');
								// }

								// unset( $_POST['user_pregnant_week'] );
								break;
							default:
								# code...
								unset($_POST['user_breastfeeding_month']);
								unset($_POST['user_pregnant_week']);
								break;
						}
					}

					if ('' === esc_html($_POST['e_request_sample_mailing'])) {
						$error_valid = false;
						array_push($error_msg, '11');
					}

					if ('' === sanitize_text_field($_POST['e_request_sample_postcode'])) {
						$error_valid = false;
						array_push($error_msg, '12');
					}

					if ('' === sanitize_text_field($_POST['e_request_sample_country'])) {
						$error_valid = false;
						array_push($error_msg, '21');
					}

					if ('' === sanitize_text_field($_POST['e_request_sample_state'])) {
						$error_valid = false;
						array_push($error_msg, '13');
					}

					if (false === isset($_POST['e_request_sample_channel_member'])) {
						$error_valid = false;
						array_push($error_msg, '14');
						// echo 'test';
					} else {
						if (false === (bool)$_POST['e_request_sample_channel_member']) {
							$check_final_q = false;

							if (false === isset($_POST['e_request_sample_channel_receive'])) {
								$error_valid = false;
								array_push($error_msg, '15');
							} else {
								if (true === (bool)$_POST['e_request_sample_channel_receive']) {
									$check_final_q = true;
								}
							}
						}
					}
					$channel_lang = '';

					if (true === $check_final_q) {
						if (false === isset($_POST['profile-memberAM-yes'])) {
							// echo 'test';
							$error_valid = false;
							array_push($error_msg, '16');
						} else {
							$chosen_channel = $_POST['profile-memberAM-yes'];

							if (1 < sizeof($chosen_channel)) {
								foreach ($chosen_channel as $key => $value) {
									$req_arr['e_request_sample_channel_' . $value] = 1;
								}
							} else {
								$req_arr['e_request_sample_channel_' . $chosen_channel] = 1;
							}

							unset($_POST['profile-memberAM-yes']);
						}


						if ('' === trim($_POST['e_request_sample_channel_lang'])) {
							$error_valid = false;
							array_push($error_msg, '17');
						}


						if (true === isset($_POST['e_request_sample_channel_lang'])) {
							$channel_lang = $_POST['e_request_sample_channel_lang'];
						}


					}

				}
			} else {
				if (isset($_POST['request-submitted'])) {
					if ('' === sanitize_text_field($_POST['e_request_sample_receiver'])) {
						$error_valid = false;
					}
				}
			}

			if (false === $error_valid) {
				# code...
				// print_r($error_msg);
				header("Content-type: application/json");
				echo json_encode($error_valid);
				echo json_encode($error_msg);
				exit;
			}


			// if input validated. proceed with curl


			$inputs = array();

			if ($_POST['product_id'] == 2) {
				$product_name = 'Anmum Materna';
				$variant = 'Materna';
				$flavour = 'Plain + Chocolate';
			} else if ($_POST['product_id'] == 5) {
				$product_name = 'Anmum Lacta Plain';
				$variant = 'Lacta';
				$flavour = 'Plain';
			} else if ($_POST['product_id'] == 6) {
				$product_name = 'Anmum Essential Plain';
				$variant = 'Essential';
				$flavour = 'Plain';
			} else if ($_POST['product_id'] == 7) {
				$product_name = 'Anmum Essential Honey';
				$variant = 'Essential';
				$flavour = 'Honey';
			}

			if ($_POST['e_request_sample_product'] == 2) {
				$product_name = 'Anmum Materna';
				$variant = 'Materna';
				$flavour = '-';
				//	$flavour = 'Plain + Chocolate';
			} else if ($_POST['e_request_sample_product'] == 5) {
				$product_name = 'Anmum Lacta Plain';
				$variant = 'Lacta';
				$flavour = 'Plain';
			} else if ($_POST['e_request_sample_product'] == 6) {
				$product_name = 'Anmum Essential Plain';
				$variant = 'Essential';
				$flavour = 'Plain';
			} else if ($_POST['e_request_sample_product'] == 7) {
				$product_name = 'Anmum Essential Honey';
				$variant = 'Essential';
				$flavour = 'Honey';
			}

			$sample_brand = ' ';
			if (true === isset($_POST['e_request_sample_brand'])) {
				$sample_brand = $_POST['e_request_sample_brand'];
			}

			if (trim($sample_brand) == '') {
				$sample_brand = ' ';
			}


			if (true === isset($_POST['e_request_sample_action']) && 'Give a sample' === $_POST['e_request_sample_action']) {
				$json_url = $base_url . 'main/api/support/sample';
				// $inputs = array(
				// 	'e_request_sample_sender'   		=> '',
				// 	'e_request_sample_receiver' 		=> '',
				// 	'e_request_sample_contact' 			=> '',
				// 	'e_request_sample_contact_prefix'	=> '',
				// 	'e_request_sample_email' 			=> '',
				// 	'product_id' 						=> '',
				// 	'e_request_sample_choice' 			=> '',
				// 	'e_request_sample_brand' 			=> '',
				// 	'e_request_sample_source'			=> '',
				// 	'e_request_sample_phone_os' 		=> ''
				// );

				$inputs = array(
					'e_request_sample_action' => '',
					'e_request_sample_sender' => '',
					'e_request_sample_receiver' => '',
					'e_request_sample_contact' => '',
					'e_request_sample_contact_prefix' => '',
					'e_request_sample_email' => '',
					'product_id' => '',
					'e_request_sample_choice' => '',
					'e_request_sample_brand' => '',
					'e_request_sample_source' => '',
					'e_request_sample_phone_os' => '',
					'e_request_sample_stage' => '',
					'e_request_sample_mailing' => '',
					'e_request_sample_postcode' => '',
					'e_request_sample_state' => '',
					'user_pregnant_week' => '',
					'user_breastfeeding_month' => '',
					'e_request_sample_channel_member' => 0,
					'e_request_sample_channel_receive' => 0,
					'e_request_sample_channel_mail' => 0,
					'e_request_sample_channel_email' => 0,
					'e_request_sample_channel_sms' => 0,
					'e_request_sample_channel_call' => 0,
					'e_request_sample_channel_lang' => '',
					'e_request_sample_source' => '',
					'e_request_sample_phone_os' => ''
				);


				$inputs = array_intersect_key($_POST, $inputs);

				// $inputs_new = array(
				// 	'action'			=> $inputs['e_request_sample_action'],
				// 	'sender'			=> $inputs['e_request_sample_sender'],
				// 	'contact'			=> $inputs['e_request_sample_contact'],
				// 	'prefix'			=> $inputs['e_request_sample_contact_prefix'],
				// 	'email'				=> $inputs['e_request_sample_email'],
				// 	'sample_product'	=> $product_name,
				// 	'sample_brand'		=> $inputs['e_request_sample_brand']
				// );

				$inputs_new = array(
					'action' => $inputs['e_request_sample_action'],
					'sender' => $inputs['e_request_sample_sender'],
					'contact' => $inputs['e_request_sample_contact'],
					'prefix' => $inputs['e_request_sample_contact_prefix'],
					'home_prefix' => $inputs['e_request_sample_homecontact_prefix'],
					'home' => $inputs['e_request_sample_homecontact'],
					'email' => $inputs['e_request_sample_email'],
					'sample_product' => $product_name,
					'sample_brand' => $sample_brand,
					'mailing1' => $inputs['e_request_sample_mailing'],
					'mailing2' => $inputs['e_request_sample_mailing_2'],
					'postcode' => $inputs['e_request_sample_postcode'],
					'city' => $_POST['e_request_sample_city'],
					'country' => $_POST['e_request_sample_country'],
					'state' => $_POST['e_request_sample_state'],
					'postcode' => $inputs['e_request_sample_postcode'],
					'channel_member' => $inputs['e_request_sample_channel_member'],
					'channel_receive' => $inputs['e_request_sample_channel_receive'],
					'channel_mail' => $inputs['e_request_sample_channel_mail'],
					'channel_email' => $inputs['e_request_sample_channel_email'],
					'channel_sms' => $inputs['e_request_sample_channel_sms'],
					'channel_call' => $inputs['e_request_sample_channel_call'],
					'channel_lang' => $channel_lang,
					'sampleflavour' => $flavour,
					'samplevariant' => $variant,
					'source' => 'External: http://www.anmum.com.my/request-for-sample/'
				);


				$subject = '[ANMUM MY Request Sample Form - Give] From ' . $_POST['e_request_sample_sender'];
				$body = "Name: " . $_POST['e_request_sample_sender'] . "\nMobile Number: " . $_POST['e_request_sample_contact_prefix'] . $_POST['e_request_sample_contact'] . "\nEmail: " . $_POST['e_request_sample_email'] . "\nRequested Product: " . $product_name . "\nFormulated Milk Powder: " . $_POST['e_request_sample_choice'] . "\nMilk Brand: " . $_POST['e_request_sample_brand'];

			} else {
				$json_url = $base_url . 'main/api/support/sample';

				$inputs = array(
					'e_request_sample_action' => '',
					'e_request_sample_sender' => '',
					'e_request_sample_contact' => '',
					'e_request_sample_contact_prefix' => '',
					'e_request_sample_email' => '',
					'product_id' => '',
					'e_request_sample_choice' => '',
					'e_request_sample_brand' => '',
					'e_request_sample_stage' => '',
					'e_request_sample_mailing' => '',
					'e_request_sample_postcode' => '',
					'e_request_sample_state' => '',
					'user_pregnant_week' => '',
					'user_breastfeeding_month' => '',
					'e_request_sample_channel_member' => 0,
					'e_request_sample_channel_receive' => 0,
					'e_request_sample_channel_mail' => 0,
					'e_request_sample_channel_email' => 0,
					'e_request_sample_channel_sms' => 0,
					'e_request_sample_channel_call' => 0,
					'e_request_sample_channel_lang' => '',
					'e_request_sample_source' => '',
					'e_request_sample_phone_os' => ''
				);

				$req_arr = array_merge($req_arr, $_POST);
				$inputs = array_intersect_key($req_arr, $inputs);

				// New Input Field
				$inputs_new = array(
					'action' => $inputs['e_request_sample_action'],
					'sender' => $inputs['e_request_sample_sender'],
					'contact' => $inputs['e_request_sample_contact'],
					'prefix' => $inputs['e_request_sample_contact_prefix'],
					'home_prefix' => $inputs['e_request_sample_homecontact_prefix'],
					'home' => $inputs['e_request_sample_homecontact'],
					'email' => $inputs['e_request_sample_email'],
					'sample_product' => $product_name,
					'sample_brand' => $sample_brand,
					'mailing1' => $inputs['e_request_sample_mailing'],
					'mailing2' => $_POST['e_request_sample_mailing_2'],
					'city' => $_POST['e_request_sample_city'],
					'country' => $_POST['e_request_sample_country'],
					'state' => $_POST['e_request_sample_state'],
					'postcode' => $inputs['e_request_sample_postcode'],
					'channel_member' => $inputs['e_request_sample_channel_member'], //1, //$inputs['e_request_sample_channel_member'],
					'channel_receive' => $inputs['e_request_sample_channel_receive'], //1, //$inputs['e_request_sample_channel_receive'],
					'channel_mail' => $inputs['e_request_sample_channel_mail'], //1, //$inputs['e_request_sample_channel_mail'],
					'channel_email' => $inputs['e_request_sample_channel_email'], //1, //$inputs['e_request_sample_channel_email'],
					'channel_sms' => $inputs['e_request_sample_channel_sms'], //1, //$inputs['e_request_sample_channel_sms'],
					'channel_call' => $inputs['e_request_sample_channel_call'], //1, //$inputs['e_request_sample_channel_call'],
					'channel_lang' => $inputs['e_request_sample_channel_lang'],
					'sampleflavour' => $flavour,
					'samplevariant' => $variant,
					'source' => 'External: http://www.anmum.com.my/request-for-sample/'
				);


				$receive_communicate = 'No';
				if ($_POST['e_request_sample_channel_receive']) {
					$receive_communicate = 'Yes';
				}

				$receive_mail = 'No';
				if ($_POST['e_request_sample_channel_mail']) {
					$receive_mail = 'Yes';
				}

				$receive_email = 'No';
				if ($_POST['e_request_sample_channel_email']) {
					$receive_email = 'Yes';
				}

				$receive_sms = 'No';
				if ($_POST['e_request_sample_channel_sms']) {
					$receive_sms = 'Yes';
				}

				$receive_call = 'No';
				if ($_POST['e_request_sample_channel_call']) {
					$receive_call = 'Yes';
				}


				$subject = '[ANMUM MY Request Sample Form - Get] From ' . $_POST['e_request_sample_sender'];
				/*
				$body = "Name: " . $_POST['e_request_sample_sender'] . "\n
				Mobile Number: " .  $_POST['e_request_sample_contact_prefix'] . $_POST['e_request_sample_contact'] . "\n
				Email: " . $_POST['e_request_sample_email'] . "\n
				Address: " . $_POST['e_request_sample_mailing'] . "\n
				Postcode: " . $_POST['e_request_sample_postcode'] . "\n
				Requested Product: " . $product_name . "\n
				State: " . $_POST['e_request_sample_state'] . "\n
				Current Stage: " . $_POST['e_request_sample_stage'] . "\n
				Pregnant Week: " . $_POST['user_pregnant_week']. "\n
				Formulated Milk Powder: " . $_POST['e_request_sample_choice']. "\n
				Milk Brand: " . $_POST['e_request_sample_brand']. "\n
				Receive Communicated: " . $receive_communicate . "\n
				Mail: " . $receive_mail . "\n
				Email: " . $receive_email . "\n
				SMS: " . $receive_sms . "\n
				Call: " . $receive_call . "\n
				Language: " . $_POST['e_request_sample_channel_lang'];
				*/

				$body = "Name: " . $_POST['e_request_sample_sender'] . "\nMobile Number: " . $_POST['e_request_sample_contact_prefix'] . $_POST['e_request_sample_contact'] . "\nEmail: " . $_POST['e_request_sample_email'] . "\nAddress: " . $_POST['e_request_sample_mailing'] . "\nPostcode: " . $_POST['e_request_sample_postcode'] . "\nRequested Product: " . $product_name . "\nState: " . $_POST['e_request_sample_state'] . "\nCurrent Stage: " . $_POST['e_request_sample_stage'] . "\nPregnant Week: " . $_POST['user_pregnant_week'] . "\nFormulated Milk Powder: " . $_POST['e_request_sample_choice'] . "\nMilk Brand: " . $_POST['e_request_sample_brand'];
				// print_r($_POST); echo '<br>';
				// print_r($inputs);
			}

			// Send email out
			//$emailTo = 'shuhui.pang@my.tribalworldwide.com';
			$emailTo = 'customerservice@fonterra.com';

			$headers = 'From: ' . $_POST['e_careline_name'] . ' <client@anmum.com.my>' . "\r\n" . 'Reply-To: ' . $_POST['e_careline_email'];

			$emailSent = wp_mail($emailTo, $subject, $body, $headers);

			break;
		case 'careline':
			// echo 'curl here';

			$json_url = $base_url . 'main/api/support/careline';

			if (isset($_POST['careline_submitted'])) {
				if ('' === sanitize_text_field($_POST['e_careline_name'])) {
					$error_valid = false;
					array_push($error_msg, '1');
				}

				if ('' === sanitize_text_field($_POST['e_careline_contact_number'])) {
					$error_valid = false;
					array_push($error_msg, '2');
				}

				if ('' === sanitize_email($_POST['e_careline_email'])) {
					$error_valid = false;
					array_push($error_msg, '4');
				}

				if ('' === trim($_POST['e_careline_contact_number_prefix'])) {
					$error_valid = false;
					array_push($error_msg, '3');
				}

				if ('' === sanitize_text_field($_POST['e_careline_topic'])) {
					$error_valid = false;
					array_push($error_msg, '13');
				}

				if ('' === sanitize_text_field($_POST['e_careline_question'])) {
					$error_valid = false;
					array_push($error_msg, '14');
				}

				if ('juno' !== trim($_POST['e_careline_source']) && 'anmum.com.my' !== trim($_POST['e_careline_source'])) {
					$error_valid = false;
				}

				if ('anmum.com.my' === trim($_POST['e_careline_source'])) {
					if ('desktop' !== $_POST['e_careline_phone_os']) {
						$error_valid = false;
						array_push($error_msg, 'os');
					}
				} else {
					if ('ios' !== $_POST['e_careline_phone_os'] && 'android' !== $_POST['e_careline_phone_os']) {
						$error_valid = false;
						array_push($error_msg, 'os2');
					}
				}

			}

			if (false === $error_valid) {
				# code...
				// print_r($error_msg);
				header("Content-type: application/json");
				echo json_encode($error_valid);
				exit;
			}

			// print_r( $_POST );

			$inputs = array(
				'e_careline_name' => '',
				'e_careline_contact_number' => '',
				'e_careline_contact_number_prefix' => '',
				'e_careline_email' => '',
				'e_careline_topic' => '',
				'e_careline_question' => '',
				'e_careline_source' => '',
				'e_careline_phone_os' => ''
			);

			$inputs = array_intersect_key($_POST, $inputs);

			$inputs_new = array(
				'name' => $inputs['e_careline_name'],
				'mobile_no' => $inputs['e_careline_contact_number'],
				'mobile_prefix' => $inputs['e_careline_contact_number_prefix'],
				'email' => $inputs['e_careline_email'],
				'topic' => $inputs['e_careline_topic'],
				'question' => $inputs['e_careline_question'],
				'source' => 'External: http://www.anmum.com.my',
			);

			// Send email out
			//$emailTo = 'alan.yeong@my.tribalworldwide.com';
			$emailTo = 'customerservice@fonterra.com';

			$subject = '[ANMUM MY Contact Form] From ' . $_POST['e_careline_name'];
			$body = "Name: " . $_POST['e_careline_name'] . " \nEmail: " . $_POST['e_careline_email'] . " \nContact Number: " . $_POST['e_careline_contact_number_prefix'] . $_POST['e_careline_contact_number'] . " \nTopic: " . $_POST['e_careline_topic'] . " \nQuestions: " . $_POST['e_careline_question'];
			$headers = 'From: ' . $_POST['e_careline_name'] . ' <contactus@anmum.com.my>' . "\r\n" . 'Reply-To: ' . $_POST['e_careline_email'];

			$emailSent = wp_mail($emailTo, $subject, $body, $headers);

			// print_r( $inputs );
			break;

		default:
			# code...
			break;
	}

	$inputs_new['passphrase'] = $passphrase;
	$inputs_new['timestamp'] = $timestamp;
	$ch = curl_init($json_url);
	if (isset($inputs_new) && !empty($inputs_new)) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $inputs_new);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// Getting results
	$json_list = curl_exec($ch); // Getting jSON result string
	curl_close($ch);

	// Treat results differently for product query
	if ('product' === $domain) {
		// Get the selected product name
		// $selected_product = $_POST['product'];

		// Getting results
		$product_list = json_decode($json_list);

		if (isset($_POST['product_id']) && 0 == $product_list->meta->status_code) {
			$product_list = $product_list->data->Product->$_POST['product_id'];
			$competitor_list = array();
			$return_json = array();

			// print_r( $product_list->product_name_sub );

			foreach ($product_list->product_name_sub as $key => $value) {
				// print_r( $value['product_name_sub'] );

				// if ( false !== array_search( $selected_product, (array) $value ) ) {
				// 	// array_push( $competitor_list, $value->product_name_sub);
				// 	$product_id = $value->product_id;

				// print_r( $value );

				array_push($competitor_list, $value);

				// foreach ($value->product_name_sub as $k => $v) {
				// 	# code...
				// 	$competitor_name = explode(' ', $v);

				// 	print_r( $v );

				// 	// if ( 2 < sizeof( $competitor_name ) ) {
				// 	// 	array_push( $competitor_list, $competitor_name[0].' '.$competitor_name[1] );
				// 	// } else {
				// 	// }
				// 	array_push( $competitor_list, $v );
				// }
				// }

			}

			$return_json['competitor_list'] = $competitor_list;
			$return_json['product_id'] = $_POST['product_id'];
			$json_list = json_encode($return_json);
		}
	}

	header("Content-type: application/json");
	die($json_list);
}

if (!function_exists('check_captcha')) {
	function check_captcha($ip, $challenge, $response)
	{
		global $recaptcha;

		$privatekey = $recaptcha['privatekey'];
		$resp = recaptcha_check_answer($privatekey,
			$ip,
			$challenge,
			$response);

		return $resp->is_valid;
	}
}

if (!function_exists('convert_number_to_words')) {
	function convert_number_to_words($number)
	{

		$hyphen = '-';
		$conjunction = ' and ';
		$separator = ', ';
		$negative = 'negative ';
		$decimal = ' point ';
		$dictionary = array(
			0 => 'zero',
			1 => 'one',
			2 => 'two',
			3 => 'three',
			4 => 'four',
			5 => 'five',
			6 => 'six',
			7 => 'seven',
			8 => 'eight',
			9 => 'nine',
			10 => 'ten',
			11 => 'eleven',
			12 => 'twelve',
			13 => 'thirteen',
			14 => 'fourteen',
			15 => 'fifteen',
			16 => 'sixteen',
			17 => 'seventeen',
			18 => 'eighteen',
			19 => 'nineteen',
			20 => 'twenty',
			30 => 'thirty',
			40 => 'fourty',
			50 => 'fifty',
			60 => 'sixty',
			70 => 'seventy',
			80 => 'eighty',
			90 => 'ninety',
			100 => 'hundred',
			1000 => 'thousand',
			1000000 => 'million',
			1000000000 => 'billion',
			1000000000000 => 'trillion',
			1000000000000000 => 'quadrillion',
			1000000000000000000 => 'quintillion'
		);

		if (!is_numeric($number)) {
			return false;
		}

		if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . convert_number_to_words(abs($number));
		}

		$string = $fraction = null;

		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}

		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens = ((int)($number / 10)) * 10;
				$units = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= $conjunction . convert_number_to_words($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int)($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= convert_number_to_words($remainder);
				}
				break;
		}

		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();
			foreach (str_split((string)$fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);
		}

		return $string;
	}
}

if (!function_exists('alter_comment_form_fields')) :
	function alter_comment_form_fields($fields)
	{
		$fields['url'] = '';  //removes website field
		return $fields;
	}
endif;

if (!function_exists('direct_curl')) :
	function direct_curl($fields)
	{
		$sso_secret = '05ad1bb16a7e61fa217a60f7f42ccb4d25a83f80';
		$timestamp = time();
		// echo $timestamp;

		$passphrase = sha1($sso_secret . $timestamp);

		// jSON URL which should be requested
		switch ($fields) {
			case 'products':
				$json_url = 'http://sky-forge.com/anmum_tcp_api/api/product';
				break;
			case 'state':
				$json_url = 'http://sky-forge.com/anmum_tcp_api/api/country';
				break;

			default:
				# code...
				break;
		}

		$inputs['passphrase'] = $passphrase;
		$inputs['timestamp'] = $timestamp;

		// print_r( $inputs );

		// Initializing curl
		$ch = curl_init($json_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $inputs);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

		// Getting results
		$json_list = curl_exec($ch); // Getting jSON result string
		curl_close($ch);

		$json_list = json_decode($json_list);

		// if ( 'state' == $fields ) {
		// 	print_r( $json_list );
		// }

		$json_list = $json_list->data;

		// print_r( $json_list );
		return $json_list;
	}
endif;

add_filter('comment_form_default_fields', 'alter_comment_form_fields');

if (!function_exists('anmum_comment')) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own anmum_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Twelve 1.0
 */
function anmum_comment($comment, $args, $depth) {
$GLOBALS['comment'] = $comment;
switch ($comment->comment_type) :
case 'pingback' :
case 'trackback' :
// Display trackbacks differently than normal comments.
?>
<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
	<p><?php _e('Pingback:', 'anmum'); ?><?php comment_author_link(); ?><?php edit_comment_link(__('(Edit)', 'anmum'), '<span class="edit-link">', '</span>'); ?></p>
	<?php
	break;
	default :
	// Proceed with normal comments.
	global $post;
	?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
	<article id="comment-<?php comment_ID(); ?>" class="comment">
		<header class="comment-meta comment-author vcard">
			<?php
			echo get_avatar($comment, 44);
			printf('<cite class="fn">%1$s %2$s</cite>',
				get_comment_author_link(),
				// If current post author is also comment author, make it known visually.
				($comment->user_id === $post->post_author) ? '<span> ' . __('Post author', 'anmum') . '</span>' : ''
			);
			printf('<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
				esc_url(get_comment_link($comment->comment_ID)),
				get_comment_time('c'),
				/* translators: 1: date, 2: time */
				sprintf(__('%1$s at %2$s', 'anmum'), get_comment_date(), get_comment_time())
			);
			?>
		</header><!-- .comment-meta -->

		<?php if ('0' == $comment->comment_approved) : ?>
			<p class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'anmum'); ?></p>
		<?php endif; ?>

		<section class="comment-content comment">
			<?php comment_text(); ?>
			<?php edit_comment_link(__('Edit', 'anmum'), '<p class="edit-link">', '</p>'); ?>
		</section><!-- .comment-content -->

		<div class="reply">
			<?php comment_reply_link(array_merge($args, array('reply_text' => __('Reply', 'anmum'), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
		</div><!-- .reply -->
	</article><!-- #comment-## -->
	<?php
	break;
	endswitch; // end comment_type check
	}
	endif;


	function twentytwelve_content_nav($html_id)
	{
		global $wp_query;

		$html_id = esc_attr($html_id);

		$curr_page = (get_query_var('paged')) ? intval(get_query_var('paged')) : 1;
		$max_page = $wp_query->max_num_pages;
		$search_key = (get_query_var('s')) ? get_query_var('s') : '';

		$url = get_pagenum_link(1);
		$base_pagination_url = substr($url, 0, strpos($url, '?'));

		$pagination_args = array(
			'base' => $base_pagination_url . '%_%',
			'format' => 'page/%#%' . '/?s=' . $search_key,
			'total' => $max_page,
			'current' => max(1, $curr_page),
			'end_size' => 3,
			'prev_text' => '&lt;',
			'next_text' => '&gt;'
		);

		if ($max_page > 1) : ?>
			<nav id="<?php echo $html_id; ?>" class="navigation pagination-wrapper sixteen columns row" role="navigation">
			<span>
				<?php echo paginate_links($pagination_args); ?>
			</span>
			</nav>
		<?php endif;
	}

	?>
