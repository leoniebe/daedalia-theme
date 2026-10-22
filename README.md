# Daedalia-thema

Een licht, klassiek WordPress-thema voor de Daedalia-pagebuilder. Het thema doet bijna niets zelf:

- **Header, footer, pagina's, berichten, archieven, zoeken en 404** komen uit Daedalia. Je maakt ze als sjabloon in de Theme Builder. Zonder sjabloon toont het thema een eenvoudige, semantische versie, met een skip link, één H1 en `<main id="content">`.
- **Kleuren, lettertypen, logo, iconen en het formaat van de site** stel je in via Daedalia › Site-instellingen. Het thema leest of laadt daar niets van.
- **CSS van het thema:** alleen een kleine reset en de skip link, samen minder dan 2 kB.
- **Opgeschoond:** het thema laat het emoji-script en wp-embed weg. De bloklibrary en globale stijlen laden alleen op pagina's met blokken, en altijd bij winkelwagen, afrekenen en mijn account. Wil je een onderdeel houden? Gebruik het filter `daedalia_thema/afslanken`, bijvoorbeeld `add_filter( 'daedalia_thema/afslanken', fn( $d ) => [ 'blokken' => false ] + $d );`.
- **Weergave › Daedalia-thema:** elk onderdeel staat los aan of uit, met uitleg. Wat iets kan breken staat standaard uit.
  - **Afslanken:** emoji, wp-embed, blokstijlen alleen waar nodig, overbodige head-regels (RSD, WLW, korte link), geen WordPress-versie in de HTML, geen dashicons voor bezoekers, geen cart-fragments van WooCommerce, WooCommerce-CSS en -JS alleen op winkelpagina’s. Standaard uit: jQuery Migrate weg.
  - **Beveiliging:** XML-RPC uit (403), de REST-gebruikerslijst dicht voor gasten, ?author=1 verraadt geen naam, één inlogmelding voor naam en wachtwoord, inlogpogingen beperkt (5 per naam of 20 per adres, dan 15 minuten), geen bestandsbewerker.
  - **Headers:** X-Content-Type-Options, Referrer-Policy, X-Frame-Options SAMEORIGIN (de editor van Daedalia blijft werken), Permissions-Policy (camera, microfoon, locatie uit; betalen niet). Standaard uit: CSP in meldmodus (filter `daedalia_thema/csp`) en HSTS (alleen op https).
- **Paginatitel:** pagina’s zonder Daedalia-inhoud krijgen de titel als H1, tenzij Pagina-instellingen › Titel van het thema op Verbergen staat. In de editor nooit.
- **WooCommerce:** het thema geeft ondersteuning aan, met zoom, lightbox en slider in de productgalerij. `woocommerce.php` is de terugval zolang er geen Daedalia-sjablonen voor de winkel zijn.

## Daedalia meeleveren of als plugin

Daedalia draait op één van twee manieren. De code is dezelfde.

1. **Meegeleverd in het thema** (één pakket voor de klant): `npm run bundel` kopieert `../daedalia` naar `daedalia/` in dit thema en maakt `dist/daedalia-thema-<versie>.zip`.
   - De bron kun je wijzigen met `DAEDALIA_BRON=/pad/naar/daedalia npm run bundel`.
   - Bouw eerst de editor (`npm run build` in `daedalia/editor`).
   - `functions.php` laadt `daedalia/daedalia.php` en zet de constante `DAEDALIA_BUNDLED`.
2. **Als plugin:** activeer de plugin Daedalia. Een actieve plugin laadt vóór het thema en wint. De meegeleverde kopie doet dan niets.

Onder Daedalia › Overzicht staat welke kopie draait. Zonder Daedalia werkt het thema nog wel, met een melding in het beheer.

De map `daedalia/` staat in `.gitignore`: hij wordt bij elke bundel opnieuw gemaakt uit de Daedalia-repository.

## Menu's

Er zijn twee menulocaties: **Hoofdmenu** en **Menu in de footer**. Een Daedalia-header met het Menu-element heeft ze niet nodig.

## Vereisten

WordPress 6.5+ en PHP 8.1+. Licentie: GPL-3.0-or-later.
