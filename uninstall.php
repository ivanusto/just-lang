<?php
/**
 * Removes the settings. The language and group of each post stay in post meta
 * (_just_lang, _just_lang_group), so reinstalling picks up where it left off.
 *
 * @package Just_Lang
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'just_lang_settings' );
