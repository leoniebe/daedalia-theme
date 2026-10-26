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
		'emoji'           => $part( 'afslanken', __( 'Emoji-script weg', 'daedalia-thema' ), __( 'Browsers tonen emoji zelf; het script en de stijl van WordPress zijn niet nodig.', 'daedalia-thema' ) ),
		'embed'           => $part( 'afslanken', __( 'wp-embed weg', 'daedalia-thema' ), __( 'Het script om deze site in andere sites in te sluiten.', 'daedalia-thema' ) ),
		'blokken'         => $part( 'afslanken', __( 'Blokstijlen alleen waar blokken staan', 'daedalia-thema' ), __( 'De stijlen van de blokeditor laden alleen op pagina’s met blokken, en bij winkelwagen, afrekenen en mijn account.', 'daedalia-thema' ) ),
		'head'            => $part( 'afslanken', __( 'Overbodige regels in de head weg', 'daedalia-thema' ), __( 'RSD, Windows Live Writer en de korte link.', 'daedalia-thema' ) ),
		'versie'          => $part( 'afslanken', __( 'Geen WordPress-versie in de HTML', 'daedalia-thema' ), __( 'De generator-tag en het versienummer achter scripts en stijlen van WordPress zelf.', 'daedalia-thema' ) ),
		'dashicons'       => $part( 'afslanken', __( 'Geen dashicons voor bezoekers', 'daedalia-thema' ), __( 'Het iconenlettertype van het beheer laadt alleen voor wie is ingelogd (de beheerbalk).', 'daedalia-thema' ) ),
		'winkelbestanden' => $part( 'afslanken', __( 'WooCommerce-CSS alleen op winkelpagina’s', 'daedalia-thema' ), __( 'De stijlen van WooCommerce laden niet op pagina’s zonder winkel. Zet het uit als een WooCommerce-shortcode op een gewone pagina staat. De scripts van WooCommerce regelt Daedalia (Daedalia › Instellingen › WooCommerce).', 'daedalia-thema' ) ),
		'migrate'         => $part( 'afslanken', __( 'jQuery Migrate weg', 'daedalia-thema' ), __( 'Alleen oude plugins hebben dit nodig. Zet het aan en kijk of alles nog werkt.', 'daedalia-thema' ), false ),
		// Beveiliging.
		'xmlrpc'          => $part( 'beveiliging', __( 'XML-RPC uit', 'daedalia-thema' ), __( 'Een oude toegang voor apps die vaak voor inlogaanvallen wordt gebruikt. Zet het aan als je de Jetpack-app of een oude publicatie-app nodig hebt.', 'daedalia-thema' ) ),
		'gebruikers'      => $part( 'beveiliging', __( 'Gebruikerslijst dicht voor gasten', 'daedalia-thema' ), __( 'De REST-route met de gebruikers (en dus de gebruikersnamen) is alleen voor wie gebruikers mag zien.', 'daedalia-thema' ) ),
		'auteurs'         => $part( 'beveiliging', __( 'Auteurs niet te raden', 'daedalia-thema' ), __( 'Een adres als ?author=1 verraadt geen gebruikersnaam meer.', 'daedalia-thema' ) ),
		'inlogfouten'     => $part( 'beveiliging', __( 'Inlogfouten verraden niets', 'daedalia-thema' ), __( 'Eén melding voor een verkeerde naam en een verkeerd wachtwoord.', 'daedalia-thema' ) ),
		'inlogpogingen'   => $part( 'beveiliging', __( 'Inlogpogingen beperken', 'daedalia-thema' ), __( 'Na 5 mislukte pogingen met dezelfde naam, of 20 vanaf hetzelfde adres, 15 minuten wachten.', 'daedalia-thema' ) ),
		'bewerker'        => $part( 'beveiliging', __( 'Bestandsbewerker uit', 'daedalia-thema' ), __( 'Thema- en pluginbestanden zijn niet in het beheer te bewerken.', 'daedalia-thema' ) ),
		// Headers.
		'nosniff'         => $part( 'headers', __( 'X-Content-Type-Options', 'daedalia-thema' ), __( 'De browser raadt het soort bestand niet zelf.', 'daedalia-thema' ) ),
		'referrer'        => $part( 'headers', __( 'Referrer-Policy', 'daedalia-thema' ), __( 'Andere sites zien alleen het domein waar een bezoeker vandaan komt, niet de hele link.', 'daedalia-thema' ) ),
		'frame'           => $part( 'headers', __( 'X-Frame-Options (alleen deze site)', 'daedalia-thema' ), __( 'Andere sites kunnen deze site niet in een frame tonen (tegen clickjacking). De editor van Daedalia blijft werken.', 'daedalia-thema' ) ),
		'permissies'      => $part( 'headers', __( 'Permissions-Policy', 'daedalia-thema' ), __( 'Camera, microfoon en locatie staan uit voor de pagina en ingesloten inhoud. Betalen blijft mogelijk.', 'daedalia-thema' ) ),
		'csp'             => $part( 'headers', __( 'Content-Security-Policy, alleen melden', 'daedalia-thema' ), __( 'Een CSP in meldmodus: de browser meldt in de console wat een strenge CSP zou tegenhouden, maar blokkeert niets.', 'daedalia-thema' ), false ),
		'hsts'            => $part( 'headers', __( 'Strict-Transport-Security (HSTS)', 'daedalia-thema' ), __( 'Alleen op https. Browsers onthouden een jaar dat de site alleen via https mag; terug naar http kan dan niet meer.', 'daedalia-thema' ), false ),
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
	add_theme_page( __( 'Daedalia-thema', 'daedalia-thema' ), __( 'Daedalia-thema', 'daedalia-thema' ), 'manage_options', 'daedalia-thema', 'daedalia_thema_settings_page' );
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
		'afslanken'   => __( 'Afslanken: minder laden', 'daedalia-thema' ),
		'beveiliging' => __( 'Beveiliging', 'daedalia-thema' ),
		'headers'     => __( 'Beveiligingsheaders', 'daedalia-thema' ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Daedalia-thema', 'daedalia-thema' ); ?></h1>
		<?php if ( isset( $_GET['opgeslagen'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only a notice. ?>
			<div class="notice notice-success" role="status"><p><?php esc_html_e( 'Instellingen opgeslagen.', 'daedalia-thema' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Wat het thema uitzet, aanzet of meestuurt. Elk onderdeel staat los: werkt iets op je site niet meer, zet dan alleen dat onderdeel uit. Onderdelen die iets kunnen breken, staan standaard uit.', 'daedalia-thema' ); ?></p>
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
							<label for="daedalia-thema-<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $part['label'] ); ?></strong><?php echo $part['standaard'] ? '' : ' <em>' . esc_html__( '(standaard uit)', 'daedalia-thema' ) . '</em>'; ?></label><br>
							<span class="description" id="daedalia-thema-<?php echo esc_attr( $key ); ?>-uitleg"><?php echo esc_html( $part['uitleg'] ); ?></span>
						</p>
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>
			<p><button type="submit" name="daedalia_thema_opslaan" value="1" class="button button-primary"><?php esc_html_e( 'Opslaan', 'daedalia-thema' ); ?></button></p>
		</form>
	</div>
	<?php
}
