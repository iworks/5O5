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

	public function __construct() {
		parent::__construct();
		$this->version = 'PLUGIN_VERSION';
		$this->capability = apply_filters( 'iworks_5o5_capability', 'manage_options' );
		/**
		 * post_types
		 */
		$post_types = array( 'boat' );
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
	}

	public function admin_init() {
		iworks_5o5_options_init();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
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
				'5o5-admin-invoice' => 'assets/scripts/admin/src/invoice.js',
				'5o5-admin-select2' => 'assets/scripts/admin/src/select2.js',
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
		wp_localize_script(
			'' == $this->dev ? '5o5-admin-invoice':'5o5-admin',
			__CLASS__,
			array(
				'messages' => array(
				),
			)
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
}
