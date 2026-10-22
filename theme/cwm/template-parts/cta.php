<?php
/**
 * Closing call to action: "Talk to us" with phone, email, WhatsApp, form link and firm profile.
 *
 * @var array $args title, text, service
 */

$title   = ! empty( $args['title'] ) ? $args['title'] : "Let's look at your numbers together.";
$text    = ! empty( $args['text'] ) ? $args['text'] : 'Tell us where you are and what is worrying you. We will reply within one working day, in plain language.';
$service = ! empty( $args['service'] ) ? (int) $args['service'] : 0;
$form    = $service ? ( (int) get_queried_object_id() === $service ? '#enquire' : get_permalink( $service ) . '#enquire' ) : cwm_page_url( 'contact' ) . '#form-contact';
?>
<section class="cta" aria-labelledby="cta-title">
	<div class="container cta__inner">
		<div class="cta__copy">
			<p class="eyebrow eyebrow--light">Talk to us</p>
			<h2 class="cta__title" id="cta-title"><?php echo esc_html( $title ); ?></h2>
			<p><?php echo esc_html( $text ); ?></p>
			<div class="cta__actions">
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( $form ); ?>">Send us a message</a>
				<?php cwm_profile_button( 'btn btn--outline-light btn--lg' ); ?>
			</div>
		</div>
		<ul class="cta__channels">
			<?php if ( cwm_opt( 'phone' ) ) : ?>
				<li<?php echo cwm_whatsapp_url() ? '' : ' class="is-wide"'; ?>><a href="<?php echo esc_url( cwm_tel_url() ); ?>"><span class="cta__icon"><?php echo cwm_icon( 'phone' ); // phpcs:ignore ?></span><span><small>Call</small><?php echo esc_html( cwm_opt( 'phone' ) ); ?></span></a></li>
			<?php endif; ?>
			<?php if ( cwm_whatsapp_url() ) : ?>
				<li><a href="<?php echo esc_url( cwm_whatsapp_url( 'Hello ' . cwm_firm() . ', I would like to talk about my business.' ) ); ?>" target="_blank" rel="noopener"><span class="cta__icon"><?php echo cwm_icon( 'whatsapp' ); // phpcs:ignore ?></span><span><small>WhatsApp</small>Chat with us</span></a></li>
			<?php endif; ?>
			<?php if ( cwm_opt( 'email' ) ) : ?>
				<li class="is-wide"><a href="mailto:<?php echo esc_attr( cwm_opt( 'email' ) ); ?>"><span class="cta__icon"><?php echo cwm_icon( 'mail' ); // phpcs:ignore ?></span><span><small>Email</small><?php echo esc_html( cwm_opt( 'email' ) ); ?></span></a></li>
			<?php endif; ?>
			<li class="is-wide"><a href="<?php echo esc_url( cwm_page_url( 'proposal' ) ); ?>"><span class="cta__icon"><?php echo cwm_icon( 'file-check' ); // phpcs:ignore ?></span><span><small>Proposal</small>Request a proposal</span></a></li>
		</ul>
	</div>
</section>
