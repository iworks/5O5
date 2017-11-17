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
	private $custom_field_year = 'year';
	private $builder_post_type_object = null;

	public function __construct() {
		parent::__construct();
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 10, 2 );
		/**
		 * fields
		 */
		$this->fields = array(
			'basic' => array(
				'date' => array(
					'type' => 'date',
					'label' => __( 'Event date', '5o5' ),
					'args' => array(
						'class' => array( 'medium-text' ),
						'default' => date_i18n( 'Y-m-d', time() ),
					),
				),
				'builder' => array(
					'type' => 'select2',
					'label' => __( 'builder', '5o5' ),
					'args' => array(
						'data-source' => 'builder',
						'data-nonce-action' => 'get-builders-list',
					),
				),
				'description' => array(
					'label' => __( 'Boat description', '5o5' ),
				),
				'type' => array(
					'type' => 'radio',
					'args' => array(
						'options' => array(
							'income' => array(
								'label' => __( 'Income', '5o5' ),
							),
							'expense' => array(
								'label' => __( 'Expense', '5o5' ),
							),
							'salary' => array(
								'label' => __( 'Salary', '5o5' ),
							),
							'asset' => array(
								'label' => __( 'Asset', '5o5' ),
							),
							'insurance' => array(
								'label' => __( 'Insurance', '5o5' ),
							),
						),
						'default' => 'expense',
					),
					'label' => __( 'Type', '5o5' ),
				),
			),
			'income' => array(
				'description' => array(
					'type' => 'description',
					'args' => array(
						'value' => __( 'Please first choose boat type.', '5o5' ),
						'class' => 'description',
					),
				),
				'sale' => array(
					'type' => 'money',
					'label' => __( 'Value of goods and services sold', '5o5' ),
				),
				'other' => array(
					'type' => 'money',
					'label' => __( 'Other income', '5o5' ),
				),
				'vat' => array(
					'type' => 'money',
					'label' => __( 'VAT', '5o5' ),
				),
				'vat_type' => array(
					'type' => 'radio',
					'args' => array(
						'options' => array(
							'c01' => array(
								'label' => __( '1. Dostawa towarów oraz świadczenie usług na terytorium kraju, zwolnione od podatku', '5o5' ),
							),
							'c06' => array(
								'label' => __( '6. Dostawa towarów oraz świadczenie usług na terytorium kraju, opodatkowane stawką 22% albo 23%', '5o5' ),
							),
						),
						'default' => 'c06',
					),
				),
			),
			'expense' => array(
				'description' => array(
					'type' => 'description',
					'args' => array(
						'value' => __( 'Please first choose boat type.', '5o5' ),
						'class' => 'description',
					),
				),
				'purchase' => array(
					'type' => 'money',
					'label' => __( 'The purchase of commercial goods and materials, according to the purchase price', '5o5' ),
				),
				'cost_of_purchase' => array(
					'type' => 'money',
					'label' => __( 'Incidental costs of purchase', '5o5' ),
				),
				'other' => array(
					'type' => 'money',
					'label' => __( 'Other expenses', '5o5' ),
				),
				'vat' => array(
					'type' => 'money',
					'label' => __( 'VAT', '5o5' ),
				),
				'car' => array(
					'type' => 'checkbox',
					'label' => __( 'Car related', '5o5' ),
					'description' => __( 'It will be calculated as half VAT return.', '5o5' ),
					'type' => 'radio',
					'args' => array(
						'options' => array(
							'yes' => array(
								'label' => __( 'Yes', '5o5' ),
							),
							'no' => array(
								'label' => __( 'No', '5o5' ),
							),
						),
						'default' => 'no',
					),
				),
			),
			'salary' => array(
				'salary' => array(
					'type' => 'money',
					'label' => __( 'Salary in cash and in kind', '5o5' ),
				),
			),
			'asset' => array(
				'depreciation' => array(
					'type' => 'money',
					'label' => __( 'Depreciation of asset', '5o5' ),
				),
			),
			'insurance' => array(
				'zus51' => array(
					'type' => 'money',
					'label' => __( 'ZUS 51', '5o5' ),
				),
				'zus52' => array(
					'type' => 'money',
					'label' => __( 'ZUS 52', '5o5' ),
				),
				'zus53' => array(
					'type' => 'money',
					'label' => __( 'ZUS 53', '5o5' ),
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
			'supports'              => array( 'title' ),
			'taxonomies'            => array(),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'capability_type'       => 'page',
			'menu_icon'             => plugins_url( '/assets/images/505_logo.svg', $this->base ),
			'register_meta_box_cb'  => array( $this, 'register_meta_boxes' ),
		);
		register_post_type( $this->post_type_name, $args );
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
			return __( 'Enter boat number', '5o5' );
		}
		return $title;
	}

	public function register_meta_boxes( $post ) {
		add_meta_box( 'basic', __( 'Basic Data', '5o5' ), array( $this, 'basic' ), $this->post_type_name );
		add_meta_box( 'income', __( 'Incomes', '5o5' ), array( $this, 'income' ), $this->post_type_name );
		add_meta_box( 'expense', __( 'Expenses (costs)', '5o5' ), array( $this, 'expense' ), $this->post_type_name );
		add_meta_box( 'salary', __( 'Salaries', '5o5' ), array( $this, 'salary' ), $this->post_type_name );
		add_meta_box( 'asset', __( 'Assets', '5o5' ), array( $this, 'asset' ), $this->post_type_name );
		add_meta_box( 'insurance', __( 'Insurances (ZUS)', '5o5' ), array( $this, 'insurance' ), $this->post_type_name );
	}

	public function basic( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function income( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function expense( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function salary( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function asset( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function insurance( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function save_year_month_to_extra_field( $post_id, $option_name, $value, $key, $data ) {
		if ( 'date' == $key ) {
			$name = $this->get_custom_field_year_month_name();
			$value = date( 'Y-m', $value );
			$result = add_post_meta( $post_id, $name, $value, true );
			if ( ! $result ) {
				update_post_meta( $post_id, $name, $value );
			}
		}
	}

	public function close_meta_boxes( $classes ) {
		$classes[] = 'closed';
		return $classes;
	}

	public function month_table( $month ) {
		$args = array(
			'post_type' => $this->get_name(),
			'meta_value' => $month,
			'meta_key' => $this->get_custom_field_year_month_name(),
			'nopaging' => true,
			'fields' => 'ids',
			'post_status' => array( 'published' ),
		);
		$the_query = new WP_Query( $args );

		$data = array(
			'income' => 0,
			'expense' => 0,
			'expense_vat' => 0,
			'vat_income' => 0,
			'vat_expense' => 0,
			'vat_zero' => 0,
			'salary' => 0,
			'asset' => 0,
		);

		foreach ( $the_query->posts as $post_id ) {
			/**
		 * check is car related cost
		 */
			$is_car_related = get_post_meta( $post_id, $this->options->get_option_name( 'expense_car' ), true );
			$is_car_related = 'yes' == $is_car_related;

			$data['income'] += $this->add_value( $post_id, 'income_sale' );
			$data['income'] += $this->add_value( $post_id, 'income_other' );
			$data['vat_income'] += $this->add_value( $post_id, 'income_vat' );

			$expense = 0;
			$expense += $this->add_value( $post_id, 'expense_purchase' );
			$expense += $this->add_value( $post_id, 'expense_cost_of_purchase' );
			$expense += $this->add_value( $post_id, 'expense_other' );
			$data['expense'] += $expense;

			$salary = 0;
			$salary += $this->add_value( $post_id, 'salary_salary' );
			$data['salary'] += $salary;
			$data['expense'] += $salary;

			$asset = 0;
			$asset += $this->add_value( $post_id, 'asset_depreciation' );
			$data['asset'] += $asset;
			$data['expense'] += $asset;

			$vat_expense = $this->add_value( $post_id, 'expense_vat' );
			if ( $vat_expense ) {
				if ( $is_car_related ) {
					$vat_expense /= 2;
					$data['vat_expense'] += $vat_expense;
					$data['expense_vat'] += $expense + $vat_expense;
					$data['expense'] += $vat_expense;
				} else {
					$data['vat_expense'] += $vat_expense;
					$data['expense_vat'] += $expense;
				}
			} else {
				$data['vat_zero'] += $expense;
			}
		}

		$labels = array(
			'income' => __( 'Incomes', '5o5' ),
			'expense' => __( 'Expenses', '5o5' ),
			'expense_vat' => __( 'Expenses (VAT)', '5o5' ),
			'vat_income' => __( 'VAT (Income) ', '5o5' ),
			'vat_expense' => __( 'VAT (Expense)', '5o5' ),
			'vat_zero' => __( 'VAT (zero)', '5o5' ),
			'salary' => __( 'Salaries', '5o5' ),
			'asset' => __( 'Depreciation of assets', '5o5' ),
		);
		echo '<table class="striped">';
		echo '<tbody>';
		foreach ( $labels as $key => $label ) {
			echo '<tr>';
			printf( '<td>%s</td>', $label );
			printf( '<td class="textright">%0.2f</td>', $data[ $key ] / 100 );
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}

	private function add_value( $post_id, $meta_name ) {
		$value = 0;
		$v = get_post_meta( $post_id, $this->options->get_option_name( $meta_name ), true );
		if ( is_array( $v ) ) {
			if ( isset( $v['integer'] ) ) {
				$value += 100 * $v['integer'];
			}
			if ( isset( $v['fractional'] ) ) {
				$value += $v['fractional'];
			}
		}
		return $value;
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

			case 'expense':
				$expense = 0;
				$expense += $this->add_value( $post_id, 'expense_purchase' );
				$expense += $this->add_value( $post_id, 'expense_cost_of_purchase' );
				$expense += $this->add_value( $post_id, 'expense_other' );
				$expense += $this->add_value( $post_id, 'expense_vat' );
				$expense += $this->add_value( $post_id, 'salary_salary' );
				if ( 0 == $expense ) {
					echo '&nbsp;';
				} else {
					printf( '%0.2f', $expense / 100 );
				}
			break;

			case 'income':
				$income = 0;
				$income += $this->add_value( $post_id, 'income_sale' );
				$income += $this->add_value( $post_id, 'income_other' );
				$income += $this->add_value( $post_id, 'income_vat' );
				if ( 0 == $income ) {
					echo '&nbsp;';
				} else {
					printf( '%0.2f', $income / 100 );
				}
			break;

			case 'date_of_boat':
				$timestamp = get_post_meta( $post_id, $this->get_custom_field_basic_date_name(), true );
				if ( empty( $timestamp ) ) {
					echo '-';
				} else {
					echo date_i18n( get_option( 'date_format' ), $timestamp );
				}
			break;

			case 'description':
				echo get_post_meta( $post_id, $this->options->get_option_name( 'basic_description' ), true );
			break;

			case 'symbol':
				$is_car_related = get_post_meta( $post_id, $this->options->get_option_name( 'expense_car' ), true );
				$is_car_related = 'yes' == $is_car_related;
				if ( $is_car_related ) {
					echo '<span class="dashicons dashicons-admin-generic"></span>';
				} else {
					echo '&nbsp;';
				}
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
		$columns['builder'] = __( 'builder', '5o5' );
		$columns['date_of_boat'] = __( 'Date', '5o5' );
		$columns['symbol'] = '<span class="dashicons dashicons-admin-generic"></span>';
		$columns['description'] = __( 'Description', '5o5' );
		$columns['expense'] = __( 'Expense', '5o5' );
		$columns['income'] = __( 'Income', '5o5' );
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
			$query->set( 'orderby', 'meta_value_num' );
			if ( isset( $_REQUEST['builder'] ) && $_REQUEST['builder'] ) {
				$query->set(
					'meta_query',
					array(
						'relation' => 'AND',
						array(
							'key' => $this->get_custom_field_basic_date_name(),
							'compare' => 'EXISTS',
						),
						array(
							'key' => $this->options->get_option_name( 'basic_builder' ),
							'value' => intval( $_REQUEST['builder'] ),
						),
					)
				);
			} else {
				$query->set( 'meta_key', $this->get_custom_field_basic_date_name() );
			}
		}
		return $query;
	}

	/**
	 * Get "basic_date" custom filed name.
	 *
	 * @since 1.0.0
	 *
	 * @return string Custom Field meta_key.
	 */
	public function get_custom_field_basic_date_name() {
		return $this->options->get_option_name( 'basic_date' );
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

