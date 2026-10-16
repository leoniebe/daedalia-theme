<?php
/**
 * Fallback for every view without a more specific template (and the blog page).
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="content" class="daedalia-thema-inhoud">
	<h1><?php echo esc_html( is_home() && get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : get_bloginfo( 'name' ) ); ?></h1>
	<?php get_template_part( 'parts/lijst' ); ?>
</main>
<?php
get_footer();
