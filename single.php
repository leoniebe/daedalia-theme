<?php
/**
 * A single post (any post type without its own template). Built with Daedalia: its own layout.
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
			<article <?php post_class( 'daedalia-theme-content' ); ?>>
				<?php if ( daedalia_thema_show_title( get_the_ID() ) ) : ?>
					<h1><?php the_title(); ?></h1>
				<?php endif; ?>
				<p><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail(
						'large',
						array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
						)
					);
				}
				the_content();
				wp_link_pages();
				?>
			</article>
			<?php
			the_post_navigation();
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endif;
	endwhile;
	?>
</main>
<?php
get_footer();
