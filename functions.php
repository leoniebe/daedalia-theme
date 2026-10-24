<?php
/**
 * Daedalia-thema: a light classic theme for the Daedalia page builder.
 *
 * The theme does almost nothing itself. Daedalia renders the header, footer and content
 * (Theme Builder templates), and the design system (colours, fonts, format) lives in
 * Daedalia › Site-instellingen. Daedalia is bundled in daedalia/ (see the README), or runs
 * as a plugin; an active plugin loads first and wins.
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

define( 'DAEDALIA_THEMA_VERSION', '0.2.1' );

// Daedalia bundled in this theme, unless the plugin already runs.
if ( ! defined( 'DAEDALIA_VERSION' ) && is_readable( __DIR__ . '/daedalia/daedalia.php' ) ) {
	define( 'DAEDALIA_BUNDLED', true );
	require_once __DIR__ . '/daedalia/daedalia.php';
}

/**
 * Whether Daedalia runs (bundled or as a plugin).
 */
function daedalia_thema_has_builder(): bool {
	return class_exists( '\Daedalia\Plugin' ) && null !== \Daedalia\Plugin::instance();
}

/**
 * Theme supports, menus and translations.
 */
function daedalia_thema_setup(): void {
	load_theme_textdomain( 'daedalia-thema', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	// Fallback only: Daedalia's own logo (Site-instellingen › Identiteit) comes first.
	add_theme_support( 'custom-logo', array( 'flex-width' => true, 'flex-height' => true ) );

	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'hoofdmenu' => __( 'Hoofdmenu', 'daedalia-thema' ),
			'voetmenu'  => __( 'Menu in de footer', 'daedalia-thema' ),
		)
	);
}
add_action( 'after_setup_theme', 'daedalia_thema_setup' );

/**
 * The theme's only stylesheet: a small reset and the skip link.
 */
function daedalia_thema_assets(): void {
	wp_enqueue_style( 'daedalia-thema', get_template_directory_uri() . '/assets/basis.css', array(), DAEDALIA_THEMA_VERSION );
}
add_action( 'wp_enqueue_scripts', 'daedalia_thema_assets', 5 );

// Settings (Weergave › Daedalia-thema), trimming, security and headers: each part on or off.
require_once __DIR__ . '/inc/instellingen.php';
require_once __DIR__ . '/inc/afslanken.php';
require_once __DIR__ . '/inc/beveiliging.php';

/**
 * Without Daedalia the theme still works, but says what is missing.
 */
function daedalia_thema_notice(): void {
	if ( daedalia_thema_has_builder() || ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'Het Daedalia-thema werkt samen met de Daedalia-pagebuilder. Zet de map daedalia/ in het thema (npm run bundel) of activeer de plugin Daedalia.', 'daedalia-thema' )
	);
}
add_action( 'admin_notices', 'daedalia_thema_notice' );

/**
 * Pages built with Daedalia set their own H1; the theme then prints no title.
 *
 * @param int $post_id Post ID.
 */
function daedalia_thema_built( int $post_id ): bool {
	return daedalia_thema_has_builder() && \Daedalia\Document::exists( $post_id );
}

/**
 * Whether the theme prints the title of a page or post without Daedalia content: not in Daedalia's
 * editor, and not when the page hides it (Pagina-instellingen › Titel van het thema).
 *
 * @param int $post_id Post ID.
 */
function daedalia_thema_show_title( int $post_id ): bool {
	return ! daedalia_thema_has_builder() || ! class_exists( '\Daedalia\Page_Title' ) || \Daedalia\Page_Title::show( $post_id );
}

/**
 * WooCommerce: the theme's own wrapper (main is opened in the template).
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
