<?php
/**
 * Homepage, built around "Count what matters": money, obligations and impact.
 */

get_header();
$home = (int) get_option( 'page_on_front' );
$f    = function ( $key, $default = '' ) use ( $home ) {
	return $home ? cwm_field( $key, $default, $home ) : $default;
};
$hero_image = (int) $f( 'hero_image' );
?>

<section class="hero">
	<div class="container hero__inner">
		<div class="hero__copy">
			<p class="eyebrow eyebrow--light"><?php echo esc_html( cwm_firm() ); ?></p>
			<?php
			$headline = $f( 'hero_title', 'Count what matters.' );
			// Underline the last word in gold.
			$words = explode( ' ', $headline );
			$last  = array_pop( $words );
			?>
			<h1 class="hero__title"><?php echo esc_html( implode( ' ', $words ) ); ?> <span class="underline-gold"><?php echo esc_html( $last ); ?></span></h1>
			<p class="hero__text"><?php echo esc_html( $f( 'hero_text', 'Accounting, tax, advisory and ESG support for Kenyan businesses that want to grow, and to do it right.' ) ); ?></p>
			<div class="hero__actions">
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( cwm_page_url( 'services' ) ); ?>">Our services</a>
				<a class="btn btn--outline-light btn--lg" href="<?php echo esc_url( cwm_page_url( 'proposal' ) ); ?>">Request a proposal</a>
			</div>
			<ul class="hero__three" aria-label="What we help you count">
				<li><?php echo cwm_icon( 'coins' ); // phpcs:ignore ?> Your money</li>
				<li><?php echo cwm_icon( 'scale' ); // phpcs:ignore ?> Your obligations</li>
				<li><?php echo cwm_icon( 'sprout' ); // phpcs:ignore ?> Your impact</li>
			</ul>
		</div>
		<div class="hero__art">
			<?php
			if ( $hero_image && wp_get_attachment_url( $hero_image ) ) {
				echo wp_get_attachment_image( $hero_image, 'hero', false, array( 'class' => 'hero__img', 'loading' => 'eager', 'fetchpriority' => 'high' ) );
			} else {
				cwm_hero_art();
			}
			?>
		</div>
	</div>
</section>

<section class="section section--services" id="services">
	<div class="container">
		<?php cwm_section_head( 'What we do', $f( 'services_title', 'Three things to account for' ), $f( 'services_text', 'Every business has its money, its obligations and its impact to answer for. We help with all three.' ), array( 'All services', cwm_page_url( 'services' ) ) ); ?>
		<div class="groups">
			<?php
			$group_icons = array(
				'money'       => 'coins',
				'obligations' => 'scale',
				'impact'      => 'sprout',
			);
			foreach ( cwm_groups() as $key => $label ) :
				$services = cwm_services( $key );
				if ( ! $services ) {
					continue;
				}
				?>
				<div class="group group--<?php echo esc_attr( $key ); ?>">
					<div class="group__head">
						<span class="group__icon"><?php echo cwm_icon( $group_icons[ $key ] ); // phpcs:ignore ?></span>
						<div>
							<h3 class="group__title"><?php echo esc_html( $label ); ?></h3>
							<p class="group__text"><?php echo esc_html( $f( 'group_' . $key ) ); ?></p>
						</div>
					</div>
					<div class="group__cards">
						<?php
						foreach ( $services as $s ) {
							cwm_service_card( $s );
						}
						?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
$promises = (array) $f( 'promises', array() );
if ( $promises ) :
	?>
	<section class="section section--tint">
		<div class="container">
			<?php cwm_section_head( 'Why choose us', $f( 'why_title', 'Our four promises' ), '', array( 'About us', cwm_page_url( 'about' ) ) ); ?>
			<div class="promises promises--<?php echo count( $promises ); ?>">
				<?php foreach ( $promises as $p ) : ?>
					<div class="promise">
						<span class="promise__icon"><?php echo cwm_icon( $p['icon'] ? $p['icon'] : 'circle-check' ); // phpcs:ignore ?></span>
						<h3><?php echo esc_html( $p['title'] ); ?></h3>
						<p><?php echo esc_html( $p['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
$stats = array_filter(
	(array) $f( 'stats', array() ),
	function ( $s ) {
		return ! empty( $s['figure'] );
	}
);
if ( $f( 'stats_show' ) && $stats ) :
	?>
	<section class="stats" aria-label="Key figures">
		<div class="container stats__inner">
			<?php foreach ( array_slice( $stats, 0, 4 ) as $s ) : ?>
				<div class="stat"><span class="stat__figure"><?php echo esc_html( $s['figure'] ); ?></span><span class="stat__label"><?php echo esc_html( $s['label'] ); ?></span></div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php
$featured = cwm_featured_post_id();
$latest   = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post__not_in'        => $featured ? array( $featured ) : array(),
		'ignore_sticky_posts' => true,
	)
);
if ( $featured || $latest->have_posts() ) :
	?>
	<section class="section">
		<div class="container">
			<?php cwm_section_head( 'Insights', $f( 'insights_title', 'Latest insights' ), '', array( 'All insights', cwm_insights_url() ) ); ?>
			<?php if ( $featured ) : ?>
				<div class="featured-insight">
					<?php cwm_insight_card( $featured, 'card--featured' ); ?>
				</div>
			<?php endif; ?>
			<div class="grid grid--3">
				<?php
				while ( $latest->have_posts() ) {
					$latest->the_post();
					cwm_insight_card();
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
$download = (int) $f( 'download' );
$res      = $download ? get_post( $download ) : null;
if ( $res && 'publish' === $res->post_status && cwm_file_url( cwm_meta( 'file', $res->ID ) ) ) :
	?>
	<section class="section section--tight">
		<div class="container">
			<div class="download-feature">
				<p class="eyebrow">Featured download</p>
				<?php cwm_resource_card( $res, 'resource--feature' ); ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
$industries = get_posts(
	array(
		'post_type'   => 'industry',
		'numberposts' => -1,
		'orderby'     => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	)
);
if ( $industries ) :
	?>
	<section class="section section--tint">
		<div class="container">
			<?php cwm_section_head( 'Who we work with', $f( 'industries_title', 'Industries we serve' ), '', array( 'All industries', cwm_page_url( 'industries' ) ) ); ?>
			<ul class="tiles">
				<?php foreach ( $industries as $ind ) : ?>
					<li><a class="tile" href="<?php echo esc_url( cwm_page_url( 'industries' ) . '#' . $ind->post_name ); ?>"><?php echo cwm_icon( cwm_meta( 'icon', $ind->ID ) ? cwm_meta( 'icon', $ind->ID ) : 'building-2' ); // phpcs:ignore ?><span><?php echo esc_html( $ind->post_title ); ?></span></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<?php
if ( $f( 'testimonials_show' ) ) :
	$quotes = get_posts(
		array(
			'post_type'   => 'testimonial',
			'numberposts' => 6,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		)
	);
	if ( $quotes ) :
		?>
		<section class="section">
			<div class="container">
				<?php cwm_section_head( 'Testimonials', $f( 'testimonials_title', 'What our clients say' ) ); ?>
				<div class="grid grid--3">
					<?php foreach ( $quotes as $q ) : ?>
						<figure class="quote">
							<?php echo cwm_icon( 'quote', 'quote__mark' ); // phpcs:ignore ?>
							<blockquote><?php echo wp_kses_post( wpautop( $q->post_content ) ); ?></blockquote>
							<figcaption><strong><?php echo esc_html( $q->post_title ); ?></strong><?php echo cwm_meta( 'role', $q->ID ) ? '<span>' . esc_html( cwm_meta( 'role', $q->ID ) ) . '</span>' : ''; ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	endif;
endif;
?>

<?php cwm_cta( $f( 'closing_title', "Let's look at your numbers together." ), $f( 'closing_text', 'Tell us where you are and what is worrying you. We will reply within one working day, in plain language.' ) ); ?>

<?php
get_footer();
