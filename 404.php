<?php
/**
 * Page not found, when no Daedalia 404 template applies.
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="content" class="daedalia-thema-inhoud">
	<h1><?php esc_html_e( 'Deze pagina bestaat niet (meer)', 'daedalia-thema' ); ?></h1>
	<p><?php esc_html_e( 'Zoek wat je nodig hebt, of ga terug naar de homepage.', 'daedalia-thema' ); ?></p>
	<?php get_search_form(); ?>
	<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Naar de homepage', 'daedalia-thema' ); ?></a></p>
</main>
<?php
get_footer();
