<?php
define( 'WP_CACHE', false ); // Disabled for debugging

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'newsearch' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost:9998' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '1024M');

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
define('AUTH_KEY', '66co7*9(6Fvd~L&|A)0#592kC*l3j1)E*P#IA@21X42z]-b63I3Xmqg:VZ7iC+m8');
define('SECURE_AUTH_KEY', '[@SJ#gJ[nC01bL4i&7_&xUHr3]:z[@7&r71v14K87rl[;wR!]q(8O3TKc(dJ6e62');
define('LOGGED_IN_KEY', '19+B2!3t9mXAm*T~1|bP8i##-8@-v~|l+-*hDDHZ&%65F6Uu8eo@5d2-tUH!+J06');
define('NONCE_KEY', '&e%;]7@5;7oXy/(91(~Y#~FA4!b0g35;~V72B0913F|coq&A(jb_*Sv)-!(r+j[s');
define('AUTH_SALT', 'E7@I0ip2c+r)#rjs3UPv&o328+|v;S;]!u3O#;ac(k9S;k14sJQlmRMn#t6V/14x');
define('SECURE_AUTH_SALT', 'DW4L]/822u6+/A~8M@2/Go-~dvKi#&![bo2u+wBb%my6_tWSO)F1Bk4(Un!Hsg&+');
define('LOGGED_IN_SALT', '2J(K1ET(YEilMPhF#z)/Hq:#gd;f|M/aB&_#e#G3e53r-PN|8%*2]a%!y9cNIe_7');
define('NONCE_SALT', '25Aj@f3Kv73210e8@+z!Ur4YH7VMN%+JQ;O-1+6Hd7!3X)/3+|[0!8]N2_50%P6Y');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'ACnMzRUu_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', true);
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
}

define( 'DISALLOW_FILE_EDIT', true );
define( 'CONCATENATE_SCRIPTS', false );

/**
 * SIT Search Plugin Configuration
 * These keys should be kept here and NOT in the plugin source code
 */
define('SIT_STRIPE_PUBLIC_KEY', 'pk_live_51Q84QqP4H8kHelKmZ2ZUadD3qCafjO69BxugX925UqNoxVJH1SWfqOUBrPxrDEd3SmYjtnNBQgP9U9z9F8nqQHJN00EqRsBLtQ');
define('SIT_STRIPE_SECRET_KEY', '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');
define('SIT_SUPABASE_URL', 'https://knqtjanxjwfjfrwoater.supabase.co');
define('SIT_SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtucXRqYW54andmamZyd29hdGVyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTQyOTczMjQsImV4cCI6MjA2OTg3MzMyNH0.r0l1zFcBQdx4tBxpp6413r5MXAmy-1Ew2TnQ8QNVB2g');
define('SIT_SUPABASE_SERVICE_ROLE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtucXRqYW54andmamZyd29hdGVyIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1NDI5NzMyNCwiZXhwIjoyMDY5ODczMzI0fQ.dW8gYm-N-zchN5Rzyg2le6ggwzeN6kHcCbdypfZkLcE');
define('SIT_SUPABASE_BUCKET', 'uploads');

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
