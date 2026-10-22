<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>
<?php cwm_preview_banner(); ?>

<div class="topbar">
	<div class="container topbar__inner">
		<div class="topbar__contact">
			<?php if ( cwm_opt( 'phone' ) ) : ?>
				<a href="<?php echo esc_url( cwm_tel_url() ); ?>"><?php echo cwm_icon( 'phone' ); // phpcs:ignore ?><span><?php echo esc_html( cwm_opt( 'phone' ) ); ?></span></a>
			<?php endif; ?>
			<?php if ( cwm_opt( 'email' ) ) : ?>
				<a href="mailto:<?php echo esc_attr( cwm_opt( 'email' ) ); ?>"><?php echo cwm_icon( 'mail' ); // phpcs:ignore ?><span><?php echo esc_html( cwm_opt( 'email' ) ); ?></span></a>
			<?php endif; ?>
		</div>
		<form class="topbar__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="sr-only" for="top-search">Search the site</label>
			<input id="top-search" type="search" name="s" placeholder="Search" value="<?php echo esc_attr( get_search_query() ); ?>">
			<button type="submit" aria-label="Search"><?php echo cwm_icon( 'search' ); // phpcs:ignore ?></button>
		</form>
	</div>
</div>

<header class="site-header" id="site-header">
	<div class="container site-header__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( cwm_firm() ); ?>, home">
			<?php cwm_logo(); ?>
		</a>

		<nav class="main-nav" id="main-nav" aria-label="Main menu">
			<ul class="main-nav__list">
				<?php foreach ( cwm_menu() as $i => $item ) : ?>
					<li class="main-nav__item<?php echo ! empty( $item['children'] ) ? ' has-children' : ''; ?><?php echo $item['current'] ? ' is-current' : ''; ?>">
						<a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $item['label'] ); ?></a>
						<?php if ( ! empty( $item['children'] ) ) : ?>
							<button class="submenu-toggle" type="button" aria-expanded="false" aria-controls="submenu-<?php echo (int) $i; ?>"><span class="sr-only"><?php echo esc_html( $item['label'] ); ?> submenu</span><?php echo cwm_icon( 'chevron-down' ); // phpcs:ignore ?></button>
							<ul class="submenu" id="submenu-<?php echo (int) $i; ?>">
								<?php foreach ( $item['children'] as $child ) : ?>
									<li><a href="<?php echo esc_url( $child['url'] ); ?>"><?php echo ! empty( $child['icon'] ) ? cwm_icon( $child['icon'] ) : ''; // phpcs:ignore ?><span><?php echo esc_html( $child['label'] ); ?></span></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="main-nav__mobile-extra">
				<a href="<?php echo esc_url( cwm_tel_url() ); ?>"><?php echo cwm_icon( 'phone' ); // phpcs:ignore ?> <?php echo esc_html( cwm_opt( 'phone' ) ); ?></a>
				<a href="mailto:<?php echo esc_attr( cwm_opt( 'email' ) ); ?>"><?php echo cwm_icon( 'mail' ); // phpcs:ignore ?> <?php echo esc_html( cwm_opt( 'email' ) ); ?></a>
			</div>
		</nav>

		<div class="site-header__actions">
			<a class="btn btn--primary btn--header" href="<?php echo esc_url( cwm_page_url( 'proposal' ) ); ?>"><span class="hide-xs">Request a proposal</span><span class="show-xs">Get a proposal</span></a>
			<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav">
				<span class="sr-only">Menu</span>
				<span class="nav-toggle__open"><?php echo cwm_icon( 'menu' ); // phpcs:ignore ?></span>
				<span class="nav-toggle__close"><?php echo cwm_icon( 'x' ); // phpcs:ignore ?></span>
			</button>
		</div>
	</div>
</header>

<main id="main" class="site-main">
