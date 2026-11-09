<?php
/**
 * Search results, when no Daedalia search template applies.
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="content" class="daedalia-theme-content">
	<?php /* translators: %s: search terms */ ?>
	<h1><?php printf( esc_html__( 'Search results for “%s”', 'daedalia-thema' ), esc_html( get_search_query() ) ); ?></h1>
	<?php get_search_form(); ?>
	<?php get_template_part( 'parts/lijst' ); ?>
</main>
<?php
get_footer();
