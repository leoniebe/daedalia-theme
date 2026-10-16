<?php
/**
 * A list of posts with pagination (archive, blog, search).
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

if ( have_posts() ) :
	?>
	<ul class="daedalia-thema-lijst" role="list">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<li <?php post_class(); ?>>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php the_excerpt(); ?>
			</li>
			<?php
		endwhile;
		?>
	</ul>
	<?php
	the_posts_pagination();
else :
	?>
	<p><?php esc_html_e( 'Er is (nog) niets gevonden.', 'daedalia-thema' ); ?></p>
	<?php
endif;
