<?php
/**
 * Checks the Dutch translation against a freshly made POT: every string translated, nothing
 * obsolete, and the built .mo and .l10n.php equal to the .po. Exits 1 with a list otherwise.
 *
 * Usage: php tools/i18n-check.php <fresh.pot>
 *
 * @package Daedalia_Thema
 */

require __DIR__ . '/../vendor/autoload.php';

$languages = dirname( __DIR__ ) . '/languages/';
$pot       = Gettext\Translations::fromPoFile( $argv[1] );
$po        = Gettext\Translations::fromPoFile( $languages . 'nl_NL.po' );
$problems  = array();

foreach ( $pot as $t ) {
	$found = $po->find( $t->getContext(), $t->getOriginal() );
	if ( false === $found || '' === $found->getTranslation() || ( $t->hasPlural() && '' === implode( '', $found->getPluralTranslations() ) ) ) {
		$problems[] = 'Not translated: ' . $t->getOriginal();
	}
}
foreach ( $po as $t ) {
	if ( false === $pot->find( $t->getContext(), $t->getOriginal() ) ) {
		$problems[] = 'Obsolete in the .po: ' . $t->getOriginal();
	}
}

$mo = Gettext\Translations::fromMoFile( $languages . 'nl_NL.mo' );
$l10n = include $languages . 'nl_NL.l10n.php';
foreach ( $po as $t ) {
	if ( '' === $t->getTranslation() ) {
		continue;
	}
	// A plural is keyed by its singular alone; its forms follow each other, split by NUL.
	$key  = ( $t->hasContext() ? $t->getContext() . "\4" : '' ) . $t->getOriginal();
	$want = $t->getTranslation() . ( $t->hasPlural() ? "\0" . implode( "\0", $t->getPluralTranslations() ) : '' );
	$in   = $mo->find( $t->getContext(), $t->getOriginal() );
	if ( false === $in || $in->getTranslation() !== $t->getTranslation() ) {
		$problems[] = 'The .mo is out of date (composer i18n:build): ' . $t->getOriginal();
	}
	if ( ( $l10n['messages'][ $key ] ?? null ) !== $want ) {
		$problems[] = 'The .l10n.php is out of date (composer i18n:build): ' . $t->getOriginal();
	}
}

if ( $problems ) {
	fwrite( STDERR, implode( "\n", array_unique( $problems ) ) . "\n" );
	exit( 1 );
}
echo count( $po ) . " strings, all translated.\n";
