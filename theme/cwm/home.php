<?php
/**
 * Insights listing: all insights and each category, with filters, search and pagination.
 * Also used for category archives (see category.php).
 */

get_header();
global $wp_query;

$current   = is_category() ? get_queried_object() : null;
$is_case   = $current && 'case-analysis' === $current->slug;
$is_tax    = $current && ( 'tax-alerts' === $current->slug || $is_case );
$is_esg    = $current && 'esg-reporting' === $current->slug;
$title     = $current ? ( $is_case ? 'Tax case analysis' : $current->name ) : 'Insights';
$line      = $current && $current->description ? wp_strip_all_tags( $current->description ) : 'Tax alerts, case analyses, ESG reporting updates, practical guides and news from ' . cwm_firm() . '.';
$base_url  = $current ? get_category_link( $current ) : cwm_insights_url();

cwm_banner( $title, $line, array( 'eyebrow' => $current ? 'Insights' : '' ) );

$tabs = array(
	array( 'All insights', cwm_insights_url(), ! $current ),
);
foreach ( array( 'tax-alerts', 'esg-reporting', 'guides', 'news-events' ) as $slug ) {
	$c = get_category_by_slug( $slug );
	if ( ! $c ) {
		continue;
	}
	$url = ( 'esg-reporting' === $slug && cwm_page_id( 'esg-hub' ) ) ? cwm_page_url( 'esg-hub' ) : get_category_link( $c );
	$tabs[] = array( 'esg-reporting' === $slug ? 'ESG reporting hub' : $c->name, $url, $current && ( $current->term_id === $c->term_id || ( 'tax-alerts' === $slug && $is_tax ) ) );
}
?>

<div class="section section--listing">
	<div class="container">
		<nav class="tabs" aria-label="Insight categories">
			<?php foreach ( $tabs as $t ) : ?>
				<a class="tabs__tab<?php echo $t[2] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $t[1] ); ?>"<?php echo $t[2] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $t[0] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<?php
		if ( $is_tax ) :
			$tax_cat  = get_category_by_slug( 'tax-alerts' );
			$case_cat = get_category_by_slug( 'case-analysis' );
			?>
			<nav class="subtabs" aria-label="Tax alerts">
				<?php if ( $tax_cat ) : ?>
					<a class="subtabs__tab<?php echo ! $is_case ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $tax_cat ) ); ?>">All tax alerts</a>
				<?php endif; ?>
				<?php if ( $case_cat ) : ?>
					<a class="subtabs__tab<?php echo $is_case ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $case_cat ) ); ?>"><?php echo cwm_icon( 'gavel' ); // phpcs:ignore ?> Case analysis</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

		<form class="filters" method="get" action="<?php echo esc_url( $base_url ); ?>" role="search">
			<div class="filters__search">
				<label class="sr-only" for="filter-q"><?php echo $is_case ? 'Search by case name or citation' : 'Search insights'; ?></label>
				<?php echo cwm_icon( 'search' ); // phpcs:ignore ?>
				<input id="filter-q" type="search" name="q" value="<?php echo esc_attr( cwm_get( 'q' ) ); ?>" placeholder="<?php echo $is_case ? 'Search by case name or citation' : 'Search insights'; ?>">
			</div>
			<?php if ( $is_case ) : ?>
				<?php foreach ( array( 'court' => 'All courts', 'taxtype' => 'All tax types', 'outcome' => 'All outcomes' ) as $k => $all ) : ?>
					<label class="sr-only" for="filter-<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $all ); ?></label>
					<select id="filter-<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $k ); ?>" class="js-autosubmit">
						<option value=""><?php echo esc_html( $all ); ?></option>
						<?php foreach ( cwm_case_options( $k ) as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"<?php selected( cwm_get( $k ), $v ); ?>><?php echo esc_html( $l ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endforeach; ?>
			<?php elseif ( ! $current || ! $is_tax ) : ?>
				<?php if ( $is_esg ) : ?>
					<label class="sr-only" for="filter-topic">Topic</label>
					<select id="filter-topic" name="topic" class="js-autosubmit">
						<option value="">All topics</option>
						<?php foreach ( get_terms( array( 'taxonomy' => 'esg_topic', 'hide_empty' => true ) ) as $t ) : ?>
							<option value="<?php echo esc_attr( $t->slug ); ?>"<?php selected( cwm_get( 'topic' ), $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<label class="sr-only" for="filter-service">Service</label>
					<select id="filter-service" name="service" class="js-autosubmit">
						<option value="">All services</option>
						<?php foreach ( cwm_service_choices() as $slug => $name ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"<?php selected( cwm_get( 'service' ), $slug ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			<?php endif; ?>
			<label class="sr-only" for="filter-yr"><?php echo $is_case ? 'Year of decision' : 'Year'; ?></label>
			<select id="filter-yr" name="yr" class="js-autosubmit">
				<option value=""><?php echo $is_case ? 'Any year of decision' : 'Any year'; ?></option>
				<?php foreach ( cwm_years( $is_case ) as $y ) : ?>
					<option value="<?php echo esc_attr( $y ); ?>"<?php selected( (int) cwm_get( 'yr' ), $y ); ?>><?php echo esc_html( $y ); ?></option>
				<?php endforeach; ?>
			</select>
			<button class="btn btn--primary" type="submit">Filter</button>
			<?php if ( cwm_get( 'q' ) || cwm_get( 'yr' ) || cwm_get( 'service' ) || cwm_get( 'topic' ) || cwm_get( 'court' ) || cwm_get( 'taxtype' ) || cwm_get( 'outcome' ) ) : ?>
				<a class="btn btn--link" href="<?php echo esc_url( $base_url ); ?>">Clear filters</a>
			<?php endif; ?>
		</form>

		<p class="results-count" aria-live="polite">
			<?php
			$n = (int) $wp_query->found_posts;
			echo esc_html( $n . ' ' . ( $is_case ? ( 1 === $n ? 'case' : 'cases' ) : ( 1 === $n ? 'article' : 'articles' ) ) );
			$svc = cwm_get( 'service' );
			if ( $svc && isset( cwm_service_choices()[ $svc ] ) ) {
				echo ' on ' . esc_html( cwm_service_choices()[ $svc ] );
			}
			?>
		</p>

		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3">
				<?php
				while ( have_posts() ) {
					the_post();
					if ( $is_case ) {
						get_template_part( 'template-parts/case-card' );
					} else {
						cwm_insight_card();
					}
				}
				?>
			</div>
			<?php cwm_pagination(); ?>
		<?php else : ?>
			<div class="empty">
				<?php echo cwm_icon( 'search' ); // phpcs:ignore ?>
				<h2>Nothing matches those filters yet</h2>
				<p>Try fewer filters, or <a href="<?php echo esc_url( $base_url ); ?>">see everything</a>. You can also ask us directly.</p>
			</div>
		<?php endif; ?>
	</div>
</div>

<section class="section section--tint section--tight">
	<div class="container newsletter-band">
		<div>
			<h2 class="section-title">Get insights in your inbox</h2>
			<p><?php echo esc_html( cwm_opt( 'newsletter_text' ) ); ?></p>
		</div>
		<?php cwm_form( 'newsletter' ); ?>
	</div>
</section>

<?php
cwm_cta();
get_footer();
