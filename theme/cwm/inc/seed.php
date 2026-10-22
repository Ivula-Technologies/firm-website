<?php
/**
 * First-run setup: creates the pages, services, industries, categories and starter content
 * the brief asks for, so the site works the moment the theme is activated.
 * Sample insights are marked so they can be removed in one click (Firm settings > Launch).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'cwm_maybe_seed' );

/** Keep /insights/page/2/ working (the posts page shares its base with post URLs). */
add_action(
	'init',
	function () {
		$pid = (int) get_option( 'page_for_posts' );
		if ( $pid ) {
			$path = get_page_uri( $pid );
			if ( $path ) {
				add_rewrite_rule( '^' . preg_quote( $path, '#' ) . '/page/([0-9]+)/?$', 'index.php?pagename=' . $path . '&paged=$matches[1]', 'top' );
			}
		}
	}
);

function cwm_maybe_seed() {
	if ( get_option( 'cwm_seeded' ) ) {
		flush_rewrite_rules( false );
		return;
	}
	cwm_seed();
	update_option( 'cwm_seeded', CWM_VERSION );
	// Permalink options changed in this request: rebuild rules now and again on the next request.
	global $wp_rewrite;
	$wp_rewrite->init();
	cwm_register_types();
	flush_rewrite_rules( false );
	update_option( 'cwm_flush_rewrite', 1 );
}

/** Turn light markdown (## headings, - bullets, paragraphs) into block markup. */
function cwm_blocks( $text ) {
	$out = '';
	foreach ( preg_split( "/\n\s*\n/", trim( $text ) ) as $block ) {
		$block = trim( $block );
		if ( 0 === strpos( $block, '### ' ) ) {
			$out .= "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . substr( $block, 4 ) . "</h3>\n<!-- /wp:heading -->\n\n";
		} elseif ( 0 === strpos( $block, '## ' ) ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . substr( $block, 3 ) . "</h2>\n<!-- /wp:heading -->\n\n";
		} elseif ( 0 === strpos( $block, '- ' ) ) {
			$items = array_map(
				function ( $l ) {
					return '<li>' . trim( substr( trim( $l ), 2 ) ) . '</li>';
				},
				explode( "\n", $block )
			);
			$out  .= "<!-- wp:list -->\n<ul>" . implode( '', $items ) . "</ul>\n<!-- /wp:list -->\n\n";
		} elseif ( 0 === strpos( $block, '> ' ) ) {
			$out .= "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>" . substr( $block, 2 ) . "</p></blockquote>\n<!-- /wp:quote -->\n\n";
		} else {
			$out .= "<!-- wp:paragraph -->\n<p>" . $block . "</p>\n<!-- /wp:paragraph -->\n\n";
		}
	}
	return $out;
}

function cwm_seed_post( $args, $meta = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'post_status' => 'publish',
			'post_author' => 1,
		)
	);
	$id   = wp_insert_post( wp_slash( $args ), true );
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, ( 0 === strpos( $k, '_' ) ? $k : '_cwm_' . $k ), $v );
	}
	return $id;
}

function cwm_rows( $pairs, $keys = array( 'title', 'text' ) ) {
	$out = array();
	foreach ( $pairs as $p ) {
		$row = array();
		foreach ( $keys as $i => $k ) {
			$row[ $k ] = isset( $p[ $i ] ) ? $p[ $i ] : '';
		}
		$out[] = $row;
	}
	return $out;
}

function cwm_seed() {
	// Site basics.
	update_option( 'blogname', cwm_firm() );
	update_option( 'blogdescription', cwm_opt( 'tagline' ) );
	update_option( 'timezone_string', 'Africa/Nairobi' );
	update_option( 'date_format', 'j F Y' );
	update_option( 'time_format', 'g:i a' );
	update_option( 'start_of_week', 1 );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'permalink_structure', '/insights/%postname%/' );
	update_option( 'category_base', 'insights/category' );
	update_option( 'tag_base', 'insights/tag' );
	update_option( 'posts_per_page', 9 );
	if ( ! get_option( 'cwm_settings' ) ) {
		update_option( 'cwm_settings', cwm_setting_defaults() );
	}

	cwm_register_types();

	// Categories.
	$cats = array(
		'tax-alerts'    => array( 'Tax alerts', 'Changes in tax law, KRA practice and deadlines, explained for businesses.', 0 ),
		'esg-reporting' => array( 'ESG reporting', 'Sustainability reporting, climate disclosure and ESG practice for Kenyan organisations.', 0 ),
		'guides'        => array( 'Guides and explainers', 'Practical guides to accounting, payroll, compliance and running a business in Kenya.', 0 ),
		'news-events'   => array( 'News and events', 'Firm news, briefings and events.', 0 ),
	);
	$cat_ids = array();
	foreach ( $cats as $slug => $c ) {
		$t                = term_exists( $slug, 'category' );
		$t                = $t ? $t : wp_insert_term( $c[0], 'category', array( 'slug' => $slug, 'description' => $c[1] ) );
		$cat_ids[ $slug ] = (int) $t['term_id'];
	}
	$t                         = term_exists( 'case-analysis', 'category' );
	$t                         = $t ? $t : wp_insert_term( 'Case analysis', 'category', array( 'slug' => 'case-analysis', 'parent' => $cat_ids['tax-alerts'], 'description' => 'Our analysis of decided tax cases from the Tax Appeals Tribunal and the courts: what was decided and what it means for you.' ) );
	$cat_ids['case-analysis'] = (int) $t['term_id'];
	update_option( 'default_category', $cat_ids['news-events'] );
	$uncat = get_term_by( 'slug', 'uncategorized', 'category' );
	if ( $uncat && (int) $uncat->term_id !== $cat_ids['news-events'] ) {
		wp_delete_term( $uncat->term_id, 'category' );
	}
	// Remove WordPress's sample content.
	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page' ) as $slug => $type ) {
		$p = get_page_by_path( $slug, OBJECT, $type );
		if ( $p ) {
			wp_delete_post( $p->ID, true );
		}
	}

	foreach ( array( 'Reporting standards', 'Climate and emissions', 'Social and people', 'Governance', 'Sustainable finance' ) as $topic ) {
		if ( ! term_exists( sanitize_title( $topic ), 'esg_topic' ) ) {
			wp_insert_term( $topic, 'esg_topic' );
		}
	}

	// Services.
	$services = cwm_seed_services();
	$svc_ids  = array();
	foreach ( $services as $i => $s ) {
		$svc_ids[ $s['slug'] ] = cwm_seed_post(
			array(
				'post_type'  => 'service',
				'post_title' => $s['title'],
				'post_name'  => $s['slug'],
				'menu_order' => $i + 1,
			),
			array(
				'role'            => $s['role'],
				'group'           => $s['group'],
				'icon'            => $s['icon'],
				'promise'         => $s['promise'],
				'who_for'         => $s['who'],
				'subservices'     => $s['subs'],
				'steps'           => $s['steps'],
				'faqs'            => $s['faqs'],
				'seo_description' => $s['seo'],
			)
		);
		// Create the matching service-line term (save_post ran before the meta existed).
		do_action( 'save_post_service', $svc_ids[ $s['slug'] ], get_post( $svc_ids[ $s['slug'] ] ), true );
	}

	// Industries.
	foreach ( cwm_seed_industries() as $i => $ind ) {
		$ids = array();
		foreach ( $ind[3] as $slug ) {
			if ( isset( $svc_ids[ $slug ] ) ) {
				$ids[] = $svc_ids[ $slug ];
			}
		}
		cwm_seed_post(
			array(
				'post_type'  => 'industry',
				'post_title' => $ind[0],
				'menu_order' => $i + 1,
			),
			array(
				'icon'     => $ind[1],
				'summary'  => $ind[2],
				'services' => $ids,
			)
		);
	}

	cwm_seed_pages();
	cwm_seed_insights( $cat_ids );
	cwm_seed_glossary();
}

function cwm_seed_services() {
	return array(
		array(
			'slug'    => 'accounting-and-bookkeeping',
			'title'   => 'Accounting and bookkeeping',
			'role'    => '',
			'group'   => 'money',
			'icon'    => 'calculator',
			'promise' => 'Books you can trust, every month.',
			'who'     => 'Businesses and institutions that want accurate, up-to-date accounts without running a full finance team.',
			'seo'     => 'Bookkeeping, management accounts and annual financial statements for Kenyan businesses and institutions. Books you can trust, every month.',
			'subs'    => cwm_rows(
				array(
					array( 'Monthly bookkeeping', 'We record sales, purchases, bank and M-Pesa transactions in QuickBooks, Xero, Sage or your own system, every month.' ),
					array( 'Bank and M-Pesa reconciliations', 'Every account matched to the statement, so the balance in your books is the balance in the bank.' ),
					array( 'Management accounts', 'A monthly profit and loss, balance sheet and cash flow, with a one-page commentary in plain language.' ),
					array( 'Annual financial statements', 'Statements under IFRS or IFRS for SMEs, ready for your auditors, lenders and KRA.' ),
					array( 'Accounting system set-up', 'Choosing, setting up and moving to cloud accounting, with a chart of accounts that fits how you work.' ),
					array( 'eTIMS and invoicing support', 'Making sure your invoices meet KRA\'s electronic tax invoice rules, so your expenses stay deductible.' ),
				)
			),
			'steps'   => cwm_rows(
				array(
					array( 'Review your records', 'We look at what you have today and agree what needs to be brought up to date.' ),
					array( 'Agree a monthly routine', 'Simple ways to send us receipts, statements and invoices, including from your phone.' ),
					array( 'Close the books every month', 'Transactions recorded and reconciled by an agreed date each month.' ),
					array( 'Report and explain', 'Management accounts in your inbox, and a short call to walk through what the numbers say.' ),
				)
			),
			'faqs'    => cwm_rows(
				array(
					array( 'Which accounting software do you work with?', 'QuickBooks Online, Xero, Sage, Zoho Books and Odoo. If you are on spreadsheets, we can work with them while we help you move to a proper system.' ),
					array( 'Can you catch up on books that are months or years behind?', 'Yes. We agree a catch-up plan and a fixed fee before we start, then bring you up to date and keep you there.' ),
					array( 'How do we send you documents?', 'Through a secure shared folder or straight into your accounting software. Receipts can be photographed and uploaded from a phone.' ),
				),
				array( 'q', 'a' )
			),
		),
		array(
			'slug'    => 'payroll-and-hr-support',
			'title'   => 'Payroll and HR support',
			'role'    => '',
			'group'   => 'money',
			'icon'    => 'wallet',
			'promise' => 'Everyone paid right, on time, every time.',
			'who'     => 'Employers who want payroll and statutory filings handled correctly.',
			'seo'     => 'Payroll processing, PAYE, SHIF, NSSF and Housing Levy filings and HR support for Kenyan employers. Everyone paid right, on time, every time.',
			'subs'    => cwm_rows(
				array(
					array( 'Monthly payroll', 'Gross-to-net calculations, payslips and bank or M-Pesa payment files, ready on time every month.' ),
					array( 'Statutory deductions and filings', 'PAYE, SHIF, NSSF, the Affordable Housing Levy and NITA, calculated correctly and filed by the deadline.' ),
					array( 'P9 forms and year-end', 'Annual P9 forms for every employee and a reconciliation of the year\'s PAYE returns.' ),
					array( 'HR policies and contracts', 'Employment contracts, HR manuals and policies in line with the Employment Act.' ),
					array( 'Payroll reviews', 'A check of past payroll for errors and exposure, before KRA or a former employee finds them.' ),
				)
			),
			'steps'   => cwm_rows(
				array(
					array( 'Collect staff data', 'Contracts, pay rates, benefits and statutory numbers, gathered securely.' ),
					array( 'Run a parallel month', 'We run payroll alongside your current process to prove the numbers match.' ),
					array( 'Process monthly', 'Payslips, payment files and reports, approved by you before anyone is paid.' ),
					array( 'File and report', 'Statutory returns filed on time, with receipts kept for your records.' ),
				)
			),
			'faqs'    => cwm_rows(
				array(
					array( 'When are statutory deductions due?', 'Most are due by the 9th of the following month. We work to a calendar that keeps you ahead of every deadline.' ),
					array( 'Is our payroll data kept confidential?', 'Yes. Payroll is handled by a small team on secure systems and is never shared beyond the people who need it.' ),
					array( 'Can you handle casual workers and contractors?', 'Yes, including withholding tax on contractors and the right treatment for casual and part-time staff.' ),
				),
				array( 'q', 'a' )
			),
		),
		array(
			'slug'    => 'tax',
			'title'   => 'Tax',
			'role'    => 'tax',
			'group'   => 'obligations',
			'icon'    => 'receipt-text',
			'promise' => 'Pay what is due. Nothing more, nothing missed.',
			'who'     => 'Businesses and individuals who want to stay right with KRA and plan ahead.',
			'seo'     => 'Tax compliance, planning, KRA reviews, objections and appeals for Kenyan businesses and individuals. Pay what is due. Nothing more, nothing missed.',
			'subs'    => cwm_rows(
				array(
					array( 'Tax compliance', 'Corporate tax, VAT, PAYE, withholding tax and instalment tax returns prepared and filed on iTax.' ),
					array( 'Tax planning', 'Structuring transactions, investments and pay so you pay what is due, and no more.' ),
					array( 'Tax health checks', 'A review of your filings to find and fix gaps before KRA does.' ),
					array( 'KRA reviews and objections', 'Support through KRA compliance checks and investigations, objections, and appeals to the Tax Appeals Tribunal.' ),
					array( 'Tax dispute resolution', 'Alternative dispute resolution with KRA and negotiation of settlements, penalties and interest.' ),
					array( 'Individual tax', 'Returns for directors, landlords, professionals and Kenyans with income from abroad.' ),
					array( 'Transfer pricing', 'Policies and documentation for groups with transactions between related companies.' ),
				)
			),
			'steps'   => cwm_rows(
				array(
					array( 'Understand your position', 'We review your returns, ledgers and correspondence with KRA.' ),
					array( 'Find the gaps and the savings', 'You get a short report: what is at risk, what is overpaid and what to do first.' ),
					array( 'Agree, prepare and file', 'Returns prepared, approved by you and filed before the deadline.' ),
					array( 'Stay ahead', 'A tax calendar, reminders and alerts when the law changes in a way that affects you.' ),
				)
			),
			'faqs'    => cwm_rows(
				array(
					array( 'KRA has sent us an assessment. What should we do?', 'Do not ignore it, and do not pay before checking it. There are strict time limits to object, usually 30 days from the date of the assessment. Contact us as soon as it arrives.' ),
					array( 'Can you help with waivers of penalties and interest?', 'Yes. We check whether you qualify under any current amnesty or waiver and prepare the application.' ),
					array( 'Do you handle tax for individuals?', 'Yes, including rental income, directors\' returns and Kenyans with income from abroad.' ),
				),
				array( 'q', 'a' )
			),
		),
		array(
			'slug'    => 'company-secretarial-and-compliance',
			'title'   => 'Company secretarial and compliance',
			'role'    => '',
			'group'   => 'obligations',
			'icon'    => 'building-2',
			'promise' => 'Stay compliant without thinking about it.',
			'who'     => 'Companies that want filings, registers and returns kept in order.',
			'seo'     => 'Company registration, annual returns, beneficial ownership filings and statutory registers for Kenyan companies. Stay compliant without thinking about it.',
			'subs'    => cwm_rows(
				array(
					array( 'Company registration', 'Registering companies, limited liability partnerships and business names on the eCitizen Business Registration Service.' ),
					array( 'Annual returns and changes', 'Annual returns and changes of directors, shareholders, share capital and addresses, filed on time.' ),
					array( 'Statutory registers', 'Registers of members, directors and beneficial owners, kept up to date.' ),
					array( 'Beneficial ownership', 'Preparing and filing beneficial ownership information as the Companies Act requires.' ),
					array( 'Board and shareholder meetings', 'Notices, minutes and resolutions for board meetings and AGMs.' ),
					array( 'Licences and permits', 'Tracking renewals of business permits and sector licences so nothing lapses.' ),
				)
			),
			'steps'   => cwm_rows(
				array(
					array( 'Health check', 'We review your records at the Registrar and your statutory books.' ),
					array( 'Fix what is missing', 'Outstanding returns and changes filed, and penalties dealt with.' ),
					array( 'Set a compliance calendar', 'Every filing and renewal date in one place, with reminders.' ),
					array( 'Run the annual cycle', 'Returns, meetings and registers handled each year without you chasing.' ),
				)
			),
			'faqs'    => cwm_rows(
				array(
					array( 'Our company has not filed annual returns for years. Can it be fixed?', 'Usually, yes. We file the outstanding returns, deal with any penalties and bring the company back into good standing.' ),
					array( 'What is beneficial ownership disclosure?', 'Every company must keep and file details of the people who ultimately own or control it. We prepare and file this for you, and update it when things change.' ),
				),
				array( 'q', 'a' )
			),
		),
		array(
			'slug'    => 'audit-and-assurance',
			'title'   => 'Audit and assurance',
			'role'    => 'audit',
			'group'   => 'obligations',
			'icon'    => 'shield-check',
			'promise' => 'Independent assurance your stakeholders can rely on.',
			'who'     => 'Organizations that need audited accounts for members, lenders, donors or regulators.',
			'seo'     => 'Statutory, donor, SACCO and cooperative audits and agreed-upon procedures in Kenya. Independent assurance your stakeholders can rely on.',
			'subs'    => cwm_rows(
				array(
					array( 'Statutory audits', 'Audits of financial statements under International Standards on Auditing.' ),
					array( 'Donor and grant audits', 'Project and grant audits to each donor\'s terms of reference.' ),
					array( 'SACCO and cooperative audits', 'Audits that meet SASRA and Commissioner for Cooperative Development requirements.' ),
					array( 'Agreed-upon procedures', 'Specific checks requested by lenders, boards and funders.' ),
					array( 'Internal audit', 'Risk-based internal audit and reviews of internal controls.' ),
					array( 'Audit readiness', 'Getting your records and statements ready so the audit runs smoothly.' ),
				)
			),
			'steps'   => cwm_rows(
				array(
					array( 'Plan', 'We agree scope, timetable and the documents we need.' ),
					array( 'Fieldwork', 'Testing and review, with as little disruption to your team as possible.' ),
					array( 'Report', 'The audit opinion and financial statements, explained to your board.' ),
					array( 'Follow up', 'A management letter with practical recommendations, and help putting them in place.' ),
				)
			),
			'faqs'    => cwm_rows(
				array(
					array( 'How long does an audit take?', 'It depends on size and the state of the records. For a small organisation with records ready, fieldwork usually takes one to three weeks.' ),
					array( 'What do we need to prepare?', 'We send a clear list at the planning stage: trial balance, bank statements, fixed asset and other registers, and key contracts.' ),
				),
				array( 'q', 'a' )
			),
		),
		array(
			'slug'    => 'business-advisory',
			'title'   => 'Business advisory',
			'role'    => '',
			'group'   => 'impact',
			'icon'    => 'trending-up',
			'promise' => 'From idea, to plan, to results.',
			'who'     => 'Owners starting, structuring, financing or improving a business.',
			'seo'     => 'Business plans, financial models, raising finance, structuring and valuations for Kenyan businesses. From idea, to plan, to results.',
			'subs'    => cwm_rows(
				array(
					array( 'Business plans and feasibility studies', 'An honest look at whether an idea works, and a plan to make it happen.' ),
					array( 'Financial models and forecasts', 'Cash flow and profit forecasts that lenders and investors can follow.' ),
					array( 'Raising finance', 'Preparing for bank loans, grants and investors, with the documents they will ask for.' ),
					array( 'Business structuring', 'Choosing between a sole proprietorship, partnership, LLP or company, and the tax effect of each.' ),
					array( 'Valuations', 'Business and share valuations for sales, buy-outs, succession and disputes.' ),
					array( 'Performance improvement', 'Costing, pricing, budgets and a few key numbers that show you what is working.' ),
				)
			),
			'steps'   => cwm_rows(
				array(
					array( 'Listen', 'We start with your goals and how the business runs today.' ),
					array( 'Diagnose', 'We find what the numbers say about where you stand.' ),
					array( 'Plan', 'A short, practical plan with figures and owners.' ),
					array( 'Support delivery', 'Regular check-ins to keep the plan on track.' ),
				)
			),
			'faqs'    => cwm_rows(
				array(
					array( 'Do you work with startups?', 'Yes. We help founders set up properly from the start: the right structure, simple books and a plan investors understand.' ),
					array( 'Can you help us get a bank loan?', 'We prepare the financial statements, projections and business plan a lender expects, and help you present them.' ),
				),
				array( 'q', 'a' )
			),
		),
		array(
			'slug'    => 'esg-and-sustainability-advisory',
			'title'   => 'ESG and sustainability advisory',
			'role'    => 'esg',
			'group'   => 'impact',
			'icon'    => 'leaf',
			'promise' => 'Know your impact. Report it with confidence.',
			'who'     => 'Organizations preparing for sustainability reporting or asked for ESG information by lenders, investors or buyers.',
			'seo'     => 'ESG readiness, sustainability reporting (IFRS S1 and S2, GRI), carbon accounting and ESG governance for Kenyan organisations.',
			'subs'    => array_merge(
				cwm_rows(
					array(
						array( 'ESG readiness assessments', 'Where you stand today against what lenders, investors and buyers will ask for.' ),
						array( 'Sustainability reporting', 'Reports aligned with IFRS S1 and S2, the GRI Standards and the NSE ESG Disclosures Guidance.' ),
						array( 'Climate and carbon accounting', 'Measuring Scope 1, 2 and 3 greenhouse gas emissions and setting realistic targets.' ),
						array( 'ESG policies and governance', 'Board oversight, policies and data systems that make ESG reporting repeatable.' ),
						array( 'Buyer and lender questionnaires', 'Answering ESG questionnaires from export buyers, development finance institutions and banks.' ),
					)
				),
				array(
					array(
						'title'     => 'Assurance and verification',
						'text'      => 'Independent limited assurance on your sustainability report and greenhouse gas statement.',
						'assurance' => '1',
					),
				)
			),
			'steps'   => cwm_rows(
				array(
					array( 'Assess', 'We review what you do and what you measure today.' ),
					array( 'Prioritise', 'A materiality assessment to agree what matters most to report.' ),
					array( 'Measure', 'Data collection, carbon accounting and the controls behind the numbers.' ),
					array( 'Report and improve', 'A report you can stand behind, and a plan for next year.' ),
				)
			),
			'faqs'    => cwm_rows(
				array(
					array( 'Do we have to report on ESG?', 'Listed companies and many borrowers from development lenders already do, and ICPAK has adopted IFRS S1 and S2 for Kenya. Even where it is not required, buyers and funders increasingly ask.' ),
					array( 'We are a small business. Is ESG relevant to us?', 'Yes, if you supply larger companies, export, or borrow from banks with sustainability commitments. We keep it proportionate to your size.' ),
					array( 'What are IFRS S1 and S2?', 'International sustainability disclosure standards. S1 covers sustainability-related risks and opportunities in general; S2 covers climate.' ),
				),
				array( 'q', 'a' )
			),
		),
	);
}

function cwm_seed_industries() {
	return array(
		array( 'SMEs and startups', 'rocket', 'Growing businesses need clean books, a tax position they understand and numbers a lender believes. We give founders and owners the finance function they cannot yet afford to hire.', array( 'accounting-and-bookkeeping', 'tax', 'business-advisory', 'company-secretarial-and-compliance' ) ),
		array( 'NGOs and non-profits', 'heart-handshake', 'Donors expect accurate fund accounting and timely, reliable reports. We help NGOs, foundations and community organisations account for every grant, report to donors on time and show their impact.', array( 'accounting-and-bookkeeping', 'audit-and-assurance', 'esg-and-sustainability-advisory', 'payroll-and-hr-support' ) ),
		array( 'SACCOs and cooperatives', 'piggy-bank', 'Members and regulators rely on your numbers. We support SACCOs and cooperatives with accounting, regulatory returns and governance that meet SASRA and the Commissioner\'s requirements.', array( 'accounting-and-bookkeeping', 'audit-and-assurance', 'tax', 'company-secretarial-and-compliance' ) ),
		array( 'Real estate and construction', 'hard-hat', 'Projects run over months and years, with retention, VAT and withholding tax at every stage. We help developers, contractors and landlords keep project accounts, tax and cash flow under control.', array( 'accounting-and-bookkeeping', 'tax', 'business-advisory', 'esg-and-sustainability-advisory' ) ),
		array( 'Professional firms', 'briefcase-business', 'Law firms, consultancies, clinics and agencies need partner accounts, client money handled correctly and tax planned in advance. We take the finance work off your desk.', array( 'accounting-and-bookkeeping', 'tax', 'payroll-and-hr-support', 'company-secretarial-and-compliance' ) ),
		array( 'Schools and religious institutions', 'graduation-cap', 'Boards, parents and congregations want to see money well managed. We help schools, churches and faith-based organisations with fees and offerings accounting, payroll, budgets and board reporting.', array( 'accounting-and-bookkeeping', 'payroll-and-hr-support', 'audit-and-assurance', 'business-advisory' ) ),
		array( 'Hospitality', 'hotel', 'Hotels, restaurants and tour operators deal with seasonal cash flow, tourism levies, VAT and large casual workforces. We keep costs, payroll and compliance in order so you can focus on guests.', array( 'accounting-and-bookkeeping', 'tax', 'payroll-and-hr-support', 'esg-and-sustainability-advisory' ) ),
		array( 'Agribusiness', 'wheat', 'From farms to processors and exporters, agribusiness faces biological assets, exemptions and buyers asking about sustainability. We help with accounting, tax incentives and ESG reporting for export markets.', array( 'accounting-and-bookkeeping', 'tax', 'esg-and-sustainability-advisory', 'business-advisory' ) ),
	);
}

function cwm_seed_pages() {
	$page = function ( $title, $slug, $template = '', $content = '', $meta = array(), $parent = 0 ) {
		$existing = get_page_by_path( $parent ? get_page_uri( $parent ) . '/' . $slug : $slug );
		if ( $existing ) {
			if ( 'publish' !== $existing->post_status ) {
				// WordPress creates a draft privacy page on install; take it over.
				wp_update_post(
					wp_slash(
						array(
							'ID'           => $existing->ID,
							'post_status'  => 'publish',
							'post_title'   => $title,
							'post_content' => $content,
						)
					)
				);
			}
			return $existing->ID;
		}
		$id = cwm_seed_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $content,
				'post_parent'  => $parent,
			),
			$meta
		);
		if ( $template ) {
			update_post_meta( $id, '_wp_page_template', 'page-templates/' . $template . '.php' );
		}
		return $id;
	};

	$home = $page(
		'Home',
		'home',
		'',
		'',
		array(
			'hero_title'        => 'Count what matters.',
			'hero_text'         => 'Accounting, tax, advisory and ESG support for Kenyan businesses that want to grow, and to do it right.',
			'services_title'    => 'Three things to account for',
			'services_text'     => 'Every business has its money, its obligations and its impact to answer for. We help with all three.',
			'group_money'       => 'What comes in, what goes out, and who gets paid.',
			'group_obligations' => 'What the law, KRA and your stakeholders expect of you.',
			'group_impact'      => 'Where the business is going, and what it leaves behind.',
			'why_title'         => 'Our four promises',
			'promises'          => cwm_four_ps(),
			'stats_show'        => '',
			'stats'             => array(),
			'insights_title'    => 'Latest insights',
			'industries_title'  => 'Industries we serve',
			'testimonials_show' => '',
			'closing_title'     => 'Let\'s look at your numbers together.',
			'closing_text'      => 'Tell us where you are and what is worrying you. We will reply within one working day, in plain language.',
		)
	);
	$page( 'Services', 'services', 'services', '', array( 'intro' => 'Seven services, one idea: we help you count what matters.' ) );
	$page( 'Industries', 'industries', 'industries', '', array( 'intro' => 'We work across the Kenyan economy. Here is how we help in each sector.' ) );
	$insights = $page( 'Insights', 'insights' );
	$page(
		'ESG reporting hub',
		'esg-reporting-hub',
		'esg-hub',
		cwm_blocks( "Sustainability reporting is moving fast in Kenya. ICPAK has adopted the IFRS sustainability standards, the Nairobi Securities Exchange has ESG disclosure guidance for listed companies, and lenders, investors and export buyers now ask for ESG information as a matter of course.\n\nThis hub brings our ESG writing together in one place. Start with the articles we recommend, follow a series in order, look up a term in the glossary, or download a checklist to use in your own organisation." ),
		array( 'intro' => 'Practical guidance on sustainability reporting for Kenyan organisations, published as a regular series.' )
	);
	$about = $page(
		'About',
		'about',
		'about',
		cwm_blocks( "[Firm name] is a Kenyan professional services firm. We keep the books, handle the tax, advise on the next move, and help our clients measure and report their impact.\n\nWe work with businesses, institutions and individuals who want their affairs in order and their decisions well informed. Whatever the assignment, we do the same thing: we count what matters, and we explain it plainly." ),
		array(
			'intro'   => 'A Kenyan professional services firm that counts what matters.',
			'three'   => array(
				array( 'icon' => 'coins', 'title' => 'Money', 'text' => 'Accurate books, management accounts and payroll, so you always know where you stand.' ),
				array( 'icon' => 'scale', 'title' => 'Obligations', 'text' => 'Tax, company filings and reporting, met in full and on time, so nothing comes back to bite you.' ),
				array( 'icon' => 'sprout', 'title' => 'Impact', 'text' => 'Advice on where to go next, and help measuring and reporting the difference you make.' ),
			),
			'mission' => 'We help businesses count what matters: their money, their obligations and their impact.',
			'vision'  => 'Kenyan businesses that are profitable, compliant and proud of how they got there.',
			'values'  => cwm_four_ps(),
		)
	);
	// Fix [Firm name] token in the story.
	wp_update_post(
		array(
			'ID'           => $about,
			'post_content' => str_replace( '[Firm name]', cwm_firm(), get_post_field( 'post_content', $about ) ),
		)
	);
	$page(
		'Our approach',
		'our-approach',
		'approach',
		cwm_blocks( "Every client is different, but the way we work is not. These four steps apply whether we are closing your monthly books, defending a KRA assessment or preparing your first sustainability report." ),
		array(
			'intro' => 'Four steps, the same on every assignment.',
			'steps' => array(
				array( 'icon' => 'ear', 'title' => 'Listen', 'text' => 'We start with your business, not our checklist.' ),
				array( 'icon' => 'lightbulb', 'title' => 'Clarify', 'text' => 'We show you where you stand, in plain figures.' ),
				array( 'icon' => 'circle-check', 'title' => 'Deliver', 'text' => 'We do the work, on time and to standard.' ),
				array( 'icon' => 'life-buoy', 'title' => 'Stay', 'text' => 'We remain available after the report is signed.' ),
			),
		),
		$about
	);
	$page( 'Our team', 'our-team', 'team', '', array( 'intro' => 'The people who will count what matters for you.' ), $about );
	$page( 'Contact', 'contact', 'contact', '', array( 'intro' => 'Call, email, WhatsApp or send the form. We reply within one working day.' ) );
	$page( 'Request a proposal', 'request-a-proposal', 'proposal', '', array( 'intro' => 'Tell us about your organisation and what you need. We will come back with a clear scope and fee.' ) );
	$privacy = $page( 'Privacy policy', 'privacy-policy', '', cwm_blocks( cwm_legal_privacy() ) );
	$page( 'Cookie policy', 'cookie-policy', '', cwm_blocks( cwm_legal_cookies() ) );
	$page( 'Terms of use and disclaimer', 'terms-of-use', '', cwm_blocks( cwm_legal_terms() ) );
	$page( 'Site map', 'site-map', 'sitemap' );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home );
	update_option( 'page_for_posts', $insights );
	update_option( 'wp_page_for_privacy_policy', $privacy );
}

function cwm_four_ps() {
	return array(
		array( 'icon' => 'message-square-text', 'title' => 'Plain', 'text' => 'We explain it in words you would use yourself.' ),
		array( 'icon' => 'crosshair', 'title' => 'Precise', 'text' => 'We get the figures right the first time.' ),
		array( 'icon' => 'scale', 'title' => 'Principled', 'text' => 'We report what is true, not what is convenient.' ),
		array( 'icon' => 'handshake', 'title' => 'Present', 'text' => 'We answer, we show up, and we stay.' ),
	);
}

/** Sample insights. All marked _cwm_sample so they can be removed before launch. */
function cwm_seed_insights( $cat ) {
	$series = term_exists( 'esg-reporting-for-kenyan-businesses', 'series' );
	$series = $series ? $series : wp_insert_term( 'ESG reporting for Kenyan businesses', 'series', array( 'slug' => 'esg-reporting-for-kenyan-businesses', 'description' => 'A step-by-step series for organisations preparing their first sustainability report.' ) );
	$series = (int) $series['term_id'];

	$items = array(
		array(
			'title'   => 'eTIMS: why your expenses may not be deductible without a valid tax invoice',
			'cat'     => array( 'tax-alerts' ),
			'service' => array( 'tax', 'accounting-and-bookkeeping' ),
			'date'    => '-6 days',
			'excerpt' => 'Since KRA tied expense deductions to electronic tax invoices, a missing eTIMS invoice can cost you the deduction. Here is what to check.',
			'meta'    => array( 'featured' => '1', 'author_team' => 'Tax Team' ),
			'body'    => "KRA now expects most business expenses to be supported by an electronic tax invoice generated through eTIMS. Where a supplier cannot give you one, the expense may be disallowed when your income tax return is reviewed, even if the money was genuinely spent.\n\n## Who this affects\n\nAny business that claims expenses against its income, and any supplier that sells to businesses. Small suppliers, landlords and service providers who are not yet on eTIMS are the most common gap.\n\n## What to do now\n\n- List your regular suppliers and check which of them issue eTIMS invoices.\n- Ask non-compliant suppliers to register, or plan to use the buyer-initiated invoice option where it applies.\n- Check that the invoice details match your PIN exactly.\n- Keep invoices with your books, not in an inbox.\n\n## How we can help\n\nWe review your expense records, flag the invoices that would not survive a KRA review and set up a simple routine to keep you covered every month.",
		),
		array(
			'title'   => 'Five tax deadlines Kenyan businesses miss most often',
			'cat'     => array( 'tax-alerts' ),
			'service' => array( 'tax' ),
			'date'    => '-40 days',
			'excerpt' => 'Instalment tax, withholding VAT and annual returns catch out even well-run businesses. A short list to pin on the wall.',
			'meta'    => array( 'author_team' => 'Tax Team' ),
			'body'    => "Most penalties we see are not about complicated tax. They are about dates.\n\n## The usual suspects\n\n- Monthly PAYE, VAT and withholding tax returns and payments.\n- Instalment tax, paid during the year rather than at the end.\n- The annual corporate income tax return, six months after the year end.\n- Rental income tax for landlords.\n- Annual returns at the Business Registration Service, which are not a KRA filing but carry their own penalties.\n\n## A simple fix\n\nPut every date in one calendar, with a reminder a week before. Our clients get one, with their own dates filled in.",
		),
		array(
			'title'   => 'Case analysis: input VAT and a supplier who did not declare the sale',
			'cat'     => array( 'tax-alerts', 'case-analysis' ),
			'service' => array( 'tax' ),
			'date'    => '-12 days',
			'excerpt' => 'Sample analysis. The Tribunal considered whether a buyer loses its input VAT claim when the supplier fails to declare the sale.',
			'meta'    => array(
				'author_team'   => 'Tax Team',
				'case_name'     => 'Sample Trading Limited v Commissioner of Domestic Taxes',
				'case_citation' => 'TAT Appeal No. E000 of 2025 (sample)',
				'case_court'    => 'tat',
				'case_date'     => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
				'case_taxtype'  => 'vat',
				'case_outcome'  => 'taxpayer',
				'case_link'     => 'https://new.kenyalaw.org/',
				'case_means'    => "Keep evidence that goods or services were actually supplied, not just the invoice.\nCheck new suppliers on the KRA PIN checker before you buy.\nRespond to KRA's data-matching queries with documents, not explanations.",
				'case_issues'   => "- Whether the taxpayer was entitled to claim input VAT on purchases from a supplier who had not declared the sales.\n- Whether the Commissioner was right to rely only on a data-matching report.",
				'case_facts'    => "The taxpayer claimed input VAT on purchases from a registered supplier. KRA's systems showed that the supplier had not declared the corresponding sales, and the Commissioner disallowed the claims.\n\nThe taxpayer produced invoices, delivery notes, bank payments and stock records showing that the goods were received and used in the business.",
				'case_decision' => 'The Tribunal allowed the appeal. It held that a buyer who proves that a genuine supply took place, and that it paid for it, should not lose its input VAT because of the supplier\'s failure to declare. The Commissioner\'s remedy lay against the supplier.',
			),
			'body'    => "This sample shows the layout of a case analysis. Replace it with a real decision before launch.\n\nThe decision is a reminder that the burden of proof in tax disputes sits with the taxpayer, and that it can be discharged with ordinary business records. Data-matching reports are a starting point for KRA's enquiries, not proof on their own.\n\nBusinesses should treat supplier due diligence as part of their VAT routine: a PIN check, a copy of the supplier's registration, and documents that show the goods or services actually changed hands.",
		),
		array(
			'title'   => 'Case analysis: deductibility of management fees paid to a related company',
			'cat'     => array( 'tax-alerts', 'case-analysis' ),
			'service' => array( 'tax' ),
			'date'    => '-26 days',
			'excerpt' => 'Sample analysis. The High Court considered what evidence is needed to deduct management fees paid within a group.',
			'meta'    => array(
				'author_team'   => 'Tax Team',
				'case_name'     => 'Example Holdings Limited v Commissioner of Domestic Taxes',
				'case_citation' => 'Income Tax Appeal No. E000 of 2025 (sample)',
				'case_court'    => 'hc',
				'case_date'     => gmdate( 'Y-m-d', strtotime( '-70 days' ) ),
				'case_taxtype'  => 'income-tax',
				'case_outcome'  => 'kra',
				'case_link'     => 'https://new.kenyalaw.org/',
				'case_means'    => "Document what a related company actually does for its fee.\nKeep a transfer pricing policy and benchmark the charge.\nA contract alone will not prove that a service was provided.",
				'case_issues'   => '- Whether management fees paid to a related company were wholly and exclusively incurred in the production of income.',
				'case_facts'    => 'The taxpayer deducted management fees paid to its parent company under a group services agreement. The Commissioner disallowed the deduction because there was no evidence of the services actually delivered.',
				'case_decision' => 'The Court dismissed the appeal. A services agreement and invoices were not enough without evidence of the services themselves, such as reports, correspondence and time records.',
			),
			'body'    => "This sample shows the layout of a case analysis. Replace it with a real decision before launch.\n\nGroups that charge management or technical fees between companies should assume KRA will ask what was delivered. The best evidence is created at the time: reports, emails, meeting notes and a clear basis for the amount charged.",
		),
		array(
			'title'   => 'Case analysis: withholding tax on services from a non-resident',
			'cat'     => array( 'tax-alerts', 'case-analysis' ),
			'service' => array( 'tax' ),
			'date'    => '-55 days',
			'excerpt' => 'Sample analysis. The Court of Appeal considered when payments to a non-resident are subject to withholding tax.',
			'meta'    => array(
				'author_team'   => 'Tax Team',
				'case_name'     => 'Illustrative Services Limited v Commissioner of Domestic Taxes',
				'case_citation' => 'Civil Appeal No. E000 of 2025 (sample)',
				'case_court'    => 'coa',
				'case_date'     => gmdate( 'Y-m-d', strtotime( '-120 days' ) ),
				'case_taxtype'  => 'withholding-tax',
				'case_outcome'  => 'partly',
				'case_link'     => 'https://new.kenyalaw.org/',
				'case_means'    => "Check withholding tax before paying any foreign supplier.\nTax treaties can reduce the rate, but only with the right documents.\nPenalties can be challenged even where the tax itself is due.",
				'case_issues'   => "- Whether payments to a non-resident for services were subject to withholding tax.\n- Whether penalties and interest should apply.",
				'case_facts'    => 'The taxpayer paid a foreign company for software support without deducting withholding tax. The Commissioner assessed the tax with penalties and interest.',
				'case_decision' => 'The Court upheld the tax but set aside part of the penalties, finding that the taxpayer had acted on a reasonable reading of the law at the time.',
			),
			'body'    => "This sample shows the layout of a case analysis. Replace it with a real decision before launch.\n\nPayments abroad are a frequent source of KRA assessments. A short check before each payment avoids most of them.",
		),
		array(
			'title'   => 'ESG reporting in Kenya: where to start',
			'cat'     => array( 'esg-reporting' ),
			'service' => array( 'esg-and-sustainability-advisory' ),
			'topic'   => array( 'reporting-standards' ),
			'date'    => '-20 days',
			'series'  => 1,
			'excerpt' => 'Who is asking for ESG information, what they want to see, and the first three steps for an organisation starting from zero.',
			'meta'    => array( 'start_here' => '1', 'author_team' => 'ESG Team' ),
			'body'    => "ESG reporting can feel like a new language. It is really a structured way of answering questions your stakeholders are already asking: how do you affect the environment, how do you treat people, and how is the business run?\n\n## Who is asking\n\n- Lenders and development finance institutions, as a condition of funding.\n- Export buyers, especially in Europe, through supplier questionnaires.\n- Investors, boards and, for listed companies, the Nairobi Securities Exchange.\n\n## Three first steps\n\n- Name one person to own ESG reporting.\n- List the ESG questions you have already been asked.\n- Gather the data you already have: energy bills, fuel, staff numbers, policies.\n\nIn the next part of this series we look at materiality: how to decide what is worth reporting.",
		),
		array(
			'title'   => 'Materiality: deciding what to report',
			'cat'     => array( 'esg-reporting' ),
			'service' => array( 'esg-and-sustainability-advisory' ),
			'topic'   => array( 'reporting-standards', 'governance' ),
			'date'    => '-13 days',
			'series'  => 2,
			'excerpt' => 'You cannot report on everything. A materiality assessment tells you what matters most to your business and your stakeholders.',
			'meta'    => array( 'start_here' => '1', 'author_team' => 'ESG Team' ),
			'body'    => "Materiality is the filter that keeps a sustainability report focused. A topic is material if it could reasonably affect decisions made by the people who use your report.\n\n## Single and double materiality\n\nIFRS S1 and S2 look at how sustainability matters affect the business: its cash flows, access to finance and cost of capital. The GRI Standards look the other way, at the business's impact on people and the environment. Doing both is called double materiality.\n\n## A simple process\n\n- List possible topics, using your sector and the questions you receive.\n- Ask stakeholders which matter to them.\n- Score each topic for impact and financial effect.\n- Agree the final list with your board.",
		),
		array(
			'title'   => 'Measuring your carbon footprint: Scope 1, 2 and 3 explained',
			'cat'     => array( 'esg-reporting' ),
			'service' => array( 'esg-and-sustainability-advisory' ),
			'topic'   => array( 'climate-and-emissions' ),
			'date'    => '-4 days',
			'series'  => 3,
			'excerpt' => 'The three scopes of greenhouse gas emissions, in plain language, with Kenyan examples of what goes where.',
			'meta'    => array( 'start_here' => '1', 'author_team' => 'ESG Team' ),
			'body'    => "Greenhouse gas emissions are grouped into three scopes under the GHG Protocol.\n\n## Scope 1: what you burn\n\nFuel in your vehicles, generators and boilers. For many Kenyan businesses, diesel for backup power is the largest item.\n\n## Scope 2: the electricity you buy\n\nPower from Kenya Power. Kenya's grid is largely renewable, so Scope 2 is often smaller here than elsewhere.\n\n## Scope 3: everything else in your value chain\n\nPurchased goods, transport, business travel, waste and the use of your products. It is usually the largest and hardest to measure, so start with the biggest categories.",
		),
		array(
			'title'   => 'What lenders are asking about ESG, and how to answer',
			'cat'     => array( 'esg-reporting' ),
			'service' => array( 'esg-and-sustainability-advisory', 'business-advisory' ),
			'topic'   => array( 'sustainable-finance' ),
			'date'    => '-33 days',
			'excerpt' => 'Banks with sustainability commitments now screen borrowers. The questions are predictable, and so are the answers that work.',
			'meta'    => array( 'author_team' => 'ESG Team' ),
			'body'    => "If you have applied for finance recently, you may have met an environmental and social questionnaire. Lenders use them to manage their own risks and commitments.\n\n## Common questions\n\n- Do you have environmental, health and safety policies?\n- How do you manage waste and emissions?\n- What are your labour practices, including for contractors?\n- Who in the business is responsible for ESG?\n\nHonest, documented answers beat polished ones. Where you have gaps, say what you are doing about them.",
		),
		array(
			'title'   => 'A plain guide to SHIF, NSSF and the Housing Levy for employers',
			'cat'     => array( 'guides' ),
			'service' => array( 'payroll-and-hr-support' ),
			'date'    => '-48 days',
			'excerpt' => 'What each statutory deduction is for, who pays it and when it is due.',
			'meta'    => array( 'author_team' => 'Accounting Team' ),
			'body'    => "Every Kenyan employer deducts several statutory amounts from pay. Getting them right protects your staff and your business.\n\n## The main deductions\n\n- PAYE: income tax on employment income.\n- SHIF: contributions to the Social Health Insurance Fund.\n- NSSF: pension contributions, shared by employer and employee.\n- Affordable Housing Levy: shared by employer and employee.\n\nRates and caps change, so check the current figures each year or let us handle it for you.",
		),
		array(
			'title'   => 'Choosing the right business structure in Kenya',
			'cat'     => array( 'guides' ),
			'service' => array( 'business-advisory', 'company-secretarial-and-compliance' ),
			'date'    => '-75 days',
			'excerpt' => 'Sole proprietorship, partnership, LLP or limited company: what each means for liability, tax and paperwork.',
			'meta'    => array( 'author_team' => 'Advisory Team' ),
			'body'    => "The structure you choose affects your personal liability, how you are taxed and what you must file each year.\n\n## The options\n\n- Sole proprietorship: simple, but you are personally liable.\n- Partnership: shared ownership, shared liability.\n- Limited liability partnership: partnership flexibility with limited liability.\n- Private limited company: a separate legal person, preferred by lenders and investors.\n\nThere is no single right answer. It depends on your plans, your risks and who you will need to convince.",
		),
		array(
			'title'   => 'Breakfast briefing: what this year\'s tax changes mean for you',
			'cat'     => array( 'news-events' ),
			'service' => array( 'tax' ),
			'date'    => '-2 days',
			'excerpt' => 'Join our tax team for a short breakfast briefing on the year\'s changes and what to do about them.',
			'meta'    => array( 'author_team' => 'Tax Team' ),
			'body'    => "Our tax team will walk through this year's changes in tax law and KRA practice, and take your questions.\n\n- Format: breakfast briefing, one hour\n- Location: Nairobi, venue to be confirmed\n- Cost: free for clients and guests\n\nTo reserve a place, contact us.",
		),
	);

	foreach ( $items as $it ) {
		$cats = array();
		foreach ( $it['cat'] as $c ) {
			$cats[] = $cat[ $c ];
		}
		$meta               = $it['meta'];
		$meta['_cwm_sample'] = '1';
		if ( ! empty( $it['series'] ) ) {
			$meta['series_part'] = (string) $it['series'];
		}
		$date = wp_date( 'Y-m-d H:i:s', strtotime( $it['date'] . ' 09:00' ) );
		$id   = cwm_seed_post(
			array(
				'post_type'     => 'post',
				'post_title'    => $it['title'],
				'post_content'  => cwm_blocks( $it['body'] ),
				'post_excerpt'  => $it['excerpt'],
				'post_category' => $cats,
				'post_date'     => $date,
			),
			$meta
		);
		if ( ! $id ) {
			continue;
		}
		wp_set_object_terms( $id, $it['service'], 'service_area' );
		if ( ! empty( $it['topic'] ) ) {
			wp_set_object_terms( $id, $it['topic'], 'esg_topic' );
		}
		if ( ! empty( $it['series'] ) ) {
			wp_set_object_terms( $id, array( $series ), 'series' );
		}
	}

	// Downloads (files are attached separately; the blocks stay hidden until a file is chosen).
	$res = array(
		array( 'ESG readiness checklist', 'checklist', 'Twenty questions to see where your organisation stands before you start sustainability reporting.', '1', array( 'esg-and-sustainability-advisory' ) ),
		array( 'Materiality assessment worksheet', 'template', 'A worksheet to list, score and prioritise sustainability topics with your team.', '1', array( 'esg-and-sustainability-advisory' ) ),
		array( 'Kenya tax compliance calendar', 'publication', 'Every monthly and annual tax and statutory deadline for a Kenyan business, on one page.', '', array( 'tax', 'payroll-and-hr-support' ) ),
	);
	foreach ( $res as $i => $r ) {
		$id = cwm_seed_post(
			array(
				'post_type'  => 'resource',
				'post_title' => $r[0],
				'menu_order' => $i + 1,
			),
			array(
				'kind'        => $r[1],
				'summary'     => $r[2],
				'esg_hub'     => $r[3],
				'_cwm_sample' => '1',
			)
		);
		wp_set_object_terms( $id, $r[4], 'service_area' );
		$att = cwm_attach_sample( sanitize_title( $r[0] ) . '.pdf', $r[0] );
		if ( $att ) {
			update_post_meta( $id, '_cwm_file', (string) $att );
		}
		if ( 'Kenya tax compliance calendar' === $r[0] ) {
			update_post_meta( (int) get_option( 'page_on_front' ), '_cwm_download', (string) $id );
		}
	}
}

/** Copy a bundled sample PDF into the media library. */
function cwm_attach_sample( $file, $title ) {
	$src = CWM_DIR . '/assets/samples/' . $file;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( $file );
	copy( $src, $tmp );
	$id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0,
		$title
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_cwm_sample', '1' );
	return $id;
}

function cwm_seed_glossary() {
	$terms = array(
		'Carbon footprint'     => 'The total greenhouse gas emissions caused by an organisation, product or activity, usually expressed in tonnes of carbon dioxide equivalent (tCO2e).',
		'Double materiality'   => 'Looking at sustainability from two directions: how sustainability matters affect the business financially, and how the business affects people and the environment.',
		'ESG'                  => 'Environmental, social and governance: the three areas investors, lenders and others use to assess how an organisation manages its wider risks and impacts.',
		'GHG Protocol'         => 'The most widely used global standard for measuring and reporting greenhouse gas emissions. It defines Scope 1, 2 and 3.',
		'GRI Standards'        => 'Global Reporting Initiative standards for reporting an organisation\'s impacts on the economy, environment and people.',
		'IFRS S1'              => 'The general sustainability disclosure standard issued by the ISSB. It requires disclosure of sustainability-related risks and opportunities that could affect an entity\'s prospects.',
		'IFRS S2'              => 'The ISSB\'s climate-related disclosure standard, covering governance, strategy, risk management, metrics and targets for climate risks and opportunities.',
		'ISSB'                 => 'The International Sustainability Standards Board, which sets the IFRS sustainability disclosure standards. ICPAK has adopted its standards for Kenya.',
		'Materiality'          => 'The test of whether information is important enough to report: would leaving it out influence decisions made by the people who use the report?',
		'Net zero'             => 'A state where the greenhouse gases an organisation emits are reduced as far as possible and the remainder balanced by removals from the atmosphere.',
		'Scope 1 emissions'    => 'Direct emissions from sources an organisation owns or controls, such as its vehicles, generators and boilers.',
		'Scope 2 emissions'    => 'Indirect emissions from the generation of electricity, steam, heating or cooling that an organisation buys.',
		'Scope 3 emissions'    => 'All other indirect emissions in an organisation\'s value chain, from purchased goods to business travel and the use of sold products.',
		'Sustainability report' => 'A report on an organisation\'s environmental, social and governance performance and impacts, often published alongside the annual report.',
		'Value chain'          => 'The full range of activities, resources and relationships involved in an organisation\'s business, from suppliers to customers.',
	);
	foreach ( $terms as $t => $d ) {
		cwm_seed_post(
			array(
				'post_type'    => 'glossary_term',
				'post_title'   => $t,
				'post_content' => $d,
			),
			array( '_cwm_sample' => '' )
		);
	}
}

function cwm_legal_privacy() {
	$firm = cwm_firm();
	return <<<TXT
This policy explains how {$firm} ("we", "us") collects, uses and protects personal data through this website, in line with the Data Protection Act, 2019 of Kenya and its regulations.

## Who we are

{$firm} is the data controller for personal data collected through this website. You can contact us using the details on our contact page.

## What we collect

- Details you give us in our forms: your name, organisation, email address, phone number, the service you are interested in, your message and any document you attach.
- Your email address if you subscribe to our newsletter.
- Basic technical data needed to run the website securely, such as your IP address, browser type and the pages you visit.
- Analytics data, only if you accept analytics cookies.

## Why we use it

- To respond to your enquiry or proposal request, which is the lawful basis of taking steps at your request before entering into a contract.
- To send you our newsletter, with your consent. You can unsubscribe at any time.
- To keep the website secure and working, which is in our legitimate interests.

## How long we keep it

We keep enquiries for up to two years unless they lead to an engagement, in which case our engagement records policy applies. Newsletter subscriptions are kept until you unsubscribe.

## Who we share it with

We do not sell personal data. We share it only with service providers who help us run the website and email, under contracts that protect it, or where the law requires it. Some providers may store data outside Kenya; where they do, we make sure appropriate safeguards are in place as the Act requires.

## Your rights

Under the Data Protection Act you have the right to be informed of how your data is used, to access it, to object to its processing, to have it corrected or deleted, and to data portability. To exercise these rights, contact us. You may also complain to the Office of the Data Protection Commissioner.

## Changes

We may update this policy. The date at the bottom of the page shows when it last changed.
TXT;
}

function cwm_legal_cookies() {
	return <<<TXT
Cookies are small files a website stores on your device. This page explains which ones we use.

## Essential cookies

These keep the website working and secure. They are set automatically and cannot be switched off. They do not identify you personally.

## Analytics cookies

If we use analytics, such as Google Analytics, they help us understand which pages are useful. They are off by default and are only set if you click "Accept" on the cookie banner. You can change your mind at any time by clearing cookies in your browser.

## Maps

Our contact page offers a Google map. It only loads if you click "Show map", and Google may then set its own cookies under its privacy policy.

## Managing cookies

Most browsers let you block or delete cookies in their settings. Blocking essential cookies may stop parts of the website working.
TXT;
}

function cwm_legal_terms() {
	$firm = cwm_firm();
	return <<<TXT
By using this website you agree to these terms.

## General information only

The content on this website, including insights, tax alerts, case analyses, guides and downloads, is general information. It is not professional advice and does not create a client relationship. Laws, rates and practice change, and every situation is different. Please take advice on your own circumstances before acting.

## No liability

While we take care to keep the content accurate and up to date, {$firm} accepts no liability for any loss arising from reliance on it. Case analyses summarise decisions for information; always refer to the full judgment.

## Intellectual property

The content and design of this website belong to {$firm} unless stated otherwise. You may share links and quote short extracts with credit. Please do not republish whole articles or downloads without permission.

## Links to other websites

We link to other websites, such as Kenya Law and KRA, for convenience. We are not responsible for their content.

## Forms and attachments

Please do not send confidential documents through our forms unless we ask for them. Information you send is handled under our privacy policy.

## Governing law

These terms are governed by the laws of Kenya.
TXT;
}
