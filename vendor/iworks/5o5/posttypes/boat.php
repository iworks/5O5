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
	protected $taxonomy_name_manufacturer = 'iworks_5o5_boat_manufacturer';

	public function __construct() {
		parent::__construct();
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'the_content' ), 10, 2 );
		add_filter( 'default_title', array( $this, 'default_title' ), 10, 2 );
		add_filter( 'international_5o5_posted_on', array( $this, 'get_manufacturer' ), 10, 2 );
		/**
		 * fields
		 */
		$this->fields = array(
			'boat' => array(
				'build_year' => array(
					'label' => __( 'Year of building', '5o5' ),
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
					'label' => __( 'In Poland:', '5o5' ),
				),
				'sails' => array(
					'label' => __( 'Sails manufacturer', '5o5' ),
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
				'helm' => array(
				'label' => __( 'Helmsman', '5o5' ),
				'crew' => array(
				'label' => __( 'Crew', '5o5' ),
				),
				'social' => array(
				'website' => array( 'label' => __( 'Web site', '5o5' ) ),
				'facebook' => array( 'label' => __( 'Facebook', '5o5' ) ),
				'twitter' => array( 'label' => __( 'Twitter', '5o5' ) ),
				'instagram' => array( 'label' => __( 'Instagram', '5o5' ) ),
				'gplus' => array( 'label' => __( 'G+', '5o5' ) ),
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
			'archives'              => __( 'Boats', '5o5' ),
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
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail' ),
			'taxonomies'            => array( $this->taxonomy_name_manufacturer ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => _x( '5o5-boats', 'slug for archive', '5o5' ),
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'menu_icon'             => plugins_url( '/assets/images/505_logo.svg', $this->base ),
			'register_meta_box_cb'  => array( $this, 'register_meta_boxes' ),
			'rewrite' => array(
				'slug' => _x( '5o5-boat', 'slug for single boat', '5o5' ),
			),
		);
		register_post_type( $this->post_type_name, $args );
		$labels = array(
			'name'                       => _x( 'Manufacturers', 'Taxonomy General Name', '5o5' ),
			'singular_name'              => _x( 'Manufacturer', 'Taxonomy Singular Name', '5o5' ),
			'menu_name'                  => __( 'Manufacturer', '5o5' ),
			'all_items'                  => __( 'All Manufacturers', '5o5' ),
			'parent_item'                => __( 'Parent Manufacturer', '5o5' ),
			'parent_item_colon'          => __( 'Parent Manufacturer:', '5o5' ),
			'new_item_name'              => __( 'New Manufacturer Name', '5o5' ),
			'add_new_item'               => __( 'Add New Manufacturer', '5o5' ),
			'edit_item'                  => __( 'Edit Manufacturer', '5o5' ),
			'update_item'                => __( 'Update Manufacturer', '5o5' ),
			'view_item'                  => __( 'View Manufacturer', '5o5' ),
			'separate_items_with_commas' => __( 'Separate items with commas', '5o5' ),
			'add_or_remove_items'        => __( 'Add or remove items', '5o5' ),
			'choose_from_most_used'      => __( 'Choose from the most used', '5o5' ),
			'popular_items'              => __( 'Popular Manufacturers', '5o5' ),
			'search_items'               => __( 'Search Manufacturers', '5o5' ),
			'not_found'                  => __( 'Not Found', '5o5' ),
			'no_terms'                   => __( 'No items', '5o5' ),
			'items_list'                 => __( 'Manufacturers list', '5o5' ),
			'items_list_navigation'      => __( 'Manufacturers list navigation', '5o5' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'show_ui'                    => true,
			'show_in_quick_edit' => true,
			'rewrite' => array(
				'slug' => '5o5-manufacturer',
			),
		);
		register_taxonomy( $this->taxonomy_name_manufacturer, array( $this->post_type_name ), $args );
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
				'manufacturer' => __( 'Hull manufacturer:', '5o5' ),
				'boat_in_poland_date' => __( 'In Poland:', '5o5' ),
				'boat_name' => __( 'Name:', '5o5' ),
				'colors' => __( 'Colors (top/side/bottom):', '5o5' ),
				'boat_sails' => __( 'Sails on:', '5o5' ),
				'boat_mast' => __( 'Mast:', '5o5' ),
				'boat_double_pole' => __( 'Double pole:', '5o5' ),
				'boat_location' => __( 'Location:', '5o5' ),
				'boat_helm' => __( 'Helmsman:', '5o5' ),
				'boat_crew' => __( 'Crew:', '5o5' ),
				'social_website' => __( 'Web site', '5o5' ),
				'social' => __( 'Social Media', '5o5' ),
			);
			foreach ( $options as $key => $label ) {
				$name = $this->options->get_option_name( $key );
				$value = get_post_meta( get_the_ID(), $name, true );
				if ( empty( $value ) ) {
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
							$colors = array_filter( $colors );
							if ( ! empty( $colors ) ) {
								$value = implode( '/', $colors );
							}
						break;
						case 'manufacturer':
							$value = get_the_term_list( get_the_ID(), $this->taxonomy_name_manufacturer );
							break;
						case 'social':
							foreach ( $this->fields['social'] as $social_key => $social ) {
								if ( 'website' == $social_key ) {
									continue;
								}
								$name = $this->options->get_option_name( 'social_'.$social_key );
								$social_value = get_post_meta( get_the_ID(), $name, true );
								if ( empty( $social_value ) ) {
									continue;
								}
								$value .= sprintf(
									'<a href="%s"><span class="dashicons dashicons-%s"></span></a>',
									$social_value,
									$social_key
								);
							}
							break;
					}
					if ( empty( $value ) ) {
						$value = _x( 'unknown', 'value of boat', '5o5' );
						$value = '-';
					}
				}
				$text .= $this->boat_single_row( $key, $label, $value );
			}
			if ( ! empty( $text ) ) {
				$content = sprintf( '<table class="boat-data">%s</table>%s', $text, $content );
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
		add_meta_box( 'social', __( 'Social Media', '5o5' ), array( $this, 'social' ), $this->post_type_name );
	}

	public function boat( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function social( $post ) {
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
				$id = get_post_meta( $post_id, $this->get_custom_field_basic_manufacturer_name() , true );
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
						get_post_meta( $id, 'iworks_5o5_manufacturer_data_full_name', true )
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
			if ( ! $query->is_main_query() ) {
				return $query;
			}
			$post_type = get_query_var( 'post_type' );
			if ( ! empty( $post_type ) && $post_type === $this->post_type_name ) {
				$query->set( 'orderby', 'post_title' );
				return $query;
			}
			$taxonomy = get_query_var( $this->taxonomy_name_manufacturer );
			if ( ! empty( $taxonomy ) ) {
				$query->set( 'orderby', 'post_title' );
				return $query;
			}
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

	public function get_manufacturer( $content, $post_id ) {
		$valid_post_type = $this->check_post_type_by_id( $post_id );
		if ( ! $valid_post_type ) {
			return $content;
		}
		$terms = wp_get_post_terms( $post_id, $this->taxonomy_name_manufacturer );
		$t = array();
		foreach ( $terms as $term ) {
			$t[] = $term->name;
		}
		return implode( ', ', $t );
	}
}

