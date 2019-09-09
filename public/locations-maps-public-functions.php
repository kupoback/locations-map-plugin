<?php

/**
 * The public-accessible functionality of the plugin that utilizes the plugins classes.
 *
 * @package    Locations_Maps
 * @subpackage Locations_Maps/public
 * @author     Nick Makris <nick@makris.io>
 */

/**
 * Returns the stored Google API key
 *
 * @return string
 */
function lm_google_api_key()
{
	$options =  get_option('lm_options');
	$get_key = isset( $options['google_api_key']) ? $options['google_api_key'] : null;
	return !is_null($get_key) ? sanitize_text_field($get_key) : '';
}

/**
 * Returns the stored Google Geocode API key for REST API
 *
 * @return string
 */
function lm_google_geocode_api_key()
{
	$options =  get_option('lm_options');
	$get_key = isset( $options['google_geocode_api_key']) ? $options['google_geocode_api_key'] : null;
	return !is_null($get_key) ? sanitize_text_field($get_key) : '';
}

/**
 * Returns the ID of the set Map Icon
 *
 * @return false|string
 */
function lm_map_icon()
{
	$options =  get_option('lm_options');
	$map_icon = isset( $options['map_icon']) ? $options['map_icon'] : '';
	return $map_icon;
}

/**
 * Returns the URL of the saved map style
 *
 * @return string
 */
function lm_map_style()
{
	$options = get_option('lm_options');
	return isset($options['map_style']) && !in_array('other', $options['map_style']) ? $options['map_style'] : ( (isset($options['style_override']) && $options['map_style']) && $options['map_style'] === 'other' ? $options['style_override'] : '' );
}

/**
 * Returns the formated No Results entered text
 *
 * @return mixed|string|void
 */
function lm_no_results()
{
	$options =  get_option('lm_options');
	return isset($options['no_results_text']) ? apply_filters( 'the_content', $options['no_results_text'] ) : '';
}

/**
 * Returns the lat and lng values
 *
 * @return null|object
 */
function lm_main_location() {
	$loc = [];
	isset(get_option('lm_options')['main_location']['lat']) ? $loc['lat'] = get_option('lm_options')['main_location']['lat'] : null;
	isset(get_option('lm_options')['main_location']['lng']) ? $loc['lng'] = get_option('lm_options')['main_location']['lng'] : null;
	
	return $loc ? (object) $loc : null;
}

/**
 * Returns a schema marked address element
 *
 * @param $post_id
 *
 * @return string|void
 */
function lm_address($post_id)
{
	
	if ( !$post_id || get_post_type($post_id) !== 'locations')
		return;
	
	$lm = get_post_meta($post_id, 'lm_meta', true);
	$address = array_key_exists('address', $lm) && $lm['address'] ? '<span property="v:streetAddress">' . $lm['address'] . (array_key_exists('address2', $lm) && $lm['address2'] ? '<span class="address2">' . $lm['address2'] . '</span>' : null) . '</span><br />' : '';
	$city    = array_key_exists('city', $lm) && $lm['city'] ? '<span property="v:addressLocality">' . $lm['city'] . '</span>' : '';
	$state   = array_key_exists('state', $lm) && $lm['state'] ? (array_key_exists('city', $lm) && $lm['city'] ? ', ' : null) . '<span property="v:addressRegion">' . $lm['state'] . '</span>' : '';
	$zip     = array_key_exists('zip', $lm) && $lm['zip'] ? ( (array_key_exists('city', $lm) && $lm['city']) || (array_key_exists('state', $lm) && $lm['state']) ? ' ' : null) . '<span property="v:postalCode">' . $lm['zip'] . '</span>' : '';
	
	return sprintf(
		'<address>%s</address>',
		$address . $city . $state . $zip
	);
}

/**
 * Returns an object array containing a clean phone number for anchor tags and the default text
 *
 * @param $post_id
 *
 * @return null|object
 */
function lm_phone($post_id) {
	$lm = get_post_meta($post_id, 'lm_meta', true);
	return array_key_exists('phone', $lm ) && $lm['phone'] ? (object) [
		'clean' =>  preg_replace( '/\s+/', '', preg_replace('/[^a-zA-Z0-9\']/', '', $lm['phone'] ) ),
		'text'  =>  $lm['phone']
	] : null;
}
