<?php 
/**
 * Quiz reports data table
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include WP List Table
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class EXMS_Taxonomies
 */
class EXMS_Taxonomies extends WP_List_Table {

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @abstract
	 */
	public function prepare_items() {

		$url_order_by = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
		$url_order = isset( $_GET['order'] ) ? $_GET['order'] : '';
		$exms_search_term = isset( $_POST['s'] ) ? $_POST['s'] : '';
		$datas = $this->exms_taxonomy_table_data( $url_order_by, $url_order, $exms_search_term );

		$per_page = 10;
		$current_page = $this->get_pagenum();

		if( NULL != $datas ) {
			$total_items = count( $datas );

			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			) );

			$this->items = array_slice( $datas, ( ( $current_page - 1 ) * $per_page ), $per_page );
		}

		$exms_columns = $this->get_columns();
		$exms_hidden = $this->get_hidden_columns();
		$exms_sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $exms_columns, $exms_hidden, $exms_sortable );
	}

	/**
	 * Display columns datas
	 *
	 * @param $url_order_by, $url_order, $exms_search_term
	 * @return Array
	 */
	public function exms_taxonomy_table_data( $url_order_by = '', $url_order = '', $exms_search_term = '' ) {

		$data_array = [];

		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
		$taxonomies = isset( $_GET['taxo_type'] ) ? $_GET['taxo_type'] : 'exms-quizzes_' .$tab;
		/**
		 * Add bulk action html
		 */
		?>
		<select class="exms-select-taxonomy-id">
			<option value=""><?php echo __( 'Bulk action', 'exms' ); ?></option>
			<option value="exms-delete"><?php echo __( 'Delete', 'exms' ); ?></option>
		</select>
		<button type="button" class="button-secondary exms-bulk-btn"><?php echo __( 'Apply', 'exms' ); ?></button>

		<!-- Add filtration html -->
		<span class="exms-category-filter">
			<form method="post">
				<input type="text" name="exms_filter_search_field" class="exms-search-box" placeholder="<?php echo __( 'Enter ', 'exms' ) . ucwords( $tab ); ?>">
				<?php wp_nonce_field( 'exms_s_taxonomy_nonce', 'exms_s_taxonomy_nonce_field' ); ?>
				<button type="submit" name="exms_search_taxonomy_submit" class="button-secondary"><?php echo __( 'Search ', 'exms' ) . ucwords( $tab ); ?></button>
			</form>
		</span>

		<?php

		// $taxonomies
		$search_field = isset($_POST['exms_filter_search_field']) ? sanitize_text_field($_POST['exms_filter_search_field']) : '';

		$args = [
			'hide_empty' => false,
		];

		if (!empty($search_field)) {
			$args['name__like'] = $search_field;
		}

		$cat_terms = get_terms($taxonomies, $args);

		$data_array = [];

		if (!is_wp_error($cat_terms) && !empty($cat_terms)) {
			foreach ($cat_terms as $cat_term) {
				if ($cat_term->parent == 0) {
					$data_array[] = [
						'name' => '<span data-parent-id="' . esc_attr($cat_term->parent) . '" data-taxo-name="' . esc_attr($cat_term->name) . '" data-taxonomy="' . esc_attr($taxonomies) . '" data-texo-id="' . esc_attr($cat_term->term_id) . '" class="exms-taxonomy-id"><a title="' . esc_attr__('Edit Taxonomy', 'exms-exams') . '" href="#TB_inline?&width=400&height=330&inlineId=exms_taxonomy_thickbox" class="thickbox exms-edit-taxonomy">' . esc_html($cat_term->name) . '</a></span>',
						'description' => esc_html($cat_term->description),
						'slug' => esc_html($cat_term->slug),
						'count' => $cat_term->count,
					];

					foreach ($cat_terms as $subcat) {
						if ($subcat->parent == $cat_term->term_id) {
							$data_array[] = [
								'name' => '<span data-parent-id="' . esc_attr($subcat->parent) . '" data-taxo-name="' . esc_attr($subcat->name) . '" data-taxonomy="' . esc_attr($taxonomies) . '" data-texo-id="' . esc_attr($subcat->term_id) . '" class="exms-taxonomy-id"><a title="' . esc_attr__('Edit Taxonomy', 'exms-exams') . '" href="#TB_inline?&width=400&height=330&inlineId=exms_taxonomy_thickbox" class="thickbox exms-edit-taxonomy"> — ' . esc_html($subcat->name) . '</a></span>',
								'description' => esc_html($subcat->description),
								'slug' => esc_html($subcat->slug),
								'count' => $subcat->count,
							];
						}
					}
				}
			}
		}
		return $data_array;
	}

	/**
	 * Gets a list of all, hidden and sortable columns
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Gets a list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'cb'       		=> '<input class="exms-select-taxonomy" type="checkbox" id="image_%3$s" name="post[%3$s][]" value="%2$s" />',
			'name'			=> __( 'Name', 'exms' ),
			'description'	=> __( 'Description', 'exms' ),
			'slug'			=> __( 'Slug', 'exms' ),
			'count'			=> __( 'Count', 'exms' ),
		];
		return $columns;
	}

	/**
	 * Checkbox column markup.
	 *
	 * @since BuddyPress 1.6.0
	 *
	 * @see WP_List_Table::single_row_columns()
	 *
	 * @param array $item A singular item (one full row).
	 */
	function column_cb( $item ) {
		
		$top_checkbox = '<input class="exms-select-taxonomy" type="checkbox" id="image_%3$s" name="post[%3$s][]" value="%2$s" />';
		return $top_checkbox;
	}

	/**
	 * Return column value
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'name':
			case 'description':
			case 'slug':
			case 'count':
			return $item[$column_name];
			default:
			return 'no lists found';
		}
	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {

		$taxonomies = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
		_e( 'No '.$taxonomies.' found', 'exms' );
	}

	public function handle_row_actions( $link, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}

		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
		$taxonomies = isset( $_GET['taxo_type'] ) ? $_GET['taxo_type'] : 'exms_quizzes_' .$tab;

		$actions           = [];
		$actions['edit']   = '<a href="#TB_inline?&width=400&height=330&inlineId=exms_taxonomy_thickbox" class="thickbox exms-edit-taxonomy"> '.__( 'Edit', 'exms' ). '</a>';
		$actions['delete'] = '<a class="exms-delete-taxonomy">' . __( 'Delete' ) . '</a>';
		$actions['quick-edit'] = '<a data-assets-url="'.EXMS_ASSETS_URL.'" class="exms-quick-edit-taxonomy">' . __( 'Quick Edit' ) . '</a>';

		return $this->row_actions( $actions );
	}

	/**
	 * Gets a list of sortable columns.
	 * @since 3.1.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		$s_columns = array (
	         'name' => [ 'Name', true], 
	         'description' => [ 'Description', true],
	         'slug' => [ 'Slug', true],
	         'count' => [ 'Count', true]
	    );
	    return $s_columns;
	}
}

/**
 * WP_list_table instance
 */
function exms_taxonomies_data_table() {

	$exms_taxonomy_table = new EXMS_Taxonomies();
	$exms_taxonomy_table->prepare_items();
	$exms_taxonomy_table->display();
}
exms_taxonomies_data_table();