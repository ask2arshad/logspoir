<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'logspoir' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '1234' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'T-,;+k|gZf8 Y2VS<bk-.-).=n=_O}/o1-hs7tpm~@M?z?T%7yrG6jV=CjJ23.#:' );
define( 'SECURE_AUTH_KEY',  '?2%[vdH;C)@GgId8*qg^M0#4?`yZFRdGt!>Be|p&jg~R0~<1zQf0_2}um>VW{A=a' );
define( 'LOGGED_IN_KEY',    'lh>A+HN.N I|8 al}S[6dPm$@zL(%iLz{)9;(& -`I5n%q eur8X>)x50~i(gn+U' );
define( 'NONCE_KEY',        'd Yba*7{?HH2;dOM3W}CcOmp%X&-8.0Rb=`Xlrs;:R WF.3Yy:z%NKHcVJ=sHJ_[' );
define( 'AUTH_SALT',        'xqBX[{ Btblv&2vQ_Xu4Mo:@|w0}m-2e_3)R)^3zQY5s(+x)[-p[xo{mR4Lyf;*A' );
define( 'SECURE_AUTH_SALT', '(nOrJ~n@fD`u JW5!lv&$X5uYTUZTHk*sIe@<iLj<ef2K-C:u-KQP!++G$c5XD=n' );
define( 'LOGGED_IN_SALT',   'uTD2=^$.NoQ~^2?t^ZLZk),@fOXX5>mV52b34&e+/*l$8_>N]SVhv8k/*$&~%xm-' );
define( 'NONCE_SALT',       '6>3^RF)dw%{!25/k@6BReb`/#,$E)fRwg}gAS(wyXg;YutND!fQ/+.8oCG4+xI?0' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
// define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
