<?php
/**
 * Updates of the theme from its GitHub releases (github.com/leoniebe/daedalia-theme). Each
 * version is a release vX.Y.Z with the zip daedalia-thema.zip; WordPress shows the update under
 * Dashboard › Updates like any other.
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

/** The repository the releases come from. */
const DAEDALIA_THEMA_REPO = 'leoniebe/daedalia-theme';

/** The zip attached to every release. */
const DAEDALIA_THEMA_ZIP = 'daedalia-thema.zip';

/**
 * The update a release offers, or null: only a newer version, only a zip on GitHub over https.
 *
 * @param array<string, mixed> $release The release as GitHub's API gives it.
 * @param string               $current The installed version.
 * @return array{new_version: string, package: string, url: string}|null
 */
function daedalia_thema_update_from( array $release, string $current ): ?array {
	$version = ltrim( (string) ( $release['tag_name'] ?? '' ), 'v' );
	if ( ! preg_match( '/^\d+\.\d+\.\d+$/', $version ) || version_compare( $version, $current, '<=' ) || ! empty( $release['draft'] ) || ! empty( $release['prerelease'] ) ) {
		return null;
	}

	foreach ( (array) ( $release['assets'] ?? array() ) as $asset ) {
		$url = (string) ( $asset['browser_download_url'] ?? '' );
		if ( DAEDALIA_THEMA_ZIP === ( $asset['name'] ?? '' ) && str_starts_with( $url, 'https://github.com/' . DAEDALIA_THEMA_REPO . '/releases/download/' ) ) {
			return array(
				'new_version' => $version,
				'package'     => $url,
				'url'         => 'https://github.com/' . DAEDALIA_THEMA_REPO . '/releases/tag/v' . $version,
			);
		}
	}
	return null;
}

/**
 * The newest release, asked of GitHub at most twice a day (an hour after a failure).
 *
 * @return array<string, mixed>
 */
function daedalia_thema_latest_release(): array {
	$cached = get_site_transient( 'daedalia_thema_release' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$response = wp_safe_remote_get(
		'https://api.github.com/repos/' . DAEDALIA_THEMA_REPO . '/releases/latest',
		array(
			'timeout' => 10,
			'headers' => array( 'Accept' => 'application/vnd.github+json' ),
		)
	);
	$release  = 200 === wp_remote_retrieve_response_code( $response ) ? json_decode( wp_remote_retrieve_body( $response ), true ) : null;
	$release  = is_array( $release ) ? $release : array();

	set_site_transient( 'daedalia_thema_release', $release, $release ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );
	return $release;
}

add_filter(
	'pre_set_site_transient_update_themes',
	static function ( $transient ) {
		if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
			return $transient;
		}

		// This file runs only with the theme active (as itself or as a child theme's parent).
		$theme  = wp_get_theme( get_template() );
		$update = daedalia_thema_update_from( daedalia_thema_latest_release(), (string) $theme->get( 'Version' ) );
		if ( null !== $update ) {
			$transient->response[ $theme->get_template() ] = array( 'theme' => $theme->get_template() ) + $update;
		}
		return $transient;
	}
);
