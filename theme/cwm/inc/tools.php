<?php
/**
 * Free tools: PAYE and VAT calculators, a KRA deadline countdown and an ESG readiness check.
 * Rates live in Firm settings > Free tools so the firm can update them after each Finance Act.
 */

defined( 'ABSPATH' ) || exit;

/** True when the tools are switched on and their page exists. */
function cwm_tools_on() {
	return (bool) cwm_opt( 'tools_on' ) && cwm_page_id( 'tools' );
}

/** Rates for the calculators, as plain numbers. */
function cwm_tools_rates() {
	$bands = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) cwm_opt( 'paye_bands' ) ) as $line ) {
		$parts = array_map( 'trim', explode( ':', $line ) );
		if ( 2 !== count( $parts ) || '' === $parts[1] ) {
			continue;
		}
		$bands[] = array(
			'upto' => '*' === $parts[0] ? null : (float) str_replace( ',', '', $parts[0] ),
			'rate' => (float) $parts[1],
		);
	}
	$num = function ( $key ) {
		return (float) cwm_opt( $key );
	};
	return array(
		'bands'   => $bands,
		'relief'  => $num( 'personal_relief' ),
		'shif'    => $num( 'shif_rate' ),
		'shifMin' => $num( 'shif_min' ),
		'ahl'     => $num( 'ahl_rate' ),
		'nssf'    => $num( 'nssf_rate' ),
		'lel'     => $num( 'nssf_lel' ),
		'uel'     => $num( 'nssf_uel' ),
		'vat'     => $num( 'vat_rate' ),
		'label'   => (string) cwm_opt( 'rates_label' ),
	);
}

/** Make sure the Free tools page exists (new installs and sites set up before the tools existed). */
function cwm_tools_ensure_page() {
	if ( cwm_page_id( 'tools' ) ) {
		return;
	}
	$id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'Free tools',
			'post_name'   => 'free-tools',
			'menu_order'  => 5,
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', 'page-templates/tools.php' );
		update_post_meta( $id, '_cwm_intro', 'Quick, free calculators and reminders for Kenyan businesses. Work it out in a minute, then talk to us about the rest.' );
	}
}
add_action(
	'init',
	function () {
		if ( get_option( 'cwm_seeded' ) && (int) get_option( 'cwm_schema' ) < 2 ) {
			cwm_tools_ensure_page();
			update_option( 'cwm_schema', 2 );
		}
	},
	30
);

/** Upcoming KRA deadlines, soonest first, worked out in Nairobi time. */
function cwm_kra_deadlines() {
	$tz    = new DateTimeZone( 'Africa/Nairobi' );
	$today = new DateTimeImmutable( 'today', $tz );
	$rules = array(
		array(
			'name'   => 'PAYE, SHIF, housing levy and NSSF',
			'note'   => 'Pay and file for last month\'s payroll.',
			'day'    => 9,
			'months' => range( 1, 12 ),
			'icon'   => 'wallet',
		),
		array(
			'name'   => 'VAT, turnover tax and excise duty',
			'note'   => 'Returns and payment for last month.',
			'day'    => 20,
			'months' => range( 1, 12 ),
			'icon'   => 'receipt-text',
		),
		array(
			'name'   => 'Instalment tax',
			'note'   => 'One of four payments in the year, for a December year-end.',
			'day'    => 20,
			'months' => array( 4, 6, 9, 12 ),
			'icon'   => 'coins',
		),
		array(
			'name'   => 'Annual income tax returns',
			'note'   => 'Individuals, and companies with a December year-end.',
			'day'    => 30,
			'months' => array( 6 ),
			'icon'   => 'file-check',
		),
	);
	$out = array();
	foreach ( $rules as $r ) {
		$y = (int) $today->format( 'Y' );
		$m = (int) $today->format( 'n' );
		for ( $i = 0; $i < 24; $i++ ) {
			if ( in_array( $m, $r['months'], true ) ) {
				$d = $today->setDate( $y, $m, $r['day'] );
				if ( $d >= $today ) {
					break;
				}
			}
			++$m;
			if ( $m > 12 ) {
				$m = 1;
				++$y;
			}
		}
		$days  = (int) $today->diff( $d )->days;
		$out[] = array_merge(
			$r,
			array(
				'date'    => $d,
				'days'    => $days,
				'weekend' => (int) $d->format( 'N' ) >= 6 ? $d->format( 'l' ) : '',
			)
		);
	}
	usort(
		$out,
		function ( $a, $b ) {
			return $a['days'] - $b['days'];
		}
	);
	return $out;
}

/** "in 12 days", "tomorrow" or "today". */
function cwm_days_phrase( $days ) {
	if ( 0 === $days ) {
		return 'today';
	}
	return 1 === $days ? 'tomorrow' : 'in ' . $days . ' days';
}

/** Deadline countdown. Compact for the homepage, full for the tools page. */
function cwm_tool_deadlines( $compact = false ) {
	$list = cwm_kra_deadlines();
	$next = array_shift( $list );
	?>
	<div class="deadlines<?php echo $compact ? ' deadlines--compact' : ''; ?>">
		<div class="deadlines__next">
			<p class="deadlines__kicker"><?php echo cwm_icon( 'calendar' ); // phpcs:ignore ?> Next KRA deadline</p>
			<p class="deadlines__count"><span class="deadlines__num" data-count="<?php echo (int) $next['days']; ?>"><?php echo (int) $next['days']; ?></span><span class="deadlines__unit"><?php echo 1 === $next['days'] ? 'day' : 'days'; ?></span></p>
			<p class="deadlines__name"><?php echo esc_html( $next['name'] ); ?></p>
			<p class="deadlines__date"><?php echo esc_html( wp_date( 'l j F Y', $next['date']->getTimestamp(), $next['date']->getTimezone() ) ); ?><?php echo $next['weekend'] ? ' <span class="deadlines__flag">Falls on a ' . esc_html( $next['weekend'] ) . ': file early</span>' : ''; ?></p>
		</div>
		<ul class="deadlines__list">
			<?php foreach ( $compact ? array_slice( $list, 0, 2 ) : $list as $d ) : ?>
				<li>
					<span class="deadlines__icon"><?php echo cwm_icon( $d['icon'] ); // phpcs:ignore ?></span>
					<span class="deadlines__what"><strong><?php echo esc_html( $d['name'] ); ?></strong><?php echo $compact ? '' : '<small>' . esc_html( $d['note'] ) . '</small>'; ?></span>
					<span class="deadlines__when"><?php echo esc_html( wp_date( 'j M', $d['date']->getTimestamp(), $d['date']->getTimezone() ) ); ?><small><?php echo esc_html( cwm_days_phrase( $d['days'] ) ); ?></small></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if ( ! $compact ) : ?>
			<p class="tool__note"><?php echo cwm_icon( 'info' ); // phpcs:ignore ?> Withholding tax is due within five working days of paying the supplier. Dates are for December year-ends; if yours differs, ask us for your own calendar.</p>
		<?php endif; ?>
	</div>
	<?php
}

/** PAYE and net pay calculator. */
function cwm_tool_paye() {
	?>
	<form class="tool tool--paye" data-tool="paye" novalidate>
		<div class="tool__inputs">
			<label for="paye-gross">Gross monthly salary (KES)</label>
			<div class="money-input"><span>KES</span><input id="paye-gross" type="number" inputmode="decimal" min="0" step="any" value="100000"></div>
			<input id="paye-range" class="tool__range" type="range" min="10000" max="1000000" step="5000" value="100000" aria-label="Gross monthly salary slider">
			<div class="tool__chips" role="group" aria-label="Try a salary">
				<button type="button" data-set="30000">30k</button><button type="button" data-set="75000">75k</button><button type="button" data-set="150000">150k</button><button type="button" data-set="400000">400k</button>
			</div>
		</div>
		<div class="tool__result" aria-live="polite">
			<p class="tool__big"><small>Take-home pay</small><span data-out="net">KES 0</span></p>
			<div class="paybar" aria-hidden="true">
				<span class="paybar__net" data-bar="net"></span><span class="paybar__paye" data-bar="paye"></span><span class="paybar__stat" data-bar="stat"></span>
			</div>
			<dl class="tool__rows">
				<div><dt><i class="dot dot--paye"></i>PAYE after relief</dt><dd data-out="paye">0</dd></div>
				<div><dt><i class="dot dot--stat"></i>NSSF</dt><dd data-out="nssf">0</dd></div>
				<div><dt><i class="dot dot--stat"></i>SHIF</dt><dd data-out="shif">0</dd></div>
				<div><dt><i class="dot dot--stat"></i>Housing levy</dt><dd data-out="ahl">0</dd></div>
				<div class="tool__rows-total"><dt>Cost to the employer</dt><dd data-out="employer">0</dd></div>
			</dl>
			<p class="tool__fine">Effective tax rate <strong data-out="rate">0%</strong>. Take-home pay over a year: <strong data-out="annual">0</strong>.</p>
		</div>
	</form>
	<?php
}

/** VAT calculator. */
function cwm_tool_vat() {
	?>
	<form class="tool tool--vat" data-tool="vat" novalidate>
		<div class="tool__inputs">
			<label for="vat-amount">Amount (KES)</label>
			<div class="money-input"><span>KES</span><input id="vat-amount" type="number" inputmode="decimal" min="0" step="any" value="58000"></div>
			<fieldset class="seg">
				<legend class="sr-only">Does the amount include VAT?</legend>
				<label><input type="radio" name="vat-mode" value="add" checked> <span>Add VAT</span></label>
				<label><input type="radio" name="vat-mode" value="remove"> <span>Remove VAT</span></label>
			</fieldset>
		</div>
		<dl class="tool__rows tool__rows--vat" aria-live="polite">
			<div><dt>Before VAT</dt><dd data-out="net">0</dd></div>
			<div><dt>VAT at <span data-out="vatrate">16%</span></dt><dd data-out="vat">0</dd></div>
			<div class="tool__rows-total"><dt>Total</dt><dd data-out="gross">0</dd></div>
		</dl>
	</form>
	<?php
}

/** ESG readiness questions: text, and the next step when the answer is not yet "yes". */
function cwm_esg_questions() {
	return array(
		array( 'Do you know where your business uses the most energy, fuel and water?', 'Measure your energy, fuel and water use for one year. It is the base of your carbon footprint.' ),
		array( 'Have you agreed which ESG topics matter most to your business and its stakeholders?', 'Run a short materiality assessment so you report on what matters, not on everything.' ),
		array( 'Do you have written policies on the environment, health and safety, and anti-bribery?', 'Put the basic policies in writing and have the board approve them.' ),
		array( 'Does a named person own ESG, with management or the board reviewing progress?', 'Name an owner and put ESG on the board or management agenda each quarter.' ),
		array( 'Do you collect ESG data regularly, the same way each time?', 'Set up a simple data sheet with sources and owners, filled in every quarter.' ),
		array( 'Have you shared ESG information with a lender, investor, customer or in an annual report?', 'Prepare a short ESG summary you can send when lenders or customers ask.' ),
	);
}

/** ESG readiness check. */
function cwm_tool_esg() {
	$qs = cwm_esg_questions();
	?>
	<form class="tool tool--esg" data-tool="esg" novalidate>
		<ol class="quiz">
			<?php foreach ( $qs as $i => $q ) : ?>
				<li class="quiz__q" data-tip="<?php echo esc_attr( $q[1] ); ?>">
					<p id="esg-q<?php echo (int) $i; ?>"><?php echo esc_html( $q[0] ); ?></p>
					<div class="seg seg--3" role="radiogroup" aria-labelledby="esg-q<?php echo (int) $i; ?>">
						<label><input type="radio" name="esg<?php echo (int) $i; ?>" value="2"> <span>Yes</span></label>
						<label><input type="radio" name="esg<?php echo (int) $i; ?>" value="1"> <span>Partly</span></label>
						<label><input type="radio" name="esg<?php echo (int) $i; ?>" value="0"> <span>Not yet</span></label>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
		<div class="quiz__result" aria-live="polite">
			<div class="ring" aria-hidden="true">
				<svg viewBox="0 0 120 120"><circle class="ring__track" cx="60" cy="60" r="52"/><circle class="ring__bar" cx="60" cy="60" r="52"/></svg>
				<span class="ring__num"><span data-out="score">0</span><small>/ <?php echo count( $qs ) * 2; ?></small></span>
			</div>
			<div class="quiz__verdict">
				<p class="quiz__answered"><span data-out="answered">0</span> of <?php echo count( $qs ); ?> answered</p>
				<h3 data-out="band">Answer the questions to see your score</h3>
				<p data-out="advice">Six quick questions. Nothing is sent anywhere; your answers stay on this page.</p>
				<ul class="quiz__steps" data-out="steps"></ul>
				<p class="quiz__cta" hidden><a class="btn btn--primary" href="<?php echo esc_url( cwm_services_url_by_role( 'esg' ) ); ?>">Talk to us about your result</a> <a class="link-arrow" href="<?php echo esc_url( cwm_page_url( 'esg-hub' ) ); ?>">Read the ESG reporting hub <?php echo cwm_icon( 'arrow-right' ); // phpcs:ignore ?></a></p>
			</div>
		</div>
	</form>
	<?php
}

/** The ESG service page, or the services page if there is none. */
function cwm_services_url_by_role( $role ) {
	$s = cwm_service_by_role( $role );
	return $s ? get_permalink( $s ) . '#enquire' : cwm_page_url( 'services' );
}

/** Homepage block: next deadline plus links to the tools. */
function cwm_home_tools() {
	if ( ! cwm_tools_on() ) {
		return;
	}
	$url   = cwm_page_url( 'tools' );
	$tools = array(
		array( 'calculator', 'PAYE and take-home pay', 'See what an employee takes home after PAYE, NSSF, SHIF and the housing levy.', '#paye' ),
		array( 'receipt-text', 'VAT calculator', 'Add or remove VAT from any amount, with the sums shown.', '#vat' ),
		array( 'leaf', 'ESG readiness check', 'Six questions, an instant score and your next three steps.', '#esg-check' ),
	);
	?>
	<section class="section section--tools" id="free-tools">
		<div class="container">
			<?php cwm_section_head( 'Free tools', 'Work it out in a minute', 'Calculators and reminders for Kenyan businesses. Free, and nothing to sign up for.', array( 'All tools', $url ) ); ?>
			<div class="home-tools">
				<?php cwm_tool_deadlines( true ); ?>
				<ul class="tool-links">
					<?php foreach ( $tools as $t ) : ?>
						<li><a class="tool-link" href="<?php echo esc_url( $url . $t[3] ); ?>">
							<span class="tool-link__icon"><?php echo cwm_icon( $t[0] ); // phpcs:ignore ?></span>
							<span class="tool-link__body"><strong><?php echo esc_html( $t[1] ); ?></strong><span><?php echo esc_html( $t[2] ); ?></span></span>
							<span class="tool-link__go"><?php echo cwm_icon( 'arrow-right' ); // phpcs:ignore ?></span>
						</a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>
	<?php
}

/** Load the tools script where a tool is on the page. */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! cwm_tools_on() || ! ( is_front_page() || is_page( cwm_page_id( 'tools' ) ) ) ) {
			return;
		}
		wp_enqueue_script( 'cwm-tools', CWM_URI . '/assets/js/tools.js', array(), CWM_VERSION, array( 'strategy' => 'defer' ) );
		wp_add_inline_script( 'cwm-tools', 'window.cwmRates=' . wp_json_encode( cwm_tools_rates() ) . ';', 'before' );
	}
);

/* Hide the page itself when the tools are switched off. */
add_action(
	'template_redirect',
	function () {
		if ( ! cwm_opt( 'tools_on' ) && cwm_page_id( 'tools' ) && is_page( cwm_page_id( 'tools' ) ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
		}
	}
);
