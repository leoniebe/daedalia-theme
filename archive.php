<?php
/**
 * Archives (categories, tags, dates, post types), when no Daedalia archive template applies.
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="content" class="daedalia-theme-content">
	<h1><?php the_archive_title(); ?></h1>
	<?php the_archive_description( '<div class="daedalia-theme-description">', '</div>' ); ?>
	<?php get_template_part( 'parts/lijst' ); ?>
</main>
<?php
get_footer();
