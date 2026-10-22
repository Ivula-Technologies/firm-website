<?php
/**
 * Team member profile (only reachable while Switch B is on and the person is published).
 */

get_header();
the_post();
$id    = get_the_ID();
$lines = wp_get_post_terms( $id, 'service_area' );
cwm_banner( get_the_title(), trim( cwm_meta( 'job_title' ) . ( cwm_meta( 'qualifications' ) ? ' · ' . cwm_meta( 'qualifications' ) : '' ) ), array( 'eyebrow' => 'Our team' ) );
?>
<div class="section">
	<div class="container profile">
		<aside class="profile__aside">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="profile__photo"><?php the_post_thumbnail( 'portrait', array( 'alt' => get_the_title() ) ); ?></div>
			<?php endif; ?>
			<ul class="contact-list contact-list--compact">
				<?php if ( cwm_meta( 'email' ) ) : ?>
					<li><span class="contact-list__icon"><?php echo cwm_icon( 'mail' ); // phpcs:ignore ?></span><a href="mailto:<?php echo esc_attr( cwm_meta( 'email' ) ); ?>"><?php echo esc_html( cwm_meta( 'email' ) ); ?></a></li>
				<?php endif; ?>
				<?php if ( cwm_meta( 'phone' ) ) : ?>
					<li><span class="contact-list__icon"><?php echo cwm_icon( 'phone' ); // phpcs:ignore ?></span><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', cwm_meta( 'phone' ) ) ); ?>"><?php echo esc_html( cwm_meta( 'phone' ) ); ?></a></li>
				<?php endif; ?>
				<?php if ( cwm_meta( 'linkedin' ) ) : ?>
					<li><span class="contact-list__icon"><?php echo cwm_icon( 'linkedin' ); // phpcs:ignore ?></span><a href="<?php echo esc_url( cwm_meta( 'linkedin' ) ); ?>" target="_blank" rel="noopener">LinkedIn profile</a></li>
				<?php endif; ?>
			</ul>
		</aside>
		<div class="profile__main">
			<div class="prose"><?php the_content(); ?></div>
			<?php
			$expertise = array_filter( array_map( 'trim', preg_split( '/\r?\n|,/', (string) cwm_meta( 'expertise' ) ) ) );
			if ( $expertise ) :
				?>
				<h2 class="block-title">Expertise</h2>
				<ul class="pill-list"><?php foreach ( $expertise as $e ) : ?><li><span class="pill pill--static"><?php echo esc_html( $e ); ?></span></li><?php endforeach; ?></ul>
			<?php endif; ?>
			<?php if ( $lines && ! is_wp_error( $lines ) ) : ?>
				<h2 class="block-title">Service lines</h2>
				<ul class="pill-list">
					<?php foreach ( $lines as $t ) : ?>
						<?php $sp = get_page_by_path( $t->slug, OBJECT, 'service' ); ?>
						<?php if ( $sp && ( cwm_audit_visible() || $sp->ID !== cwm_audit_service_id() ) ) : ?>
							<li><a class="pill" href="<?php echo esc_url( get_permalink( $sp ) ); ?>"><?php echo esc_html( $sp->post_title ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<p><a class="link-arrow" href="<?php echo esc_url( cwm_page_url( 'team' ) ); ?>"><?php echo cwm_icon( 'arrow-left' ); // phpcs:ignore ?> Back to our team</a></p>
		</div>
	</div>
</div>
<?php
cwm_cta();
get_footer();
