<?php
/**
 * Fallback template.
 */

get_header();
cwm_banner( is_archive() ? get_the_archive_title() : get_the_title() );
?>
<div class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3">
				<?php
				while ( have_posts() ) {
					the_post();
					cwm_insight_card();
				}
				?>
			</div>
			<?php cwm_pagination(); ?>
		<?php else : ?>
			<p>Nothing here yet.</p>
		<?php endif; ?>
	</div>
</div>
<?php
cwm_cta();
get_footer();
