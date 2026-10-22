<?php
/**
 * Not found.
 */

get_header();
?>
<section class="banner">
	<div class="container banner__inner">
		<div class="banner__body"><div>
			<p class="eyebrow eyebrow--light">Error 404</p>
			<h1 class="banner__title">We could not find that page</h1>
			<p class="banner__line">It may have moved. These links will get you back on track.</p>
		</div></div>
	</div>
</section>
<div class="section">
	<div class="container container--narrow">
		<form class="filters filters--search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<div class="filters__search"><?php echo cwm_icon( 'search' ); // phpcs:ignore ?><label class="sr-only" for="s404">Search</label><input id="s404" type="search" name="s" placeholder="Search the site"></div>
			<button class="btn btn--primary" type="submit">Search</button>
		</form>
		<ul class="pill-list pill-list--center">
			<li><a class="pill" href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
			<li><a class="pill" href="<?php echo esc_url( cwm_page_url( 'services' ) ); ?>">Services</a></li>
			<li><a class="pill" href="<?php echo esc_url( cwm_insights_url() ); ?>">Insights</a></li>
			<li><a class="pill" href="<?php echo esc_url( cwm_page_url( 'contact' ) ); ?>">Contact</a></li>
		</ul>
	</div>
</div>
<?php
cwm_cta();
get_footer();
