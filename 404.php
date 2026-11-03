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
	<h1><?php esc_html_e( 'This page doesn’t exist (anymore)', 'daedalia-thema' ); ?></h1>
	<p><?php esc_html_e( 'Search for what you need, or go back to the homepage.', 'daedalia-thema' ); ?></p>
	<?php get_search_form(); ?>
	<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the homepage', 'daedalia-thema' ); ?></a></p>
</main>
<?php
get_footer();
