<?php
/**
 * Front end: html lang, hreflang, og:locale, the document title and auto-detection.
 *
 * @package Just_Lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'language_attributes', 'just_lang_language_attributes', 20, 2 );
function just_lang_language_attributes( $output, $doctype ) {
	$id = just_lang_current_id();
	if ( ! $id || 'html' !== $doctype ) {
		return $output;
	}
	$lang = just_lang_of( $id );
	if ( preg_match( '/lang="[^"]*"/', $output ) ) {
		return preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( $lang ) . '"', $output );
	}
	return trim( $output . ' lang="' . esc_attr( $lang ) . '"' );
}

add_filter( 'document_title_parts', 'just_lang_title_parts', 20 );
function just_lang_title_parts( $parts ) {
	$id = just_lang_current_id();
	if ( ! $id ) {
		return $parts;
	}
	$lang  = just_lang_of( $id );
	$names = just_lang_settings()['site_names'];
	/** Site name shown in the title of pages in $lang; '' keeps the title as WordPress builds it. */
	$site = (string) apply_filters( 'just_lang_site_name', isset( $names[ $lang ] ) ? $names[ $lang ] : '', $lang );
	if ( '' === $site ) {
		return $parts;
	}
	if ( isset( $parts['title'] ) && preg_match( '/\b' . preg_quote( $site, '/' ) . '\b/u', $parts['title'] ) ) {
		unset( $parts['site'] );
	} else {
		$parts['site'] = $site;
	}
	unset( $parts['tagline'] );
	return $parts;
}

/** og:locale of the post being viewed, or $locale when there is none. */
function just_lang_og_locale( $locale ) {
	$id = just_lang_current_id();
	return $id ? just_lang_languages()[ just_lang_of( $id ) ][1] : $locale;
}

// Omni Webmaster & SEO Suite asks for its og:locale through this filter.
add_filter( 'omni_og_locale', 'just_lang_og_locale' );

// Other SEO plugins read get_locale() for og:locale; switch it only while head tags print.
add_action( 'wp_head', 'just_lang_locale_on', 1 );
add_action( 'wp_head', 'just_lang_locale_off', 99 );
function just_lang_locale_on() {
	if ( just_lang_current_id() && apply_filters( 'just_lang_switch_locale', true ) ) {
		add_filter( 'locale', 'just_lang_og_locale', 99 );
	}
}
function just_lang_locale_off() {
	remove_filter( 'locale', 'just_lang_og_locale', 99 );
}

add_action( 'wp_head', 'just_lang_head', 2 );
function just_lang_head() {
	$id = just_lang_current_id();
	if ( ! $id ) {
		return;
	}
	$links = just_lang_group_links( $id );
	if ( ! $links ) {
		return;
	}
	$cur   = just_lang_of( $id );
	$langs = just_lang_languages();
	$xdef  = just_lang_x_default( $links );

	echo "\n<!-- Just Lang -->\n";
	foreach ( $links as $tag => $url ) {
		printf( "<link rel=\"alternate\" hreflang=\"%s\" href=\"%s\" />\n", esc_attr( $tag ), esc_url( $url ) );
	}
	if ( '' !== $xdef ) {
		printf( "<link rel=\"alternate\" hreflang=\"x-default\" href=\"%s\" />\n", esc_url( $links[ $xdef ] ) );
	}
	foreach ( $links as $tag => $url ) {
		if ( $tag !== $cur ) {
			printf( "<meta property=\"og:locale:alternate\" content=\"%s\" />\n", esc_attr( $langs[ $tag ][1] ) );
		}
	}

	if ( just_lang_settings()['autodetect'] && apply_filters( 'just_lang_autodetect', true, $id ) ) {
		$cfg = array(
			'map'      => $links,
			'cur'      => $cur,
			'fallback' => $xdef,
		);
		wp_print_inline_script_tag( '(' . just_lang_detect_js() . ')(' . wp_json_encode( $cfg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ');' );
	}
	echo "<!-- End Just Lang -->\n";
}

/**
 * Language auto-detection. It runs in the browser before anything paints, so pages
 * stay cacheable as a whole: a server-side redirect would be stored by the CDN and
 * served to every visitor.
 *
 * Only first arrivals from outside the site are redirected. Moving between pages
 * inside the site, or following a link with ?lang=keep, records the visitor's
 * choice instead, and that choice wins on later visits. Crawlers are never
 * redirected, so every language version stays indexable.
 */
function just_lang_detect_js() {
	return <<<'JS'
function(c){try{
if(/bot|crawl|spider|slurp|facebookexternalhit|lighthouse|preview/i.test(navigator.userAgent))return;
var K="just-lang",s=window.localStorage,r="";
try{r=document.referrer?new URL(document.referrer).host:"";}catch(e){}
if(r===location.host||/[?&]lang=keep\b/.test(location.search)){s.setItem(K,c.cur);return;}
var tags=Object.keys(c.map),w=s.getItem(K);
function zh(t){return t.indexOf("zh")?"":/hant|tw|hk|mo/.test(t)?"t":"s";}
function pick(l){l=l.toLowerCase();var p=l.split("-")[0],i,t,a="",b="";
for(i=0;i<tags.length;i++){t=tags[i].toLowerCase();
if(t===l)return tags[i];
if(t.split("-")[0]===p){if(!a&&zh(t)===zh(l))a=tags[i];if(!b)b=tags[i];}}
return a||b;}
if(!w||!c.map[w]){w="";var ls=navigator.languages&&navigator.languages.length?navigator.languages:[navigator.language||""];
for(var i=0;i<ls.length&&!w;i++)w=pick(ls[i]);}
if(!w)w=c.fallback||c.cur;
if(w!==c.cur)location.replace(c.map[w]+location.hash);
}catch(e){}}
JS;
}
