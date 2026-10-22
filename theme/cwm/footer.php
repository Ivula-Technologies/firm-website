</main>

<footer class="site-footer">
	<div class="container">
		<div class="footer-top">
			<div class="footer-brand">
				<a class="brand brand--light" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( cwm_firm() ); ?>, home"><?php cwm_logo( 'light' ); ?></a>
				<p><?php echo esc_html( cwm_opt( 'description' ) ); ?></p>
				<?php cwm_profile_button( 'btn btn--outline-light btn--sm' ); ?>
				<ul class="social">
					<?php
					$social = array(
						'linkedin' => array( cwm_opt( 'linkedin' ), 'LinkedIn' ),
						'x-brand'  => array( cwm_opt( 'x' ), 'X' ),
						'facebook' => array( cwm_opt( 'facebook' ), 'Facebook' ),
						'whatsapp' => array( cwm_whatsapp_url(), 'WhatsApp' ),
					);
					foreach ( $social as $icon => $s ) :
						if ( ! $s[0] ) {
							continue;
						}
						?>
						<li><a href="<?php echo esc_url( $s[0] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( cwm_firm() . ' on ' . $s[1] ); ?>"><?php echo cwm_icon( $icon ); // phpcs:ignore ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="footer-col">
				<h2 class="footer-title">Services</h2>
				<ul>
					<?php foreach ( cwm_services() as $s ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $s ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="footer-col">
				<h2 class="footer-title">Quick links</h2>
				<ul>
					<li><a href="<?php echo esc_url( cwm_page_url( 'about' ) ); ?>">About us</a></li>
					<li><a href="<?php echo esc_url( cwm_page_url( 'approach' ) ); ?>">Our approach</a></li>
					<?php if ( cwm_team_on() && cwm_page_id( 'team' ) ) : ?>
						<li><a href="<?php echo esc_url( cwm_page_url( 'team' ) ); ?>">Our team</a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( cwm_page_url( 'industries' ) ); ?>">Industries</a></li>
					<li><a href="<?php echo esc_url( cwm_insights_url() ); ?>">Insights</a></li>
					<?php if ( cwm_page_id( 'esg-hub' ) ) : ?>
						<li><a href="<?php echo esc_url( cwm_page_url( 'esg-hub' ) ); ?>">ESG reporting hub</a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( cwm_page_url( 'proposal' ) ); ?>">Request a proposal</a></li>
					<li><a href="<?php echo esc_url( cwm_page_url( 'contact' ) ); ?>">Contact</a></li>
				</ul>
			</div>

			<div class="footer-col footer-contact">
				<h2 class="footer-title">Visit or call</h2>
				<p class="with-icon"><?php echo cwm_icon( 'map-pin' ); // phpcs:ignore ?><span><?php echo nl2br( esc_html( cwm_opt( 'address' ) ) ); ?></span></p>
				<?php if ( cwm_opt( 'phone' ) ) : ?>
					<p class="with-icon"><?php echo cwm_icon( 'phone' ); // phpcs:ignore ?><a href="<?php echo esc_url( cwm_tel_url() ); ?>"><?php echo esc_html( cwm_opt( 'phone' ) ); ?></a></p>
				<?php endif; ?>
				<?php if ( cwm_opt( 'email' ) ) : ?>
					<p class="with-icon"><?php echo cwm_icon( 'mail' ); // phpcs:ignore ?><a href="mailto:<?php echo esc_attr( cwm_opt( 'email' ) ); ?>"><?php echo esc_html( cwm_opt( 'email' ) ); ?></a></p>
				<?php endif; ?>
				<?php if ( cwm_opt( 'hours' ) ) : ?>
					<p class="with-icon"><?php echo cwm_icon( 'clock' ); // phpcs:ignore ?><span><?php echo nl2br( esc_html( cwm_opt( 'hours' ) ) ); ?></span></p>
				<?php endif; ?>
			</div>

			<div class="footer-col footer-newsletter">
				<h2 class="footer-title">Newsletter</h2>
				<p><?php echo esc_html( cwm_opt( 'newsletter_text' ) ); ?></p>
				<?php cwm_form( 'newsletter' ); ?>
			</div>
		</div>

		<div class="footer-bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( cwm_firm() ); ?>. All rights reserved.</p>
			<ul class="legal-links">
				<li><a href="<?php echo esc_url( cwm_legal_url( 'privacy' ) ); ?>">Privacy policy</a></li>
				<li><a href="<?php echo esc_url( cwm_legal_url( 'cookies' ) ); ?>">Cookie policy</a></li>
				<li><a href="<?php echo esc_url( cwm_legal_url( 'terms' ) ); ?>">Terms of use and disclaimer</a></li>
				<li><a href="<?php echo esc_url( cwm_legal_url( 'sitemap' ) ); ?>">Site map</a></li>
			</ul>
			<?php if ( trim( (string) cwm_opt( 'licence_line' ) ) ) : ?>
				<p class="licence-line"><?php echo esc_html( cwm_opt( 'licence_line' ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php if ( cwm_whatsapp_url() ) : ?>
	<a class="wa-float" href="<?php echo esc_url( cwm_whatsapp_url( 'Hello ' . cwm_firm() . ', I would like to ask about your services.' ) ); ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"><?php echo cwm_icon( 'whatsapp' ); // phpcs:ignore ?></a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
