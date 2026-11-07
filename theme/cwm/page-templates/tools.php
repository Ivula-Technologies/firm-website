<?php
/**
 * Template Name: Free tools
 */

get_header();
the_post();
$rates = cwm_tools_rates();
$nav   = array(
	'#paye'       => array( 'calculator', 'PAYE' ),
	'#vat'        => array( 'receipt-text', 'VAT' ),
	'#deadlines'  => array( 'calendar', 'KRA deadlines' ),
	'#esg-check'  => array( 'leaf', 'ESG check' ),
);
$chips = '<nav class="tool-nav" aria-label="Tools on this page">';
foreach ( $nav as $href => $n ) {
	$chips .= '<a href="' . esc_attr( $href ) . '">' . cwm_icon( $n[0] ) . esc_html( $n[1] ) . '</a>';
}
$chips .= '</nav>';
cwm_banner( get_the_title(), cwm_field( 'intro', 'Quick, free calculators and reminders for Kenyan businesses.' ), array( 'eyebrow' => 'Free tools', 'extra' => $chips ) );
?>
<div class="section tools-page">
	<div class="container">
		<section class="tool-block" id="paye">
			<?php cwm_section_head( 'Payroll', 'PAYE and take-home pay', 'Type a gross monthly salary. We take off NSSF, SHIF and the housing levy, then PAYE after personal relief.' ); ?>
			<?php cwm_tool_paye(); ?>
		</section>

		<section class="tool-block" id="vat">
			<?php cwm_section_head( 'VAT', 'VAT calculator', 'Add VAT to a price, or take it out of an amount that already includes it.' ); ?>
			<?php cwm_tool_vat(); ?>
		</section>

		<section class="tool-block" id="deadlines">
			<?php cwm_section_head( 'Compliance', 'KRA deadlines coming up', 'The main filing and payment dates, counted down from today in Nairobi time.' ); ?>
			<?php cwm_tool_deadlines(); ?>
		</section>

		<section class="tool-block" id="esg-check">
			<?php cwm_section_head( 'ESG', 'How ready are you for ESG reporting?', 'Lenders, investors and large customers are asking. Answer six questions to see where you stand and what to do next.' ); ?>
			<?php cwm_tool_esg(); ?>
		</section>

		<aside class="tools-disclaimer">
			<?php echo cwm_icon( 'info' ); // phpcs:ignore ?>
			<p>Rates checked as at <strong><?php echo esc_html( $rates['label'] ); ?></strong>. These tools give an estimate for a typical case and are not tax advice. Allowances, reliefs, exemptions and your own facts can change the answer, so talk to us before you rely on a figure.</p>
		</aside>

		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="prose prose--intro"><?php the_content(); ?></div>
		<?php endif; ?>
	</div>
</div>
<?php
cwm_cta( 'Want us to run the numbers properly?', 'Send us your figures and we will come back within one working day with a clear answer and a fixed quote.' );
get_footer();
