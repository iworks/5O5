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
	/**
	 * sailors to id
	 */
	private $sailors = array();

	public function __construct() {
		parent::__construct();
		add_filter( 'the_content', array( $this, 'the_content' ), 10, 2 );
		/**
		 * change default columns
		 */
		add_filter( "manage_{$this->get_name()}_posts_columns", array( $this, 'add_columns' ) );
		add_action( 'manage_posts_custom_column' , array( $this, 'custom_columns' ), 10, 2 );
		/**
		 * fields
		 */
		$this->fields = array(
			'result' => array(
				'location' => array( 'label' => __( 'Location', '5o5' ) ),
				'date_start' => array( 'type' => 'date', 'label' => __( 'Event start', '5o5' ) ),
				'date_end' => array( 'type' => 'date', 'label' => __( 'Event end', '5o5' ) ),
				'number_of_races' => array( 'type' => 'number', 'label' => __( 'Number of races', '5o5' ) ),
				'number_of_competitors' => array( 'type' => 'number', 'label' => __( 'Number of competitors', '5o5' ) ),
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
		 * handle results
		 */
		add_action( 'wp_ajax_iworks_5o5_upload_races', array( $this, 'upload' ) );
		/**
		 * content filters
		 */
        add_filter( 'iworks_5o5_result_sailor_regata_list', array( $this, 'regatta_list_by_sailor_id' ), 10, 2 );
        add_filter( 'the_title', array( $this, 'add_year_to_title' ), 10, 2 );
	}

    public function add_year_to_title( $title, $post_id ) {
        $post_type = get_post_type( $post_id );
        if ( $post_type != $this->post_type_name ) {
            return $title;
        }
        if ( is_admin() ) {
            $screen = get_current_screen();
            l($screen);


            l($post_type);
        } else {
            $start = $this->options->get_option_name( 'result_date_start' );
            $start = get_post_meta( $post_id, $start, true );
            $year = date( 'Y', $start );
            if ( ! empty( $year ) ) {
                return sprintf( '%d - %s', $year, $title );
            }
        }

        return $title;
    }

	private function get_list_by_sailor_id( $sailor_id ) {
		global $wpdb;
		$table_name_regatta = $wpdb->prefix . '505_regatta';
		$sql = $wpdb->prepare(
			"select * from {$table_name_regatta} where helm_id = %d or crew_id = %d order by date, year desc",
			$sailor_id,
			$sailor_id
		);
		return $wpdb->get_results( $sql );
	}

	public function regatta_list_by_sailor_id( $content, $sailor_id ) {
		if ( empty( $content ) ) {
			$content = __( 'There is no register regatta for this sailor.', '5o5' );
		}
		$regattas = $this->get_list_by_sailor_id( $sailor_id );
		if ( ! empty( $regattas ) ) {
			$content = '<table><thead><tr>';
			$content .= sprintf( '<th class="year">%s</th>', esc_html__( 'Year', '5o5' ) );
			$content .= sprintf( '<th class="name">%s</th>', esc_html__( 'Name', '5o5' ) );
			$content .= sprintf( '<th class="helmsman">%s</th>', esc_html__( 'Helmsman', '5o5' ) );
			$content .= sprintf( '<th class="crew">%s</th>', esc_html__( 'Crew', '5o5' ) );
			$content .= sprintf( '<th class="place">%s</th>', esc_html__( 'Place', '5o5' ) );
			$content .= sprintf( '<th class="points">%s</th>', esc_html__( 'Points', '5o5' ) );
			$content .= '</tr></thead><tbody>';
			foreach ( $regattas as $regatta ) {
				$content .= '<tr>';
				$content .= sprintf( '<td class="year">%d</td>', $regatta->year );
				$content .= sprintf( '<td class="name"><a href="%s">%s</a></td>', get_permalink( $regatta->post_regata_id ), get_the_title( $regatta->post_regata_id ) );
				/**
				 * Helmsman
				 */
				if ( $regatta->helm_id ) {
					$content .= sprintf( '<td class="helmsman"><a href="%s">%s</a></td>', get_permalink( $regatta->helm_id ), get_the_title( $regatta->helm_id ) );
				} else {
					$content .= sprintf( '<td class="helmsman">%s</td>', $regatta->helm_name );
				}
				/**
				 * crew
				 */
				if ( $regatta->crew_id ) {
					$content .= sprintf( '<td class="crew"><a href="%s">%s</a></td>', get_permalink( $regatta->crew_id ), get_the_title( $regatta->crew_id ) );
				} else {
					$content .= sprintf( '<td class="crew">%s</td>', $regatta->crew_name );
				}
				$content .= sprintf( '<td class="place">%d</td>', $regatta->place );
				$content .= sprintf( '<td class="points">%d</td>', $regatta->points );
				$content .= '</tr>';
			}
			$content .= '</tbody></table>';

		}

		$content = sprintf(
			'<div class="iworks-5o5-regatta-list"><h2>%s</h2>%s</div>',
			esc_html__( 'Regatta list', '5o5' ),
			$content
		);

		return $content;
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
	}

	public function register_meta_boxes( $post ) {
		add_meta_box( 'result', __( 'Result data', '5o5' ), array( $this, 'result' ), $this->post_type_name );
		add_meta_box( 'race', __( 'Races data', '5o5' ), array( $this, 'races' ), $this->post_type_name );
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

	public function races( $post ) {
		echo '<input type="file" name="file" id="file_5o5_races"/>';
		wp_nonce_field( 'upload-races', __CLASS__ );
		echo '<button>import</button>';
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

	public function upload() {
		if ( ! isset( $_POST['id'] ) ) {
			wp_send_json_error();
		}
		if ( ! isset( $_POST['_wpnonce'] ) ) {
			wp_send_json_error();
		}
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'upload-races' ) ) {
			wp_send_json_error();
		}
		if ( empty( $_FILES ) || ! isset( $_FILES['file'] ) ) {
			wp_send_json_error();
		}
		$file = $_FILES['file'];
		if ( 'text/csv' != $file['type'] ) {
			wp_send_json_error();
		}
		$row = 1;
		$data = array();
		if ( ($handle = fopen( $file['tmp_name'], 'r' )) !== false ) {
			while ( ($d = fgetcsv( $handle, 1000, ',' )) !== false ) {
				$data[] = $d;
			}
			fclose( $handle );
		}
		if ( empty( $data ) ) {
			wp_send_json_error();
		}
		global $wpdb, $iworks_5o5;
		$table_name_regatta = $wpdb->prefix . '505_regatta';
		$table_name_regatta_race = $wpdb->prefix . '505_regatta_race';
		array_shift( $data );
		$sailors = $iworks_5o5->get_list_by_post_type( 'person' );
		$wpdb->delete( $table_name_regatta, array( 'post_regata_id' => $_POST['id'] ), array( '%d' ) );
		$wpdb->delete( $table_name_regatta_race, array( 'post_regata_id' => $_POST['id'] ), array( '%d' ) );
		$year = date( 'Y', get_post_meta( $_POST['id'], 'iworks_5o5_result_date_end', true ) );
		foreach ( $data as $row ) {
			$boat = array_shift( $row );
			$boat_id = intval( preg_replace( '/[^\d]+/', '', $boat ) );
			$country = preg_replace( '/[^a-zA-Z]+/', '', $boat );
			$helm = trim( array_shift( $row ) );
			$crew = trim( array_shift( $row ) );
			$club = trim( array_shift( $row ) );
			$place = intval( array_pop( $row ) );
			$points = intval( array_pop( $row ) );
			$regatta = array(
				'year' => $year,
				'post_regata_id' => $_POST['id'],
				'boat_id' => $boat_id,
				'country' => $country,
				'helm_id' => isset( $sailors[ $helm ] )? intval( $sailors[ $helm ] ):0,
				'helm_name' => $helm,
				'crew_id' => isset( $sailors[ $crew ] )? intval( $sailors[ $crew ] ):0,
				'crew_name' => $crew,
				'place' => $place,
				'points' => $points,
			);
			$wpdb->insert( $table_name_regatta, $regatta );
			$regatta_id = $wpdb->insert_id;
			if ( empty( $row ) ) {
				continue;
			}
			$races = array();
			foreach ( $row as $one ) {
				$races[] = $one;
			}
			$number = 1;
			foreach ( $races as $one ) {
				$race = array(
					'post_regata_id' => $_POST['id'],
					'regata_id' => $regatta_id,
					'number' => $number++,
				);
				if ( preg_match( '/\*/', $one ) ) {
					$race['discard'] = true;
				}
				$one = preg_replace( '/\*/', '', $one );
				if ( preg_match( '/^[\s]+$/', $one ) ) {
					$race['code'] = $one;
				}
				$race['points'] = $one;
				$wpdb->insert( $table_name_regatta_race, $race );
			}
		}
		wp_send_json_success();
	}

	public function the_content( $content ) {
		if ( ! is_singular() ) {
			return $content;
		}
		$post_type = get_post_type();
		if ( $post_type != $this->post_type_name ) {
			return $content;
		}
		$post_id = get_the_ID();
		global $wpdb, $iworks_5o5;
		$table_name_regatta = $wpdb->prefix . '505_regatta';
		$table_name_regatta_race = $wpdb->prefix . '505_regatta_race';

		/**
		 * get regata data
		 */
		$query = $wpdb->prepare( "SELECT * FROM {$table_name_regatta} where post_regata_id = %d order by place", $post_id );
		$regatta = $wpdb->get_results( $query );
		/**
		 * get regata races data
		 */
		$query = $wpdb->prepare( "SELECT * FROM {$table_name_regatta_race} where post_regata_id = %d order by regata_id, number", $post_id );
		$r = $wpdb->get_results( $query );

		$races = array();
		foreach ( $r as $one ) {
			if ( ! isset( $races[ $one->regata_id ] ) ) {
				$races[ $one->regata_id ] = array();
			}
			$races[ $one->regata_id ][ $one->number ] = $one->points;
			if ( $one->discard ) {
				$races[ $one->regata_id ][ $one->number ] .= '*';
			}
		}
		$content .= '<table>';
		$content .= '<thead>';
		$content .= '<tr>';
		$content .= sprintf( '<td class="place">%s</td>', esc_html__( 'Place', '5o5' ) );
		$content .= sprintf( '<td class="boat">%s</td>', esc_html__( 'Boat', '5o5' ) );
		$content .= sprintf( '<td class="helm">%s</td>', esc_html__( 'Helm', '5o5' ) );
		$content .= sprintf( '<td class="crew">%s</td>', esc_html__( 'Crew', '5o5' ) );
		$number = intval( get_post_meta( $post_id, 'iworks_5o5_result_number_of_races', true ) );
		for ( $i = 1; $i <= $number; $i++ ) {
			$content .= sprintf( '<td class="race race-%d">%d</td>', $i, $i );
		}
		$content .= sprintf( '<td class="sum">%s</td>', esc_html__( 'Sum', '5o5' ) );
		$content .= '</tr>';
		$content .= '</thead>';
		$content .= '<tbody>';
		foreach ( $regatta as $one ) {
			$content .= '<tr>';
			$content .= sprintf( '<td class="place">%d</td>', $one->place );
			$content .= sprintf(
				'<td class="boat_id country-%s">%s %d</td>',
				esc_attr( strtolower( $one->country ) ),
				$one->country,
				$one->boat_id
			);
			/**
			 * helmsman
			 */
			if ( ! empty( $one->helm_id ) ) {
				$content .= sprintf( '<td class="helm_name"><a href="%s">%s</a></td>', get_permalink( $one->helm_id ), $one->helm_name );
			} else {
				$content .= sprintf( '<td class="helm_name">%s</td>', $one->helm_name );
			}
			/**
			 * crew
			 */
			if ( ! empty( $one->crew_id ) ) {
				$content .= sprintf( '<td class="crew_name"><a href="%s">%s</a></td>', get_permalink( $one->crew_id ), $one->crew_name );
			} else {
				$content .= sprintf( '<td class="crew_name">%s</td>', $one->crew_name );
			}
			if ( isset( $races[ $one->ID ] ) && ! empty( $races[ $one->ID ] ) ) {
				foreach ( $races[ $one->ID ] as $race_number => $race_points ) {
					$class = preg_match( '/\*/', $race_points )? 'race-discard':'';
					$content .= sprintf(
						'<td class="race race-%d %s">%s</td>',
						esc_attr( $race_number ),
						esc_attr( $class ),
						esc_html( $race_points )
					);
				}
			}
			$content .= sprintf( '<td class="points">%d</td>', $one->points );
			$content .= '</tr>';
		}
		$content .= '<tbody>';
		$content .= '</table>';
		return $content;
	}
}

