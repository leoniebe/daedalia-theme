<?php
/**
 * Site footer. When a Daedalia footer template applies, Daedalia prints its own and this
 * file's output is swallowed (Theme_Builder::replace_footer).
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="daedalia-theme-footer">
	<?php if ( has_nav_menu( 'voetmenu' ) ) : ?>
		<nav aria-label="<?php esc_attr_e( 'Footer menu', 'daedalia-thema' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'voetmenu',
					'container'      => '',
					'depth'          => 1,
				)
			);
			?>
		</nav>
	<?php endif; ?>
	<p>&copy; <?php echo esc_html( gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?></p>
</footer>
<?php wp_footer(); ?>
</body>
</html>
