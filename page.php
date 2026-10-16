<?php
/**
 * A page. Pages built with Daedalia bring their own H1 and layout.
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="content">
	<?php
	while ( have_posts() ) :
		the_post();
		if ( daedalia_thema_built( get_the_ID() ) ) :
			the_content();
		else :
			?>
			<article <?php post_class( 'daedalia-thema-inhoud' ); ?>>
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
			</article>
			<?php
		endif;
	endwhile;
	?>
</main>
<?php
get_footer();
