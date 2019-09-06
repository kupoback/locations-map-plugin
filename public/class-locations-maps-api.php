<?php

//Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Class Name: Locations_Maps_API
 * Description: The API specifically for the locations map
 * Class Locations_Maps_API
 *
 * @link       https://makris.io
 * @since      1.0.0
 *
 * @package    Locations_Maps
 * @subpackage Locations_Maps/admin
 * @author     Nick Makris <nick@makris.io>
 */
class Locations_Maps_API
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
	 * The Google Request URL
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $google_api_url The URL request from Google
	 */
	private $google_api_url;
	
	/**
	 * The Google API key
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $apiKey THe API key for Google Maps
	 */
	private $apiKey;
	
	/**
	 * The libraries for loaded for google
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $libraries The libraries loaded for Google Maps
	 */
	private $libraries;
	
	/**
	 *
	 * The address to gather data from
	 *
	 * @since       1.0.0
	 * @access      private
	 * @var string $address The address to gather data from
	 */
	private $address;
	
	protected $namespace;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct($plugin_name, $version)
	{
		$this->namespace      = 'locations/v1';
		$this->plugin_name    = $plugin_name;
		$this->version        = $version;
		$this->google_api_url = 'https://maps.googleapis.com/maps/api/geocode/json?';
		$this->address        = 'address=';
		$this->libraries      = '&libraries=geometry';
		$this->apiKey         = lm_google_geocode_api_key();
	}
	
	public function register_routes()
	{
		register_rest_route($this->namespace, '/places', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [
				$this,
				'get_locations',
			],
			'args'     => [],
		]);
		
		register_rest_route($this->namespace, '/nearby', [
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => [
				$this,
				'get_nearby_locations',
			],
			'args'     => [
				'zip'    => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_title',
					'validate_callback' => [
						$this,
						'validate_zip',
					],
				],
				'radius' => [
					'type'              => 'number',
					'sanitize_callback' => 'absint',
				],
			],
		]);
	}
	
	private function get_posts($tax_terms = [])
	{
		$args = [
			'post_type'       => 'locations',
			'posts_per_parge' => - 1,
			'post_status'     => 'publish',
			'orderby'         => 'menu_order',
			'order'           => 'ASC',
			'hide_empty'      => true,
		];
		
		if (isset($tax_terms) && !empty($tax_terms) && is_array($tax_terms))
		{
			$terms_query = [];
			foreach ($tax_terms as $tax_term)
			{
				$term_query = [
					'taxonomy' => $tax_term['tax'],
					'field'    => 'slug',
					'terms'    => $tax_term['terms'],
				];
				array_push($terms_query, $term_query);
			}
			$args['tax_query'] = $terms_query;
		}
		
		$query = get_posts($args);
		
		return $query;
	}
	
	public function get_locations()
	{
		
		$no_posts_text = get_field('no_locations_text', 'option') ?: 'No Locations Found';
		$locations     = [];
		$posts         = $this->get_posts();
		
		if (!empty($posts))
		{
			$i = 0;
			global $post;
			foreach ($posts as $post)
			{
				setup_postdata($post);
				$post_id     = get_the_ID();
				$locations[] = [
					'id'      => $post_id,
					'slug'    => $post->post_name,
					'title'   => get_the_title(),
					'address' => [
						'address'  => get_post_meta($post_id, '_map_address', true) ?: null,
						'address2' => get_post_meta($post_id, '_map_address2', true) ?: null,
						'city'     => get_post_meta($post_id, '_map_city', true) ?: null,
						'state'    => get_post_meta($post_id, '_map_state', true) ?: null,
						'zip'      => get_post_meta($post_id, '_map_zip', true) ?: null,
					],
					'phone'   => get_post_meta($post_id, '_map_phone', true) ?
						[
							'clean' => preg_replace('/\s+/', '', preg_replace('/[^a-zA-Z0-9\']/', '', get_post_meta($post_id, '_map_phone', true))),
							'text'  => get_post_meta($post_id, '_map_phone', true),
						] : '',
					'email'   => get_post_meta($post_id, '_map_email', true) ?: '',
					'website' => get_post_meta($post_id, '_map_website', true) ?: '',
					'lat'     => get_post_meta($post_id, '_map_lat', true) ?: null,
					'lng'     => get_post_meta($post_id, '_map_lng', true) ?: null,
				];
				
				$i ++;
			}
			wp_reset_postdata();
		}
		
		if (empty($locations))
		{
			$locations['no_posts_found'] = $no_posts_text;
		}
		
		return rest_ensure_response($locations);
	}
	
	public function get_nearby_locations(WP_REST_Request $request)
	{
		$zip    = $request->get_param('zip');
		$radius = $request->get_param('radius');
		
		$dist_radius      = !(empty($radius)) ? $radius : 5;
		$near_by_services = [];
		$no_posts_text    = \get_field('no_locations_text', 'option');
		$no_posts_text    = (!empty($no_posts_text)) ? $no_posts_text : 'No Locations Found';
		$coords_array     = $this->get_zip_coords($zip);
		
		if (
			is_array($coords_array) && \array_key_exists('results', $coords_array) && empty($coords_array['results'])
		)
		{
			return [];
		}
		
		$posts = $this->get_posts();
		
		if (!empty($posts))
		{
			global $post;
			$near_by_service_post_ids = $this->order_by_nearby($coords_array['lat'], $coords_array['lng'], $posts, $dist_radius);
			
			if (isset($near_by_service_post_ids) && !empty($near_by_service_post_ids))
			{
				foreach ($near_by_service_post_ids as $key => $post)
				{
					$post_id = $post['post']->ID;
					// error_log( print_r($post['post'], TRUE));
					$near_by_services[] = [
						'id'       => $post_id,
						'slug'     => $post['post']->post_name,
						'title'    => get_the_title($post_id),
						'address'  => [
							'address'  => get_post_meta($post_id, '_map_address', true) ?: '',
							'address2' => get_post_meta($post_id, '_map_address2', true) ?: '',
							'city'     => get_post_meta($post_id, '_map_city', true) ?: '',
							'state'    => get_post_meta($post_id, '_map_state', true) ?: '',
							'zip'      => get_post_meta($post_id, '_map_zip', true) ?: '',
						],
						'phone'    => get_post_meta($post_id, '_map_phone', true) ?
							[
								'clean' => preg_replace('/\s+/', '', preg_replace('/[^a-zA-Z0-9\']/', '', get_post_meta($post_id, '_map_phone', true))),
								'text'  => get_post_meta($post_id, '_map_phone', true),
							] : '',
						'website'  => get_post_meta($post_id, '_map_website', true) ?: '',
						'lat'      => get_post_meta($post_id, '_map_lat', true) ?: '',
						'lng'      => get_post_meta($post_id, '_map_lng', true) ?: '',
						'distance' => \number_format((float) $post['distance'], 2),
					];
				}
				
				wp_reset_postdata();
				
				usort($near_by_services, function ($dist_a, $dist_b)
				{
					return ((float) $dist_a['distance'] < (float) $dist_b['distance']) ? - 1 : 1;
				});
			}
		}
		
		if (empty($near_by_services))
		{
			$near_by_services['no_posts_found'] = $no_posts_text;
		}
		
		return \rest_ensure_response($near_by_services);
	}
	
	public function get_zip_coords($zip)
	{
		$url    = $this->google_api_url . $this->address . \urlencode($zip) . '&key=' . $this->apiKey;
		$coords = [];
		
		try
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			curl_close($ch);
			$response_a = json_decode($response);
			
			if ($response_a->status == 'OK')
			{
				$lat = $response_a->results[0]->geometry->location->lat;
				$lng = $response_a->results[0]->geometry->location->lng;
				
				if (isset($lat) && !empty($lat) && isset($lng) && !empty($lng))
				{
					$coords = [
						'lat' => $lat,
						'lng' => $lng,
					];
				}
				
				return $coords;
			}
			
			return [];
		}
		catch (\Exception $e)
		{
			return [];
		}
	}
	
	public function order_by_nearby($lat, $lon, $array_of_lat_posts, $distance = 0)
	{
		$ordered_Ids_and_distances = [];
		
		if ($lat !== 0 && $lon !== 0 && $array_of_lat_posts && count($array_of_lat_posts) > 0)
		{
			
			foreach ($array_of_lat_posts as $array_of_lat_post)
			{
				$course_lat  = get_post_meta($array_of_lat_post->ID, '_map_lat', true) ?: null;
				$course_long = get_post_meta($array_of_lat_post->ID, '_map_lng', true) ?: null;
				
				//Prevent call to gmaps API
				if (isset($course_lat) && isset($course_long))
				{
					$dist = $this->getDistance($course_lat, $course_long, $lat, $lon);
				}
				else
				{
					$dist = 999999999;
				}
				
				if ($distance > 0)
				{
					if ($dist < $distance)
					{
						$ordered_Ids_and_distances[] = [
							'distance' => $dist,
							'post'     => $array_of_lat_post,
						];
					}
				}
			}
			return $ordered_Ids_and_distances;
		}
		return false;
	}
	
	public function getDistance($post_lat, $post_lon, $user_lat, $user_lon)
	{
		try
		{
			$theta = (float) $post_lon - (float) $user_lon;
			$dist  = sin(deg2rad((float) $post_lat)) * sin(deg2rad((float) $user_lat)) + cos(deg2rad((float) $post_lat)) * cos(deg2rad((float) $user_lat)) * cos(deg2rad((float) $theta));
			$dist  = acos($dist);
			$dist  = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
		}
		catch (\Exception $e)
		{
			//Return unrealistic distance
			$miles = 999999999999999;
		}
		
		return $miles;
	}
	
	public function validate_zip($value, $request, $param)
	{
		
		if (!is_string($value) || !\preg_match('/^[0-9]{5}(?:-[0-9]{4})?$/', $value))
		{
			return new \WP_Error('rest_invalid_param', esc_html__('Must be a valid zip code.', 'united-way-theme'), ['status' => 400]);
		}
		
		return $value;
	}
	
}
