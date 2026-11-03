<?php
/**
 * The theme's settings: Weergave › Daedalia-thema. Every part can be switched off on its own.
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

/**
 * All parts: key => group, label, explanation, on by default. What could break something (an old
 * plugin, a site that later goes back to http) is off by default.
 *
 * @return array<string, array{groep: string, label: string, uitleg: string, standaard: bool}>
 */
function daedalia_thema_parts(): array {
	$part = static fn( string $group, string $label, string $explanation, bool $on_by_default = true ): array => array(
		'groep'     => $group,
		'label'     => $label,
		'uitleg'    => $explanation,
		'standaard' => $on_by_default,
	);

	return array(
		// Afslanken.
		'emoji'           => $part( 'afslanken', __( 'Remove the emoji script', 'daedalia-thema' ), __( 'Browsers show emoji themselves; WordPress’s script and style aren’t needed.', 'daedalia-thema' ) ),
		'embed'           => $part( 'afslanken', __( 'Remove wp-embed', 'daedalia-thema' ), __( 'The script for embedding this site in other sites.', 'daedalia-thema' ) ),
		'blokken'         => $part( 'afslanken', __( 'Block styles only where there are blocks', 'daedalia-thema' ), __( 'The block editor’s styles load only on pages with blocks, and on cart, checkout and my account.', 'daedalia-thema' ) ),
		'head'            => $part( 'afslanken', __( 'Remove unneeded lines from the head', 'daedalia-thema' ), __( 'RSD, Windows Live Writer and the shortlink.', 'daedalia-thema' ) ),
		'versie'          => $part( 'afslanken', __( 'No WordPress version in the HTML', 'daedalia-thema' ), __( 'The generator tag and the version number after WordPress’s own scripts and styles.', 'daedalia-thema' ) ),
		'dashicons'       => $part( 'afslanken', __( 'No Dashicons for visitors', 'daedalia-thema' ), __( 'The admin icon font loads only for logged-in users (the admin bar).', 'daedalia-thema' ) ),
		'winkelbestanden' => $part( 'afslanken', __( 'WooCommerce CSS only on shop pages', 'daedalia-thema' ), __( 'WooCommerce’s styles don’t load on pages without the shop. Turn this off if a WooCommerce shortcode is on a regular page. Daedalia handles WooCommerce’s scripts (Daedalia › Settings › WooCommerce).', 'daedalia-thema' ) ),
		'migrate'         => $part( 'afslanken', __( 'Remove jQuery Migrate', 'daedalia-thema' ), __( 'Only old plugins need this. Turn it on and check that everything still works.', 'daedalia-thema' ), false ),
		// Beveiliging.
		'xmlrpc'          => $part( 'beveiliging', __( 'Turn off XML-RPC', 'daedalia-thema' ), __( 'An old way in for apps that is often used for login attacks. Turn this off if you need the Jetpack app or an old publishing app.', 'daedalia-thema' ) ),
		'gebruikers'      => $part( 'beveiliging', __( 'User list closed to guests', 'daedalia-thema' ), __( 'The REST route with the users (and so the usernames) is only for those allowed to see users.', 'daedalia-thema' ) ),
		'auteurs'         => $part( 'beveiliging', __( 'Authors can’t be guessed', 'daedalia-thema' ), __( 'An address like ?author=1 no longer gives away a username.', 'daedalia-thema' ) ),
		'inlogfouten'     => $part( 'beveiliging', __( 'Login errors give nothing away', 'daedalia-thema' ), __( 'One message for a wrong name and a wrong password.', 'daedalia-thema' ) ),
		'inlogpogingen'   => $part( 'beveiliging', __( 'Limit login attempts', 'daedalia-thema' ), __( 'After 5 failed attempts with the same name, or 20 from the same address, wait 15 minutes.', 'daedalia-thema' ) ),
		'bewerker'        => $part( 'beveiliging', __( 'Turn off the file editor', 'daedalia-thema' ), __( 'Theme and plugin files can’t be edited in the admin.', 'daedalia-thema' ) ),
		// Headers.
		'nosniff'         => $part( 'headers', __( 'X-Content-Type-Options', 'daedalia-thema' ), __( 'The browser doesn’t guess the file type itself.', 'daedalia-thema' ) ),
		'referrer'        => $part( 'headers', __( 'Referrer-Policy', 'daedalia-thema' ), __( 'Other sites see only the domain a visitor comes from, not the whole link.', 'daedalia-thema' ) ),
		'frame'           => $part( 'headers', __( 'X-Frame-Options (this site only)', 'daedalia-thema' ), __( 'Other sites can’t show this site in a frame (against clickjacking). Daedalia’s editor keeps working.', 'daedalia-thema' ) ),
		'permissies'      => $part( 'headers', __( 'Permissions-Policy', 'daedalia-thema' ), __( 'Camera, microphone and location are off for the page and embedded content. Payments still work.', 'daedalia-thema' ) ),
		'csp'             => $part( 'headers', __( 'Content-Security-Policy, report only', 'daedalia-thema' ), __( 'A CSP in report mode: the browser reports in the console what a strict CSP would block, but blocks nothing.', 'daedalia-thema' ), false ),
		'hsts'            => $part( 'headers', __( 'Strict-Transport-Security (HSTS)', 'daedalia-thema' ), __( 'Only on https. Browsers remember for a year that the site is https only; going back to http is then no longer possible.', 'daedalia-thema' ), false ),
	);
}

/**
 * Whether a part is on: the stored setting, or its default.
 *
 * @param string $key Part.
 */
function daedalia_thema_on( string $key ): bool {
	$parts  = daedalia_thema_parts();
	$stored = get_option( 'daedalia_thema_instellingen', array() );

	return is_array( $stored ) && array_key_exists( $key, $stored ) ? (bool) $stored[ $key ] : ! empty( $parts[ $key ]['standaard'] );
}

/**
 * The settings page.
 */
function daedalia_thema_settings_menu(): void {
	add_theme_page( __( 'Daedalia theme', 'daedalia-thema' ), __( 'Daedalia theme', 'daedalia-thema' ), 'manage_options', 'daedalia-thema', 'daedalia_thema_settings_page' );
}
add_action( 'admin_menu', 'daedalia_thema_settings_menu' );

/**
 * Store the settings (all parts, on or off).
 */
function daedalia_thema_settings_save(): void {
	if ( ! isset( $_POST['daedalia_thema_opslaan'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'daedalia_thema_instellingen' );

	$values = array();
	foreach ( array_keys( daedalia_thema_parts() ) as $key ) {
		$values[ $key ] = ! empty( $_POST['daedalia_thema'][ $key ] );
	}
	update_option( 'daedalia_thema_instellingen', $values, true );

	wp_safe_redirect( add_query_arg( 'opgeslagen', '1', admin_url( 'themes.php?page=daedalia-thema' ) ) );
	exit;
}
add_action( 'admin_init', 'daedalia_thema_settings_save' );

/**
 * The page: per group a fieldset with a checkbox and explanation per part.
 */
function daedalia_thema_settings_page(): void {
	$groups = array(
		'afslanken'   => __( 'Slimming down: load less', 'daedalia-thema' ),
		'beveiliging' => __( 'Security', 'daedalia-thema' ),
		'headers'     => __( 'Security headers', 'daedalia-thema' ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Daedalia theme', 'daedalia-thema' ); ?></h1>
		<?php if ( isset( $_GET['opgeslagen'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only a notice. ?>
			<div class="notice notice-success" role="status"><p><?php esc_html_e( 'Settings saved.', 'daedalia-thema' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'What the theme turns off, turns on or sends along. Each part stands alone: if something on your site stops working, turn off only that part. Parts that can break something are off by default.', 'daedalia-thema' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'daedalia_thema_instellingen' ); ?>
			<?php foreach ( $groups as $group => $title ) : ?>
				<fieldset class="daedalia-thema-groep">
					<legend><h2><?php echo esc_html( $title ); ?></h2></legend>
					<?php foreach ( daedalia_thema_parts() as $key => $part ) : ?>
						<?php
						if ( $part['groep'] !== $group ) {
							continue;
						}
						?>
						<p>
							<input type="checkbox" id="daedalia-thema-<?php echo esc_attr( $key ); ?>" name="daedalia_thema[<?php echo esc_attr( $key ); ?>]" value="1" aria-describedby="daedalia-thema-<?php echo esc_attr( $key ); ?>-uitleg" <?php checked( daedalia_thema_on( $key ) ); ?>>
							<label for="daedalia-thema-<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $part['label'] ); ?></strong><?php echo $part['standaard'] ? '' : ' <em>' . esc_html__( '(off by default)', 'daedalia-thema' ) . '</em>'; ?></label><br>
							<span class="description" id="daedalia-thema-<?php echo esc_attr( $key ); ?>-uitleg"><?php echo esc_html( $part['uitleg'] ); ?></span>
						</p>
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>
			<p><button type="submit" name="daedalia_thema_opslaan" value="1" class="button button-primary"><?php esc_html_e( 'Save', 'daedalia-thema' ); ?></button></p>
		</form>
	</div>
	<?php
}
