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
		add_filter(
			'wp_headers',
			static function ( array $headers ): array {
				unset( $headers['X-Pingback'] );
				return $headers;
			}
		);
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
		return new WP_Error( 'daedalia_thema_inloggen', __( 'Login failed. Check your username or email address and your password.', 'daedalia-thema' ) );
	}
	return $user;
}

/**
 * Cloudflare's addresses, as published on https://www.cloudflare.com/ips/ (26-10-2026). A request
 * from one of them carries the visitor's address in CF-Connecting-IP.
 */
const DAEDALIA_THEMA_CLOUDFLARE = array(
	'173.245.48.0/20',
	'103.21.244.0/22',
	'103.22.200.0/22',
	'103.31.4.0/22',
	'141.101.64.0/18',
	'108.162.192.0/18',
	'190.93.240.0/20',
	'188.114.96.0/20',
	'197.234.240.0/22',
	'198.41.128.0/17',
	'162.158.0.0/15',
	'104.16.0.0/13',
	'104.24.0.0/14',
	'172.64.0.0/13',
	'131.0.72.0/22',
	'2400:cb00::/32',
	'2606:4700::/32',
	'2803:f800::/32',
	'2405:b500::/32',
	'2405:8100::/32',
	'2a06:98c0::/29',
	'2c0f:f248::/32',
);

/**
 * The address of the visitor. That is the address the server sees, unless the request comes from
 * a trusted proxy (Cloudflare): then the proxy's header. A visitor cannot choose it: the header
 * counts only from the proxy's own addresses. Behind Cloudflare the server sees only Cloudflare,
 * so without this every visitor would share a few addresses and the limiter would lock them all out.
 */
function daedalia_thema_ip(): string {
	$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP ) : false;
	if ( ! is_string( $remote ) ) {
		return '';
	}

	/**
	 * Proxies whose header with the visitor's address is trusted.
	 *
	 * @param array<string, string> $proxies Address range (CIDR) => $_SERVER key of the header.
	 */
	$proxies = (array) apply_filters( 'daedalia_thema/proxies', array_fill_keys( DAEDALIA_THEMA_CLOUDFLARE, 'HTTP_CF_CONNECTING_IP' ) );
	foreach ( $proxies as $range => $header ) {
		if ( daedalia_thema_in_range( $remote, (string) $range ) ) {
			$visitor = isset( $_SERVER[ $header ] ) ? filter_var( wp_unslash( $_SERVER[ $header ] ), FILTER_VALIDATE_IP ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated as an IP address.
			return is_string( $visitor ) ? $visitor : $remote;
		}
	}
	return $remote;
}

// Daedalia's forms count their sends per visitor: give them the same address as the limiter, always.
add_filter(
	'daedalia/bezoeker_ip',
	static function ( string $ip ): string {
		$visitor = daedalia_thema_ip();
		return '' !== $visitor ? $visitor : $ip;
	}
);

/**
 * Whether an address lies in a range (CIDR, IPv4 or IPv6).
 *
 * @param string $ip    Address.
 * @param string $range Range, e.g. 103.21.244.0/22.
 */
function daedalia_thema_in_range( string $ip, string $range ): bool {
	$parts  = explode( '/', $range, 2 );
	$packed = inet_pton( $ip );
	$subnet = inet_pton( $parts[0] );
	if ( false === $packed || false === $subnet || strlen( $packed ) !== strlen( $subnet ) ) {
		return false;
	}
	$bits  = isset( $parts[1] ) ? (int) $parts[1] : strlen( $packed ) * 8;
	$bytes = intdiv( $bits, 8 );
	if ( substr( $packed, 0, $bytes ) !== substr( $subnet, 0, $bytes ) ) {
		return false;
	}
	$rest = $bits % 8;
	if ( 0 === $rest ) {
		return true;
	}
	$mask = chr( ( 0xff << ( 8 - $rest ) ) & 0xff );
	return ( $packed[ $bytes ] & $mask ) === ( $subnet[ $bytes ] & $mask );
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
		return new WP_Error( 'daedalia_thema_te_vaak', __( 'Too many failed attempts. Try again in 15 minutes.', 'daedalia-thema' ) );
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
