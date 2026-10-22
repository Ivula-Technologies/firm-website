<?php
/**
 * Count What Matters — corporate theme for an accounting, tax, advisory and ESG firm.
 *
 * Everything the firm edits lives in the WordPress admin:
 *   Firm settings (site-wide details and the two switches), page fields, Services,
 *   Industries, Insights (posts), ESG resources, Glossary, Team and Testimonials.
 */

defined( 'ABSPATH' ) || exit;

define( 'CWM_VERSION', '1.1.0' );
define( 'CWM_DIR', get_template_directory() );
define( 'CWM_URI', get_template_directory_uri() );

require CWM_DIR . '/inc/helpers.php';
require CWM_DIR . '/inc/fields.php';
require CWM_DIR . '/inc/settings.php';
require CWM_DIR . '/inc/content-types.php';
require CWM_DIR . '/inc/meta-boxes.php';
require CWM_DIR . '/inc/switches.php';
require CWM_DIR . '/inc/queries.php';
require CWM_DIR . '/inc/forms.php';
require CWM_DIR . '/inc/template-tags.php';
require CWM_DIR . '/inc/seo.php';
require CWM_DIR . '/inc/privacy.php';
require CWM_DIR . '/inc/preview.php';
require CWM_DIR . '/inc/setup.php';
require CWM_DIR . '/inc/seed.php';
require CWM_DIR . '/inc/admin.php';
