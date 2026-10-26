<?php
/**
 * The comments of a post: the list, its pages and the form. Without this file WordPress falls back
 * to its deprecated theme-compat template.
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="daedalia-thema-inhoud" aria-labelledby="daedalia-thema-reacties">
	<?php if ( have_comments() ) : ?>
		<h2 id="daedalia-thema-reacties">
			<?php
			$daedalia_thema_count = get_comments_number();
			/* translators: %s: number of comments. */
			echo esc_html( sprintf( _n( '%s reactie', '%s reacties', $daedalia_thema_count, 'daedalia-thema' ), number_format_i18n( $daedalia_thema_count ) ) );
			?>
		</h2>
		<ol>
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>
		<?php
		the_comments_navigation();
	else :
		?>
		<h2 id="daedalia-thema-reacties"><?php esc_html_e( 'Reacties', 'daedalia-thema' ); ?></h2>
		<?php
	endif;

	if ( comments_open() ) {
		comment_form();
	} else {
		echo '<p>' . esc_html__( 'Reageren is gesloten.', 'daedalia-thema' ) . '</p>';
	}
	?>
</section>
