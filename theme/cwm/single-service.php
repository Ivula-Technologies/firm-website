<?php
/**
 * One template for every service page.
 */

get_header();
the_post();
$sid      = get_the_ID();
$is_audit = 'audit' === cwm_meta( 'role' );
$is_tax   = 'tax' === cwm_meta( 'role' );
$extra    = '<div class="banner__actions"><a class="btn btn--primary" href="#enquire">Enquire about this service</a>';
$pdf      = cwm_file_url( cwm_meta( 'pdf' ) );
if ( $pdf ) {
	$extra .= '<a class="btn btn--outline-light" href="' . esc_url( $pdf ) . '" download>' . cwm_icon( 'download' ) . ' Download service profile (PDF)</a>';
}
$extra .= '</div>';
cwm_banner( get_the_title(), cwm_meta( 'promise' ), array( 'icon' => cwm_meta( 'icon' ), 'extra' => $extra, 'class' => 'banner--service' ) );

$subs = array_filter(
	(array) cwm_meta( 'subservices' ),
	function ( $row ) {
		return ! empty( $row['title'] ) && ( empty( $row['assurance'] ) || cwm_audit_visible() );
	}
);
$steps = (array) cwm_meta( 'steps' );
$faqs  = (array) cwm_meta( 'faqs' );
?>

<?php if ( $is_audit && 'partner' === cwm_audit_state() ) : ?>
	<div class="notice-bar" role="note">
		<div class="container"><?php echo cwm_icon( 'info' ); // phpcs:ignore ?><p>Audit and assurance engagements are carried out by <strong><?php echo esc_html( cwm_opt( 'audit_partner' ) ); ?></strong>, licensed by ICPAK.</p></div>
	</div>
<?php elseif ( $is_audit && 'live' === cwm_audit_state() && cwm_opt( 'audit_licence' ) ) : ?>
	<div class="notice-bar notice-bar--licence" role="note">
		<div class="container"><?php echo cwm_icon( 'badge-check' ); // phpcs:ignore ?><p><?php echo esc_html( cwm_opt( 'audit_licence' ) ); ?></p></div>
	</div>
<?php endif; ?>

<div class="section">
	<div class="container service-layout">
		<div class="service-main">
			<section class="service-block" id="overview">
				<p class="eyebrow">Who it is for</p>
				<p class="lead"><?php echo esc_html( cwm_meta( 'who_for' ) ); ?></p>
				<?php if ( trim( get_the_content() ) ) : ?>
					<div class="prose"><?php the_content(); ?></div>
				<?php endif; ?>
			</section>

			<?php if ( $subs ) : ?>
				<section class="service-block" id="what-we-do">
					<h2 class="block-title">What we do</h2>
					<ul class="subservices">
						<?php foreach ( $subs as $row ) : ?>
							<li>
								<span class="subservices__tick"><?php echo cwm_icon( 'check' ); // phpcs:ignore ?></span>
								<div><h3><?php echo esc_html( $row['title'] ); ?></h3><p><?php echo esc_html( $row['text'] ); ?></p></div>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( $steps ) : ?>
				<section class="service-block" id="how-we-work">
					<h2 class="block-title">How we work</h2>
					<ol class="steps">
						<?php foreach ( $steps as $i => $st ) : ?>
							<li class="step"><span class="step__num"><?php echo (int) $i + 1; ?></span><div><h3><?php echo esc_html( $st['title'] ); ?></h3><p><?php echo esc_html( $st['text'] ); ?></p></div></li>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endif; ?>

			<?php if ( $pdf ) : ?>
				<div class="download-inline">
					<?php echo cwm_icon( 'file-text' ); // phpcs:ignore ?>
					<div><strong><?php the_title(); ?>: service profile</strong><span>PDF<?php echo cwm_file_size( cwm_meta( 'pdf' ) ) ? ', ' . esc_html( cwm_file_size( cwm_meta( 'pdf' ) ) ) : ''; ?></span></div>
					<a class="btn btn--primary" href="<?php echo esc_url( $pdf ); ?>" download><?php echo cwm_icon( 'download' ); // phpcs:ignore ?> Download service profile (PDF)</a>
				</div>
			<?php endif; ?>

			<?php if ( $faqs ) : ?>
				<section class="service-block" id="faqs">
					<h2 class="block-title">Frequently asked questions</h2>
					<div class="accordion">
						<?php foreach ( $faqs as $i => $faq ) : ?>
							<details class="accordion__item"<?php echo 0 === $i ? ' open' : ''; ?>>
								<summary><?php echo esc_html( $faq['q'] ); ?><?php echo cwm_icon( 'plus', 'accordion__icon' ); // phpcs:ignore ?></summary>
								<div class="accordion__body"><?php echo cwm_format( $faq['a'] ); // phpcs:ignore ?></div>
							</details>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
		</div>

		<aside class="service-aside">
			<div class="aside-card aside-card--sticky">
				<p class="eyebrow">On this page</p>
				<ul class="toc">
					<li><a href="#overview">Who it is for</a></li>
					<?php echo $subs ? '<li><a href="#what-we-do">What we do</a></li>' : ''; ?>
					<?php echo $steps ? '<li><a href="#how-we-work">How we work</a></li>' : ''; ?>
					<?php echo $faqs ? '<li><a href="#faqs">FAQs</a></li>' : ''; ?>
					<?php echo $is_tax ? '<li><a href="#recent-decisions">Recent tax decisions</a></li>' : ''; ?>
					<li><a href="#related">Related insights</a></li>
					<li><a href="#enquire">Enquire</a></li>
				</ul>
				<?php cwm_key_contact( cwm_meta( 'key_contact' ) ); ?>
				<div class="aside-card__cta">
					<p>Prefer to talk?</p>
					<a href="<?php echo esc_url( cwm_tel_url() ); ?>"><?php echo cwm_icon( 'phone' ); // phpcs:ignore ?> <?php echo esc_html( cwm_opt( 'phone' ) ); ?></a>
					<?php if ( cwm_whatsapp_url() ) : ?>
						<a href="<?php echo esc_url( cwm_whatsapp_url( 'Hello, I have a question about ' . get_the_title() . '.' ) ); ?>" target="_blank" rel="noopener"><?php echo cwm_icon( 'whatsapp' ); // phpcs:ignore ?> WhatsApp us</a>
					<?php endif; ?>
				</div>
			</div>
		</aside>
	</div>
</div>

<?php
if ( $is_tax ) :
	$cases = cwm_recent_cases( 3 );
	if ( $cases->have_posts() ) :
		$case_cat = get_category_by_slug( 'case-analysis' );
		?>
		<section class="section section--tint" id="recent-decisions">
			<div class="container">
				<?php cwm_section_head( 'Tax case analysis', 'Recent tax decisions', 'What the Tax Appeals Tribunal and the courts decided, and what it means for you.', $case_cat ? array( 'All case analyses', get_category_link( $case_cat ) ) : array() ); ?>
				<div class="grid grid--3">
					<?php
					while ( $cases->have_posts() ) {
						$cases->the_post();
						get_template_part( 'template-parts/case-card' );
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
endif;
?>

<?php
// Case analyses already have their own section on the Tax page.
$related = cwm_service_insights( get_post( $sid ), 3, '', $is_tax ? 'case-analysis' : '' );
?>
<section class="section" id="related">
	<div class="container">
		<?php cwm_section_head( 'Insights', 'Related articles', '', array( 'View all', cwm_insights_url( array( 'service' => get_post_field( 'post_name', $sid ) ) ) ) ); ?>
		<?php if ( $related->have_posts() ) : ?>
			<div class="grid grid--3">
				<?php
				while ( $related->have_posts() ) {
					$related->the_post();
					cwm_insight_card();
				}
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="muted">Articles on <?php echo esc_html( strtolower( get_the_title( $sid ) ) ); ?> will appear here as we publish them.</p>
		<?php endif; ?>
	</div>
</section>

<section class="section section--tint" id="enquire">
	<div class="container enquiry">
		<div class="enquiry__intro">
			<p class="eyebrow">Enquire</p>
			<h2 class="section-title">Talk to us about <?php echo esc_html( strtolower( get_the_title( $sid ) ) ); ?></h2>
			<p>Tell us a little about your organisation and what you need. We will reply within one working day with next steps, and a fee proposal if you want one.</p>
			<ul class="checklist">
				<li><?php echo cwm_icon( 'check' ); // phpcs:ignore ?> No obligation, no jargon</li>
				<li><?php echo cwm_icon( 'check' ); // phpcs:ignore ?> A clear scope and fixed fee where possible</li>
				<li><?php echo cwm_icon( 'check' ); // phpcs:ignore ?> Your details stay confidential</li>
			</ul>
		</div>
		<div class="form-card">
			<?php cwm_form( 'enquiry', array( 'service' => get_post_field( 'post_name', $sid ) ) ); ?>
		</div>
	</div>
</section>

<?php
cwm_cta( '', '', $sid );
get_footer();
