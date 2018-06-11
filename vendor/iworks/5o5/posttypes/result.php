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

if ( class_exists( 'iworks_5o5_posttypes_result' ) ) {
	return;
}

require_once( dirname( dirname( __FILE__ ) ) . '/posttypes.php' );

class iworks_5o5_posttypes_result extends iworks_5o5_posttypes {

	protected $post_type_name = 'iworks_5o5_result';
	/**
	 * Sinle crew meta field name
	 */
	private $single_crew_field_name = 'iworks_5o5_result_crew';
	/**
	 * Sinle result meta field name
	 */
	private $single_result_field_name = 'iworks_5o5_result_result';

	public function __construct() {
		parent::__construct();
		add_filter( 'the_content', array( $this, 'the_content' ), 10, 2 );
		/**
		 * change default columns
		 */
		add_filter( "manage_{$this->get_name()}_posts_columns", array( $this, 'add_columns' ) );
        add_action( 'manage_posts_custom_column' , array( $this, 'custom_columns' ), 10, 2 );
        /**
         * replace names to proper
         */
		if ( is_a( $this->options, 'iworks_options' ) ) {
			/**
			 * Sinle crew meta field name
			 */
			$this->single_crew_field_name = $this->options->get_option_name( 'result_crew' );
		}
		/**
		 * fields
		 */
		$this->fields = array(
            'result' => array(
                'location' => array( 'label' => __( 'Location', '5o5' ) ),
				'date_start' => array( 'type' => 'date', 'label' => __( 'Event start', '5o5' ) ),
                'date_end' => array( 'type' => 'date', 'label' => __( 'Event end', '5o5' ) ),
                'number_of_races' => array( 'type' => 'number', 'label' => __( 'Number of races', '5o5' ), ),
                'number_of_competitors' => array( 'type' => 'number', 'label' => __( 'Number of competitors', '5o5' ), ),
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
		global $iworks_5o5;
		$show_in_menu = add_query_arg( 'post_type', $iworks_5o5->get_post_type_name( 'person' ), 'edit.php' );
		$labels = array(
			'name'                  => _x( 'Results', 'Result General Name', '5o5' ),
			'singular_name'         => _x( 'Result', 'Result Singular Name', '5o5' ),
			'menu_name'             => __( '5O5', '5o5' ),
			'name_admin_bar'        => __( 'Result', '5o5' ),
			'archives'              => __( 'Results', '5o5' ),
			'attributes'            => __( 'Item Attributes', '5o5' ),
			'all_items'             => __( 'Results', '5o5' ),
			'add_new_item'          => __( 'Add New Result', '5o5' ),
			'add_new'               => __( 'Add New', '5o5' ),
			'new_item'              => __( 'New Result', '5o5' ),
			'edit_item'             => __( 'Edit Result', '5o5' ),
			'update_item'           => __( 'Update Result', '5o5' ),
			'view_item'             => __( 'View Result', '5o5' ),
			'view_items'            => __( 'View Results', '5o5' ),
			'search_items'          => __( 'Search Result', '5o5' ),
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
			'label'                 => __( 'Result', '5o5' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'revision' ),
			'taxonomies'            => array(),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => $show_in_menu,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => _x( '5o5-results', 'slug for archive', '5o5' ),
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'menu_icon'             => plugins_url( '/assets/images/505_logo.svg', $this->base ),
			'register_meta_box_cb'  => array( $this, 'register_meta_boxes' ),
			'rewrite' => array(
				'slug' => _x( 'result', 'slug for single result', '5o5' ),
			),
		);
		register_post_type( $this->post_type_name, $args );
	}

	public function save_post_meta( $post_id, $post, $update ) {
		$result = $this->save_post_meta_fields( $post_id, $post, $update, $this->fields );
		if ( ! $result ) {
			return;
		}
		/**
		 * save crews
		 */
		if ( isset( $_POST[ $this->single_crew_field_name ] ) ) {
			$value = $_POST[ $this->single_crew_field_name ];
			if ( ! isset( $value['crew'] ) ) {
				$value['crew'] = array();
			}
			/**
			 * clear empty data
			 */
			foreach ( $value['crew'] as $key => $data ) {
				if ( isset( $data['helmsman'] ) && ! empty( $data['helmsman'] ) ) {
					continue;
				}
				if ( isset( $data['crew'] ) && ! empty( $data['crew'] ) ) {
					continue;
				}
				unset( $value['crew'][ $key ] );
			}
			/**
			 * prepare to add to persons
			 */
			$before = $this->get_crews_data( $post_id );
			if ( ! isset( $before['crew'] ) ) {
				$before['crew'] = array();
			}
			$added_users = array();
			/**
			 * delete if empty
			 */
			if ( empty( $value['crew'] ) ) {
				delete_post_meta( $post_id, $this->single_crew_field_name );
			} else {
				$result = add_post_meta( $post_id, $this->single_crew_field_name, $value, true );
				if ( ! $result ) {
					update_post_meta( $post_id, $this->single_crew_field_name, $value );
				}
				foreach ( $value['crew'] as $key => $data ) {
					if ( isset( $data['helmsman'] ) && ! empty( $data['helmsman'] ) ) {
						$added_users[] = $data['helmsman'];
					}
					if ( isset( $data['crew'] ) && ! empty( $data['crew'] ) ) {
						$added_users[] = $data['crew'];
					}
				}
			}
			/**
			 * remove users
			 */
			foreach ( $before['crew'] as $key => $data ) {
				foreach ( array( 'helmsman', 'crew' ) as $key ) {
					if ( isset( $data[ $key ] ) && ! empty( $data[ $key ] ) ) {
						$user_post_id = $data[ $key ];
						if ( ! in_array( $user_post_id, $added_users ) ) {
							delete_post_meta( $user_post_id, $this->single_result_field_name, $post_id );
						}
					}
				}
			}
			/**
			 * add result meta to user
			 */
			foreach ( $added_users as $user_post_id ) {
				$assigned_results = get_post_meta( $user_post_id, $this->single_result_field_name );
				if ( ! in_array( $user_post_id, $assigned_results ) ) {
					add_post_meta( $user_post_id, $this->single_result_field_name, $post_id, false );
				}
			}
		}
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
		if ( $post_type != $this->post_type_name ) {
			return $content;
		}
		$post_id = get_the_ID();
		$text = '';
		$options = array(
			'result_build_year' => __( 'Year of building', '5o5' ),
			'manufacturer' => __( 'Hull manufacturer', '5o5' ),
			'result_first_certified_date' => __( 'First certified date', '5o5' ),
			'result_hull_material' => __( 'Hull material', '5o5' ),
			'result_in_poland_date' => __( 'In Poland', '5o5' ),
			'result_name' => __( 'Name', '5o5' ),
			'colors' => __( 'Colors (top/side/bottom)', '5o5' ),
			'sails' => __( 'Sails manufacturer', '5o5' ),
			'mast' => __( 'Mast manufacturer', '5o5' ),
			'result_location' => __( 'Location', '5o5' ),
			'social_website' => __( 'Web site', '5o5' ),
			'social' => __( 'Social Media', '5o5' ),
		);
		$separator = _x( ', ', 'Custom taxonomies separator.', '5o5' );
		$dateformat = get_option( 'date_format' );
		foreach ( $options as $key => $label ) {
			$name = $this->options->get_option_name( $key );
			$value = get_post_meta( $post_id, $name, true );
			if ( empty( $value ) ) {
				switch ( $key ) {
					/**
					 * handle colors
					 */
					case 'colors':
						$colors = array();
						$colors_keys = array( 'top', 'side', 'bottom' );
						foreach ( $colors_keys as $ckey ) {
							$cname = $this->options->get_option_name( 'result_color_'.$ckey );
							$colors[] = get_post_meta( $post_id, $cname, true );
						}
						$colors = array_filter( $colors );
						if ( ! empty( $colors ) ) {
							$value = implode( '/', $colors );
						}
					break;
					case 'manufacturer':
						$value = get_the_term_list(
							$post_id,
							$this->taxonomy_name_manufacturer,
							sprintf( '<span class="%s">', esc_attr( $this->taxonomy_name_manufacturer ) ),
							$separator,
							'</span>'
						);
					break;
					case 'sails':
						$value = get_the_term_list(
							$post_id,
							$this->taxonomy_name_sails,
							sprintf( '<span class="%s">', esc_attr( $this->taxonomy_name_sails ) ),
							$separator,
							'</span>'
						);
					break;
					case 'mast':
						$value = get_the_term_list(
							$post_id,
							$this->taxonomy_name_mast,
							sprintf( '<span class="%s">', esc_attr( $this->taxonomy_name_mast ) ),
							$separator,
							'</span>'
						);
					break;
					case 'social':
						foreach ( $this->fields['social'] as $social_key => $social ) {
							if ( 'website' == $social_key ) {
								continue;
							}
							$name = $this->options->get_option_name( 'social_'.$social_key );
							$social_value = get_post_meta( $post_id, $name, true );
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
					$value = _x( 'unknown', 'value of result', '5o5' );
					$value = '-';
				}
			} else {
				switch ( $key ) {
					/**
					 * handle date
					 */
					case 'result_first_certified_date':
						$value = date_i18n( $dateformat, $value );
						break;
					case 'social_website':
						$value = sprintf( '<a href="%s" class="result-website">%s</a>', esc_url( $value ), esc_html( $value ) );
						break;
				}
			}
			$text .= $this->result_single_row( $key, $label, $value );
		}
		if ( ! empty( $text ) ) {
			$content = sprintf(
				'<h2>%s</h2><table class="result-data">%s</table>%s',
				esc_html__( 'Result details', '5o5' ),
				$text,
				$content
			);
		}

		/**
		 * attach gallery
		 */
		$ids = $this->get_media( $post_id );
		if ( ! empty( $ids ) ) {
			$shortcode = sprintf( '[gallery ids="%s" link="file"]', implode( ',', $ids ) );
			$content .= do_shortcode( $shortcode );
		}
		/**
		 * return content
		 */
		return $content;
	}

	private function result_single_row( $key, $label, $value ) {
		if ( empty( $value ) || '-' == $value ) {
			return '';
		}
		$text = '';
		$text .= sprintf( '<tr class="result-%s">', esc_attr( $key ) );
		$text .= sprintf( '<td>%s</td>', esc_html( $label ) );
		$text .= sprintf( '<td>%s</td>', $value );
		$text .= '</tr>';
		return $text;
	}

	public function register_meta_boxes( $post ) {
		add_meta_box( 'result', __( 'Result data', '5o5' ), array( $this, 'result' ), $this->post_type_name );
	}

	public function print_js_templates() {
?>
<script type="text/html" id="tmpl-iworks-result-crew">
<tr class="iworks-crew-single-row" id="iworks-crew-{{{data.id}}}">
    <td class="iworks-crew-current">
<input type="radio" name="<?php echo $this->single_crew_field_name; ?>[current]" value="{{{data.id}}}" />
    </td>
    <td class="iworks-crew-helmsman">
        <select name="<?php echo $this->single_crew_field_name; ?>[crew][{{{data.id}}}][helmsman]">
            <option value=""><?php esc_html_e( 'Select a helmsman', '5o5' ); ?></option>
        </select>
    </td>
    <td class="iworks-crew-crew">
        <select name="<?php echo $this->single_crew_field_name; ?>[crew][{{{data.id}}}][crew]">
            <option value=""><?php esc_html_e( 'Select a crew', '5o5' ); ?></option>
        </select>
    </td>
    <td>
        <a href="#" class="iworks-crew-single-delete" data-id="{{{data.id}}}"><?php esc_html_e( 'Delete', '5o5' ); ?></a>
    </td>
</tr>
</script>
<?php
	}


	public function result( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	/**
	 * Get custom column values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column Column name,
	 * @param integer $post_id Current post id (Result),
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
							'post_type' => 'iworks_5o5_result',
							),
							admin_url( 'edit.php' )
						),
						get_post_meta( $id, 'iworks_5o5_manufacturer_data_full_name', true )
					);
				}
			break;
			case 'build_year':
				$name = $this->options->get_option_name( 'result_build_year' );
				echo get_post_meta( $post_id, $name, true );
			break;
			case 'location':
				$name = $this->options->get_option_name( 'result_location' );
				echo get_post_meta( $post_id, $name, true );
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
		$columns['location'] = __( 'Location', '5o5' );
		return $columns;
	}

}

