<?php
/**
 * Plugin Name:       Category Filter Block (Forked)
 * Description:       A forked version with enhanced link display. Original by Misha Rudrastyh.
 * Version:           1.0.1
 * Requires at least: 6.5
 * Requires PHP:      7.0
 * Author:            Qara Yahya
 * Author URI:        https://github.com/qarayahya
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       category-filter-block-fork
 *
 * @package           category-filter-block-fork
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if( ! class_exists( 'Rudr_Category_Filter_Block' ) ) {

	class Rudr_Category_Filter_Block{

		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );


			add_filter( 'pre_get_posts', array( $this, 'filter_main_query' ) );
			add_filter( 'query_loop_block_query_vars', array( $this, 'filter_secondary_queries' ), 999, 3 );
			add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 4 );

		}

		public function init() {
			register_block_type_from_metadata( __DIR__ . '/build' );

			load_plugin_textdomain( 'category-filter-block-fork', false, plugin_basename( __DIR__ ) . '/languages' );

			wp_set_script_translations( 'rudr-category-filter-editor-script', 'category-filter-block-fork', plugin_dir_path( __FILE__ ) . '/languages/' );
		}


		public function filter_main_query( $query ) {
			// do nothing if it is not a main query
			if( ! $query->is_main_query() ) {
				return;
			}

			$tax_query = $this->prepare_tax_query( $query->get( 'tax_query' ) );

			$query->set( 'tax_query', $tax_query );

		}

		public function filter_secondary_queries( $query, $block, $page ) {

			// get current tax query
			$query_tax_query = ! empty( $query[ 'tax_query' ] ) ? $query[ 'tax_query' ] : array();
			// get currrent query ID from the block context, can be 0
			$query_id = isset( $block->context[ 'queryId' ] ) && $block->context[ 'queryId' ] ? $block->context[ 'queryId' ] : 0;

			if( $tax_query = $this->prepare_tax_query( $query_tax_query, $query_id ) ) {
				$query[ 'tax_query' ] = $tax_query;
			}

			return $query;

		}

		private function prepare_tax_query( $query_tax_query, $query_id = null ) {

			$tax_query = array();

			$key = isset( $query_id ) ? "filter-{$query_id}-category" : "filter-category";

			$selected_categories = ! empty( $_REQUEST[ $key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) : null;
			if( ! $selected_categories ) {
				return $query_tax_query;
			}

			$terms = ( false !== strpos( $selected_categories, ',' ) ) ? array_map( 'trim', explode( ',', $selected_categories ) ) : array( $selected_categories );

			$tax_query[] = array(
				'taxonomy' => 'category',
				'field' => 'slug',
				'terms' => $terms,
			);

			if( ! empty( $tax_query ) && ! empty( $query_tax_query ) ) {
				$tax_query = array( 'relation' => 'AND', $query_tax_query, $tax_query );
			}

			return $tax_query;

		}

		public function row_meta( $links, $plugin_file_name, $plugin_data, $status ){

			if( strpos( $plugin_file_name, basename(__FILE__) ) ) {
				$links[] = '<a href="https://rudrastyh.com/plugins/taxonomy-filter-block">Upgrade to Pro</a>';
			}

			return $links;
		}
	}

	new Rudr_Category_Filter_Block;

}
