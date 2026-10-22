<?php
/**
 * Insight article, with the structured case analysis layout for tax cases.
 */

get_header();
the_post();
$pid     = get_the_ID();
$is_case = cwm_is_case( $pid );
$byline  = cwm_byline( $pid );
$cat     = cwm_primary_category( $pid );
$series  = cwm_series_info( $pid );
$pdf     = cwm_file_url( cwm_meta( 'pdf' ) );
?>

<section class="banner banner--article">
	<div class="container banner__inner banner__inner--narrow">
		<?php cwm_breadcrumbs_html(); ?>
		<div class="article-meta">
			<?php echo cwm_post_label( $pid ); // phpcs:ignore ?>
			<?php if ( $series ) : ?>
				<span class="tag tag--series"><?php echo esc_html( $series['term']->name ); ?> · Part <?php echo (int) $series['index'] + 1; ?> of <?php echo (int) $series['total']; ?></span>
			<?php endif; ?>
		</div>
		<h1 class="banner__title banner__title--article"><?php the_title(); ?></h1>
		<p class="article-byline">
			<span><?php echo $byline['url'] ? '<a href="' . esc_url( $byline['url'] ) . '">' . esc_html( $byline['name'] ) . '</a>' : esc_html( $byline['name'] ); ?></span>
			<span><?php echo cwm_icon( 'calendar' ); // phpcs:ignore ?><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></span>
			<span><?php echo cwm_icon( 'clock' ); // phpcs:ignore ?><?php echo esc_html( cwm_reading_time( $pid ) ); ?> min read</span>
		</p>
	</div>
</section>

<div class="section section--article">
	<div class="container article-layout">
		<article class="article">
			<?php if ( $is_case ) : ?>
				<?php
				$fields = array(
					'Case'             => cwm_meta( 'case_name' ),
					'Citation'         => cwm_meta( 'case_citation' ),
					'Court or tribunal' => cwm_case_label( 'court', cwm_meta( 'case_court' ) ),
					'Date of decision' => cwm_meta( 'case_date' ) ? mysql2date( 'j F Y', cwm_meta( 'case_date' ) ) : '',
					'Tax type'         => cwm_case_label( 'taxtype', cwm_meta( 'case_taxtype' ) ),
					'Outcome'          => cwm_case_label( 'outcome', cwm_meta( 'case_outcome' ) ),
				);
				?>
				<section class="case-box" aria-label="Case details">
					<h2 class="case-box__title"><?php echo cwm_icon( 'gavel' ); // phpcs:ignore ?> Case details</h2>
					<dl>
						<?php foreach ( $fields as $label => $value ) : ?>
							<?php if ( $value ) : ?>
								<div class="<?php echo 'Outcome' === $label ? 'case-box__outcome outcome--' . esc_attr( cwm_meta( 'case_outcome' ) ) : ''; ?>"><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
							<?php endif; ?>
						<?php endforeach; ?>
					</dl>
					<?php if ( cwm_meta( 'case_link' ) ) : ?>
						<a class="link-arrow" href="<?php echo esc_url( cwm_meta( 'case_link' ) ); ?>" target="_blank" rel="noopener">Read the full decision<?php echo cwm_icon( 'external-link' ); // phpcs:ignore ?></a>
					<?php endif; ?>
				</section>

				<?php
				$means = array_filter( array_map( 'trim', preg_split( '/\r?\n/', (string) cwm_meta( 'case_means' ) ) ) );
				if ( $means ) :
					?>
					<section class="means">
						<h2>What this means for you</h2>
						<ul>
							<?php foreach ( array_slice( $means, 0, 3 ) as $m ) : ?>
								<li><?php echo cwm_icon( 'arrow-right' ); // phpcs:ignore ?><span><?php echo esc_html( ltrim( $m, '-•* ' ) ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<div class="prose">
					<?php
					foreach ( array(
						'case_issues'   => 'The issues',
						'case_facts'    => 'The facts in brief',
						'case_decision' => 'The decision',
					) as $key => $heading ) {
						if ( cwm_meta( $key ) ) {
							echo '<h2>' . esc_html( $heading ) . '</h2>' . cwm_format( cwm_meta( $key ) ); // phpcs:ignore
						}
					}
					if ( trim( get_the_content() ) ) {
						echo '<h2>Our analysis</h2>';
						the_content();
					}
					?>
				</div>
			<?php else : ?>
				<div class="prose">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php if ( $pdf ) : ?>
				<div class="download-inline">
					<?php echo cwm_icon( 'file-text' ); // phpcs:ignore ?>
					<div><strong><?php echo $is_case ? 'Download this case analysis' : 'Download this article'; ?></strong><span>PDF<?php echo cwm_file_size( cwm_meta( 'pdf' ) ) ? ', ' . esc_html( cwm_file_size( cwm_meta( 'pdf' ) ) ) : ''; ?></span></div>
					<a class="btn btn--primary" href="<?php echo esc_url( $pdf ); ?>" download><?php echo cwm_icon( 'download' ); // phpcs:ignore ?> Download PDF</a>
				</div>
			<?php endif; ?>

			<?php if ( $series && $series['total'] > 1 ) : ?>
				<nav class="series-nav" aria-label="<?php echo esc_attr( $series['term']->name ); ?>">
					<p class="series-nav__title"><?php echo esc_html( $series['term']->name ); ?> <span>Part <?php echo (int) $series['index'] + 1; ?> of <?php echo (int) $series['total']; ?></span></p>
					<div class="series-nav__links">
						<?php if ( $series['prev'] ) : ?>
							<a class="series-nav__prev" href="<?php echo esc_url( get_permalink( $series['prev'] ) ); ?>"><small><?php echo cwm_icon( 'arrow-left' ); // phpcs:ignore ?> Previous: Part <?php echo (int) $series['index']; ?></small><span><?php echo esc_html( get_the_title( $series['prev'] ) ); ?></span></a>
						<?php endif; ?>
						<?php if ( $series['next'] ) : ?>
							<a class="series-nav__next" href="<?php echo esc_url( get_permalink( $series['next'] ) ); ?>"><small>Next: Part <?php echo (int) $series['index'] + 2; ?> <?php echo cwm_icon( 'arrow-right' ); // phpcs:ignore ?></small><span><?php echo esc_html( get_the_title( $series['next'] ) ); ?></span></a>
						<?php endif; ?>
					</div>
				</nav>
			<?php endif; ?>

			<?php cwm_share( $pid ); ?>

			<aside class="disclaimer" role="note"><?php echo cwm_icon( 'info' ); // phpcs:ignore ?><p><?php echo esc_html( cwm_opt( 'disclaimer' ) ); ?></p></aside>
		</article>

		<aside class="article-aside">
			<div class="aside-card aside-card--sticky">
				<?php
				$areas = wp_get_post_terms( $pid, 'service_area' );
				$svc   = array();
				foreach ( (array) $areas as $t ) {
					$sp = get_page_by_path( $t->slug, OBJECT, 'service' );
					if ( $sp && 'publish' === $sp->post_status && ( cwm_audit_visible() || $sp->ID !== cwm_audit_service_id() ) ) {
						$svc[] = $sp;
					}
				}
				if ( $svc ) :
					?>
					<p class="eyebrow">Related services</p>
					<ul class="aside-links">
						<?php foreach ( $svc as $sp ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $sp ) ); ?>"><?php echo cwm_icon( cwm_meta( 'icon', $sp->ID ) ? cwm_meta( 'icon', $sp->ID ) : 'circle-check' ); // phpcs:ignore ?><?php echo esc_html( $sp->post_title ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<div class="aside-card__cta">
					<p>Questions about how this affects you?</p>
					<a class="btn btn--primary btn--block" href="<?php echo esc_url( $svc ? get_permalink( $svc[0] ) . '#enquire' : cwm_page_url( 'contact' ) ); ?>">Talk to us</a>
				</div>
			</div>
		</aside>
	</div>
</div>

<?php
$related = cwm_related( $pid, 3 );
if ( $related->have_posts() ) :
	?>
	<section class="section section--tint">
		<div class="container">
			<?php cwm_section_head( '', $is_case ? 'Related cases' : 'Related articles', '', $cat ? array( 'More ' . strtolower( $is_case ? 'case analyses' : $cat->name ), get_category_link( $cat ) ) : array() ); ?>
			<div class="grid grid--3">
				<?php
				while ( $related->have_posts() ) {
					$related->the_post();
					if ( $is_case ) {
						get_template_part( 'template-parts/case-card' );
					} else {
						cwm_insight_card();
					}
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
cwm_cta();
get_footer();
