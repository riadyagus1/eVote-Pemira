<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u356808956_TKObT' );

/** MySQL database username */
define( 'DB_USER', 'u356808956_kX1nl' );

/** MySQL database password */
define( 'DB_PASSWORD', 'W9BvV5Jnas' );

/** MySQL hostname */
define( 'DB_HOST', 'mysql' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '1@EQe;n&lG`.#7L@iW0=1zNq<7{T^W2Y=xTB}>e56Y5=s8:/C>f6Gpv7fgL~`<p9' );
define( 'SECURE_AUTH_KEY',   'Vr77ySN6l2VTb!pq@Owo&QLSI<@,hH^Jo 61K k6L:Xc2nd{dMnKK0@EHqpNYTdV' );
define( 'LOGGED_IN_KEY',     '5IrHNqU9!TQgCu!YTU>w;q0$aG~RxG-;-w@Wk=ykp$Oo?T~oG5b++SoUpf$lOUN ' );
define( 'NONCE_KEY',         'P>eE3M{![&J!G6&PEg}S|uK*{!(JrG[EC9I*<&8bxsX.3tB{zuZD&vM<CEs7A.aG' );
define( 'AUTH_SALT',         '|d}J4ii-aI q^Ub|?5wH-1[ay}>=,UZHQ1) +o&Uo[Pk>=8:!mx`)a?{hrj[[.F}' );
define( 'SECURE_AUTH_SALT',  'Led G!S-fo0.D{&R+f,>w<D1DT)uwC.Pgav]s7yqZtJL_|u@52^A:>7+^KEsQ:%*' );
define( 'LOGGED_IN_SALT',    'YH/]@?hKqgRbo?((g]H+d-h|gKu:y#2Iz;hvrq^G]74yR@MW3yc4PeL{f/(=<P7O' );
define( 'NONCE_SALT',        'gv}.&)I%LzX)N73{d|+.(> 8G?e&m;|u&f:hUp3=gzc8 ]T9Oi|K,jq5M!kutud`' );
define( 'WP_CACHE_KEY_SALT', 'SJs=1bD:Mn+~q~,HS /IgwPZ8yj^{kT:_tB{NBxfgm_UQ.Y. a1%>-7&B8a5/v`s' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
