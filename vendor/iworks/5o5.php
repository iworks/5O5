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

if ( class_exists( 'iworks_5o5' ) ) {
	return;
}

require_once( dirname( dirname( __FILE__ ) ) . '/iworks.php' );

class iworks_5o5 extends iworks {

	private $capability;
	private $post_type_boat;
	private $post_type_person;

	public function __construct() {
		parent::__construct();
		$this->version = 'PLUGIN_VERSION';
		$this->capability = apply_filters( 'iworks_5o5_capability', 'manage_options' );
		/**
		 * post_types
		 */
		$post_types = array( 'boat', 'person' );
		foreach ( $post_types as $post_type ) {
			include_once( $this->base.'/iworks/5o5/posttypes/'.$post_type.'.php' );
			$class = sprintf( 'iworks_5o5_posttypes_%s', $post_type );
			$value = sprintf( 'post_type_%s', $post_type );
			$this->$value = new $class();
		}
		/**
		 * admin init
		 */
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'init', array( $this, 'register_boat_number' ) );
	}

	public function admin_init() {
		iworks_5o5_options_init();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	public function get_post_type_name( $post_type ) {
		$value = sprintf( 'post_type_%s', $post_type );
		if ( isset( $this->$value ) ) {
			return $this->$value->get_name();
		}
		return new WP_Error( 'broke', __( '5o5 do not have such post type!', '5O5' ) );
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		/**
		 * off on not 5O5 pages
		 */
		$re = sprintf( '/%s_/', __CLASS__ );
		if ( ! preg_match( $re, $screen->id ) ) {
			return;
		}
		/**
		 * datepicker
		 */
		$file = 'assets/externals/datepicker/css/jquery-ui-datepicker.css';
		$file = plugins_url( $file, $this->base );
		wp_register_style( 'jquery-ui-datepicker', $file, false, '1.12.1' );
		/**
		 * select2
		 */
		$file = 'assets/externals/select2/css/select2.min.css';
		$file = plugins_url( $file, $this->base );
		wp_register_style( 'select2', $file, false, '4.0.3' );
		/**
		 * Admin styles
		 */
		$file = sprintf( '/assets/styles/5o5-admin%s.css', $this->dev );
		$version = $this->get_version( $file );
		$file = plugins_url( $file, $this->base );
		wp_register_style( 'admin-5o5', $file, array( 'jquery-ui-datepicker', 'select2' ), $version );
		wp_enqueue_style( 'admin-5o5' );
		/**
		 * select2
		 */
		wp_register_script( 'select2', plugins_url( 'assets/externals/select2/js/select2.full.min.js', $this->base ), array(), '4.0.3' );
		/**
		 * Admin scripts
		 */
		$files = array(
			'5o5-admin' => sprintf( 'assets/scripts/admin/5o5%s.js', $this->dev ),
		);
		if ( '' == $this->dev ) {
			$files = array(
				'5o5-admin-datepicker' => 'assets/scripts/admin/src/datepicker.js',
				'5o5-admin-select2' => 'assets/scripts/admin/src/select2.js',
				'5o5-admin-person' => 'assets/scripts/admin/src/person.js',
				'5o5-admin-boat' => 'assets/scripts/admin/src/boat.js',
				'5o5-admin' => 'assets/scripts/admin/src/5o5.js',
			);
		}
		$deps = array(
			'jquery-ui-datepicker',
			'select2',
		);
		foreach ( $files as $handle => $file ) {
			wp_register_script(
				$handle,
				plugins_url( $file, $this->base ),
				$deps,
				$this->get_version(),
				true
			);
			wp_enqueue_script( $handle );
		}
		/**
		 * JavaScript messages
		 *
		 * @since 1.0.0
		 */
		$data = array(
			'messages' => array(),
			'nonces' => array(),
			'user_id' => get_current_user_id(),
		);
		wp_localize_script(
			'5o5-admin',
			__CLASS__,
			apply_filters( 'wp_localize_script_5o5_admin', $data )
		);
	}

	public function init() {
		if ( is_admin() ) {
		} else {
			$file = 'assets/styles/5o5'.$this->dev.'.css';
			wp_enqueue_style( '5o5', plugins_url( $file, $this->base ), array(), $this->get_version( $file ) );
		}
	}

	/**
	 * Plugin row data
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $this->dir.'/5o5.php' == $file ) {
			if ( ! is_multisite() && current_user_can( $this->capability ) ) {
				$links[] = '<a href="themes.php?page='.$this->dir.'/admin/index.php">' . __( 'Settings' ) . '</a>';
			}
			/* start:free */
			$links[] = '<a href="http://iworks.pl/donate/5o5.php">' . __( 'Donate' ) . '</a>';
			/* end:free */
		}
		return $links;
	}

	public function register_boat_number() {
		$labels = array(
			'name'                       => _x( 'Boat Numbers', 'Taxonomy General Name', '5o5' ),
			'singular_name'              => _x( 'Boat Number', 'Taxonomy Singular Name', '5o5' ),
			'menu_name'                  => __( 'Boat Number', '5o5' ),
			'all_items'                  => __( 'All Boat Numbers', '5o5' ),
			'new_item_name'              => __( 'New Boat Number Name', '5o5' ),
			'add_new_item'               => __( 'Add New Boat Number', '5o5' ),
			'edit_item'                  => __( 'Edit Boat Number', '5o5' ),
			'update_item'                => __( 'Update Boat Number', '5o5' ),
			'view_item'                  => __( 'View Boat Number', '5o5' ),
			'separate_items_with_commas' => __( 'Separate Boat Numbers with commas', '5o5' ),
			'add_or_remove_items'        => __( 'Add or remove Boat Numbers', '5o5' ),
			'choose_from_most_used'      => __( 'Choose from the most used', '5o5' ),
			'popular_items'              => __( 'Popular Boat Numbers', '5o5' ),
			'search_items'               => __( 'Search Boat Numbers', '5o5' ),
			'not_found'                  => __( 'Not Found', '5o5' ),
			'no_terms'                   => __( 'No items', '5o5' ),
			'items_list'                 => __( 'Boat Numbers list', '5o5' ),
			'items_list_navigation'      => __( 'Boat Numbers list navigation', '5o5' ),
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
				'slug' => _x( '5o5-boat-number', 'slug for images', '5o5' ),
			),
		);
		register_taxonomy( 'boat_number', array( 'attachment' ), $args );
	}
}
