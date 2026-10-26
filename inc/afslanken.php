<?php
/**
 * Leave out what WordPress (and WooCommerce) load by default but a Daedalia site does not use.
 * Each part is a setting (Weergave › Daedalia-thema); the filter daedalia_thema/afslanken can
 * still keep one (return false for its key).
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a part of the trimming is on.
 *
 * @param string $key Part.
 */
function daedalia_thema_trims( string $key ): bool {
	/**
	 * Which default output the theme removes (on top of the settings).
	 *
	 * @param array<string, bool> $parts emoji, embed, blokken, ….
	 */
	$parts = (array) apply_filters( 'daedalia_thema/afslanken', array_map( 'daedalia_thema_on', array_combine( array_keys( daedalia_thema_parts() ), array_keys( daedalia_thema_parts() ) ) ) );

	return ! empty( $parts[ $key ] );
}

/**
 * The removals that hook in early.
 */
function daedalia_thema_trim(): void {
	if ( daedalia_thema_trims( 'emoji' ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}

	if ( daedalia_thema_trims( 'embed' ) ) {
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_action( 'wp_footer', static fn() => wp_dequeue_script( 'wp-embed' ), 1 );
	}

	if ( daedalia_thema_trims( 'head' ) ) {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	}

	if ( daedalia_thema_trims( 'versie' ) ) {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
		add_filter( 'style_loader_src', 'daedalia_thema_strip_version' );
		add_filter( 'script_loader_src', 'daedalia_thema_strip_version' );
	}

	add_action( 'wp_enqueue_scripts', 'daedalia_thema_trim_assets', 100 );
	add_filter( 'woocommerce_enqueue_styles', 'daedalia_thema_shop_styles' );
}
add_action( 'init', 'daedalia_thema_trim' );

/**
 * No WordPress version behind WordPress's own files (other files keep theirs: it is their cache key).
 *
 * @param string $src URL.
 */
function daedalia_thema_strip_version( $src ): string {
	$src = (string) $src;

	return str_contains( $src, 'ver=' . get_bloginfo( 'version' ) ) ? remove_query_arg( 'ver', $src ) : $src;
}

/**
 * Styles and scripts on the front.
 */
function daedalia_thema_trim_assets(): void {
	if ( daedalia_thema_trims( 'blokken' ) ) {
		daedalia_thema_trim_block_styles();
	}

	if ( daedalia_thema_trims( 'dashicons' ) && ! is_user_logged_in() ) {
		wp_dequeue_style( 'dashicons' );
	}
}

/**
 * WooCommerce's stylesheets only on its own pages, through its own filter. (A WooCommerce block
 * loads its styles itself where it is used; WooCommerce's scripts are Daedalia's: Woo\Scripts.)
 *
 * @param array<string, array<string, mixed>> $styles WooCommerce's stylesheets.
 * @return array<string, array<string, mixed>>
 */
function daedalia_thema_shop_styles( $styles ): array {
	$shop = is_woocommerce() || is_cart() || is_checkout() || is_account_page();

	return daedalia_thema_trims( 'winkelbestanden' ) && ! $shop ? array() : (array) $styles;
}

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
 * Loads jQuery without jQuery Migrate on the front (only when switched on: old plugins may need it).
 *
 * @param WP_Scripts $scripts Scripts.
 */
function daedalia_thema_no_migrate( $scripts ): void {
	if ( is_admin() || ! daedalia_thema_trims( 'migrate' ) || empty( $scripts->registered['jquery'] ) ) {
		return;
	}
	$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
}
add_action( 'wp_default_scripts', 'daedalia_thema_no_migrate' );
