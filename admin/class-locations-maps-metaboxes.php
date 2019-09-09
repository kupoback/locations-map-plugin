<?php

//Exit if accessed directly
if (!defined('ABSPATH'))
{
	exit;
}

/**
 * File: class-locations-maps-metaboxes.php
 * Description: Our metabox built out for the locations maps
 *
 * @link       https://makris.io
 * @since      1.0.0
 *
 * @package    Locations_Maps
 * @subpackage Locations_Maps/admin
 * @author     Nick Makris <nick@makris.io>
 */
class Locations_Maps_Metaboxes
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
	 * The meta_fields of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $meta_fields = [];
	
	/**
	 * The save_fields of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $save_fields = [];
	
	/**
	 * The save_fields of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $custom_fields = [];
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name   The name of this plugin.
	 * @param string $version       The version of this plugin.
	 * @param array  $fields        An array of fields for the locations post type
	 * @param array  $save_fields   An array of fields to save
	 * @param array  $custom_fields An array of non-core custom fields
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct($plugin_name, $version, $fields, $save_fields, $custom_fields)
	{
		
		$this->plugin_name   = $plugin_name;
		$this->version       = $version;
		$this->meta_fields   = $fields;
		$this->save_fields   = $save_fields;
		$this->custom_fields = $custom_fields;
	}
	
	/**
	 * Function Name: locations_maps_metabox
	 * Description: Define the metabox
	 * Version: 1.0
	 * Author: Nick Makris @kupoback
	 *
	 */
	function locations_maps_metabox()
	{
		
		$screens = [
			'locations',
			'location-maps-settings',
		];
		foreach ($screens as $screen)
		{
			if ($screen === 'locations')
			{
				add_meta_box(
					'locations_maps_metabox',
					'Location Information',
					[
						$this,
						'locations_maps_metabox_html',
					],
					$screen,
					'advanced',
					'high'
				);
			}
		}
	}
	
	/**
	 * Function Name: locations_maps_metabox_html
	 * Description: Create the metabox content. Includes a switch, state list, and loop to build out fields quickly.
	 * Version: 1.0
	 * Author: Nick Makris @kupoback
	 *
	 * @param $post
	 *
	 */
	function locations_maps_metabox_html($post)
	{
		
		if (get_post_type($post->ID) !== 'locations')
			return;
		
		if (!empty($this->meta_fields))
		{
			wp_nonce_field('locations_maps_nonce', 'locations_maps_nonce'); ?>

					<div class="map-elements-inside" id="map-elements">

						<div id="map-loading">
							<svg enable-background="new 0 0 100 100" version="1.1" viewBox="0 0 100 100" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><path d="m31.6 3.5c-25.7 10.1-38.2 39.2-28.1 64.9s39.2 38.3 64.9 28.1l-3.1-7.9c-21.3 8.4-45.4-2-53.8-23.3s2-45.4 23.3-53.8l-3.2-8z" fill="#397DA2">
									<animateTransform attributeName="transform" attributeType="XML" dur="2s" from="0 50 50" repeatCount="indefinite" to="360 50 50" type="rotate"></animateTransform>
								</path>
								<path d="M82,35.7C74.1,18,53.4,10.1,35.7,18S10.1,46.6,18,64.3l7.6-3.4c-6-13.5,0-29.3,13.5-35.3s29.3,0,35.3,13.5 L82,35.7z" fill="#54AEC7">
									<animateTransform attributeName="transform" attributeType="XML" dur="2s" from="0 50 50" repeatCount="indefinite" to="360 50 50" type="rotate"></animateTransform>
								</path>
								<path d="m42.3 39.6c5.7-4.3 13.9-3.1 18.1 2.7 4.3 5.7 3.1 13.9-2.7 18.1l4.1 5.5c8.8-6.5 10.6-19 4.1-27.7-6.5-8.8-19-10.6-27.7-4.1l4.1 5.5z" fill="#59C29F">
									<animateTransform attributeName="transform" attributeType="XML" dur="1s" from="0 50 50" repeatCount="indefinite" to="-360 50 50" type="rotate"></animateTransform>
								</path></svg>
						</div>

						<div class="map-row">
				
				<?php
				
				$hidden = [];
				foreach ((array) $this->meta_fields as $field)
				{
					$markup  = '';
					$lm_meta = get_post_meta($post->ID, 'lm_meta', true);
					$value   = array_key_exists($field['euid'], $lm_meta) ? $lm_meta[$field['value']] : '';
					$type    = $field['type'];
					$fid     = $field['euid'];
					$title   = $field['title'];
					$class   = isset($field['class']) ? $field['class'] : '';
					$val     = $field['value'];
					
					switch ($field['type'])
					{
						case 'text' :
						case 'email' :
						case 'number' :
							if (!$value)
								$value = '';
							
							$atts = '';
							if ($fid === 'lat_text' || $fid === 'lng_text')
							{
								$atts = 'readonly';
								$val  = $field['euid'];
							}
							
							if ($fid === 'zip')
							{
								$atts = 'maxlength="5"';
							}
							
							$markup = sprintf(
								'<input type="%1$s" name="lm_meta[%2$s]" id="lm_meta[%2$s]" value="%3$s" %4$s autocomplete="off" data-%2$s/>',
								$type,
								$val,
								$value,
								$atts
							);
							break;
						case 'select' :
							if ($value === '')
								$value = '';
							
							$options = sprintf('<option value="" data-%s>Select</option>', $field['euid']);
							foreach ($field['choices'] as $key => $label) :
								$options .= sprintf(
									'<option value="%s" %s>%s</option>',
									$label,
									selected($value, $label, false),
									__($label)
								);
							endforeach;
							$markup .= sprintf('<select name="lm_meta[%1$s]" id="lm_meta[%1$s]" data-%1$s>%2$s</select>', $field['value'], $options);
							break;
						case 'hidden' :
							$hidden_input = sprintf('<input type="%1$s" name="lm_meta[%2$s]" id="lm_meta[%2$s]" value="%3$s" data-%2$s />', $type, $field['value'], $value);
							array_push($hidden, $hidden_input);
							break;
					}
					
					if ($type !== 'hidden')
					{
						printf('<div class="map-field map-%1$s %4$s"><div class="map-label"><label for="%1$s">%2$s</label></div><div class="map-%5$s">%3$s</div></div>', $fid, $title, $markup, $class, $type);
					}
				}
				?>

						</div>

						<div class="map-row">
							<div class="map-field map-get-geolocation">
								<div class="map-geo-button">
									<input name="_save_address" type="button" data-postid="<?php echo $post->ID; ?>" class="btn" id="save_address" value="<?php _e('Save Address'); ?>">
									<input name="_get_geoloc" type="button" data-postid="<?php echo $post->ID; ?>" class="btn" id="get_geoloc" value="<?php _e('Get Place'); ?>">
									<input type="button" name="geo_reset" class="btn" id="geo_reset" value="<?php _e('Reset Geo Location'); ?>" />
									<input type="button" name="form-reset" class="btn" id="form-reset" value="<?php _e('Form Reset'); ?>" />
								</div>
				  <?php foreach ($hidden as $hid)
				  {
					  echo $hid;
				  } ?>
							</div>
						</div>

					</div>
			
			<?php
			
		}
	}
	
	/**
	 * Function Name: locations_maps_metabox_save
	 * Description: The function we will use to save the metabox
	 * Version: 1.0
	 * Author: Nick Makris @kupoback
	 *
	 * @param $post_id
	 *
	 */
	function locations_maps_metabox_save($post_id)
	{
		
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;
		if (!isset($_POST['locations_maps_nonce']) || !wp_verify_nonce($_POST['locations_maps_nonce'], 'locations_maps_nonce'))
			return;
		if (!current_user_can('edit_post', $post_id))
			return;
		
		if (!empty($this->save_fields))
		{
			
			// Sanitize our fields
			if (isset($_POST['lm_meta']) && !empty($_POST['lm_meta']))
				array_map(function ($input) { return sanitize_text_field($input); }, $_POST['lm_meta']);
			
			// Save our fields
			if (isset($_POST['lm_meta']) && !empty($_POST['lm_meta']))
				update_post_meta($post_id, 'lm_meta', $_POST['lm_meta']);
			else
				delete_post_meta($post_id, 'lm_meta');
			
			// We need to run through the other posts and uncheck the box of the others if they are checked.
			$args         = [
				'post_type'    => 'locations',
				'per_page'     => - 1,
				'post__not_in' => [$post_id],
			];
			$post_objects = get_posts($args);
			foreach ($post_objects as $post)
			{
				setup_postdata($post);
				if (!empty(get_post_meta($post->ID, 'center_location', true)) && isset($_POST['center_location']))
					delete_post_meta($post->ID, 'center_location');
			}
			wp_reset_postdata();
			
			if (isset($_POST['center_location']))
			{
				update_post_meta($post_id, 'center_location', $_POST['center_location']);
				if (array_key_exists('lat', get_post_meta($post_id, 'lm_meta', true)) && isset(get_option('lm_options')['center_lat']) !== get_post_meta($post_id, 'lm_meta', true)['lat'])
				{
					update_option('lm_options', [
						'lat' => $_POST['lm_meta']['lat'],
						'lng' => $_POST['lm_meta']['lng']
					]);
				}
			}
			else
			{
				delete_post_meta($post_id, 'center_location');
				
				if (array_key_exists('lat', get_post_meta($post_id, 'lm_meta', true)) && isset(get_option('lm_options')['center_lat']) === get_post_meta($post_id, 'lm_meta', true)['lat'])
				{
					delete_option('lm_options'['lat']);
				}
			}
		}
		
		error_log( print_r($_POST['center_location'], TRUE));
	}
	
	/**
	 * Function Name: locations_maps_metabox_ajax
	 * Description: The function that triggers an auto save when lat/lng are populated/removed
	 * Version: 1.0
	 * Author: Nick Makris @kupoback
	 *
	 *
	 */
	function locations_maps_metabox_ajax()
	{
		$id       = $_POST['id'];
		$lm_meta  = get_post_meta($id, 'lm_meta', true);
		$lat      = isset($_POST['lat']) ? sanitize_text_field($_POST['lat']) : $lm_meta['lat'];
		$lng      = isset($_POST['lng']) ? sanitize_text_field($_POST['lng']) : $lm_meta['lng'];
		$address  = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : $lm_meta['address'];
		$address2 = isset($_POST['address2']) ? sanitize_text_field($_POST['address2']) : $lm_meta['address2'];
		$city     = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : $lm_meta['city'];
		$state    = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : $lm_meta['state'];
		$zip      = isset($_POST['zip']) ? sanitize_text_field($_POST['zip']) : $lm_meta['zip'];
		$email    = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : $lm_meta['email'];
		$phone    = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : $lm_meta['phone'];
		$site     = isset($_POST['website']) ? sanitize_text_field($_POST['website']) : $lm_meta['website'];
		$placeID  = isset($_POST['placeID']) ? sanitize_text_field($_POST['placeID']) : in_array('placeID', $lm_meta) ? $lm_meta['placeID'] : null;
		$country  = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : in_array('country', $lm_meta) ? $lm_meta['country'] : null;
		
		$return_array = [
			'address'  => $address,
			'address2' => $address2,
			'city'     => $city,
			'state'    => $state,
			'zip'      => $zip,
			'email'    => $email,
			'phone'    => $phone,
			'website'  => $site,
			'lat'      => $lat,
			'lng'      => $lng,
		];
		
		if (!is_null($country))
			$return_array['country'] = $country;
		if (!is_null($placeID))
			$return_array['placeID'] = $placeID;
		
		update_post_meta($id, 'lm_meta', $return_array);
	}
	
	/**
	 * Function Name: locations_maps_add_custom_fields
	 * Description: Register a new fieldset in the REST API
	 * Version: 1.0
	 * Author: Nick Makris @kupoback
	 *
	 *
	 */
	function locations_maps_add_custom_fields()
	{
		
		register_rest_field(
			'locations',
			'map_fields', //New Field Name in JSON RESPONSEs
			[
				'get_callback'    => [
					$this,
					'locations_maps_custom_fields',
					// custom function name
				],
				'update_callback' => null,
				'schema'          => null,
			]
		);
	}
	
	/**
	 * Function Name: locations_maps_custom_fields
	 * Description: Our REST callback
	 * Version: 1.0
	 * Author: Nick Makris @kupoback
	 *
	 * @param $object
	 *
	 * @return mixed
	 *
	 */
	function locations_maps_custom_fields($object)
	{
		
		$data = [];
		
		if (!empty($this->save_fields))
		{
			foreach ((array) $this->save_fields as $field)
			{
				$data[$field] = (object) get_post_meta($object['id'], $field);
			}
		}
		
		//		return $data;
		
		return get_post_meta($object['id']);
	}
	
	/**
	 * Function Name: locations_maps_update_box_custom_fields
	 * Description:
	 */
	function locations_maps_update_box_custom_fields()
	{
		
		$post_id = get_the_ID();
		
		if (get_post_type($post_id) !== 'locations')
			return;
		
		if (!empty($this->custom_fields))
		{
			
			wp_nonce_field('locations_maps_nonce_' . $post_id, 'locations_maps');
			foreach ((array) $this->custom_fields as $cf)
			{
				$post_item = get_post_meta($post_id, $cf['id'], true);
				printf(
					'<div class="misc-pub-section"><input type="checkbox" value="1" %3$s name="%1$s" id="%1$s" /><label for="%1$s">%2$s</label></div>',
					$cf['id'],
					__($cf['title'], 'locations-maps'),
					checked($post_item, true, false)
				);
			}
		}
	}
	
}
