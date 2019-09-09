<?php

// State Array
$states = [
	'AL' => 'Alabama',
	'AK' => 'Alaska',
	'AZ' => 'Arizona',
	'AR' => 'Arkansas',
	'CA' => 'California',
	'CO' => 'Colorado',
	'CT' => 'Connecticut',
	'DE' => 'Delaware',
	'DC' => 'District of Colombia',
	'FL' => 'Florida',
	'GA' => 'Georgia',
	'HI' => 'Hawaii',
	'ID' => 'Idaho',
	'IL' => 'Illinois',
	'IN' => 'Indiana',
	'IA' => 'Iowa',
	'KS' => 'Kansas',
	'KY' => 'Kentucky',
	'LA' => 'Louisiana',
	'ME' => 'Maine',
	'MD' => 'Maryland',
	'MA' => 'Massachusetts',
	'MI' => 'Michigan',
	'MN' => 'Minnesota',
	'MS' => 'Mississippi',
	'MO' => 'Missouri',
	'MT' => 'Montana',
	'NE' => 'Nebraska',
	'NV' => 'Nevada',
	'NH' => 'New Hampshire',
	'NJ' => 'New Jersey',
	'NM' => 'New Mexico',
	'NY' => 'New York',
	'NC' => 'North Carolina',
	'ND' => 'North Dakota',
	'OH' => 'Ohio',
	'OK' => 'Oklahoma',
	'OR' => 'Oregon',
	'PA' => 'Pennsylvania',
	'PR' => 'Puerto Rico',
	'RI' => 'Rhode Island',
	'SC' => 'South Carolina',
	'SD' => 'South Dakota',
	'TN' => 'Tennessee',
	'TX' => 'Texas',
	'UT' => 'Utah',
	'VT' => 'Vermont',
	'VA' => 'Virginia',
	'WA' => 'Washington',
	'WV' => 'West Virginia',
	'WI' => 'Wisconsin',
	'WY' => 'Wyoming',
];
$fields = [
	[
		'euid'  => 'address',
		'value' => 'address',
		'title' => 'Address',
		'type'  => 'text',
		'class' => 'one-half',
	],
	[
		'euid'  => 'address2',
		'value' => 'address2',
		'title' => 'Address 2',
		'type'  => 'text',
		'class' => 'one-half',
	],
	[
		'euid'  => 'city',
		'value' => 'city',
		'title' => 'City',
		'type'  => 'text',
		'class' => 'one-half',
	],
	[
		'euid'    => 'state',
		'value'   => 'state',
		'title'   => 'State',
		'type'    => 'select',
		'choices' => $states,
		'class'   => 'one-quarter',
	],
	[
		'euid'  => 'zip',
		'value' => 'zip',
		'title' => 'Zip code',
		'type'  => 'number',
		'class' => 'one-quarter',
	],
	[
		'euid'  => 'email',
		'value' => 'email',
		'title' => 'Email',
		'type'  => 'email',
		'class' => 'one-third',
	],
	[
		'euid'  => 'phone',
		'value' => 'phone',
		'title' => 'Phone Number',
		'type'  => 'text',
		'class' => 'one-third',
	],
	[
		'euid'  => 'website',
		'value' => 'website',
		'title' => 'Website',
		'type'  => 'text',
		'class' => 'one-third',
	],
	[
		'euid'  => 'lat_text',
		'value' => 'lat',
		'title' => 'Latitude',
		'type'  => 'text',
		'class' => 'one-half readonly',
	],
	[
		'euid'  => 'lng_text',
		'value' => 'lng',
		'title' => 'Longitude',
		'type'  => 'text',
		'class' => 'one-half readonly',
	],
	[
		'euid'  => 'lat',
		'value' => 'lat',
		'title' => 'Latitude',
		'type'  => 'hidden',
	],
	[
		'euid'  => 'lng',
		'value' => 'lng',
		'title' => 'Longitude',
		'type'  => 'hidden',
	],
	[
		'euid'  => 'placeID',
		'value' => 'placeID',
		'title' => 'Place ID',
		'type'  => 'hidden',
	],
];

return $fields;
