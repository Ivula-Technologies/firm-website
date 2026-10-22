<?php
/**
 * Template Name: Site map
 */

get_header();
the_post();
cwm_banner( get_the_title(), 'Every page on this site.' );
$exclude = cwm_hidden_ids();
?>
<div class="section">
	<div class="container sitemap">
		<section>
			<h2>Pages</h2>
			<ul>
				<?php
				wp_list_pages(
					array(
						'title_li' => '',
						'exclude'  => implode( ',', $exclude ),
					)
				);
				?>
			</ul>
		</section>
		<section>
			<h2>Services</h2>
			<ul>
				<?php foreach ( cwm_services() as $s ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $s ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php if ( cwm_team_on() ) : ?>
				<h2>Our team</h2>
				<ul>
					<?php foreach ( get_posts( array( 'post_type' => 'team_member', 'numberposts' => -1, 'meta_key' => '_cwm_published', 'meta_value' => '1', 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $m ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $m ) ); ?>"><?php echo esc_html( $m->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
		<section>
			<h2>Insights</h2>
			<ul>
				<li><a href="<?php echo esc_url( cwm_insights_url() ); ?>">All insights</a></li>
				<?php wp_list_categories( array( 'title_li' => '', 'hide_empty' => false ) ); ?>
			</ul>
			<h2>Latest articles</h2>
			<ul>
				<?php foreach ( get_posts( array( 'numberposts' => 15, 'suppress_filters' => false ) ) as $p ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</section>
	</div>
</div>
<?php
cwm_cta();
get_footer();
