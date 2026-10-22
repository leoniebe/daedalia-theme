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

define( 'DAEDALIA_THEMA_VERSION', '0.1.0' );

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

/**
 * Leave out what WordPress loads by default but a Daedalia site does not use:
 * the emoji script and styles, wp-embed, and the block library and global styles
 * on pages without blocks. Each part can be kept with the filter
 * `daedalia_thema/afslanken` (return false for a key to keep it).
 */
function daedalia_thema_trim(): void {
	/**
	 * Which default WordPress output the theme removes.
	 *
	 * @param array<string, bool> $parts emoji, embed, blokken.
	 */
	$parts = (array) apply_filters(
		'daedalia_thema/afslanken',
		array(
			'emoji'   => true,
			'embed'   => true,
			'blokken' => true,
		)
	);

	if ( ! empty( $parts['emoji'] ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}

	if ( ! empty( $parts['embed'] ) ) {
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_action( 'wp_footer', static fn() => wp_dequeue_script( 'wp-embed' ), 1 );
	}

	if ( ! empty( $parts['blokken'] ) ) {
		add_action( 'wp_enqueue_scripts', 'daedalia_thema_trim_block_styles', 100 );
	}
}
add_action( 'init', 'daedalia_thema_trim' );

/**
 * Block library and global styles only where the content has blocks.
 */
function daedalia_thema_trim_block_styles(): void {
	if ( is_singular() && has_blocks( get_queried_object_id() ) ) {
		return;
	}
	// WooCommerce's own blocks (cart, checkout) need their styles.
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		return;
	}
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles', 'core-block-supports' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
}

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
