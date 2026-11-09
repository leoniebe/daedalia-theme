<?php
/**
 * WooCommerce shop, product, category and tag pages, when no Daedalia template applies.
 * WooCommerce prints its own H1 (product title or page title).
 *
 * @package DaedaliaThema
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="content" class="daedalia-theme-content daedalia-theme-shop">
	<?php woocommerce_content(); ?>
</main>
<?php
get_footer();
