<?php
/**
 * Default page (legal pages and any other simple page).
 */

get_header();
the_post();
cwm_banner( get_the_title(), cwm_meta( 'intro' ) );
?>
<div class="section">
	<div class="container container--narrow">
		<div class="prose">
			<?php the_content(); ?>
		</div>
		<?php if ( get_the_modified_date() ) : ?>
			<p class="muted small">Last updated <?php echo esc_html( get_the_modified_date() ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
cwm_cta( cwm_meta( 'cta_title' ), cwm_meta( 'cta_text' ) );
get_footer();
