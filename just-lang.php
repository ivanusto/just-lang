<?php
/**
 * Plugin Name:       Just Lang
 * Plugin URI:        https://github.com/ivanusto/just-lang
 * Description:       Lightweight multilingual signals for hand-built pages: per-page language and translation group, html lang, hreflang, og:locale, localized site name in titles, a language switcher and cache-friendly language auto-detection. No URL rewriting, no extra tables.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Ivan Lin
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       just-lang
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JUST_LANG_VERSION', '1.0.0' );
define( 'JUST_LANG_FILE', __FILE__ );
define( 'JUST_LANG_DIR', plugin_dir_path( __FILE__ ) );
define( 'JUST_LANG_META', '_just_lang' );
define( 'JUST_LANG_GROUP_META', '_just_lang_group' );
define( 'JUST_LANG_OPTION', 'just_lang_settings' );

require_once JUST_LANG_DIR . 'includes/core.php';
require_once JUST_LANG_DIR . 'includes/frontend.php';
require_once JUST_LANG_DIR . 'includes/switcher.php';

if ( is_admin() ) {
	require_once JUST_LANG_DIR . 'includes/admin.php';
}

register_activation_hook( __FILE__, 'just_lang_activate' );
