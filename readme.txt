=== Just Lang ===
Contributors: ivanusto
Tags: multilingual, hreflang, language, translation, language switcher
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Multilingual signals for sites where every language version is its own page: html lang, hreflang, og:locale, a switcher and cache-friendly detection.

== Description ==

Just Lang is for sites that already build each language version as its own page, with its own URL, and only need WordPress to say so correctly. It does not rewrite URLs, translate strings, add tables or manage a translation workflow.

Give each page a language and a translation group. Pages sharing a group are translations of each other. Just Lang then:

* Sets `<html lang>` to the page's language.
* Prints `hreflang` alternates for every published page in the group, plus `x-default`.
* Sets `og:locale` for the page's language and lists the others as `og:locale:alternate`. Works with SEO plugins that read the site locale, and hooks into Omni Webmaster & SEO Suite directly.
* Replaces the site name in the document title per language, if you want it to.
* Adds a Language Switcher block, a `[just_lang_switcher]` shortcode and a `just_lang_switcher()` template tag.
* Optionally sends first-time visitors to the version in their browser language.

= Auto-detection that survives page caching =

Detection runs in the browser before the page paints, never on the server, so full-page caches and CDNs such as Cloudflare keep serving one cached copy per URL. Only visitors arriving from another site are redirected. Once someone moves between pages of your site, the language they are reading is remembered and wins on later visits. Crawlers are never redirected, so every version stays indexable. Add `?lang=keep` to a link to pin its language.

= When to use something else =

If you want one URL structure generated for you (`/en/...`), string translation for themes and plugins, or machine translation, use Polylang, WPML or TranslatePress. Just Lang warns when one of them is active, because both would print hreflang.

= Core multilingual =

Multilingual support is planned for a future phase of WordPress core, with no release date yet. Just Lang keeps its data in two plain post meta fields (`_just_lang`, `_just_lang_group`) so it can be migrated once core offers its own.

== Installation ==

1. Upload the `just-lang` folder to `/wp-content/plugins/`, or install the zip from Plugins > Add New > Upload Plugin.
2. Activate the plugin.
3. Go to Settings > Just Lang, enable your languages and pick the default.
4. On each page, set Language and Translation group in the Language box.

== Frequently Asked Questions ==

= Can I set the language over the REST API? =

Yes. Both fields are registered post meta: send `{"meta":{"_just_lang":"en","_just_lang_group":"about"}}` to `/wp/v2/pages/<id>`. The settings are available at `/wp/v2/settings` as `just_lang_settings`.

= My language is not in the list =

Add it with the `just_lang_catalog` filter: `$catalog['ca'] = array( 'Català', 'ca_ES' );`

= Which filters are there? =

* `just_lang_catalog`: languages offered on the settings screen.
* `just_lang_languages`: the enabled languages, default first.
* `just_lang_post_types`: post types that carry a language. Defaults to all public types.
* `just_lang_x_default`: the language answering x-default for a group.
* `just_lang_site_name`: the site name in titles for a language.
* `just_lang_autodetect`: turn detection off for a given post.
* `just_lang_switch_locale`: stop switching the locale while head tags print.

= What happens on uninstall? =

The settings are removed. The language and group stored on each post are kept, so reinstalling restores everything.

== Changelog ==

= 1.0.0 =
* First public release: per-post language and translation group, html lang, hreflang with x-default, og:locale, per-language site name in titles, settings screen, Language Switcher block and shortcode, browser-side auto-detection.
