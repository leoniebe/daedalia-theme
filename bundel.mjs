// Bundles Daedalia into this theme: copies ../daedalia (or DAEDALIA_BRON) into ./daedalia,
// without the editor sources, tests, docs, node_modules and git, and zips the theme into
// ./dist/daedalia-thema-<version>.zip. Usage: npm run bundel   |   npm run bundel:alleen-kopie
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const here = path.dirname( fileURLToPath( import.meta.url ) );
const source = path.resolve( process.env.DAEDALIA_BRON || path.join( here, '..', 'daedalia' ) );
const target = path.join( here, 'daedalia' );
const version = ( fs.readFileSync( path.join( here, 'style.css' ), 'utf8' ).match( /Version:\s*([\d.]+)/ ) || [] )[ 1 ] || '0.0.0';

if ( ! fs.existsSync( path.join( source, 'daedalia.php' ) ) ) {
	console.error( `Daedalia niet gevonden in ${ source }. Zet DAEDALIA_BRON naar de map van de plugin.` );
	process.exit( 1 );
}

// Never in a bundle: repository files anywhere; at the top level what Daedalia's .gitattributes
// marks export-ignore (editor sources, tests, developer tooling: the same as its release zip), and
// what is not in its repository at all (Composer's vendor/, builds, docs).
const SKIP = [ '.git', '.github', 'node_modules', '.gitignore', '.gitattributes', '.editorconfig' ];
const exportIgnored = fs.readFileSync( path.join( source, '.gitattributes' ), 'utf8' )
	.split( /\r?\n/ )
	.map( ( line ) => line.match( /^\/([^\s/]+)\s+export-ignore\s*$/ ) )
	.filter( Boolean )
	.map( ( match ) => match[ 1 ] );
const SKIP_TOP = [ ...exportIgnored, 'vendor', 'dist', 'docs', '.phpunit.result.cache' ];
if ( ! fs.existsSync( path.join( source, 'assets', 'editor', 'editor.js' ) ) ) {
	console.error( 'De editor is nog niet gebouwd (assets/editor/editor.js ontbreekt). Draai eerst npm run build in daedalia/editor.' );
	process.exit( 1 );
}

fs.rmSync( target, { recursive: true, force: true } );
let files = 0;
const copy = ( from, to ) => {
	fs.mkdirSync( to, { recursive: true } );
	const top = from === source;
	for ( const entry of fs.readdirSync( from, { withFileTypes: true } ) ) {
		if ( SKIP.includes( entry.name ) || ( top && SKIP_TOP.includes( entry.name ) ) ) {
			continue;
		}
		const a = path.join( from, entry.name );
		const b = path.join( to, entry.name );
		if ( entry.isDirectory() ) {
			copy( a, b );
		} else {
			fs.copyFileSync( a, b );
			files++;
		}
	}
};
copy( source, target );
for ( const needed of [ 'daedalia.php', 'src/autoload.php', 'assets/editor/editor.js', 'assets/editor/stijlgids.js', 'data/design-system.json', 'templates/header.php' ] ) {
	if ( ! fs.existsSync( path.join( target, needed ) ) ) {
		console.error( `Bundel onvolledig: ${ needed } ontbreekt.` );
		process.exit( 1 );
	}
}
console.log( `Daedalia gekopieerd naar daedalia/ (${ files } bestanden).` );

if ( process.argv.includes( '--zonder-zip' ) ) {
	process.exit( 0 );
}

// The zip holds the folder daedalia-thema/ so WordPress installs it under that name.
const dist = path.join( here, 'dist' );
fs.mkdirSync( dist, { recursive: true } );
const zip = path.join( dist, `daedalia-thema-${ version }.zip` );
fs.rmSync( zip, { force: true } );
const stage = fs.mkdtempSync( path.join( dist, 'tmp-' ) );
const root = path.join( stage, 'daedalia-thema' );
const THEME_SKIP = [
	'.git', 'node_modules', 'vendor', 'dist', 'tests', 'bundel.mjs', 'package.json', 'package-lock.json',
	'composer.json', 'composer.lock', 'phpcs.xml.dist', 'phpstan.neon.dist',
];
const copyTheme = ( from, to ) => {
	fs.mkdirSync( to, { recursive: true } );
	for ( const entry of fs.readdirSync( from, { withFileTypes: true } ) ) {
		if ( THEME_SKIP.includes( entry.name ) || entry.name.startsWith( '.' ) ) {
			continue;
		}
		const a = path.join( from, entry.name );
		const b = path.join( to, entry.name );
		entry.isDirectory() ? copyTheme( a, b ) : fs.copyFileSync( a, b );
	}
};
copyTheme( here, root );
if ( process.platform === 'win32' ) {
	execFileSync( 'powershell.exe', [ '-NoProfile', '-Command', `Compress-Archive -Path '${ root }' -DestinationPath '${ zip }' -Force` ], { stdio: 'inherit' } );
} else {
	execFileSync( 'zip', [ '-rq', zip, 'daedalia-thema' ], { cwd: stage, stdio: 'inherit' } );
}
fs.rmSync( stage, { recursive: true, force: true } );
console.log( `Zip: ${ path.relative( here, zip ) } (${ ( fs.statSync( zip ).size / 1024 ).toFixed( 0 ) } kB)` );
