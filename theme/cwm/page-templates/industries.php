<?php
/**
 * Template Name: Industries
 */

get_header();
the_post();
cwm_banner( get_the_title(), cwm_field( 'intro', 'We work across the Kenyan economy. Here is how we help in each sector.' ) );
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
?>
<div class="section">
	<div class="container">
		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="prose prose--intro"><?php the_content(); ?></div>
		<?php endif; ?>
		<nav class="jump" aria-label="Jump to a sector">
			<?php foreach ( $industries as $ind ) : ?>
				<a href="#<?php echo esc_attr( $ind->post_name ); ?>"><?php echo esc_html( $ind->post_title ); ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="industries">
			<?php foreach ( $industries as $ind ) : ?>
				<?php
				$services = array_filter(
					array_map( 'get_post', (array) cwm_meta( 'services', $ind->ID ) ),
					function ( $s ) {
						return $s && 'publish' === $s->post_status && ( cwm_audit_visible() || $s->ID !== cwm_audit_service_id() );
					}
				);
				?>
				<section class="industry" id="<?php echo esc_attr( $ind->post_name ); ?>">
					<span class="industry__icon"><?php echo cwm_icon( cwm_meta( 'icon', $ind->ID ) ? cwm_meta( 'icon', $ind->ID ) : 'building-2' ); // phpcs:ignore ?></span>
					<div class="industry__body">
						<h2><?php echo esc_html( $ind->post_title ); ?></h2>
						<p><?php echo esc_html( cwm_meta( 'summary', $ind->ID ) ); ?></p>
						<?php if ( $services ) : ?>
							<ul class="pill-list" aria-label="Relevant services">
								<?php foreach ( $services as $s ) : ?>
									<li><a class="pill" href="<?php echo esc_url( get_permalink( $s ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php
cwm_cta( cwm_meta( 'cta_title' ) ? cwm_meta( 'cta_title' ) : 'Not sure where your organisation fits?', cwm_meta( 'cta_text' ) ? cwm_meta( 'cta_text' ) : 'Tell us about it. We will tell you plainly whether we can help, and how.' );
get_footer();
