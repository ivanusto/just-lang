# Just Lang

[繁體中文說明請參見 README.zh-TW.md](README.zh-TW.md)

Lightweight multilingual signals for WordPress sites where every language version is its own page. Just Lang sets `html lang`, `hreflang`, `og:locale` and the site name in titles, adds a language switcher, and can send first-time visitors to the version in their language without breaking full-page caching.

It does **not** rewrite URLs, translate strings, add database tables or manage a translation workflow. You build each language version as a normal page with whatever URL you like; Just Lang tells browsers, search engines and social networks how those pages relate.

## Who it is for

* Company and product sites with a handful of pages per language, often hand-built in HTML blocks or a page builder.
* Sites behind a CDN that caches whole pages, where server-side language redirects would be cached and served to everyone.
* Anyone who finds Polylang or WPML too heavy for "these three pages are the same thing in three languages".

If you need a generated URL structure, string translation for themes and plugins, or machine translation, use Polylang, WPML or TranslatePress instead. Just Lang shows a warning when one of them is active, since both would print hreflang.

## Features

* **Per-post language and translation group**: two post meta fields, editable in the Language box, over the REST API, or from WP-CLI.
* **`<html lang>`** set to the page's language.
* **hreflang** for every published page in the group, plus a configurable **x-default**.
* **og:locale** for the page's language and **og:locale:alternate** for the others. Works with SEO plugins that read the site locale, and hooks into [Omni Webmaster & SEO Suite](https://github.com/ivanusto/omni-webmaster-seo-suite) directly through `omni_og_locale`.
* **Site name per language** in the document title, for example your brand name in Latin letters on English and Japanese pages.
* **Language switcher**: a block, the `[just_lang_switcher]` shortcode and the `just_lang_switcher()` template tag.
* **Cache-friendly auto-detection** in the browser (see below).
* **Optional page excerpts**, so SEO plugins get a description in each page's own language.
* **Conflict warning** when Polylang, WPML, TranslatePress or Weglot is active.

## Requirements

* WordPress 6.0 or higher
* PHP 7.4 or higher

## Installation

1. Download `just-lang-<version>.zip` from the [Releases](https://github.com/ivanusto/just-lang/releases) page.
2. In your WordPress admin, go to **Plugins > Add New > Upload Plugin**, select the zip file and click **Install Now**.
3. Activate the plugin. It starts with your site language plus English enabled.
4. Go to **Settings > Just Lang** to choose languages, the default and the x-default.

## Usage

1. Create one page per language, with any slugs and parents you like, for example `/about/`, `/en/about/`, `/ja/about/`.
2. On each page, set **Language** and type the same **Translation group** (for example `about`) in the Language box.
3. Publish. Every page in the group now lists the others as alternates.

Pages without a language count as the default language, so on an existing single-language site you only need to tag the pages you add in other languages.

Over the REST API:

```bash
curl -u user:app-password -X POST https://example.com/wp-json/wp/v2/pages/42 \
  -H 'Content-Type: application/json' \
  -d '{"meta":{"_just_lang":"en","_just_lang_group":"about"}}'
```

With WP-CLI:

```bash
wp post meta update 42 _just_lang en
wp post meta update 42 _just_lang_group about
```

### Language switcher

Add the **Language Switcher** block to a template, a template part or a page, or use:

```
[just_lang_switcher style="inline" labels="name" current="1"]
```

* `style`: `inline` or `list`
* `labels`: `name` (English, 日本語) or `code` (EN, JA)
* `current`: `1` to show the current language, `0` to hide it

It renders nothing on pages without a published translation.

## How auto-detection works

A small inline script in `<head>` decides before the page paints:

1. Crawlers are never redirected, so every language version stays indexable.
2. A visitor arriving from another page of **your** site is never redirected; the language of the page they are on is remembered instead. So is a visit to a link carrying `?lang=keep`.
3. A visitor arriving from anywhere else goes to their remembered language if this page has it, otherwise to the best match for their browser languages (exact tag, then same language with the same Chinese script, then same language), otherwise to x-default.

Because the decision happens in the browser, the server sends the same HTML to everyone and a CDN can cache every URL as a single copy. A server-side redirect based on `Accept-Language` would be cached once and then served to every visitor.

Turn it off under **Settings > Just Lang**, or per post with the `just_lang_autodetect` filter.

## Filters

* `just_lang_catalog( array $catalog )`: languages offered on the settings screen, as `tag => [ native label, og:locale ]`. Add one with `$catalog['ca'] = array( 'Català', 'ca_ES' );`.
* `just_lang_languages( array $languages )`: the enabled languages, default first.
* `just_lang_post_types( string[] $types )`: post types that carry a language. Defaults to all public types except attachments.
* `just_lang_x_default( string $tag, array $links )`: the language answering x-default; return `''` to leave it out.
* `just_lang_site_name( string $name, string $tag )`: the site name in titles for a language; `''` keeps the title as WordPress builds it.
* `just_lang_autodetect( bool $on, int $post_id )`: turn detection off for a post.
* `just_lang_switch_locale( bool $on )`: Just Lang switches `get_locale()` to the page's locale while `wp_head` prints (priorities 1 to 99) so SEO plugins emit the right `og:locale`. Return `false` to stop that.

## WordPress core and multilingual

Multilingual support is the planned fourth phase of the Gutenberg project, and the [WordPress roadmap](https://wordpress.org/about/roadmap/) gives it no date yet. When core ships its own model, the language tagging part of Just Lang becomes redundant. The data is deliberately kept in two plain post meta fields, `_just_lang` and `_just_lang_group`, so moving to core should be a straightforward migration.

## Uninstall

Uninstalling removes the settings. The language and group stored on each post are kept, so reinstalling restores everything. To remove them as well:

```bash
wp db query "DELETE FROM $(wp db prefix)postmeta WHERE meta_key IN ('_just_lang','_just_lang_group')"
```

## Sister projects

* [Omni Webmaster & SEO Suite](https://github.com/ivanusto/omni-webmaster-seo-suite) ([WordPress.org](https://wordpress.org/plugins/omni-webmaster-seo-suite/)): SEO and site optimization in one settings panel, including meta description, Open Graph and structured data. Just Lang hooks into it through `omni_og_locale`, so its `og:locale` follows each page's language.
* [Just Share](https://github.com/ivanusto/just-share): share buttons and related posts that ad blockers leave alone, built from plain server-rendered links and inline SVG with no third-party requests.
* [Omni Performance Hardening](https://github.com/ivanusto/omni-wp-perf-hardening): reduces server load from search scans, archive queries, low-value feeds and oEmbed endpoints, and tunes CDN cache headers.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
