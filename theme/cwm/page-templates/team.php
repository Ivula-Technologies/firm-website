<?php
/**
 * Template Name: Our team
 *
 * Only reachable while "Show team section" is on (Switch B); otherwise it redirects to About.
 */

get_header();
the_post();
cwm_banner( get_the_title(), cwm_field( 'intro', 'The people who will count what matters for you.' ), array( 'eyebrow' => 'About us' ) );
$line    = sanitize_title( cwm_get( 'line' ) );
$args    = array(
	'post_type'   => 'team_member',
	'numberposts' => -1,
	'meta_key'    => '_cwm_published',
	'meta_value'  => '1',
	'orderby'     => array(
		'menu_order' => 'ASC',
		'title'      => 'ASC',
	),
);
if ( $line ) {
	$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
		array(
			'taxonomy' => 'service_area',
			'field'    => 'slug',
			'terms'    => array( $line ),
		),
	);
}
$members = cwm_team_on() ? get_posts( $args ) : array();
?>
<div class="section">
	<div class="container">
		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="prose prose--intro"><?php the_content(); ?></div>
		<?php endif; ?>
		<nav class="chips" aria-label="Filter by service line">
			<a class="chip<?php echo ! $line ? ' is-active' : ''; ?>" href="<?php the_permalink(); ?>">Everyone</a>
			<?php foreach ( cwm_service_choices() as $slug => $name ) : ?>
				<a class="chip<?php echo $line === $slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'line', $slug, get_permalink() ) ); ?>"><?php echo esc_html( $name ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php if ( $members ) : ?>
			<div class="team-grid">
				<?php foreach ( $members as $m ) : ?>
					<article class="person">
						<a class="person__photo" href="<?php echo esc_url( get_permalink( $m ) ); ?>" tabindex="-1" aria-hidden="true">
							<?php echo has_post_thumbnail( $m ) ? get_the_post_thumbnail( $m, 'portrait', array( 'alt' => '' ) ) : '<span class="person__initials">' . esc_html( implode( '', array_map( function ( $w ) { return mb_substr( $w, 0, 1 ); }, array_slice( explode( ' ', $m->post_title ), 0, 2 ) ) ) ) . '</span>'; ?>
						</a>
						<h2 class="person__name"><a href="<?php echo esc_url( get_permalink( $m ) ); ?>"><?php echo esc_html( $m->post_title ); ?></a></h2>
						<p class="person__role"><?php echo esc_html( cwm_meta( 'job_title', $m->ID ) ); ?></p>
						<?php if ( cwm_meta( 'qualifications', $m->ID ) ) : ?>
							<p class="person__quals"><?php echo esc_html( cwm_meta( 'qualifications', $m->ID ) ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="muted">No team members to show here yet.</p>
		<?php endif; ?>
	</div>
</div>
<?php
cwm_cta( cwm_meta( 'cta_title' ), cwm_meta( 'cta_text' ) );
get_footer();
