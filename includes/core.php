<?php
/**
 * Settings, language list, and the per-post language and translation group data.
 *
 * @package Just_Lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Languages offered on the settings screen: BCP 47 tag => [native label, og:locale].
 * Add entries with the `just_lang_catalog` filter to offer a language not listed here.
 */
function just_lang_catalog() {
	return apply_filters(
		'just_lang_catalog',
		array(
			'en'    => array( 'English', 'en_US' ),
			'en-GB' => array( 'English (UK)', 'en_GB' ),
			'zh-TW' => array( '繁體中文', 'zh_TW' ),
			'zh-HK' => array( '繁體中文（香港）', 'zh_HK' ),
			'zh-CN' => array( '简体中文', 'zh_CN' ),
			'ja'    => array( '日本語', 'ja_JP' ),
			'ko'    => array( '한국어', 'ko_KR' ),
			'fr'    => array( 'Français', 'fr_FR' ),
			'de'    => array( 'Deutsch', 'de_DE' ),
			'es'    => array( 'Español', 'es_ES' ),
			'pt'    => array( 'Português', 'pt_PT' ),
			'pt-BR' => array( 'Português (Brasil)', 'pt_BR' ),
			'it'    => array( 'Italiano', 'it_IT' ),
			'nl'    => array( 'Nederlands', 'nl_NL' ),
			'sv'    => array( 'Svenska', 'sv_SE' ),
			'da'    => array( 'Dansk', 'da_DK' ),
			'nb'    => array( 'Norsk bokmål', 'nb_NO' ),
			'fi'    => array( 'Suomi', 'fi_FI' ),
			'pl'    => array( 'Polski', 'pl_PL' ),
			'cs'    => array( 'Čeština', 'cs_CZ' ),
			'ru'    => array( 'Русский', 'ru_RU' ),
			'uk'    => array( 'Українська', 'uk_UA' ),
			'el'    => array( 'Ελληνικά', 'el_GR' ),
			'tr'    => array( 'Türkçe', 'tr_TR' ),
			'ar'    => array( 'العربية', 'ar_AR' ),
			'he'    => array( 'עברית', 'he_IL' ),
			'hi'    => array( 'हिन्दी', 'hi_IN' ),
			'th'    => array( 'ไทย', 'th_TH' ),
			'vi'    => array( 'Tiếng Việt', 'vi_VN' ),
			'id'    => array( 'Bahasa Indonesia', 'id_ID' ),
			'ms'    => array( 'Bahasa Melayu', 'ms_MY' ),
		)
	);
}

/**
 * The site language as a catalog tag. Reads the option rather than get_locale(),
 * because the locale filter this plugin adds on the front end ends up here.
 */
function just_lang_site_tag() {
	$locale = get_option( 'WPLANG' );
	return just_lang_tag_for_locale( $locale ? $locale : 'en_US' );
}

/** Settings merged over their defaults. */
function just_lang_settings() {
	$saved = get_option( JUST_LANG_OPTION, array() );
	return wp_parse_args(
		is_array( $saved ) ? $saved : array(),
		array(
			'default'      => just_lang_site_tag(),
			'languages'    => array(),
			'x_default'    => '',
			'site_names'   => array(),
			'autodetect'   => true,
			'page_excerpt' => false,
		)
	);
}

/** The catalog tag that best matches a WordPress locale such as zh_TW or fr_CA. */
function just_lang_tag_for_locale( $locale ) {
	$catalog = just_lang_catalog();
	foreach ( $catalog as $tag => $info ) {
		if ( $info[1] === $locale ) {
			return $tag;
		}
	}
	$primary = strtolower( strtok( (string) $locale, '_' ) );
	return isset( $catalog[ $primary ] ) ? $primary : 'en';
}

/**
 * Enabled languages: BCP 47 tag => [label, og:locale]. The first entry is the site
 * default, used for every post without a language of its own.
 */
function just_lang_languages() {
	$s       = just_lang_settings();
	$catalog = just_lang_catalog();
	$list    = array();
	foreach ( array_merge( array( $s['default'] ), (array) $s['languages'] ) as $tag ) {
		if ( isset( $catalog[ $tag ] ) && ! isset( $list[ $tag ] ) ) {
			$list[ $tag ] = $catalog[ $tag ];
		}
	}
	if ( ! $list ) {
		$list = array( 'en' => array( 'English', 'en_US' ) );
	}
	return apply_filters( 'just_lang_languages', $list );
}

function just_lang_default() {
	$langs = just_lang_languages();
	reset( $langs );
	return key( $langs );
}

/** Post types that carry a language. */
function just_lang_post_types() {
	$types = get_post_types( array( 'public' => true ) );
	unset( $types['attachment'] );
	return array_values( apply_filters( 'just_lang_post_types', $types ) );
}

/** Language of a post, falling back to the site default. */
function just_lang_of( $post_id ) {
	$lang = get_post_meta( $post_id, JUST_LANG_META, true );
	return isset( just_lang_languages()[ $lang ] ) ? $lang : just_lang_default();
}

/**
 * Published members of a post's translation group, keyed by language.
 * Empty unless the group holds at least two languages.
 *
 * @return array lang => permalink
 */
function just_lang_group_links( $post_id ) {
	static $cache = array();
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}
	$links = array();
	$group = (string) get_post_meta( $post_id, JUST_LANG_GROUP_META, true );
	if ( '' !== $group ) {
		$ids = get_posts(
			array(
				'post_type'      => just_lang_post_types(),
				'post_status'    => 'publish',
				'posts_per_page' => 4 * count( just_lang_languages() ),
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				// A handful of posts per group; the meta lookup is cheap at this size.
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => JUST_LANG_GROUP_META,
						'value' => $group,
					),
				),
			)
		);
		foreach ( $ids as $id ) {
			$lang = just_lang_of( $id );
			// Keep the first post per language so a stray duplicate cannot win.
			if ( ! isset( $links[ $lang ] ) ) {
				$links[ $lang ] = get_permalink( $id );
			}
		}
		// The post being viewed always represents its own language.
		$links[ just_lang_of( $post_id ) ] = get_permalink( $post_id );
		// List in the order languages are configured, default first.
		$links = array_merge( array_intersect_key( just_lang_languages(), $links ), $links );
	}
	$cache[ $post_id ] = count( $links ) > 1 ? $links : array();
	return $cache[ $post_id ];
}

/** The singular post being viewed on the front end, or 0. */
function just_lang_current_id() {
	if ( is_admin() || ! is_singular( just_lang_post_types() ) ) {
		return 0;
	}
	return (int) get_queried_object_id();
}

/** Language whose page answers hreflang="x-default" among $links, or '' for none. */
function just_lang_x_default( $links ) {
	$pick = just_lang_settings()['x_default'];
	if ( 'none' === $pick ) {
		$tag = '';
	} elseif ( '' !== $pick && isset( $links[ $pick ] ) ) {
		$tag = $pick;
	} else {
		$tag = isset( $links[ just_lang_default() ] ) ? just_lang_default() : '';
	}
	/** Language whose page answers x-default; return '' to leave x-default out. */
	$tag = apply_filters( 'just_lang_x_default', $tag, $links );
	return isset( $links[ $tag ] ) ? $tag : '';
}

/* -------------------------------------------------------------- settings */

add_action( 'admin_init', 'just_lang_register_setting' );
add_action( 'rest_api_init', 'just_lang_register_setting' );
function just_lang_register_setting() {
	register_setting(
		'just_lang',
		JUST_LANG_OPTION,
		array(
			'type'              => 'object',
			'sanitize_callback' => 'just_lang_sanitize_settings',
			'show_in_rest'      => array(
				'schema' => array(
					'type'       => 'object',
					'properties' => array(
						'default'      => array( 'type' => 'string' ),
						'languages'    => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'x_default'    => array( 'type' => 'string' ),
						'site_names'   => array(
							'type'                 => 'object',
							'additionalProperties' => array( 'type' => 'string' ),
						),
						'autodetect'   => array( 'type' => 'boolean' ),
						'page_excerpt' => array( 'type' => 'boolean' ),
					),
				),
			),
		)
	);
}

/** Keep only known languages; the default is always enabled. */
function just_lang_sanitize_settings( $in ) {
	$catalog = just_lang_catalog();
	$in      = is_array( $in ) ? $in : array();
	$langs   = array_values( array_intersect( array_unique( array_map( 'strval', (array) ( $in['languages'] ?? array() ) ) ), array_keys( $catalog ) ) );
	$default = isset( $in['default'], $catalog[ $in['default'] ] ) ? $in['default'] : ( $langs ? $langs[0] : 'en' );
	if ( ! in_array( $default, $langs, true ) ) {
		array_unshift( $langs, $default );
	}
	$xdef = isset( $in['x_default'] ) ? (string) $in['x_default'] : '';
	if ( 'none' !== $xdef && ! in_array( $xdef, $langs, true ) ) {
		$xdef = '';
	}
	$names = array();
	foreach ( (array) ( $in['site_names'] ?? array() ) as $tag => $name ) {
		$name = sanitize_text_field( (string) $name );
		if ( '' !== $name && in_array( $tag, $langs, true ) ) {
			$names[ $tag ] = $name;
		}
	}
	return array(
		'default'      => $default,
		'languages'    => $langs,
		'x_default'    => $xdef,
		'site_names'   => $names,
		'autodetect'   => ! empty( $in['autodetect'] ),
		'page_excerpt' => ! empty( $in['page_excerpt'] ),
	);
}

/* ------------------------------------------------------------------ meta */

add_action( 'init', 'just_lang_register', 20 );
function just_lang_register() {
	// Bundled translations for installs from GitHub, which WordPress.org language packs do not cover.
	// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
	load_plugin_textdomain( 'just-lang', false, dirname( plugin_basename( JUST_LANG_FILE ) ) . '/languages' );

	if ( just_lang_settings()['page_excerpt'] ) {
		// Pages get a manual excerpt so SEO plugins have a real description to use.
		add_post_type_support( 'page', 'excerpt' );
	}

	$auth = function ( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	};
	foreach ( just_lang_post_types() as $type ) {
		register_post_meta(
			$type,
			JUST_LANG_META,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'sanitize_callback' => function ( $v ) {
					return isset( just_lang_languages()[ $v ] ) ? $v : '';
				},
				'auth_callback'     => $auth,
			)
		);
		register_post_meta(
			$type,
			JUST_LANG_GROUP_META,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'sanitize_callback' => 'sanitize_key',
				'auth_callback'     => $auth,
			)
		);
	}
}

/** First activation: enable the site language plus English. */
function just_lang_activate() {
	if ( false !== get_option( JUST_LANG_OPTION ) ) {
		return;
	}
	$default = just_lang_site_tag();
	add_option(
		JUST_LANG_OPTION,
		array(
			'default'      => $default,
			'languages'    => array_values( array_unique( array( $default, 'en' ) ) ),
			'x_default'    => '',
			'site_names'   => array(),
			'autodetect'   => true,
			'page_excerpt' => false,
		)
	);
}
