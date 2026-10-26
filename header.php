<?php
/**
 * Site header. When a Daedalia header template applies, Daedalia prints its own and this
 * file's output is swallowed (Theme_Builder::replace_header).
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="daedalia-thema-skiplink" href="#content"><?php esc_html_e( 'Naar de inhoud', 'daedalia-thema' ); ?></a>
<header class="daedalia-thema-kop">
	<?php
	$daedalia_thema_logo = daedalia_thema_has_builder() ? \Daedalia\Site_Identity::logo_id() : (int) get_theme_mod( 'custom_logo' );
	$daedalia_thema_name = get_bloginfo( 'name' );
	?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>
		<?php
		echo $daedalia_thema_logo
			? wp_get_attachment_image(
				$daedalia_thema_logo,
				'medium',
				false,
				array(
					'alt'     => $daedalia_thema_name,
					'loading' => 'eager',
				)
			)
			: esc_html( $daedalia_thema_name );
		?>
	</a>
	<?php if ( has_nav_menu( 'hoofdmenu' ) ) : ?>
		<nav aria-label="<?php esc_attr_e( 'Hoofdmenu', 'daedalia-thema' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'hoofdmenu',
					'container'      => '',
					'depth'          => 2,
				)
			);
			?>
		</nav>
	<?php endif; ?>
</header>
