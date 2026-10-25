<?php
/**
 * PHPStan: the part of Daedalia the theme uses, as declared in Daedalia (src/Document.php and
 * src/Site_Identity.php). This file is the whole contract between theme and builder: when the
 * theme starts using more of Daedalia, it is added here, and a change on Daedalia's side to
 * anything listed here must keep the theme working.
 *
 * @package Daedalia_Thema
 */

namespace Daedalia;

/**
 * A post built with Daedalia.
 */
final class Document {
	/**
	 * Whether the post has a Daedalia document.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function exists( int $post_id ): bool {
		return $post_id > 0;
	}
}

/**
 * Logo and name of the site, from Daedalia › Site-instellingen.
 */
final class Site_Identity {
	/**
	 * The attachment ID of the logo, 0 without one.
	 */
	public static function logo_id(): int {
		return 0;
	}
}
