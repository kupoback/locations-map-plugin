<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Locations_Maps
 * @subpackage Locations_Maps/public
 * @author     Nick Makris <nick@makris.io>
 */
class Locations_Maps_Public
{
	
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;
	
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct($plugin_name, $version)
	{
		
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		
		add_shortcode('map', [$this, 'map_module_short_code',]);
		add_shortcode('location', [$this, 'location_short_code',]);
	}
	
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Locations_Maps_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Locations_Maps_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		wp_register_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/locations-maps-public.min.css', [], $this->version, 'all');
	}
	
	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Locations_Maps_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Locations_Maps_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		$api_key = isset(get_option('lm_options')['google_api_key']) ? get_option('lm_options')['google_api_key'] : null;
		
		// wp_register_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/locations-maps-public.min.js', ['jquery'], $this->version, true);
		wp_register_script($this->plugin_name . '-google-maps', "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=geometry&callback=initMap", ['jquery'], '', true);
	}
	
	/**
	 * Function Name: add_async_defer_google_maps
	 * Description: Need to add defer and possibly async to google maps script
	 *
	 * @param $tag
	 * @param $handle
	 *
	 * @return mixed
	 */
	public function add_async_defer_google_maps($tag, $handle)
	{
		return $this->plugin_name . '-google-maps' === $handle ? str_replace(' src', ' defer src', $tag) : $tag;
	}
	
	/**
	 * Our map shortcode which will display a google map to the page.
	 *
	 * @param array $atts   map_id = an ID used to identify the map, can allow for multiple calls of the shortcode, granted a different ID is used
	 *                      post_id = allows for returning of a single location
	 *                      zoom = Allows for the adjustment of the zoom level
	 * @shortcode [map map_id="" zoom="8"]
	 *
	 * @return
	 * @since 1.0.0
	 */
	public function map_module_short_code($atts)
	{
		// Attributes
		$atts = shortcode_atts(
			[
				// Our Short code parameters
				'map_id'  => 'lm_map',
				'post_id' => null,
				'zoom'    => 8,
			],
			$atts
		);
		
		// Starting to setup our localzie array
		$map_vars = [
			'mapStyling' => lm_map_style() ?: plugin_dir_url(__FILE__) . 'map-styles/dark.json',
			'mapZoom'    => $atts['zoom'],
			'mapIcon'    => lm_map_icon() ? wp_get_attachment_image_url(lm_map_icon()) : plugin_dir_url(__FILE__) . 'media/pin-circle.svg',
		];
		
		if ( !is_null($atts['post_id'] ))
		{
			$map_vars['post_id'] = $atts['post_id'];
			$map_vars['mapCenterLat'] = get_post_meta($atts['post_id'], '_map_lat', true);
			$map_vars['mapCenterLng'] = get_post_meta($atts['post_id'], '_map_lng', true);
		}
		else {
			!is_null(lm_main_location()) && isset(lm_main_location()->lat) ? $map_vars['mapCenterLat'] = lm_main_location()->lat : null;
			!is_null(lm_main_location()) && isset(lm_main_location()->lng) ? $map_vars['mapCenterLng'] = lm_main_location()->lng : null;
		}
		
		
		wp_enqueue_style($this->plugin_name);
		// wp_enqueue_script($this->plugin_name . '-google-maps');
		isset(get_option('lm_options')['enqueued_script']) ? wp_localize_script( get_option('lm_options')['enqueued_script'], 'MAP_VARS', $map_vars) : null;
		
		// Templates will be loaded here
		ob_start();
		printf('<div id="%s" class="map-container"></div>', $atts['map_id']);
		wp_reset_postdata();
		
		return ob_get_clean();
	}
	
	/**
	 * Function Name: location_short_code
	 * Description: Post Template Part displaying data from Locations Post Type. Contains overridable template
	 *
	 * @param array $atts   post_ids = a string of post ID's to show, must be comma delimted
	 *                      order = Change the ordering of the posts
	 *                      orderby = Change the orderby of the posts
	 *                      template = the template to return, must have full slug
	 *
	 * @shortcode [location post_ids="" order="" orderby="" template=""]
	 *
	 * @return false|string
	 * @since 1.2.0
	 */
	public function location_short_code($atts)
	{
		
		$atts = shortcode_atts(
			[
				'post_ids' => '',
				'order'    => 'ASC',
				'orderby'  => 'menu_order',
				'template' => null
			],
			$atts
		);
		// Exit if no template degined
		if ( $atts['template'] === null ) { return; }
		
		$args = [
			'post_type'      => 'locations',
			'posts_per_page' => - 1,
			'order'          => $atts['order'],
			'orderby'        => $atts['orderby'],
		];
		
		isset($atts['post_ids']) && is_string($atts['post_ids']) ? $args['post__in'] = $atts['post_ids'] : null;
		
		$loop = new WP_Query($args);
		
		ob_start();
		if ($loop->have_posts()) :
			while ($loop->have_posts()) : $loop->the_post();
				get_template_part($atts['template']);
			endwhile;
		endif;
		wp_reset_postdata();
		
		return ob_get_clean();
	}
	
}
