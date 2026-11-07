/**
 * Megacenter Core – editor-scripts zonder build-stap.
 * - Registreert de dynamische blokken (weergave via ServerSideRender, instellingen in de zijbalk).
 * - Laat Block Bindings (megacenter/site en megacenter/activity) echte waarden tonen in de editor.
 */
( function ( wp ) {
	'use strict';

	const { registerBlockType, registerBlockBindingsSource } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, SelectControl, ToggleControl, Disabled, Placeholder } = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const data = window.megacenterEditor || { site: {}, units: {} };

	const ACTIVITY_BLOCKS = [ 'megacenter/activity-facts', 'megacenter/activity-highlights', 'megacenter/activity-faq' ];

	function preview( name, props ) {
		const ctx = props.context || {};
		const needsActivity = ACTIVITY_BLOCKS.includes( name );
		if ( needsActivity && ( ! ctx.postId || ctx.postType !== 'activiteit' ) ) {
			return el( Placeholder, {
				className: 'mc-editor-placeholder',
				label: wp.blocks.getBlockType( name ).title,
				instructions: 'Toont automatisch de gegevens van de activiteit (vul ze in onder "Activiteit-gegevens").',
			} );
		}
		return el(
			Disabled,
			null,
			el( ServerSideRender, {
				block: name,
				attributes: props.attributes,
				urlQueryArgs: ctx.postId ? { post_id: ctx.postId } : {},
			} )
		);
	}

	function controls( name, props ) {
		const a = props.attributes;
		const set = props.setAttributes;
		switch ( name ) {
			case 'megacenter/fun-rotator':
				return [
					el( TextControl, { key: 'p', label: 'Begin van de kop', value: a.prefix, onChange: ( v ) => set( { prefix: v } ), __nextHasNoMarginBottom: true } ),
					el( TextControl, { key: 'w', label: 'Eigen woorden (komma-gescheiden)', help: 'Leeg = automatisch alle activiteiten.', value: a.words, onChange: ( v ) => set( { words: v } ), __nextHasNoMarginBottom: true } ),
					el( SelectControl, { key: 'l', label: 'Kopniveau', value: String( a.level ), options: [ { label: 'H1 (paginatitel)', value: '1' }, { label: 'H2', value: '2' }, { label: 'H3', value: '3' } ], onChange: ( v ) => set( { level: parseInt( v, 10 ) } ), __nextHasNoMarginBottom: true } ),
				];
			case 'megacenter/marquee':
				return [ el( TextControl, { key: 'i', label: 'Eigen woorden (komma-gescheiden)', help: 'Leeg = automatisch alle activiteiten.', value: a.items, onChange: ( v ) => set( { items: v } ), __nextHasNoMarginBottom: true } ) ];
			case 'megacenter/activity-facts':
				return [ el( SelectControl, { key: 'v', label: 'Weergave', value: a.variant, options: [ { label: 'Compact (kaart)', value: 'card' }, { label: 'Chips (detailpagina)', value: 'chips' } ], onChange: ( v ) => set( { variant: v } ), __nextHasNoMarginBottom: true } ) ];
			case 'megacenter/opening-hours':
				return [
					el( SelectControl, { key: 'v', label: 'Weergave', value: a.variant, options: [ { label: 'Tabel (vandaag uitgelicht)', value: 'table' }, { label: 'Compact (footer)', value: 'compact' }, { label: 'Status: nu open?', value: 'today' } ], onChange: ( v ) => set( { variant: v } ), __nextHasNoMarginBottom: true } ),
					el( ToggleControl, { key: 'n', label: 'Toelichting tonen', checked: a.showNote, onChange: ( v ) => set( { showNote: v } ), __nextHasNoMarginBottom: true } ),
				];
			case 'megacenter/request-form':
				return [
					el( SelectControl, { key: 't', label: 'Soort aanvraag', value: a.type, options: [ { label: 'Kinderfeestje', value: 'kinderfeest' }, { label: 'Groepsuitje', value: 'groep' }, { label: 'Zakelijk', value: 'zakelijk' }, { label: 'Contact', value: 'contact' } ], onChange: ( v ) => set( { type: v } ), __nextHasNoMarginBottom: true } ),
					el( TextControl, { key: 's', label: 'Tekst op de knop', help: 'Leeg = standaardtekst.', value: a.submitLabel, onChange: ( v ) => set( { submitLabel: v } ), __nextHasNoMarginBottom: true } ),
				];
		}
		return [];
	}

	const names = [
		'megacenter/fun-rotator', 'megacenter/marquee', 'megacenter/activity-facts', 'megacenter/activity-highlights',
		'megacenter/activity-faq', 'megacenter/activity-filter', 'megacenter/opening-hours', 'megacenter/reservation',
		'megacenter/request-form', 'megacenter/socials',
	];

	names.forEach( function ( name ) {
		// Metadata (titel, attributen) komt van de server via block.json; hier alleen edit/save.
		registerBlockType( name, {
			edit: function Edit( props ) {
				const blockProps = useBlockProps();
				const ctrl = controls( name, props );
				return el(
					Fragment,
					null,
					ctrl.length ? el( InspectorControls, null, el( PanelBody, { title: 'Instellingen' }, ctrl ) ) : null,
					el( 'div', blockProps, preview( name, props ) )
				);
			},
			save: function () {
				return null;
			},
		} );
	} );

	// Block Bindings: echte waarden tonen in de editor (alleen lezen; bewerken via instellingen/meta box).
	if ( typeof registerBlockBindingsSource === 'function' ) {
		const fromBindings = ( bindings, getter ) => {
			const out = {};
			Object.keys( bindings ).forEach( ( attr ) => {
				out[ attr ] = getter( ( bindings[ attr ].args || {} ).key );
			} );
			return out;
		};

		registerBlockBindingsSource( {
			name: 'megacenter/site',
			getValues: ( { bindings } ) => fromBindings( bindings, ( key ) => ( data.site && data.site[ key ] ) || '' ),
			canUserEditValue: () => false,
		} );

		const price = ( n ) => {
			const v = Number( n );
			return '€ ' + ( Number.isInteger( v ) ? String( v ) : v.toFixed( 2 ).replace( '.', ',' ) );
		};

		registerBlockBindingsSource( {
			name: 'megacenter/activity',
			getValues: ( { select, context, bindings } ) => {
				const rec = context.postId ? select( 'core' ).getEntityRecord( 'postType', context.postType || 'activiteit', context.postId ) : null;
				const m = ( rec && rec.meta ) || {};
				const title = rec && rec.title ? rec.title.raw || rec.title.rendered || '' : '';
				return fromBindings( bindings, ( key ) => {
					switch ( key ) {
						case 'tagline': return m.megacenter_tagline || '';
						case 'badge': return m.megacenter_badge || '';
						case 'title': return title;
						case 'age':
							if ( m.megacenter_age_min && m.megacenter_age_max ) { return m.megacenter_age_min + '–' + m.megacenter_age_max + ' jaar'; }
							return m.megacenter_age_min ? 'Vanaf ' + m.megacenter_age_min + ' jaar' : 'Alle leeftijden';
						case 'duration': return m.megacenter_duration ? m.megacenter_duration + ' min' : 'Vrij spelen';
						case 'group':
							if ( m.megacenter_group_min && m.megacenter_group_max ) { return m.megacenter_group_min + '–' + m.megacenter_group_max + ' pers.'; }
							return 'Iedereen';
						case 'price':
						case 'price_short':
							return m.megacenter_price_from ? ( key === 'price_short' ? 'vanaf ' : '' ) + price( m.megacenter_price_from ) + ' ' + ( data.units[ m.megacenter_price_unit ] || 'p.p.' ) : '';
						case 'reserve_url': return m.megacenter_reserve_url || ( data.site.reserve_url || '' );
						case 'reserve_label': return 'Reserveer ' + title;
					}
					return '';
				} );
			},
			canUserEditValue: () => false,
		} );
	}
}( window.wp ) );
