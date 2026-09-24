<?php
/**
 * Admin: settings screen, the Language box on the edit screen and the list column.
 *
 * @package Just_Lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------- settings */

add_action( 'admin_menu', 'just_lang_menu' );
function just_lang_menu() {
	add_options_page( __( 'Just Lang', 'just-lang' ), __( 'Just Lang', 'just-lang' ), 'manage_options', 'just-lang', 'just_lang_settings_page' );
}

add_filter( 'plugin_action_links_' . plugin_basename( JUST_LANG_FILE ), 'just_lang_action_links' );
function just_lang_action_links( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=just-lang' ) ) . '">' . esc_html__( 'Settings', 'just-lang' ) . '</a>' );
	return $links;
}

function just_lang_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s       = just_lang_settings();
	$catalog = just_lang_catalog();
	$on      = array_flip( array_keys( just_lang_languages() ) );
	// Enabled languages first, in their configured order, then the rest of the catalog.
	$rows = array_merge( array_intersect_key( $on, $catalog ), array_diff_key( $catalog, $on ) );
	$opt  = JUST_LANG_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Just Lang', 'just-lang' ); ?></h1>
		<p><?php esc_html_e( 'Give each page a language and put its translations in the same group. Just Lang then prints html lang, hreflang and og:locale, and can send first-time visitors to the version in their language.', 'just-lang' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'just_lang' ); ?>
			<h2><?php esc_html_e( 'Languages', 'just-lang' ); ?></h2>
			<table class="widefat striped" style="max-width:52rem">
				<thead><tr>
					<th scope="col"><?php esc_html_e( 'Enabled', 'just-lang' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Default', 'just-lang' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Language', 'just-lang' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Site name in titles', 'just-lang' ); ?></th>
				</tr></thead>
				<tbody>
				<?php foreach ( array_keys( $rows ) as $tag ) : ?>
					<tr>
						<td><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[languages][]" value="<?php echo esc_attr( $tag ); ?>" <?php checked( isset( $on[ $tag ] ) ); ?> aria-label="<?php echo esc_attr( $tag ); ?>"></td>
						<td><input type="radio" name="<?php echo esc_attr( $opt ); ?>[default]" value="<?php echo esc_attr( $tag ); ?>" <?php checked( $s['default'], $tag ); ?> aria-label="<?php echo esc_attr( $tag ); ?>"></td>
						<td><?php echo esc_html( $catalog[ $tag ][0] ); ?> <code><?php echo esc_html( $tag ); ?></code></td>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt ); ?>[site_names][<?php echo esc_attr( $tag ); ?>]" value="<?php echo esc_attr( $s['site_names'][ $tag ] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Unchanged', 'just-lang' ); ?>"></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description"><?php esc_html_e( 'Pages without a language of their own count as the default language. The site name, when filled in, replaces the one WordPress puts in the title of pages in that language.', 'just-lang' ); ?></p>

			<h2><?php esc_html_e( 'Behaviour', 'just-lang' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="just-lang-xdef"><?php esc_html_e( 'x-default', 'just-lang' ); ?></label></th>
					<td>
						<select id="just-lang-xdef" name="<?php echo esc_attr( $opt ); ?>[x_default]">
							<option value="" <?php selected( $s['x_default'], '' ); ?>><?php esc_html_e( 'Default language', 'just-lang' ); ?></option>
							<?php foreach ( just_lang_languages() as $tag => $info ) : ?>
								<option value="<?php echo esc_attr( $tag ); ?>" <?php selected( $s['x_default'], $tag ); ?>><?php echo esc_html( $info[0] . ' (' . $tag . ')' ); ?></option>
							<?php endforeach; ?>
							<option value="none" <?php selected( $s['x_default'], 'none' ); ?>><?php esc_html_e( 'Do not print x-default', 'just-lang' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'The version for visitors whose language is not offered. Auto-detection sends them there too. When a group has no page in this language, the default language answers.', 'just-lang' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto-detection', 'just-lang' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[autodetect]" value="1" <?php checked( $s['autodetect'] ); ?>> <?php esc_html_e( 'Send visitors arriving from other sites to the version in their browser language', 'just-lang' ); ?></label>
						<p class="description"><?php esc_html_e( 'Runs in the browser, so full-page caching and CDNs keep working. Visitors moving within the site are never redirected, and the language they pick is remembered. Crawlers are never redirected. Add ?lang=keep to a link to pin its language.', 'just-lang' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Page excerpts', 'just-lang' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[page_excerpt]" value="1" <?php checked( $s['page_excerpt'] ); ?>> <?php esc_html_e( 'Add an excerpt box to pages', 'just-lang' ); ?></label>
						<p class="description"><?php esc_html_e( 'Many SEO plugins use the excerpt as the meta description, which gives each language version a description in its own language.', 'just-lang' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<h2><?php esc_html_e( 'Language switcher', 'just-lang' ); ?></h2>
		<p><?php esc_html_e( 'Add the Language Switcher block, or use the shortcode below. Both list the other published pages in the current page\'s group.', 'just-lang' ); ?></p>
		<p><code>[just_lang_switcher style="inline" labels="name" current="1"]</code></p>
	</div>
	<?php
}

/* ------------------------------------------------------------- conflicts */

/** Names of active plugins that manage languages themselves. */
function just_lang_conflicts() {
	$found = array();
	if ( defined( 'POLYLANG_VERSION' ) ) {
		$found[] = 'Polylang';
	}
	if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
		$found[] = 'WPML';
	}
	if ( defined( 'TRP_PLUGIN_VERSION' ) ) {
		$found[] = 'TranslatePress';
	}
	if ( defined( 'WEGLOT_VERSION' ) ) {
		$found[] = 'Weglot';
	}
	return $found;
}

add_action( 'admin_notices', 'just_lang_conflict_notice' );
function just_lang_conflict_notice() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'plugins', 'settings_page_just-lang' ), true ) ) {
		return;
	}
	$found = just_lang_conflicts();
	if ( ! $found ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: %s: plugin names, such as Polylang */
				__( 'Just Lang is active alongside %s. Both print hreflang and html lang, so search engines will see conflicting signals. Keep only one of them.', 'just-lang' ),
				implode( ', ', $found )
			)
		)
	);
}

/* --------------------------------------------------------- Language box */

add_action( 'add_meta_boxes', 'just_lang_meta_box' );
function just_lang_meta_box() {
	add_meta_box( 'just-lang', __( 'Language', 'just-lang' ), 'just_lang_meta_box_html', just_lang_post_types(), 'side', 'high' );
}

function just_lang_meta_box_html( $post ) {
	wp_nonce_field( 'just_lang_save', 'just_lang_nonce' );
	$lang  = get_post_meta( $post->ID, JUST_LANG_META, true );
	$group = get_post_meta( $post->ID, JUST_LANG_GROUP_META, true );
	echo '<p><label for="just-lang-lang"><strong>' . esc_html__( 'Language', 'just-lang' ) . '</strong></label><br><select id="just-lang-lang" name="just_lang_lang" style="width:100%">';
	/* translators: %s: language tag of the site default, such as en */
	echo '<option value="">' . esc_html( sprintf( __( 'Default (%s)', 'just-lang' ), just_lang_default() ) ) . '</option>';
	foreach ( just_lang_languages() as $tag => $info ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $tag ), selected( $lang, $tag, false ), esc_html( $info[0] . ' (' . $tag . ')' ) );
	}
	echo '</select></p>';
	printf(
		'<p><label for="just-lang-group"><strong>%s</strong></label><br><input id="just-lang-group" name="just_lang_group" type="text" value="%s" style="width:100%%" placeholder="%s"></p>',
		esc_html__( 'Translation group', 'just-lang' ),
		esc_attr( $group ),
		esc_attr__( 'e.g. about-us', 'just-lang' )
	);
	echo '<p class="description">' . esc_html__( 'Pages sharing the same group are translations of each other. Use lowercase letters, digits, dashes and underscores.', 'just-lang' ) . '</p>';
	$links = just_lang_group_links( $post->ID );
	if ( $links ) {
		echo '<ul style="margin:0">';
		foreach ( $links as $tag => $url ) {
			printf( '<li>%s: <a href="%s" target="_blank" rel="noopener">%s</a></li>', esc_html( $tag ), esc_url( $url ), esc_html( wp_make_link_relative( $url ) ) );
		}
		echo '</ul>';
	}
}

add_action( 'save_post', 'just_lang_save_meta_box', 10, 2 );
function just_lang_save_meta_box( $post_id, $post ) {
	if ( ! isset( $_POST['just_lang_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['just_lang_nonce'] ) ), 'just_lang_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$lang  = isset( $_POST['just_lang_lang'] ) ? sanitize_text_field( wp_unslash( $_POST['just_lang_lang'] ) ) : '';
	$group = isset( $_POST['just_lang_group'] ) ? sanitize_key( wp_unslash( $_POST['just_lang_group'] ) ) : '';
	update_post_meta( $post_id, JUST_LANG_META, isset( just_lang_languages()[ $lang ] ) ? $lang : '' );
	update_post_meta( $post_id, JUST_LANG_GROUP_META, $group );
}

/* ---------------------------------------------------------- list column */

add_action( 'admin_init', 'just_lang_list_columns' );
function just_lang_list_columns() {
	foreach ( just_lang_post_types() as $type ) {
		add_filter( "manage_{$type}_posts_columns", 'just_lang_add_column' );
		add_action( "manage_{$type}_posts_custom_column", 'just_lang_column_html', 10, 2 );
	}
}

function just_lang_add_column( $columns ) {
	$columns['just_lang'] = __( 'Language', 'just-lang' );
	return $columns;
}

function just_lang_column_html( $column, $post_id ) {
	if ( 'just_lang' !== $column ) {
		return;
	}
	$group = (string) get_post_meta( $post_id, JUST_LANG_GROUP_META, true );
	echo '<code>' . esc_html( just_lang_of( $post_id ) ) . '</code>';
	if ( '' !== $group ) {
		echo '<br><small>' . esc_html( $group ) . '</small>';
	}
}
