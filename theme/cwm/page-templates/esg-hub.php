<?php
/**
 * Template Name: ESG reporting hub
 */

get_header();
the_post();
$esg_service = cwm_service_by_role( 'esg' );
$cat         = get_category_by_slug( 'esg-reporting' );
$topic       = sanitize_title( cwm_get( 'topic' ) );
$paged       = max( 1, absint( cwm_get( 'pg' ) ) );
$extra       = '<div class="banner__actions">';
if ( $esg_service ) {
	$extra .= '<a class="btn btn--primary" href="' . esc_url( get_permalink( $esg_service ) . '#enquire' ) . '">Talk to our ESG team</a>';
	$extra .= '<a class="btn btn--outline-light" href="' . esc_url( get_permalink( $esg_service ) ) . '">ESG and sustainability advisory</a>';
}
$extra .= '</div>';
cwm_banner( get_the_title(), cwm_field( 'intro', 'Practical guidance on sustainability reporting for Kenyan organisations, published as a regular series.' ), array( 'eyebrow' => 'Insights', 'icon' => 'leaf', 'extra' => $extra, 'class' => 'banner--esg' ) );
?>

<div class="section">
	<div class="container hub-intro">
		<div class="prose prose--lead"><?php the_content(); ?></div>
		<nav class="hub-nav aside-card" aria-label="In this hub">
			<p class="eyebrow">In this hub</p>
			<ul class="toc">
				<li><a href="#start-here">Start here</a></li>
				<li><a href="#series">Article series</a></li>
				<li><a href="#articles">All ESG articles</a></li>
				<li><a href="#glossary">Glossary</a></li>
				<li><a href="#downloads">Checklists and templates</a></li>
			</ul>
		</nav>
	</div>
</div>

<?php
$start = get_posts(
	array(
		'post_type'        => 'post',
		'numberposts'      => 4,
		'meta_key'         => '_cwm_start_here',
		'meta_value'       => '1',
		'suppress_filters' => false,
	)
);
if ( $start ) :
	?>
	<section class="section section--tint" id="start-here">
		<div class="container">
			<?php cwm_section_head( 'New to ESG reporting?', 'Start here', 'The articles we recommend reading first.' ); ?>
			<ol class="start-here">
				<?php foreach ( $start as $i => $p ) : ?>
					<li>
						<a class="start-here__item" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
							<span class="start-here__num"><?php echo (int) $i + 1; ?></span>
							<span class="start-here__body"><strong><?php echo esc_html( get_the_title( $p ) ); ?></strong><small><?php echo esc_html( cwm_reading_time( $p->ID ) ); ?> min read</small></span>
							<?php echo cwm_icon( 'arrow-right' ); // phpcs:ignore ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>
<?php endif; ?>

<?php
$series_terms = get_terms(
	array(
		'taxonomy'   => 'series',
		'hide_empty' => true,
	)
);
if ( $series_terms && ! is_wp_error( $series_terms ) ) :
	?>
	<section class="section" id="series">
		<div class="container">
			<?php cwm_section_head( 'Read in order', 'Article series' ); ?>
			<div class="series-grid">
				<?php foreach ( $series_terms as $term ) : ?>
					<?php
					$ids = cwm_series_posts( $term->term_id );
					if ( ! $ids ) {
						continue;
					}
					?>
					<article class="series-card">
						<p class="series-card__count"><?php echo cwm_icon( 'layers' ); // phpcs:ignore ?> <?php echo count( $ids ); ?> parts</p>
						<h3><?php echo esc_html( $term->name ); ?></h3>
						<?php if ( $term->description ) : ?>
							<p><?php echo esc_html( $term->description ); ?></p>
						<?php endif; ?>
						<ol>
							<?php foreach ( $ids as $n => $id ) : ?>
								<li><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><span>Part <?php echo (int) $n + 1; ?></span><?php echo esc_html( get_the_title( $id ) ); ?></a></li>
							<?php endforeach; ?>
						</ol>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section section--tint" id="articles">
	<div class="container">
		<?php cwm_section_head( 'Newest first', 'All ESG articles' ); ?>
		<?php
		$topics = get_terms(
			array(
				'taxonomy'   => 'esg_topic',
				'hide_empty' => true,
			)
		);
		if ( $topics && ! is_wp_error( $topics ) ) :
			?>
			<nav class="chips" aria-label="Filter by topic">
				<a class="chip<?php echo ! $topic ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_permalink() . '#articles' ); ?>">All topics</a>
				<?php foreach ( $topics as $t ) : ?>
					<a class="chip<?php echo $topic === $t->slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'topic', $t->slug, get_permalink() ) . '#articles' ); ?>"><?php echo esc_html( $t->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
		<?php
		$args = array(
			'post_type'           => 'post',
			'posts_per_page'      => 9,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
			'category_name'       => 'esg-reporting',
		);
		if ( $topic ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => 'esg_topic',
					'field'    => 'slug',
					'terms'    => array( $topic ),
				),
			);
		}
		$articles = new WP_Query( $args );
		if ( $articles->have_posts() ) :
			?>
			<div class="grid grid--3">
				<?php
				while ( $articles->have_posts() ) {
					$articles->the_post();
					cwm_insight_card();
				}
				wp_reset_postdata();
				?>
			</div>
			<?php
			if ( $articles->max_num_pages > 1 ) {
				$links = paginate_links(
					array(
						'base'      => add_query_arg( 'pg', '%#%', get_permalink() ) . '#articles',
						'format'    => '',
						'current'   => $paged,
						'total'     => $articles->max_num_pages,
						'type'      => 'array',
						'add_args'  => $topic ? array( 'topic' => $topic ) : array(),
						'prev_text' => cwm_icon( 'chevron-left' ) . '<span class="sr-only">Previous</span>',
						'next_text' => '<span class="sr-only">Next</span>' . cwm_icon( 'chevron-right' ),
					)
				);
				echo '<nav class="pagination" aria-label="Pages"><ul><li>' . implode( '</li><li>', $links ) . '</li></ul></nav>'; // phpcs:ignore
			}
			?>
		<?php else : ?>
			<p class="muted">No ESG articles on this topic yet.</p>
		<?php endif; ?>
	</div>
</section>

<?php
$glossary = get_posts(
	array(
		'post_type'   => 'glossary_term',
		'numberposts' => -1,
		'orderby'     => 'title',
		'order'       => 'ASC',
	)
);
if ( $glossary ) :
	$letters = array();
	foreach ( $glossary as $g ) {
		$letters[ strtoupper( mb_substr( $g->post_title, 0, 1 ) ) ][] = $g;
	}
	?>
	<section class="section" id="glossary">
		<div class="container">
			<?php cwm_section_head( 'Plain-language definitions', 'ESG glossary' ); ?>
			<div class="glossary-tools">
				<label class="sr-only" for="glossary-filter">Find a term</label>
				<div class="filters__search"><?php echo cwm_icon( 'search' ); // phpcs:ignore ?><input id="glossary-filter" type="search" placeholder="Find a term" data-glossary-filter></div>
				<nav class="letters" aria-label="Glossary letters">
					<?php foreach ( array_keys( $letters ) as $l ) : ?>
						<a href="#glossary-<?php echo esc_attr( strtolower( $l ) ); ?>"><?php echo esc_html( $l ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>
			<div class="glossary">
				<?php foreach ( $letters as $l => $terms ) : ?>
					<div class="glossary__letter" id="glossary-<?php echo esc_attr( strtolower( $l ) ); ?>">
						<h3 class="glossary__heading"><?php echo esc_html( $l ); ?></h3>
						<dl>
							<?php foreach ( $terms as $g ) : ?>
								<div class="glossary__term" data-term="<?php echo esc_attr( strtolower( $g->post_title ) ); ?>">
									<dt><?php echo esc_html( $g->post_title ); ?></dt>
									<dd><?php echo wp_kses_post( wpautop( do_shortcode( $g->post_content ) ) ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
$downloads = array_filter(
	get_posts(
		array(
			'post_type'        => 'resource',
			'numberposts'      => -1,
			'meta_key'         => '_cwm_esg_hub',
			'meta_value'       => '1',
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
			'suppress_filters' => false,
		)
	),
	function ( $r ) {
		return (bool) cwm_file_url( cwm_meta( 'file', $r->ID ) );
	}
);
if ( $downloads ) :
	?>
	<section class="section section--tint" id="downloads">
		<div class="container">
			<?php cwm_section_head( 'Free to download', 'Checklists and templates' ); ?>
			<div class="resources">
				<?php
				foreach ( $downloads as $r ) {
					cwm_resource_card( $r );
				}
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
cwm_cta( cwm_meta( 'cta_title' ) ? cwm_meta( 'cta_title' ) : 'Getting ready to report on ESG?', cwm_meta( 'cta_text' ) ? cwm_meta( 'cta_text' ) : 'Whether a lender has asked for ESG information or you are preparing your first sustainability report, we can help you start in the right place.', $esg_service );
get_footer();
