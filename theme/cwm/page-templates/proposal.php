<?php
/**
 * Template Name: Request a proposal
 */

get_header();
the_post();
cwm_banner( get_the_title(), cwm_field( 'intro', 'Tell us about your organisation and what you need. We will come back with a clear scope and fee.' ) );
?>
<div class="section">
	<div class="container contact">
		<div class="form-card">
			<?php cwm_form( 'proposal', array( 'services' => array_filter( array( sanitize_title( cwm_get( 'service' ) ) ) ) ) ); ?>
		</div>
		<aside class="contact__details">
			<div class="aside-card">
				<p class="eyebrow">What happens next</p>
				<?php if ( trim( get_the_content() ) ) : ?>
					<div class="prose prose--small"><?php the_content(); ?></div>
				<?php else : ?>
					<ol class="mini-steps">
						<li><strong>We read your request</strong> and reply within one working day.</li>
						<li><strong>A short call</strong> to understand your business and timelines.</li>
						<li><strong>A written proposal</strong> with scope, team and a clear fee.</li>
					</ol>
				<?php endif; ?>
			</div>
			<div class="aside-card aside-card--plain">
				<p class="eyebrow">Rather talk first?</p>
				<p><a href="<?php echo esc_url( cwm_tel_url() ); ?>"><?php echo cwm_icon( 'phone' ); // phpcs:ignore ?> <?php echo esc_html( cwm_opt( 'phone' ) ); ?></a></p>
				<p><a href="mailto:<?php echo esc_attr( cwm_opt( 'email' ) ); ?>"><?php echo cwm_icon( 'mail' ); // phpcs:ignore ?> <?php echo esc_html( cwm_opt( 'email' ) ); ?></a></p>
				<?php cwm_profile_button( 'btn btn--ghost btn--block' ); ?>
			</div>
		</aside>
	</div>
</div>
<?php
get_footer();
