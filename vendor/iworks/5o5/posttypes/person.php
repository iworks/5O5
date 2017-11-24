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

if ( class_exists( 'iworks_5o5_posttypes_person' ) ) {
	return;
}

require_once( dirname( dirname( __FILE__ ) ) . '/posttypes.php' );

class iworks_5o5_posttypes_person extends iworks_5o5_posttypes {

	protected $post_type_name = 'iworks_5o5_person';
	protected $taxonomy_name_club = 'iworks_5o5_club';
	private $nonce_list = '';
	private $users_list = array();

	public function __construct() {
		parent::__construct();
		add_filter( 'the_content', array( $this, 'the_content' ), 10, 2 );
		add_filter( 'international_5o5_posted_on', array( $this, 'get_club' ), 10, 2 );
		/**
		 * change default columns
		 */
		add_filter( "manage_{$this->get_name()}_posts_columns", array( $this, 'add_columns' ) );
		add_action( 'manage_posts_custom_column' , array( $this, 'custom_columns' ), 10, 2 );
		/**
		 * apply default sort order
		 */
		add_action( 'pre_get_posts', array( $this, 'apply_default_sort_order' ) );
		/**
		 * AJAX list
		 */
		$this->nonce_list = $this->options->get_option_name( 'persons_list_nonce' );
		add_action( 'wp_ajax_iworks_5o5_persons_list', array( $this, 'get_select2_list' ) );
		/**
		 * add nonce
		 */
		add_filter( 'wp_localize_script_5o5_admin', array( $this, 'add_nonce' ) );
		/**
		 * fields
		 */
		$this->fields = array(
			'social' => array(
				'website' => array( 'label' => __( 'Web site', '5o5' ) ),
				'facebook' => array( 'label' => __( 'Facebook', '5o5' ) ),
				'twitter' => array( 'label' => __( 'Twitter', '5o5' ) ),
				'instagram' => array( 'label' => __( 'Instagram', '5o5' ) ),
				'gplus' => array( 'label' => __( 'G+', '5o5' ) ),
			),
			'contact' => array(
				'mobile' => array( 'label' => __( 'Mobile', '5o5' ) ),
				'email' => array( 'label' => __( 'E-mail', '5o5' ) ),
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
	}

	/**
	 * Add default class to postbox,
	 */
	public function add_defult_class_to_postbox( $classes ) {
		$classes[] = 'iworks-type';
		return $classes;
	}

	public function register() {
		$parent = true;
		$labels = array(
			'name'                  => _x( 'Persons', 'Person General Name', '5o5' ),
			'singular_name'         => _x( 'Person', 'Person Singular Name', '5o5' ),
			'menu_name'             => __( '5O5', '5o5' ),
			'name_admin_bar'        => __( 'Person', '5o5' ),
			'archives'              => __( 'Persons', '5o5' ),
			'attributes'            => __( 'Item Attributes', '5o5' ),
			'all_items'             => __( 'Persons', '5o5' ),
			'add_new_item'          => __( 'Add New Person', '5o5' ),
			'add_new'               => __( 'Add New Person', '5o5' ),
			'new_item'              => __( 'New Person', '5o5' ),
			'edit_item'             => __( 'Edit Person', '5o5' ),
			'update_item'           => __( 'Update Person', '5o5' ),
			'view_item'             => __( 'View Person', '5o5' ),
			'view_items'            => __( 'View Persons', '5o5' ),
			'search_items'          => __( 'Search person', '5o5' ),
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
			'label'                 => __( 'person', '5o5' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail' ),
			'taxonomies'            => array(
				$this->taxonomy_name_club,
			),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => $parent,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => _x( '5o5-persons', 'slug for archive', '5o5' ),
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'menu_icon'             => plugins_url( '/assets/images/505_logo.svg', $this->base ),
			'register_meta_box_cb'  => array( $this, 'register_meta_boxes' ),
			'rewrite' => array(
				'slug' => _x( '5o5-person', 'slug for single person', '5o5' ),
			),
		);
		register_post_type( $this->post_type_name, $args );
		/**
		 * person hull club Taxonomy.
		 */
		$labels = array(
			'name'                       => _x( 'Clubs', 'Club General Name', '5o5' ),
			'singular_name'              => _x( 'Club', 'Club Singular Name', '5o5' ),
			'menu_name'                  => __( 'Clubs', '5o5' ),
			'all_items'                  => __( 'Clubs', '5o5' ),
			'new_item_name'              => __( 'New Club Name', '5o5' ),
			'add_new_item'               => __( 'Add New Club', '5o5' ),
			'edit_item'                  => __( 'Edit Club', '5o5' ),
			'update_item'                => __( 'Update Club', '5o5' ),
			'view_item'                  => __( 'View Club', '5o5' ),
			'separate_items_with_commas' => __( 'Separate items with commas', '5o5' ),
			'add_or_remove_items'        => __( 'Add or remove items', '5o5' ),
			'choose_from_most_used'      => __( 'Choose from the most used', '5o5' ),
			'popular_items'              => __( 'Popular Clubs', '5o5' ),
			'search_items'               => __( 'Search Clubs', '5o5' ),
			'not_found'                  => __( 'Not Found', '5o5' ),
			'no_terms'                   => __( 'No items', '5o5' ),
			'items_list'                 => __( 'Clubs list', '5o5' ),
			'items_list_navigation'      => __( 'Clubs list navigation', '5o5' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'show_ui'                    => true,
			'show_in_menu'          => true,
			'show_in_quick_edit' => true,
			'rewrite' => array( 'slug' => '5o5-club' ),
		);
		register_taxonomy( $this->taxonomy_name_club, array( $this->post_type_name ), $args );
	}

	public function save_post_meta( $post_id, $post, $update ) {
		$result = $this->save_post_meta_fields( $post_id, $post, $update, $this->fields );
	}
	/**
	 *
	 * @since 1.0
	 */
	public function the_content( $content ) {
		if ( ! is_singular() ) {
			return $content;
		}
		return $content;
	}

	public function register_meta_boxes( $post ) {
		add_meta_box( 'social', __( 'Social Media', '5o5' ), array( $this, 'social' ), $this->post_type_name );
		add_meta_box( 'contact', __( 'Contact data', '5o5' ), array( $this, 'contact' ), $this->post_type_name );
	}

	public function contact( $post ) {
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
	 * @param integer $post_id Current post id (person),
	 *
	 */
	public function custom_columns( $column, $post_id ) {
		switch ( $column ) {
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
		$columns['title'] = __( 'Name', '5o5' );
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
			$query->set( 'orderby', 'post_title' );
		}
		return $query;
	}

	public function get_club( $content, $post_id ) {
		$valid_post_type = $this->check_post_type_by_id( $post_id );
		if ( ! $valid_post_type ) {
			return $content;
		}
		$terms = wp_get_post_terms( $post_id, $this->taxonomy_name_club );
		$t = array();
		foreach ( $terms as $term ) {
			$t[] = $term->name;
		}
		return implode( ', ', $t );
	}

	public function get_select2_list() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error();
		}
		$nonce = $_POST['_wpnonce'];
		if ( ! wp_verify_nonce( $nonce, $this->nonce_list.$_POST['user_id'] ) ) {
			wp_send_json_error();
		}
		$data = array();
		$args = array(
			'nopaging' => true,
			'post_type' => $this->get_name(),
			'orderby' => 'post_title',
			'order' => 'ASC',
		);
		$the_query = new WP_Query( $args );
		// The Loop
		if ( $the_query->have_posts() ) {
			foreach ( $the_query->posts as $post ) {
				$data[] = array(
					'id' => $post->ID,
					'text' => $post->post_title,
				);
			}
			wp_send_json_success( $data );
		}
		wp_send_json_error();
	}

	public function add_nonce( $data ) {
		$data['nonces'][ $this->nonce_list ] = wp_create_nonce( $this->nonce_list.get_current_user_id() );
		return $data;
	}

	/**
	 * Get person name
	 */
	public function get_person_name_by_id( $user_post_id ) {
		if ( empty( $user_post_id ) ) {
			return _x( 'not set', 'Person name on crews list if it is not set', '5o5' );
		}
		$correct_post_type = $this->check_post_type_by_id( $user_post_id );
		if ( ! $correct_post_type ) {
			return _x( 'not set', 'Person name on crews list if it is not set', '5o5' );
		}
		if ( ! isset( $this->users_list[ $user_post_id ] ) ) {
			$post = get_post( $user_post_id );
			$this->users_list[ $user_post_id ] = array(
				'permalink' => get_permalink( $post ),
				'post_title' => get_the_title( $post ),
			);
		}
		return sprintf(
			'<a href="%s">%s</a>',
			esc_url( $this->users_list[ $user_post_id ]['permalink'] ),
			esc_html( $this->users_list[ $user_post_id ]['post_title'] )
		);
	}
}

