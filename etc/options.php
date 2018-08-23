<?php

function iworks_5o5_options() {
	$iworks_5o5_options = array();
	/**
	 * main settings
	 */
	$parent = add_query_arg( 'post_type', 'iworks_5o5_person', 'edit.php' );

	$iworks_5o5_options['index'] = array(
		'version'  => '0.0',
		'use_tabs' => true,
		'page_title' => __( 'Configuration', '5o5' ),
		'menu' => 'submenu',
		'parent' => $parent,
		'options'  => array(
			array(
				'type'              => 'heading',
				'label'             => __( 'General', '5o5' ),
			),
			array(
				'type'              => 'heading',
				'label'             => __( 'Results', '5o5' ),
			),
			array(
				'name'              => 'results_show_points',
				'type'              => 'checkbox',
				'th'                => __( 'Show points', '5o5' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes' => array( 'switch-button' ),
			),
			array(
				'type'              => 'heading',
				'label'             => __( 'Persons', '5o5' ),
			),
			array(
				'name'              => 'person_show_social_media',
				'type'              => 'checkbox',
				'th'                => __( 'Show social media links', '5o5' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes' => array( 'switch-button' ),
			),
			array(
				'name'              => 'person_show_boats_table',
				'type'              => 'checkbox',
				'th'                => __( 'Show boats table', '5o5' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes' => array( 'switch-button' ),
			),
		),
		//      'metaboxes' => array(),
		'pages' => array(
			'new-boat' => array(
				'menu' => 'submenu',
				'parent' => $parent,
				'page_title'  => __( 'Add New Boat', '5o5' ),
				'menu_slug' => htmlentities(
					add_query_arg(
						array(
							'post_type' => 'iworks_5o5_boat',
						),
						'post-new.php'
					)
				),
				'set_callback_to_null' => true,
			),
			'series' => array(
				'menu' => 'submenu',
				'parent' => $parent,
				'page_title'  => __( 'Series', '5o5' ),
				'menu_title'  => __( 'Series', '5o5' ),
				'menu_slug' => htmlentities(
					add_query_arg(
						array(
							'taxonomy' => 'iworks_dinghy_serie',
							'post_type' => 'iworks_5o5_result',
						),
						'edit-tags.php'
					)
				),
				'set_callback_to_null' => true,
			),
			'hull' => array(
				'menu' => 'submenu',
				'parent' => $parent,
				'page_title'  => __( 'Hulls Manufaturers', '5o5' ),
				'menu_title'  => __( 'Hulls Manufaturers', '5o5' ),
				'menu_slug' => htmlentities(
					add_query_arg(
						array(
							'taxonomy' => 'iworks_5o5_boat_manufacturer',
							'post_type' => 'iworks_5o5_person',
						),
						'edit-tags.php'
					)
				),
				'set_callback_to_null' => true,
			),
			'sail' => array(
				'menu' => 'submenu',
				'parent' => $parent,
				'page_title'  => __( 'Sails Manufaturers', '5o5' ),
				'menu_title'  => __( 'Sails Manufaturers', '5o5' ),
				'menu_slug' => htmlentities(
					add_query_arg(
						array(
							'taxonomy' => 'iworks_5o5_sails_manufacturer',
							'post_type' => 'iworks_5o5_person',
						),
						'edit-tags.php'
					)
				),
				'set_callback_to_null' => true,
			),
			'mast' => array(
				'menu' => 'submenu',
				'parent' => $parent,
				'page_title'  => __( 'Masts Manufaturers', '5o5' ),
				'menu_title'  => __( 'Masts Manufaturers', '5o5' ),
				'menu_slug' => htmlentities(
					add_query_arg(
						array(
							'taxonomy' => 'iworks_5o5_mast_manufacturer',
							'post_type' => 'iworks_5o5_person',
						),
						'edit-tags.php'
					)
				),
				'set_callback_to_null' => true,
			),
			'location' => array(
				'menu' => 'submenu',
				'parent' => $parent,
				'page_title'  => __( 'Locations', '5o5' ),
				'menu_title'  => __( 'Locations', '5o5' ),
				'menu_slug' => htmlentities(
					add_query_arg(
						array(
							'taxonomy' => 'iworks_dinghy_location',
							'post_type' => 'iworks_5o5_person',
						),
						'edit-tags.php'
					)
				),
				'set_callback_to_null' => true,
			),
		),
	);
	return $iworks_5o5_options;
}

