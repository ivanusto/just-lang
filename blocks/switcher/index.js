( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType( 'just-lang/switcher', {
		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;
			// The preview renders for the post being edited, so links match what visitors see.
			var editor = wp.data.select( 'core/editor' );
			var postId = props.context.postId || ( editor && editor.getCurrentPostId() );
			return el(
				'div',
				useBlockProps(),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Settings', 'just-lang' ) },
						el( SelectControl, {
							label: __( 'Layout', 'just-lang' ),
							value: a.style,
							options: [
								{ label: __( 'Inline', 'just-lang' ), value: 'inline' },
								{ label: __( 'List', 'just-lang' ), value: 'list' }
							],
							onChange: function ( v ) { set( { style: v } ); }
						} ),
						el( SelectControl, {
							label: __( 'Labels', 'just-lang' ),
							value: a.labels,
							options: [
								{ label: __( 'Language name', 'just-lang' ), value: 'name' },
								{ label: __( 'Language code', 'just-lang' ), value: 'code' }
							],
							onChange: function ( v ) { set( { labels: v } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Show the current language', 'just-lang' ),
							checked: a.showCurrent,
							onChange: function ( v ) { set( { showCurrent: v } ); }
						} )
					)
				),
				el( ServerSideRender, {
					block: 'just-lang/switcher',
					attributes: a,
					urlQueryArgs: postId ? { post_id: postId } : {}
				} )
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp );
