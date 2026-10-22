<?php
/**
 * Security: closing what attackers use, limiting login attempts, and security headers. Each part
 * is a setting (Weergave › Daedalia-thema); what could break something is off by default.
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

/**
 * The hooks, per part.
 */
function daedalia_thema_security(): void {
	if ( daedalia_thema_on( 'xmlrpc' ) ) {
		// xmlrpc.php itself answers nothing (WordPress keeps its system methods otherwise).
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			status_header( 403 );
			exit;
		}
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', static fn(): array => array() );
		add_filter( 'wp_headers', static function ( array $headers ): array {
			unset( $headers['X-Pingback'] );
			return $headers;
		} );
		add_filter( 'pings_open', '__return_false', 20 );
	}

	if ( daedalia_thema_on( 'gebruikers' ) ) {
		add_filter( 'rest_endpoints', 'daedalia_thema_rest_users' );
	}

	if ( daedalia_thema_on( 'auteurs' ) ) {
		add_action( 'template_redirect', 'daedalia_thema_no_author_scan', 1 );
	}

	if ( daedalia_thema_on( 'inlogfouten' ) ) {
		add_filter( 'authenticate', 'daedalia_thema_login_error', 99 );
	}

	if ( daedalia_thema_on( 'inlogpogingen' ) ) {
		add_filter( 'authenticate', 'daedalia_thema_login_blocked', 30, 2 );
		add_action( 'wp_login_failed', 'daedalia_thema_login_failed' );
	}

	if ( daedalia_thema_on( 'bewerker' ) ) {
		add_filter( 'map_meta_cap', 'daedalia_thema_no_file_editor', 10, 2 );
	}

	add_action( 'send_headers', 'daedalia_thema_headers' );
}
add_action( 'init', 'daedalia_thema_security', 1 );

/**
 * The REST routes of the users only for who may see users.
 *
 * @param array $endpoints Routes.
 */
function daedalia_thema_rest_users( array $endpoints ): array {
	if ( current_user_can( 'list_users' ) ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( str_starts_with( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
}

/**
 * ?author=1 (and /author/<name>/ found that way) tells no username: for guests a 404.
 */
function daedalia_thema_no_author_scan(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only read, to refuse.
	if ( ! is_user_logged_in() && isset( $_GET['author'] ) && ! is_admin() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}

/**
 * One message for a wrong name and a wrong password (other messages, such as too many attempts, stay).
 *
 * @param WP_User|WP_Error|null $user User so far.
 * @return WP_User|WP_Error|null
 */
function daedalia_thema_login_error( $user ) {
	if ( is_wp_error( $user ) && array_intersect( $user->get_error_codes(), array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
		return new WP_Error( 'daedalia_thema_inloggen', __( 'Inloggen is niet gelukt. Controleer je gebruikersnaam of e-mailadres en je wachtwoord.', 'daedalia-thema' ) );
	}
	return $user;
}

/**
 * The address of the visitor, as the server sees it (no headers a visitor can choose).
 */
function daedalia_thema_ip(): string {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? (string) preg_replace( '/[^0-9a-f:.]/i', '', (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
}

/**
 * The keys of the counters: per address, and per address and name.
 *
 * @param string $username Name.
 * @return string[]
 */
function daedalia_thema_login_keys( string $username ): array {
	$ip = daedalia_thema_ip();
	return array(
		'adres' => 'daedalia_thema_inlog_' . md5( $ip ),
		'naam'  => 'daedalia_thema_inlog_' . md5( $ip . '|' . strtolower( trim( $username ) ) ),
	);
}

/**
 * A failed login: count it (15 minutes).
 *
 * @param string $username Name.
 */
function daedalia_thema_login_failed( $username ): void {
	foreach ( daedalia_thema_login_keys( (string) $username ) as $key ) {
		set_transient( $key, (int) get_transient( $key ) + 1, 15 * MINUTE_IN_SECONDS );
	}
}

/**
 * Too many failures: refuse before the password is checked.
 *
 * @param WP_User|WP_Error|null $user     User so far.
 * @param string                $username Name.
 * @return WP_User|WP_Error|null
 */
function daedalia_thema_login_blocked( $user, $username ) {
	if ( '' === (string) $username ) {
		return $user;
	}
	$keys = daedalia_thema_login_keys( (string) $username );
	if ( (int) get_transient( $keys['naam'] ) >= 5 || (int) get_transient( $keys['adres'] ) >= 20 ) {
		return new WP_Error( 'daedalia_thema_te_vaak', __( 'Te veel mislukte pogingen. Probeer het over 15 minuten opnieuw.', 'daedalia-thema' ) );
	}
	return $user;
}

/**
 * No editing of theme and plugin files in the admin.
 *
 * @param string[] $caps Capabilities.
 * @param string   $cap  Capability asked.
 * @return string[]
 */
function daedalia_thema_no_file_editor( $caps, $cap ): array {
	return in_array( $cap, array( 'edit_themes', 'edit_plugins', 'edit_files' ), true ) ? array( 'do_not_allow' ) : (array) $caps;
}

/**
 * The security headers that are switched on.
 */
function daedalia_thema_headers(): void {
	if ( headers_sent() ) {
		return;
	}
	if ( daedalia_thema_on( 'nosniff' ) ) {
		header( 'X-Content-Type-Options: nosniff' );
	}
	if ( daedalia_thema_on( 'referrer' ) ) {
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	}
	if ( daedalia_thema_on( 'frame' ) ) {
		header( 'X-Frame-Options: SAMEORIGIN' );
	}
	if ( daedalia_thema_on( 'permissies' ) ) {
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
	}
	if ( daedalia_thema_on( 'csp' ) ) {
		/**
		 * The policy that is reported on (not enforced).
		 *
		 * @param string $policy CSP.
		 */
		header( 'Content-Security-Policy-Report-Only: ' . (string) apply_filters( 'daedalia_thema/csp', "default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' https:; font-src 'self' data: https:; connect-src 'self' https:; frame-src 'self' https:; frame-ancestors 'self'; base-uri 'self'; form-action 'self' https:" ) );
	}
	if ( daedalia_thema_on( 'hsts' ) && is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000' );
	}
}
