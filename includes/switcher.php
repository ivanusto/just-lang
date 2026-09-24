<?php
/**
 * Language switcher: a block, a shortcode and a template tag sharing one renderer.
 *
 * @package Just_Lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Markup for the switcher of a post's translation group, or '' when the post has
 * no published translation.
 *
 * @param int   $post_id Post whose group to list; 0 for the post being viewed.
 * @param array $args    style: list|inline, labels: name|code, current: show the current language,
 *                       wrapper: callback turning the class list into the nav's attributes.
 */
function just_lang_switcher_html( $post_id = 0, $args = array() ) {
	$args    = wp_parse_args(
		$args,
		array(
			'style'   => 'list',
			'labels'  => 'name',
			'current' => true,
			'wrapper' => null,
		)
	);
	$post_id = $post_id ? (int) $post_id : just_lang_current_id();
	$links   = $post_id ? just_lang_group_links( $post_id ) : array();
	if ( ! $links ) {
		return '';
	}
	$cur   = just_lang_of( $post_id );
	$langs = just_lang_languages();
	$items = '';
	foreach ( $links as $tag => $url ) {
		$label = 'code' === $args['labels'] ? strtoupper( $tag ) : $langs[ $tag ][0];
		if ( $tag === $cur ) {
			if ( $args['current'] ) {
				$items .= sprintf( '<li class="just-lang-current"><span lang="%s" aria-current="true">%s</span></li>', esc_attr( $tag ), esc_html( $label ) );
			}
			continue;
		}
		$items .= sprintf( '<li><a href="%s" hreflang="%s" lang="%s">%s</a></li>', esc_url( $url ), esc_attr( $tag ), esc_attr( $tag ), esc_html( $label ) );
	}
	wp_enqueue_style( 'just-lang' );
	$class = 'just-lang-switcher just-lang-' . ( 'inline' === $args['style'] ? 'inline' : 'list' );
	$attrs = is_callable( $args['wrapper'] ) ? call_user_func( $args['wrapper'], $class ) : 'class="' . esc_attr( $class ) . '"';
	return sprintf(
		'<nav %s aria-label="%s"><ul>%s</ul></nav>',
		$attrs,
		esc_attr__( 'Language', 'just-lang' ),
		$items
	);
}

/** Template tag: echo the switcher for the post being viewed. */
function just_lang_switcher( $args = array() ) {
	echo just_lang_switcher_html( 0, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
}

add_shortcode( 'just_lang_switcher', 'just_lang_switcher_shortcode' );
function just_lang_switcher_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'style'   => 'list',
			'labels'  => 'name',
			'current' => '1',
		),
		$atts,
		'just_lang_switcher'
	);
	$atts['current'] = ! in_array( strtolower( (string) $atts['current'] ), array( '0', 'false', 'no' ), true );
	return just_lang_switcher_html( 0, $atts );
}

add_action( 'init', 'just_lang_register_block', 20 );
function just_lang_register_block() {
	$block = register_block_type(
		JUST_LANG_DIR . 'blocks/switcher',
		array( 'render_callback' => 'just_lang_render_block' )
	);
	// block.json only looks for editor translations in wp-content/languages; point it at the bundled ones.
	if ( $block && $block->editor_script_handles ) {
		wp_set_script_translations( $block->editor_script_handles[0], 'just-lang', JUST_LANG_DIR . 'languages' );
	}
}

function just_lang_render_block( $attributes, $content, $block ) {
	$post_id = just_lang_current_id();
	$editing = defined( 'REST_REQUEST' ) && REST_REQUEST;
	if ( ! $post_id && $editing ) {
		// The editor preview renders over REST, where nothing is being viewed.
		$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
	}
	$html = just_lang_switcher_html(
		$post_id,
		array(
			'style'   => isset( $attributes['style'] ) ? $attributes['style'] : 'list',
			'labels'  => isset( $attributes['labels'] ) ? $attributes['labels'] : 'name',
			'current' => ! empty( $attributes['showCurrent'] ),
			'wrapper' => function ( $class ) {
				return get_block_wrapper_attributes( array( 'class' => $class ) );
			},
		)
	);
	if ( '' === $html ) {
		return $editing ? '<p><em>' . esc_html__( 'Language switcher: shows here once this page has a published translation in the same group.', 'just-lang' ) . '</em></p>' : '';
	}
	return $html;
}

// Registered on every request, enqueued only where a switcher is rendered.
add_action( 'init', 'just_lang_switcher_style' );
function just_lang_switcher_style() {
	wp_register_style( 'just-lang', false, array(), JUST_LANG_VERSION );
	wp_add_inline_style(
		'just-lang',
		'.just-lang-switcher ul{list-style:none;margin:0;padding:0}'
		. '.just-lang-inline ul{display:flex;flex-wrap:wrap;gap:.25em .9em}'
		. '.just-lang-current span{font-weight:600}'
	);
}
