<?php
/*
Plugin Name: 5o5
Text Domain: 5o5
Plugin URI: http://iworks.pl/5o5/
Description: 5O5 class plugin: regata, boats, crew
Version: PLUGIN_VERSION
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

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

/**
 * static options
 */
define( 'IWORKS_5O5_VERSION', 'PLUGIN_VERSION' );
define( 'IWORKS_5O5_PREFIX',  'iworks_5o5_' );
$base = dirname( __FILE__ );
$vendor = $base.'/vendor';

/**
 * require: Iworks5o5 Class
 */
if ( ! class_exists( 'iworks_5o5' ) ) {
	require_once $vendor.'/iworks/5o5.php';
}
/**
 * configuration
 */
require_once $base.'/etc/options.php';
/**
 * require: IworksOptions Class
 */
if ( ! class_exists( 'iworks_options' ) ) {
	require_once $vendor.'/iworks/options/options.php';
}

/**
 * i18n
 */
load_plugin_textdomain( '5o5', false, plugin_basename( __FILE__ ).'/languages' );

/**
 * load options
 */
$iworks_5o5_options = new iworks_options();
$iworks_5o5_options->set_option_function_name( 'iworks_5o5_options' );
$iworks_5o5_options->set_option_prefix( IWORKS_5O5_PREFIX );

function iworks_5o5_options_init() {
	global $iworks_5o5_options;
	$iworks_5o5_options->options_init();
}

function iworks_5o5_activate() {
	$iworks_5o5_options = new iworks_options();
	$iworks_5o5_options->set_option_function_name( 'iworks_5o5_options' );
	$iworks_5o5_options->set_option_prefix( IWORKS_5O5_PREFIX );
	$iworks_5o5_options->activate();
	add_role( 'sailor', __( 'Sailor', '5o5' ), array( 'read' => true, 'level_0' => true ) );
}

function iworks_5o5_deactivate() {
	global $iworks_5o5_options;
	$iworks_5o5_options->deactivate();
}

$iworks_5o5 = new iworks_5o5();

/**
 * install & uninstall
 */
register_activation_hook( __FILE__, 'iworks_5o5_activate' );
register_deactivation_hook( __FILE__, 'iworks_5o5_deactivate' );
