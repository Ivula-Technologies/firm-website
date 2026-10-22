<?php
/**
 * Compact card for a tax case analysis.
 */

$pid     = get_the_ID();
$outcome = cwm_meta( 'case_outcome' );
$date    = cwm_meta( 'case_date' );
?>
<article class="card card--case">
	<div class="card__body">
		<div class="card__meta">
			<span class="tag tag--case"><?php echo cwm_icon( 'gavel' ); // phpcs:ignore ?>Case analysis</span>
			<?php if ( $outcome ) : ?>
				<span class="outcome outcome--<?php echo esc_attr( $outcome ); ?>"><?php echo esc_html( cwm_case_label( 'outcome', $outcome ) ); ?></span>
			<?php endif; ?>
		</div>
		<h3 class="card__title"><a class="stretched" href="<?php the_permalink(); ?>"><?php echo esc_html( cwm_meta( 'case_name' ) ? cwm_meta( 'case_name' ) : get_the_title() ); ?></a></h3>
		<?php if ( cwm_meta( 'case_citation' ) ) : ?>
			<p class="card__citation"><?php echo esc_html( cwm_meta( 'case_citation' ) ); ?></p>
		<?php endif; ?>
		<dl class="case-mini">
			<?php if ( cwm_meta( 'case_court' ) ) : ?>
				<div><dt>Court</dt><dd><?php echo esc_html( cwm_case_label( 'court', cwm_meta( 'case_court' ) ) ); ?></dd></div>
			<?php endif; ?>
			<?php if ( cwm_meta( 'case_taxtype' ) ) : ?>
				<div><dt>Tax</dt><dd><?php echo esc_html( cwm_case_label( 'taxtype', cwm_meta( 'case_taxtype' ) ) ); ?></dd></div>
			<?php endif; ?>
			<?php if ( $date ) : ?>
				<div><dt>Decided</dt><dd><?php echo esc_html( mysql2date( 'j M Y', $date ) ); ?></dd></div>
			<?php endif; ?>
		</dl>
	</div>
</article>
