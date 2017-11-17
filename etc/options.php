<?php

function iworks_5o5_options() {
	$iworks_5o5_options = array();
	/**
	 * main settings
	 */
	$iworks_5o5_options['index'] = array(
		'version'  => '0.0',
		'page_title' => __( 'Configuration', '5o5' ),
		'menu' => 'submenu',
		'parent' => add_query_arg(
			array(
				'post_type' => 'iworks_5o5_boat',
			),
			'edit.php'
		),
		'options'  => array(
		),
		//      'metaboxes' => array(),
		'pages' => array(
		),
	);
	return $iworks_5o5_options;
}

