<?php
/**
 * Template Name: Contact
 */

get_header();
the_post();
cwm_banner( get_the_title(), cwm_field( 'intro', 'Call, email, WhatsApp or send the form. We reply within one working day.' ) );
?>
<div class="section">
	<div class="container contact">
		<div class="form-card">
			<h2 class="block-title">Send us a message</h2>
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="prose"><?php the_content(); ?></div>
			<?php endif; ?>
			<?php cwm_form( 'contact', array( 'service' => sanitize_title( cwm_get( 'service' ) ) ) ); ?>
		</div>
		<aside class="contact__details">
			<ul class="contact-list">
				<?php if ( cwm_opt( 'phone' ) ) : ?>
					<li><span class="contact-list__icon"><?php echo cwm_icon( 'phone' ); // phpcs:ignore ?></span><div><small>Phone</small><a href="<?php echo esc_url( cwm_tel_url() ); ?>"><?php echo esc_html( cwm_opt( 'phone' ) ); ?></a></div></li>
				<?php endif; ?>
				<?php if ( cwm_opt( 'email' ) ) : ?>
					<li><span class="contact-list__icon"><?php echo cwm_icon( 'mail' ); // phpcs:ignore ?></span><div><small>Email</small><a href="mailto:<?php echo esc_attr( cwm_opt( 'email' ) ); ?>"><?php echo esc_html( cwm_opt( 'email' ) ); ?></a></div></li>
				<?php endif; ?>
				<li><span class="contact-list__icon"><?php echo cwm_icon( 'map-pin' ); // phpcs:ignore ?></span><div><small>Office</small><span><?php echo nl2br( esc_html( cwm_opt( 'address' ) ) ); ?></span></div></li>
				<?php if ( cwm_opt( 'hours' ) ) : ?>
					<li><span class="contact-list__icon"><?php echo cwm_icon( 'clock' ); // phpcs:ignore ?></span><div><small>Hours</small><span><?php echo nl2br( esc_html( cwm_opt( 'hours' ) ) ); ?></span></div></li>
				<?php endif; ?>
			</ul>
			<?php if ( cwm_whatsapp_url() ) : ?>
				<a class="btn btn--whatsapp btn--block" href="<?php echo esc_url( cwm_whatsapp_url( 'Hello ' . cwm_firm() . ', I would like to ask about your services.' ) ); ?>" target="_blank" rel="noopener"><?php echo cwm_icon( 'whatsapp' ); // phpcs:ignore ?> Chat on WhatsApp</a>
			<?php endif; ?>
			<a class="btn btn--ghost btn--block" href="<?php echo esc_url( cwm_page_url( 'proposal' ) ); ?>">Need a quote? Request a proposal</a>
			<?php cwm_map(); ?>
		</aside>
	</div>
</div>
<?php
get_footer();
