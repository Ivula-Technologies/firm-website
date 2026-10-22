<?php
/**
 * Site search results.
 */

get_header();
global $wp_query;
cwm_banner( 'Search', $wp_query->found_posts . ' result' . ( 1 === (int) $wp_query->found_posts ? '' : 's' ) . ' for “' . get_search_query() . '”' );
$types = array(
	'post'        => 'Insight',
	'page'        => 'Page',
	'service'     => 'Service',
	'team_member' => 'Team',
);
?>
<div class="section">
	<div class="container container--narrow">
		<form class="filters filters--search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<div class="filters__search"><?php echo cwm_icon( 'search' ); // phpcs:ignore ?><label class="sr-only" for="s2">Search</label><input id="s2" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Search the site"></div>
			<button class="btn btn--primary" type="submit">Search</button>
		</form>
		<?php if ( have_posts() ) : ?>
			<ul class="search-results">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li>
						<p class="search-results__type"><?php echo esc_html( isset( $types[ get_post_type() ] ) ? $types[ get_post_type() ] : 'Page' ); ?><?php echo 'post' === get_post_type() ? ' · ' . esc_html( get_the_date() ) : ''; ?></p>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( 'service' === get_post_type() ? cwm_meta( 'promise' ) : wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php cwm_pagination(); ?>
		<?php else : ?>
			<div class="empty">
				<?php echo cwm_icon( 'search' ); // phpcs:ignore ?>
				<h2>No results</h2>
				<p>Try another word, browse <a href="<?php echo esc_url( cwm_page_url( 'services' ) ); ?>">our services</a> or <a href="<?php echo esc_url( cwm_insights_url() ); ?>">insights</a>, or <a href="<?php echo esc_url( cwm_page_url( 'contact' ) ); ?>">ask us</a>.</p>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
cwm_cta();
get_footer();
