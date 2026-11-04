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

define( 'DAEDALIA_THEMA_VERSION', '0.3.0' );

// The oldest Daedalia this theme works with: its templates call Daedalia's API. npm run bundel
// refuses an older one, and an older plugin gets a notice.
define( 'DAEDALIA_THEMA_DAEDALIA_MIN', '0.9.1' );

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
	add_theme_support(
		'custom-logo',
		array(
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'hoofdmenu' => __( 'Main menu', 'daedalia-thema' ),
			'voetmenu'  => __( 'Footer menu', 'daedalia-thema' ),
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
require_once __DIR__ . '/inc/updates.php';

/**
 * Without Daedalia the theme still works, but says what is missing; with a plugin that is too old,
 * or older than the copy in the theme, it says that.
 */
function daedalia_thema_notice(): void {
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	$message = daedalia_thema_has_builder()
		? daedalia_thema_version_problem()
		: __( 'The Daedalia theme works with the Daedalia page builder. Put the daedalia/ folder in the theme (npm run bundel) or activate the Daedalia plugin.', 'daedalia-thema' );
	if ( '' !== $message ) {
		printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html( $message ) );
	}
}

/**
 * What is wrong with the version of the Daedalia that runs, or ''. The bundled copy is checked
 * when it is bundled; an active plugin goes first, so it can be older than both.
 */
function daedalia_thema_version_problem(): string {
	if ( defined( 'DAEDALIA_BUNDLED' ) || ! defined( 'DAEDALIA_VERSION' ) ) {
		return '';
	}
	$bundled = is_readable( __DIR__ . '/daedalia/daedalia.php' ) ? (string) get_file_data( __DIR__ . '/daedalia/daedalia.php', array( 'version' => 'Version' ) )['version'] : '';

	if ( version_compare( DAEDALIA_VERSION, DAEDALIA_THEMA_DAEDALIA_MIN, '<' ) ) {
		return '' !== $bundled
			/* translators: 1: running Daedalia version, 2: minimum version, 3: version bundled in the theme */
			? sprintf( __( 'The Daedalia plugin %1$s is too old for this theme (%2$s or newer). Update the plugin, or deactivate it: then Daedalia %3$s runs from the theme.', 'daedalia-thema' ), DAEDALIA_VERSION, DAEDALIA_THEMA_DAEDALIA_MIN, $bundled )
			/* translators: 1: running Daedalia version, 2: minimum version */
			: sprintf( __( 'The Daedalia plugin %1$s is too old for this theme (%2$s or newer). Update the plugin.', 'daedalia-thema' ), DAEDALIA_VERSION, DAEDALIA_THEMA_DAEDALIA_MIN );
	}
	if ( '' !== $bundled && version_compare( DAEDALIA_VERSION, $bundled, '<' ) ) {
		/* translators: 1: plugin version, 2: version bundled in the theme */
		return sprintf( __( 'The Daedalia plugin %1$s is older than Daedalia %2$s in the theme, and an active plugin comes first. Update or deactivate the plugin.', 'daedalia-thema' ), DAEDALIA_VERSION, $bundled );
	}
	return '';
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
