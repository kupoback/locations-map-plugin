<?php

namespace Locations_Maps_API;

//Exit if accessed directly
if (!defined('ABSPATH')) { exit; }

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
class Locations_Maps_API extends \WP_REST_Controller
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
	 * @var      string $api_key THe API key for Google Maps
	 */
	private $api_key;
	
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
		
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->google_api_url = 'https://maps.googleapis.com/maps/api/geocode/json?';
		$this->address = 'address=';
		$this->libraries = '&libraries=geometry';
		$this->api_key = get_option('locations_maps_google_api_key');
		
	}
	
	public function get_locations(\WP_REST_Request $request)
	{
		$zip = $request->get_param('zip');
		$radius = $request->get_param('radius');
		$subcategories = $request->get_param('subcategory');
		
		$dist_radius = !(empty($radius)) ? $radius : 5;
		$near_by_services = [];
		$no_posts_text = \get_field('no_locations_text', 'option');
		$no_posts_text = (!empty($no_posts_text)) ? $no_posts_text : 'No Locations Found';
		$coords_array = $this->get_zip_coords($zip);
		
		if (is_array($coords_array) &&
			\array_key_exists('results', $coords_array) &&
			empty($coords_array['results'])
		) {
			return [];
		}
		
		$tax_terms = (!empty($subcategories)) ? $subcategories : [];
		$posts = $this->get_posts($tax_terms);
		
		if (!empty($posts)) {
			$near_by_service_post_ids = $this->order_by_nearby($coords_array['lat'], $coords_array['lng'], $posts, $dist_radius);
			
			if (isset($near_by_service_post_ids) && !empty($near_by_service_post_ids)) {
				foreach ($near_by_service_post_ids as $key => $service_id) {
					
					$near_by_services[] = [
						'id' => $service_id['post_id'],
						'title' => \get_the_title($service_id['post_id']),
						'address' => \get_field('address', $service_id['post_id']),
						'city' => \get_field('city', $service_id['post_id']),
						'zip' => \get_field('zip', $service_id['post_id']),
						'phone' => get_field('phone', $service_id['post_id']),
						'distance' => \number_format((float)$service_id['distance'], 2),
						'external_link' => \get_field('external_link', $service_id['post_id']),
						'lat' => \get_field('latitude', $service_id['post_id']),
						'lng' => \get_field('longitude', $service_id['post_id']),
					];
					
					$post_cats = \get_the_terms($service_id['post_id'], 'service-category');
					
					if ($post_cats && !\is_wp_error($post_cats)) {
						$near_by_services[$key]['service_categories'] = $post_cats;
					} else {
						$near_by_services[$key]['service_categories'] = [];
					}
				}
				
				usort($near_by_services, function($dist_a, $dist_b) {
					return ((float)$dist_a['distance'] < (float)$dist_b['distance']) ? -1 : 1;
				});
			}
		}
		
		if (empty($near_by_services)) {
			$near_by_services['no_posts_found'] = $no_posts_text;
		}
		
		return \rest_ensure_response($near_by_services);
	}
	
}
