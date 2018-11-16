<?php
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
define('DB_NAME', 'edpfocusgroups');

/** MySQL database username */
define('DB_USER', 'intelpatsar');

/** MySQL database password */
define('DB_PASSWORD', 'x3EijCXSEeAT');

/** MySQL hostname */
define('DB_HOST', 'mysql.s3platform.eu');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '<5<GC}<3m#(KeScTF?d#v-^a9Tsm@^V3ypkgq#AYgK>9#s[#lM*9Yf_{`iN@lc.z');
define('SECURE_AUTH_KEY',  'ZpF/?hdwA=;LOGkF:sT;-{~Y61sEKU+P)V9&/hz~yCWU]9I^IG[781z8%~u3U2C>');
define('LOGGED_IN_KEY',    '!2>6O#Vu!]$mNnWJdE#pe}>IGN?o=gZEaVmmhvSnV*$m;XM(XINJ/<$6L%8Qp<8Z');
define('NONCE_KEY',        '*0|}Ja&xj<I<$>)^,jbSGIAwkqv)C<JYV0Xd(Yrm:]s-:S7wPni#lMrf~K}4=H%[');
define('AUTH_SALT',        'W36K4mV}0d9:pS*-6G<3O5H|]?(=pRCMM1dNsk.t=`M}5&v]5s,^!xN{prJ/ROZj');
define('SECURE_AUTH_SALT', 'p Iu/c~r_p Z)GY1aP@/b~?&WMd;PfK#G{rVB&La,<Mf52B<;s7H(7NA.9<:GZR7');
define('LOGGED_IN_SALT',   '#A=,B7vkD$Mb`oOK|#OWS|NGbHvft`VQ|{9@H<`$9q~mEy.*wVi72;Ala[E44[vS');
define('NONCE_SALT',       'a~.Y(oKGN=kdk).>M9dSWc`:e|b/~V|)0!wDCI;#^<@%Ip!= lc`whl.^Os>6|@r');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
