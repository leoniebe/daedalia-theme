<?php
/**
 * The visitor address of the login limiter (inc/beveiliging.php), without WordPress: a header
 * counts only from a trusted proxy, and the ranges are matched to the bit.
 *
 *   php tests/ip.php
 *
 * @package DaedaliaThema
 */
define( 'ABSPATH', __DIR__ );
function wp_unslash( $v ) { return $v; }
function apply_filters( $h, $v ) { return $v; }
function add_action() {} function add_filter() {}
require dirname( __DIR__ ) . "/inc/beveiliging.php";
$cases = [
	[ '203.0.113.5', null, '203.0.113.5', 'direct visitor' ],
	[ '203.0.113.5', '1.2.3.4', '203.0.113.5', 'header from a non-proxy is ignored' ],
	[ '172.70.1.2', '198.51.100.7', '198.51.100.7', 'Cloudflare v4: header used' ],
	[ '104.16.0.1', 'nonsense', '104.16.0.1', 'Cloudflare, invalid header: proxy address' ],
	[ '2606:4700:10::ac43:1', '2001:db8::1', '2001:db8::1', 'Cloudflare v6: header used' ],
	[ '2606:4701::1', '2001:db8::1', '2606:4701::1', 'just outside 2606:4700::/32' ],
	[ '172.63.255.255', '198.51.100.7', '172.63.255.255', 'just below 172.64.0.0/13' ],
	[ '172.71.255.255', '198.51.100.7', '198.51.100.7', 'top of 172.64.0.0/13' ],
	[ '172.72.0.0', '198.51.100.7', '172.72.0.0', 'just above 172.64.0.0/13' ],
];
$fail = 0;
foreach ( $cases as [ $remote, $header, $want, $label ] ) {
	$_SERVER['REMOTE_ADDR'] = $remote;
	if ( null === $header ) { unset( $_SERVER['HTTP_CF_CONNECTING_IP'] ); } else { $_SERVER['HTTP_CF_CONNECTING_IP'] = $header; }
	$got = daedalia_thema_ip();
	echo ( $got === $want ? 'OK   ' : 'FAIL ' ) . "$label ($got)\n";
	$fail += $got === $want ? 0 : 1;
}
exit( $fail ? 1 : 0 );
