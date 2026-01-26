<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// we need to get a query ID
$page_key = isset( $block->context[ 'queryId' ] ) ? 'query-' . $block->context[ 'queryId' ] . '-page' : 'query-page';
// main or not main query $block->context['query']['inherit']
$is_main_query = isset( $block->context[ 'query' ][ 'inherit' ] ) && $block->context[ 'query' ][ 'inherit' ] ? true : false;
// force page reload
$has_enhanced_pagination = isset( $block->context[ 'enhancedPagination' ] ) && $block->context[ 'enhancedPagination' ] ? true : false;

$classes = array( "rudr-category-filter--{$attributes[ 'filterType' ]}" );
$styles = array();

if( ! empty( $attributes[ 'textAlign' ] ) ) {
	$classes[] = "has-text-align-{$attributes[ 'textAlign' ]}";
}

?>

<div
	<?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => join( ' ', $classes ), 'style' => join( '', $styles ) ) ) ) ?>
	data-wp-interactive="rudr"
>
	<?php

		$key = isset( $block->context[ 'queryId' ] ) && ! $is_main_query ? "filter-{$block->context[ 'queryId' ]}-category" : "filter-category";
		$all_items = ! empty( $attributes[ 'allItemsText' ] ) ? $attributes[ 'allItemsText' ] : __( 'All categories', 'category-filter-block' );

		$args = array(
			'taxonomy' => 'category',
			'hide_empty' => true,
		);
		$terms = get_terms( $args );
		if( $terms ) :
			// currently we have 2 types of the filter, maybe it is going to be more in the future
			switch( $attributes[ 'filterType' ] ) :

			case 'links' :

				?>
					<span>
						<a href="<?php echo esc_url( remove_query_arg( $key ) ) ?>"<?php echo ! isset( $_REQUEST[ $key ] ) ? ' class="rudr-filter-current"' : '' ?> data-wp-on--click="core/query::actions.navigate" data-wp-on-async--mouseenter="core/query::actions.prefetch" data-wp-watch="core/query::callbacks.prefetch"><?php echo esc_html( $all_items ) ?></a>
						<?php foreach( $terms as $term ) :

							$base_url = $is_main_query ? get_pagenum_link( 1, false ) : add_query_arg( array( $page_key => 1 ) );
							?>
							<a href="<?php echo esc_url( add_query_arg( array( $key => $term->slug ), $base_url ) ) ?>"<?php echo isset( $_REQUEST[ $key ] ) && $term->slug === $_REQUEST[ $key ] ? ' class="rudr-filter-current"' : '' ?> data-wp-on--click="core/query::actions.navigate" data-wp-on-async--mouseenter="core/query::actions.prefetch" data-wp-watch="core/query::callbacks.prefetch"><?php echo esc_html( $term->name ) ?><?php echo $attributes[ 'showCount' ] ? ' (' . absint( $term->count ) . ')' : '' ?></a>
						<?php endforeach ?>
					</span>
				<?php
				break;

				default: {

					?>
						<select data-wp-on--change="<?php if( $has_enhanced_pagination ) : ?>actions.navigate<?php else : ?>actions.onchange<?php endif ?>">
							<option value="<?php echo esc_url( remove_query_arg( $key ) ) ?>"><?php echo esc_html( $all_items ) ?></option>
							<?php foreach( $terms as $term ) :

								$base_url = $is_main_query ? get_pagenum_link( 1, false ) : add_query_arg( array( $page_key => 1 ) );
								$current_slug = ! empty( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
								?>
								<option value="<?php echo esc_url( add_query_arg( array( $key => $term->slug ), $base_url ) ) ?>"<?php selected( $term->slug, $current_slug ) ?>><?php echo $attributes[ 'showCount' ] ? esc_html( "{$term->name} ({$term->count})" ) : esc_html( $term->name ) ?></option>
							<?php endforeach ?>
						</select>
					<?php
					break;

				}
			endswitch;

		endif;
	?>
</div>
