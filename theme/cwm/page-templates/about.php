<?php
/**
 * Template Name: About (Who we are)
 */

get_header();
the_post();
cwm_banner( get_the_title(), cwm_field( 'intro', 'A Kenyan professional services firm that counts what matters.' ) );
$three  = (array) cwm_meta( 'three' );
$values = (array) cwm_meta( 'values' );
?>
<div class="section">
	<div class="container about-intro">
		<div class="prose prose--lead"><?php the_content(); ?></div>
		<aside class="about-card">
			<p class="eyebrow">In short</p>
			<p class="about-card__big"><?php echo esc_html( cwm_opt( 'tagline' ) ); ?></p>
			<?php cwm_profile_button( 'btn btn--primary btn--block' ); ?>
			<a class="btn btn--ghost btn--block" href="<?php echo esc_url( cwm_page_url( 'approach' ) ); ?>">Our approach</a>
			<?php if ( cwm_team_on() && cwm_page_id( 'team' ) ) : ?>
				<a class="btn btn--ghost btn--block" href="<?php echo esc_url( cwm_page_url( 'team' ) ); ?>">Meet our team</a>
			<?php endif; ?>
		</aside>
	</div>
</div>

<?php if ( $three ) : ?>
	<section class="section section--tint">
		<div class="container">
			<?php cwm_section_head( 'Count what matters', 'Three things every business accounts for' ); ?>
			<div class="three">
				<?php foreach ( $three as $i => $t ) : ?>
					<div class="three__item">
						<span class="three__num"><?php echo (int) $i + 1; ?></span>
						<span class="promise__icon"><?php echo cwm_icon( ! empty( $t['icon'] ) ? $t['icon'] : 'circle-check' ); // phpcs:ignore ?></span>
						<h3><?php echo esc_html( $t['title'] ); ?></h3>
						<p><?php echo esc_html( $t['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section">
	<div class="container mv">
		<?php if ( cwm_meta( 'mission' ) ) : ?>
			<div class="mv__item"><p class="eyebrow">Our mission</p><p class="mv__text"><?php echo esc_html( cwm_meta( 'mission' ) ); ?></p></div>
		<?php endif; ?>
		<?php if ( cwm_meta( 'vision' ) ) : ?>
			<div class="mv__item"><p class="eyebrow">Our vision</p><p class="mv__text"><?php echo esc_html( cwm_meta( 'vision' ) ); ?></p></div>
		<?php endif; ?>
	</div>
</section>

<?php if ( $values ) : ?>
	<section class="section section--tint">
		<div class="container">
			<?php cwm_section_head( 'Our values', 'The four Ps' ); ?>
			<div class="promises promises--<?php echo count( $values ); ?>">
				<?php foreach ( $values as $v ) : ?>
					<div class="promise">
						<span class="promise__icon"><?php echo cwm_icon( ! empty( $v['icon'] ) ? $v['icon'] : 'circle-check' ); // phpcs:ignore ?></span>
						<h3><?php echo esc_html( $v['title'] ); ?></h3>
						<p><?php echo esc_html( $v['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
cwm_cta( cwm_meta( 'cta_title' ), cwm_meta( 'cta_text' ) );
get_footer();
