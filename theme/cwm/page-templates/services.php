<?php
/**
 * Template Name: Services overview
 */

get_header();
the_post();
cwm_banner( get_the_title(), cwm_field( 'intro', 'Seven services, one idea: we help you count what matters.' ) );
?>
<div class="section">
	<div class="container">
		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="prose prose--intro"><?php the_content(); ?></div>
		<?php endif; ?>
		<?php
		$icons = array(
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
			<section class="overview-group">
				<div class="overview-group__head">
					<span class="group__icon"><?php echo cwm_icon( $icons[ $key ] ); // phpcs:ignore ?></span>
					<h2 class="section-title"><?php echo esc_html( $label ); ?></h2>
				</div>
				<div class="overview-list">
					<?php foreach ( $services as $s ) : ?>
						<article class="overview-item">
							<span class="card__icon"><?php echo cwm_icon( cwm_meta( 'icon', $s->ID ) ? cwm_meta( 'icon', $s->ID ) : 'circle-check' ); // phpcs:ignore ?></span>
							<div class="overview-item__body">
								<h3><a class="stretched" href="<?php echo esc_url( get_permalink( $s ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></h3>
								<p class="overview-item__promise"><?php echo esc_html( cwm_meta( 'promise', $s->ID ) ); ?></p>
								<p><?php echo esc_html( cwm_meta( 'who_for', $s->ID ) ); ?></p>
							</div>
							<span class="link-arrow">Find out more<?php echo cwm_icon( 'arrow-right' ); // phpcs:ignore ?></span>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>
	</div>
</div>
<?php
cwm_cta( cwm_meta( 'cta_title' ), cwm_meta( 'cta_text' ) );
get_footer();
