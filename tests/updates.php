<?php
/**
 * Updates from GitHub releases (inc/updates.php), without WordPress: only a newer, published
 * version with the zip from this repository counts.
 *
 *   php tests/updates.php
 *
 * @package DaedaliaThema
 */
define( 'ABSPATH', __DIR__ );
function add_filter() {}
require dirname( __DIR__ ) . '/inc/updates.php';

$zip     = 'https://github.com/leoniebe/daedalia-theme/releases/download/v0.4.0/daedalia-thema.zip';
$release = static fn( array $changes = array() ): array => array_merge(
	array(
		'tag_name' => 'v0.4.0',
		'assets'   => array(
			array(
				'name'                 => 'daedalia-thema.zip',
				'browser_download_url' => $zip,
			),
		),
	),
	$changes
);
$cases   = array(
	array( $release(), '0.3.0', '0.4.0', 'a newer release' ),
	array( $release(), '0.4.0', null, 'the same version' ),
	array( $release(), '0.5.0', null, 'an older release' ),
	array( $release( array( 'draft' => true ) ), '0.3.0', null, 'a draft' ),
	array( $release( array( 'prerelease' => true ) ), '0.3.0', null, 'a pre-release' ),
	array( $release( array( 'tag_name' => 'nieuw' ) ), '0.3.0', null, 'a tag that is no version' ),
	array( $release( array( 'assets' => array( array( 'name' => 'daedalia-thema.zip', 'browser_download_url' => 'https://evil.example/daedalia-thema.zip' ) ) ) ), '0.3.0', null, 'a zip from elsewhere' ),
	array( $release( array( 'assets' => array( array( 'name' => 'iets-anders.zip', 'browser_download_url' => $zip ) ) ) ), '0.3.0', null, 'another file' ),
	array( array(), '0.3.0', null, 'no answer from GitHub' ),
);
$fail = 0;
foreach ( $cases as [ $given, $current, $want, $label ] ) {
	$update = daedalia_thema_update_from( $given, $current );
	$got    = $update['new_version'] ?? null;
	$ok     = $got === $want && ( null === $update || $zip === $update['package'] );
	echo ( $ok ? 'ok   ' : 'FOUT ' ) . $label . "\n";
	$fail  += $ok ? 0 : 1;
}
exit( $fail ? 1 : 0 );
