<?php
/*

Copyright 2017 Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( class_exists( 'iworks_5o5_posttypes_boat' ) ) {
	return;
}

require_once( dirname( dirname( __FILE__ ) ) . '/posttypes.php' );

class iworks_5o5_posttypes_boat extends iworks_5o5_posttypes {

	protected $post_type_name = 'iworks_5o5_boat';
	protected $taxonomy_name_builder = 'iworks_5o5_boat_builder';

	public function __construct() {
		parent::__construct();
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'the_content' ), 10, 2 );
		add_filter( 'default_title', array( $this, 'default_title' ), 10, 2 );
		/**
		 * fields
		 */
		$this->fields = array(
			'boat' => array(
				'build_year' => array(
					'type' => 'date',
					'label' => __( 'Year of building', '5o5' ),
					'args' => array(
						'class' => array( 'medium-text' ),
						'default' => date_i18n( 'Y', time() ),
						'data' => array(
							'date-format' => 'yy',
						),
					),
				),
				'name' => array(
					'label' => __( 'Boat name', '5o5' ),
				),
				'color_top' => array(
					'label' => __( 'Color top', '5o5' ),
				),
				'color_side' => array(
					'label' => __( 'Color side', '5o5' ),
				),
				'color_bottom' => array(
					'label' => __( 'Color bottom', '5o5' ),
				),
				'in_poland_date' => array(
					'type' => 'date',
					'label' => __( 'In Poland:', '5o5' ),
					'args' => array(
						'class' => array( 'medium-text' ),
						'default' => date_i18n( 'Y-m', time() ),
						'data' => array(
							'date-format' => 'yy-mm',
						),
					),
				),
				'sails' => array(
					'label' => __( 'Sails producer name', '5o5' ),
				),
				'mast' => array(
					'label' => __( 'Mast', '5o5' ),
				),
				'location' => array(
					'label' => __( 'Location', '5o5' ),
				),
				'double_pole' => array(
					'label' => __( 'Double pole', '5o5' ),
					'type' => 'checkbox',
					'args' => array(),
				),
			),
		);

		/**
		 * add class to metaboxes
		 */
		foreach ( array_keys( $this->fields ) as $name ) {
			if ( 'basic' == $name ) {
				continue;
			}
			$key = sprintf( 'postbox_classes_%s_%s', $this->get_name(), $name );
			add_filter( $key, array( $this, 'add_defult_class_to_postbox' ) );
		}

		/**
		 * save extra field
		 */
		$this->post_type_objects[ $this->get_name() ] = $this;
		add_action( 'iworks_5o5_posttype_update_post_meta', array( $this, 'save_year_month_to_extra_field' ), 10, 5 );

		/**
		 * Meta Boxes to close by default
		 */
		$meta_boxes_to_close = array( 'income', 'expense' );
		foreach ( $meta_boxes_to_close as $meta_box ) {
			$filter = sprintf( 'postbox_classes_%s_%s', $this->get_name(), $meta_box );
			add_filter( $filter, array( $this, 'close_meta_boxes' ) );
		}

		/**
		 * change default columns
		 */
		add_filter( "manage_{$this->get_name()}_posts_columns", array( $this, 'add_columns' ) );
		add_action( 'manage_posts_custom_column' , array( $this, 'custom_columns' ), 10, 2 );

		/**
		 * apply default sort order
		 */
		add_action( 'pre_get_posts', array( $this, 'apply_default_sort_order' ) );
	}

	/**
	 * Add default class to postbox,
	 */
	public function add_defult_class_to_postbox( $classes ) {
		$classes[] = 'iworks-type';
		return $classes;
	}

	public function register() {
		$labels = array(
			'name'                  => _x( 'Boats', 'Boat General Name', '5o5' ),
			'singular_name'         => _x( 'Boat', 'Boat Singular Name', '5o5' ),
			'menu_name'             => __( '5O5', '5o5' ),
			'name_admin_bar'        => __( 'Boat', '5o5' ),
			'archives'              => __( 'Item Archives', '5o5' ),
			'attributes'            => __( 'Item Attributes', '5o5' ),
			'parent_item_colon'     => __( 'Parent Boat:', '5o5' ),
			'all_items'             => __( 'All Boats', '5o5' ),
			'add_new_item'          => __( 'Add New Boat', '5o5' ),
			'add_new'               => __( 'Add New', '5o5' ),
			'new_item'              => __( 'New Boat', '5o5' ),
			'edit_item'             => __( 'Edit Boat', '5o5' ),
			'update_item'           => __( 'Update Boat', '5o5' ),
			'view_item'             => __( 'View Boat', '5o5' ),
			'view_items'            => __( 'View Boats', '5o5' ),
			'search_items'          => __( 'Search Boat', '5o5' ),
			'not_found'             => __( 'Not found', '5o5' ),
			'not_found_in_trash'    => __( 'Not found in Trash', '5o5' ),
			'featured_image'        => __( 'Featured Image', '5o5' ),
			'set_featured_image'    => __( 'Set featured image', '5o5' ),
			'remove_featured_image' => __( 'Remove featured image', '5o5' ),
			'use_featured_image'    => __( 'Use as featured image', '5o5' ),
			'insert_into_item'      => __( 'Insert into item', '5o5' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', '5o5' ),
			'items_list'            => __( 'Items list', '5o5' ),
			'items_list_navigation' => __( 'Items list navigation', '5o5' ),
			'filter_items_list'     => __( 'Filter items list', '5o5' ),
		);
		$args = array(
			'label'                 => __( 'Boat', '5o5' ),
			'description'           => __( 'Boat Description', '5o5' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail' ),
			'taxonomies'            => array( $this->taxonomy_name_builder ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'menu_icon'             => plugins_url( '/assets/images/505_logo.svg', $this->base ),
			'register_meta_box_cb'  => array( $this, 'register_meta_boxes' ),
			'rewrite' => array(
				'slug' => '5o5-boat',
			),
		);
		register_post_type( $this->post_type_name, $args );
		$labels = array(
			'name'                       => _x( 'Builders', 'Taxonomy General Name', 'text_domain' ),
			'singular_name'              => _x( 'Builder', 'Taxonomy Singular Name', 'text_domain' ),
			'menu_name'                  => __( 'Builder', 'text_domain' ),
			'all_items'                  => __( 'All Builders', 'text_domain' ),
			'parent_item'                => __( 'Parent Builder', 'text_domain' ),
			'parent_item_colon'          => __( 'Parent Builder:', 'text_domain' ),
			'new_item_name'              => __( 'New Builder Name', 'text_domain' ),
			'add_new_item'               => __( 'Add New Builder', 'text_domain' ),
			'edit_item'                  => __( 'Edit Builder', 'text_domain' ),
			'update_item'                => __( 'Update Builder', 'text_domain' ),
			'view_item'                  => __( 'View Builder', 'text_domain' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
			'popular_items'              => __( 'Popular Builders', 'text_domain' ),
			'search_items'               => __( 'Search Builders', 'text_domain' ),
			'not_found'                  => __( 'Not Found', 'text_domain' ),
			'no_terms'                   => __( 'No items', 'text_domain' ),
			'items_list'                 => __( 'Builders list', 'text_domain' ),
			'items_list_navigation'      => __( 'Builders list navigation', 'text_domain' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'show_admin_column'          => true,
			'show_in_quick_edit' => true,
			'rewrite' => array(
				'slug' => '5o5-manufacturer',
			),
		);
		register_taxonomy( $this->taxonomy_name_builder, array( $this->post_type_name ), $args );
	}

	public function save_post_meta( $post_id, $post, $update ) {
		$this->save_post_meta_fields( $post_id, $post, $update, $this->fields );
	}

	/**
	 * Change "Enter title here" to "Enter boat number"
	 *
	 * @since 1.0
	 */
	public function enter_title_here( $title, $post ) {
		if ( $post->post_type == $this->post_type_name ) {
			return __( 'Enter boat number eg. POL 7020', '5o5' );
		}
		return $title;
	}
	/**
	 *
	 * @since 1.0
	 */
	public function default_title( $title, $post ) {
		if ( ! empty( $title ) ) {
			return $title;
		}
		if ( $post->post_type == $this->post_type_name ) {
			return __( 'POL ', '5o5' );
		}
		return $title;
	}

	/**
	 *
	 * @since 1.0
	 */
	public function the_content( $content ) {
		if ( ! is_singular() ) {
			return $content;
		}
		$post_type = get_post_type();
		if ( $post_type == $this->post_type_name ) {
			$text = '';
			$options = array(
				'boat_build_year' => __( 'Year of building:', '5o5' ),
			'producer' => __( 'Hull manufacturer:', '5o5' ),
			   'boat_in_poland_date' => __( 'In Poland:', '5o5' ),
			   'boat_name' => __( 'Name:', '5o5' ),
			   'colors' => __( 'Colors (top/side/bottom):', '5o5' ),
			   'boat_sails' => __( 'Sails on:', '5o5' ),
			   'boat_mast' => __( 'Mast:', '5o5' ),
			   'boat_double_pole' => __( 'Double pole:', '5o5' ),
			);
			foreach ( $options as $key => $label ) {
				$name = $this->options->get_option_name( $key );
				$value = get_post_meta( get_the_ID(), $name, true );
				if ( empty( $value ) ) {
					$value = _x( 'unknown', 'value of boat', '5o5' );
					$value = '-';
					switch ( $key ) {
						/**
						 * handle colors
						 */
						case 'colors':
							$colors = array();
							$colors_keys = array( 'top', 'side', 'bottom' );
							foreach ( $colors_keys as $ckey ) {
								$cname = $this->options->get_option_name( 'boat_color_'.$ckey );
								$colors[] = get_post_meta( get_the_ID(), $cname, true );
							}
							$value = implode( '/', $colors );
						break;
						case 'producer':
							$value = get_the_term_list( get_the_ID(), $this->taxonomy_name_builder );
						break;
					}
				} else {
					switch ( $key ) {
						case 'boat_build_year':
							$value = date_i18n( 'Y', $value );
						break;
						case 'boat_in_poland_date':
							$value = date_i18n( 'Y-m', $value );
						break;
					}
				}
				$text .= $this->boat_single_row( $key, $label, $value );
			}
			if ( ! empty( $text ) ) {
				$content = sprintf( '<table>%s</table>%s', $text, $content );
			}
		}
		return $content;
	}

	private function boat_single_row( $key, $label, $value ) {
		$text = '';
		$text .= sprintf( '<tr class="boat-%s">', esc_attr( $key ) );
		$text .= sprintf( '<td>%s</td>', esc_html( $label ) );
		$text .= sprintf( '<td>%s</td>', $value );
		$text .= '</tr>';
		return $text;
	}

	public function register_meta_boxes( $post ) {
		add_meta_box( 'boat', __( 'Boat data', '5o5' ), array( $this, 'boat' ), $this->post_type_name );
	}

	public function boat( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	/**
	 * Get custom column values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column Column name,
	 * @param integer $post_id Current post id (Boat),
	 *
	 */
	public function custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'builder':
				$id = get_post_meta( $post_id, $this->get_custom_field_basic_builder_name() , true );
				if ( empty( $id ) ) {
					echo '-';
				} else {
					printf(
						'<a href="%s">%s</a>',
						add_query_arg(
							array(
							'builder' => $id,
							'post_type' => 'iworks_5o5_boat',
							),
							admin_url( 'edit.php' )
						),
						get_post_meta( $id, 'iworks_5o5_builder_data_full_name', true )
					);
				}
			break;

			case 'date_of_boat':
				$timestamp = get_post_meta( $post_id, 'boat_date', true );
				if ( empty( $timestamp ) ) {
					echo '-';
				} else {
					echo date_i18n( get_option( 'date_format' ), $timestamp );
				}
			break;

		}
	}

	/**
	 * change default columns
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns list of columns.
	 * @return array $columns list of columns.
	 */
	public function add_columns( $columns ) {
		unset( $columns['date'] );
		$columns['date_of_boat'] = __( 'Date', '5o5' );
		$columns['title'] = __( 'Boat Number', '5o5' );
		return $columns;
	}

	/**
	 * Add default sorting
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query WP Query object.
	 */
	public function apply_default_sort_order( $query ) {
		/**
		 * do not change if it is already set by request
		 */
		if ( isset( $_REQUEST['orderby'] ) ) {
			return $query;
		}
		/**
		 * do not change outsite th admin area
		 */
		if ( ! is_admin() ) {
			return $query;
		}
		/**
		 * check get_current_screen()
		 */
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $query;
		}
		/**
		 * check screen post type
		 */
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $query;
		}
		/**
		 * query post type
		 */
		if ( isset( $query->query['post_type'] ) && $this->get_name() != $query->query['post_type'] ) {
			return $query;
		}
		/**
		 * screen post type
		 */
		$screen = get_current_screen();
		if ( isset( $screen->post_type ) && $this->get_name() == $screen->post_type ) {
		}
		return $query;
	}

	/**
	 * Get "year_month" custom filed name.
	 *
	 * @since 1.0.0
	 *
	 * @return string Custom Field meta_key.
	 */
	public function get_custom_field_year_month_name() {
		return $this->options->get_option_name( 'year_month' );
	}

	/**
	 * Get "basic_builder" custom filed name.
	 *
	 * @since 1.0.0
	 *
	 * @return string Custom Field meta_key.
	 */
	public function get_custom_field_basic_builder_name() {
		return $this->options->get_option_name( 'basic_builder' );
	}
}

