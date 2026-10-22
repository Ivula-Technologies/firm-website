<?php
/**
 * Template Name: Our approach
 */

get_header();
the_post();
cwm_banner( get_the_title(), cwm_field( 'intro', 'Four steps, the same on every assignment.' ), array( 'eyebrow' => 'About us' ) );
$steps = (array) cwm_meta( 'steps' );
?>
<div class="section">
	<div class="container">
		<ol class="approach">
			<?php foreach ( $steps as $i => $st ) : ?>
				<li class="approach__step">
					<span class="approach__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<span class="approach__icon"><?php echo cwm_icon( ! empty( $st['icon'] ) ? $st['icon'] : 'circle-check' ); // phpcs:ignore ?></span>
					<h2><?php echo esc_html( $st['title'] ); ?></h2>
					<p><?php echo esc_html( $st['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="prose prose--intro approach__more"><?php the_content(); ?></div>
		<?php endif; ?>
		<div class="center-actions">
			<a class="btn btn--ghost" href="<?php echo esc_url( cwm_page_url( 'about' ) ); ?>">Who we are</a>
			<?php cwm_profile_button( 'btn btn--ghost' ); ?>
		</div>
	</div>
</div>
<?php
cwm_cta( cwm_meta( 'cta_title' ), cwm_meta( 'cta_text' ) );
get_footer();
